using System;
using System.Net;
using System.Net.WebSockets;
using System.Text;
using System.Threading;
using System.Threading.Tasks;

class Save
{
    public static async Task FFFFF()
    {
        Console.OutputEncoding = Encoding.Unicode;
        Console.InputEncoding = Encoding.Unicode;
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

    private static async Task HandleWebSocketAsync(HttpListenerContext context)
    {
        var wsContext = await context.AcceptWebSocketAsync(null);
        var socket = wsContext.WebSocket;

        Console.WriteLine($"Connected: {context.Request.RemoteEndPoint}");

        string dataReceived = await ReceiveDataFromWebSocketAsync(socket);

        if (dataReceived.Trim().ToLower() == "hi")
        {
            Console.WriteLine("Client đang chào");
            await SendToClient(socket, "Server đã nhận lệnh");
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
