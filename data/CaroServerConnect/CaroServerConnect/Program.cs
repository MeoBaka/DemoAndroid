using System;
using System.Net;
using System.Net.WebSockets;
using System.Text;
using System.Threading;
using System.Threading.Tasks;

class SimpleServer
{
    public static async Task Main(string[] args)
    {
        Console.OutputEncoding = Encoding.Unicode;
        Console.OutputEncoding = Encoding.Unicode;
        IPAddress ipAddress = IPAddress.Any;
        int port = 8788;

        var listener = new HttpListener();
        listener.Prefixes.Add($"http://127.0.0.1:{port}/");
        listener.Start();
        Console.WriteLine($"Server is running and listening for connections on port {port}");

        while (true)
        {
            var context = await listener.GetContextAsync();
            if (context.Request.IsWebSocketRequest)
            {
                _ = HandleWebSocketAsync(context);
            }
            else
            {
                context.Response.StatusCode = 400;
                context.Response.Close();
            }
        }
    }
    private static Dictionary<string, List<WebSocket>> ongoingMatches = new Dictionary<string, List<WebSocket>>();
    private static readonly object lockObject = new object();

    private static async Task HandleWebSocketAsync(HttpListenerContext context)
    {
        var wsContext = await context.AcceptWebSocketAsync(null);
        var socket = wsContext.WebSocket;

        Console.WriteLine($"Connected: {context.Request.RemoteEndPoint}");

        while (true)
        {
            string dataReceived = await ReceiveDataFromWebSocketAsync(socket);
            Console.WriteLine($"{context.Request.RemoteEndPoint} Send: {dataReceived}");
            
            // Xử lý tìm trận
            if (dataReceived.Trim().ToLower() == "findmatch")
            {
                await SendToClient(socket, "finding");

                string waitingMatchId;
                lock (lockObject)
                {
                    waitingMatchId = ongoingMatches.FirstOrDefault(x => x.Value.Count == 1).Key;
                }

                if (!string.IsNullOrEmpty(waitingMatchId))
                {
                    lock (lockObject)
                    {
                        ongoingMatches[waitingMatchId].Add(socket);
                    }

                    foreach (var client in ongoingMatches[waitingMatchId])
                    {
                        await SendToClient(client, $"MatchFound,{waitingMatchId}");
                    }

                    Console.WriteLine($"Player joined match {waitingMatchId}");
                }
                else
                {
                    string matchId = Guid.NewGuid().ToString();
                    lock (lockObject)
                    {
                        ongoingMatches.Add(matchId, new List<WebSocket> { socket });
                    }

                    Console.WriteLine($"Created new match {matchId}");

                    var result = await socket.ReceiveAsync(new ArraySegment<byte>(new byte[1024]), CancellationToken.None);
                    if (result.MessageType == WebSocketMessageType.Close)
                    {
                        lock (lockObject)
                        {
                            ongoingMatches.Remove(matchId);
                        }

                        Console.WriteLine($"Match {matchId} cancelled due to player disconnection");
                        break;
                    }
                }
            //xử lý huỷ trận
            }else if (dataReceived.Trim().ToLower() == "cancelfind")
            {
                var matchToRemove = ongoingMatches.FirstOrDefault(pair => pair.Value.Contains(socket));
                await SendToClient(socket, "huytimtran");
                if (!string.IsNullOrEmpty(matchToRemove.Key))
                {
                    lock (lockObject)
                    {
                        ongoingMatches[matchToRemove.Key].Remove(socket);
                        if (ongoingMatches[matchToRemove.Key].Count == 0)
                        {
                            ongoingMatches.Remove(matchToRemove.Key);
                            Console.WriteLine($"Match {matchToRemove.Key} cancelled due to player cancellation");
                        }
                    }
                }
            }
            // Xử start game
            else if (dataReceived.StartsWith("start-game"))
            {
                var roomId = dataReceived.Split(',')[1];
                List<WebSocket> match;
                lock (lockObject)
                {
                    ongoingMatches.TryGetValue(roomId, out match);
                }
                if (match != null)
                {
                    string player1 = new Random().Next(2) == 0 ? "O" : "X";
                    string player2 = player1 == "O" ? "X" : "O";
                    await SendToClient(match[0], $"GameStarted,{player1}");
                    await SendToClient(match[1], $"GameStarted,{player2}");

                    Console.WriteLine($"Game started in match {roomId} with {player1} going first");
                }
                else
                {
                    Console.WriteLine($"No match found with id {roomId}");
                }
            }
            // xử lý nước cờ
            else if (dataReceived.StartsWith("check"))
            {
                var parts = dataReceived.Split(',');
                var player = parts[1];
                var x = parts[2];
                var y = parts[3];

                var match = ongoingMatches.FirstOrDefault(m => m.Value.Contains(socket));

                if (match.Value != null)
                {
                    foreach (var client in match.Value)
                    {
                        if (client != socket)
                        {
                            await SendToClient(client, $"check,{player},{x},{y}");
                        }
                    }
                }
            }
            // xử lý win game kết thúc game
            else if (dataReceived.StartsWith("endgame"))
            {
                var data = dataReceived.Split(',');
                var status = data[2];
                var playerWin = data[1];
                var match = ongoingMatches.FirstOrDefault(m => m.Value.Contains(socket));
                Console.WriteLine(status + " " + playerWin);
                if (match.Value != null)
                {
                    if (status == "win")
                    {
                        string message = $"gamewin,{playerWin}";
                        foreach (var client in match.Value)
                        {
                            if (client.State == WebSocketState.Open)
                            {
                                await SendToClient(client, message);
                                Console.WriteLine(client+ " " +message); 
                            }
                            else
                            {
                                Console.WriteLine("Cannot send message, WebSocket is not open.");
                            }
                        }
                    }
                    else if (status == "draw")
                    {
                        string message = "gamedraw";
                        foreach (var client in match.Value)
                        {
                            if (client.State == WebSocketState.Open)
                            {
                                await SendToClient(client, message);
                            }
                            else
                            {
                                Console.WriteLine("Cannot send message, WebSocket is not open.");
                            }
                        }
                    }
                }
                else
                {
                    Console.WriteLine("Match not found.");
                }
            }


        }
        var matchToRemove2 = ongoingMatches.FirstOrDefault(pair => pair.Value.Contains(socket));
        if (!string.IsNullOrEmpty(matchToRemove2.Key))
        {
            lock (lockObject)
            {
                ongoingMatches.Remove(matchToRemove2.Key);
            }
        }
        await socket.CloseAsync(WebSocketCloseStatus.NormalClosure, "", CancellationToken.None);
        Console.WriteLine($"Disconnected: {context.Request.RemoteEndPoint}");
    }




    private static async Task<string> ReceiveDataFromWebSocketAsync(WebSocket socket)
    {
        var buffer = new byte[1024];
        var result = await socket.ReceiveAsync(new ArraySegment<byte>(buffer), CancellationToken.None);
        string dataReceived = Encoding.UTF8.GetString(buffer, 0, result.Count);
        return dataReceived;
    }


    private static async Task SendToClient(WebSocket socket, string data)
    {
        var buffer = Encoding.UTF8.GetBytes(data);
        await socket.SendAsync(new ArraySegment<byte>(buffer), WebSocketMessageType.Text, true, CancellationToken.None);
    }
}
