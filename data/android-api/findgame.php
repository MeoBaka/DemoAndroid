<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="user-scalable=no" />
    <title>Caro Game Client</title>
    <?php
      $randomNumber = mt_rand(100000, 999999);
      echo "<link rel='stylesheet' href='css/online.css?v=".$randomNumber."'>"
    ?>
</head>
<body>
	<div class="Android">
		<div class="GameMenu" id="MenuGame">
			<button class="GameMenu-BtnFind" id="find-match-btn" onclick="findMatch()">Find Match</button>
			<button class="GameMenu-BreakFind" id="cancel-match-btn" style="display: none;" onclick="cancelMatch()">Cancel</button>
			<div id="match-status" class="TimTran"></div>
		</div>
		<div class="GamePlay" id="GameStarted">
			<div class="GamePlay-Player1" id="isUTurn">
				<div class="GamePlay-Title">
					Player 1 <div id="player-role1">⭕</div>
				</div>
				<div class="GamePLay-isMyTurn" id="GamePlay-Select1">
					Lượt Đi Của Player 1
				</div>
				<div class="GamePlay-Cooldown" id="Player1-Cooldown">
					60s
				</div>
			</div>
			<div class="GamePlay-Player2" id="isUTurn">
				<div class="GamePlay-Title2">
					<div id="player-role2">❌</div> Player 2  
				</div>
				<div class="GamePLay-isMyTurn2" id="GamePlay-Select2">
					Lượt Đi Của Player 2
				</div>
				<div class="GamePlay-Cooldown2" id="Player2-Cooldown">
					60s
				</div>
			</div>
		</div>
		<div>
			<div id="Alert" class="game-alert"></div>
			<div id="game-board"></div>
		</div>
      <div class="Caro-Rules">
            Luật chơi cờ caro khá đơn giản, nhiệm vụ của mỗi người chơi cờ caro đó là đạt được một đường thẳng, đường chéo, đường ngang với 5 ô nhanh nhất. Tuy nhiên, chỉ cần người chơi nào có thể đạt 4 nước mà bị chặn 1 đầu hoặc không bị chặn hai đầu là đã có thể chiến thắng.
      </div>
	</div>
    <script>
		var socket;
		var countdownInterval;
		var countdown = 5;
		var cancelRequested = false;
		var currentPlayer;
		var isMyTurn = false;
		var gameOver = false;
        var board = [];
		
		window.onload = function() {
		  findMatch();
		};

		
		function findMatch() {
			cancelRequested = false;
			var countdown = 0;
			var matchFound = false;
			socket = new WebSocket("ws://147.185.221.19:36360/");
			//socket = new WebSocket("ws://127.0.0.1:8788/");
			document.getElementById("find-match-btn").style.display = "none";
			document.getElementById("cancel-match-btn").style.display = "inline";
			socket.onopen = function (event) {
				console.log("Connected to Caro Server.");
				document.getElementById("match-status").innerHTML = "Đang Tìm Trận... (0 seconds)";
				socket.send("FindMatch");
				countdownInterval = setInterval(function () {
					if (cancelRequested) {
						clearInterval(countdownInterval);
						socket.send("cancelfind");
						return;
					}
					countdown++;
					document.getElementById("match-status").innerHTML = "Đang Tìm Trận... (" + countdown + " seconds)";
				}, 1000);
			};

			socket.onmessage = function (event) {
				console.log("Received data from Caro Server:", event.data);
				var data = event.data.split(',');
				if (data[0] === 'GameStarted') {
					currentPlayer = data[1];
					startCountdown(currentPlayer);
					//document.getElementById('current-player').innerHTML = `${currentPlayer}`;
					if (currentPlayer == 'O'){
						isMyTurn = true;
						document.getElementById('player-role1').style.visibility = 'visible';
						document.getElementById('player-role2').style.visibility = 'visible';
						document.getElementById('GamePlay-Select1').innerHTML = 'Lượt Đi Của Bạn';
						document.getElementById('GamePlay-Select1').style.visibility = 'visible';
						document.getElementById('GamePlay-Select2').innerHTML = 'Lượt Đi Của Đối Thủ';
					} else {
						document.getElementById('player-role1').style.visibility = 'visible';
						document.getElementById('player-role2').style.visibility = 'visible';
						document.getElementById('GamePlay-Select1').innerHTML = 'Lượt Đi Của Đối Thủ';
						document.getElementById('GamePlay-Select1').style.visibility = 'visible';
						document.getElementById('GamePlay-Select2').innerHTML = 'Lượt Đi Của Bạn';
					}
				} else if (data[0] === 'check' && data[1] !== currentPlayer) {
					var x = data[2];
					var y = data[3];
					var cell = document.getElementById(`cell-${x}-${y}`);
					document.getElementById("Alert").style.scale = "0";
					cell.innerHTML = data[1];
					stopCountdown(currentPlayer);
					startCountdown(currentPlayer);
					isMyTurn = true; 
					if (currentPlayer == 'O'){
						document.getElementById('GamePlay-Select1').style.visibility = 'visible';
						document.getElementById('GamePlay-Select2').style.visibility = 'hidden';
					} else {
						document.getElementById('GamePlay-Select1').style.visibility = 'hidden';
						document.getElementById('GamePlay-Select2').style.visibility = 'visible';
					}
					if (checkWin(board, currentPlayer)) {
						console.log(currentPlayer + " wins!");
						socket.send(`endgame,${currentPlayer},win`);
						gameOver = true;
					} else if (checkDraw(board)) {
						console.log("The game is a draw.");
						socket.send(`endgame,${currentPlayer},draw`);
						gameOver = true;
					}
				} else if (data[0] === "MatchFound") {
					var roomId = data[1];
					matchFound = true;
					clearInterval(countdownInterval);
					document.getElementById("match-status").innerHTML = "Match found. Starting game in 5 seconds...";
					var countdown = 5;
					countdownInterval = setInterval(function () {
						countdown--;
						document.getElementById("match-status").innerHTML = `Match found. Starting game in ${countdown} seconds...`;
						if (countdown <= 0) {
							clearInterval(countdownInterval);
							document.getElementById("GameStarted").style.visibility = "visible";
							document.getElementById("MenuGame").style.height = "0px";
							createCaroBoard(16);
							socket.send(`start-game,${roomId}`);
						}
					}, 1000);
				} else if (data[0] === "gamewin") {
					var playerWin = data[1];
					gameOver = true;
					isMyTurn = false;
					document.getElementById("Alert").innerHTML = `Player ${playerWin} wins!`;
					document.getElementById("Alert").style.scale = "1";
					stopCountdown(currentPlayer);
				} else if (data[0] === "gamedraw") {
					gameOver = true;
					isMyTurn = false;
					document.getElementById("Alert").innerHTML = `Game Draw`;
					document.getElementById("Alert").style.scale = "1";
					stopCountdown(currentPlayer);
				} else if (data[0] === "huytimtran"){
                    window.location.href = "main.php";
                }

			};



			socket.onclose = function (event) {
				console.log("Connection to Caro Server closed.");
			};

			socket.onerror = function (error) {
				console.error("Error connecting to Caro Server:", error);
			};
		}

		function cancelMatch() {
			cancelRequested = true;
			if (socket && socket.readyState === WebSocket.OPEN) {
				socket.send("cancelfind");
                //window.location.href = "main.php";
			} else {
				console.log("Socket is not available or closed.");
                window.location.href = "main.php";
			}
		}


		function handleMove(event) {
			if (gameOver){
				document.getElementById("Alert").innerHTML = "Trò Chơi Kết Thúc, Về Main Sau 5s";
				document.getElementById("Alert").style.scale = "1";
                setTimeout(function() {
                  window.location.href = "main.php";
                }, 5000);
				return;
			}
			if (!isMyTurn) {
				document.getElementById("Alert").innerHTML = "Đây Không Phải Lượt Đi Của Bạn";
				document.getElementById("Alert").style.scale = "1";
				return;
			}
			var cell = event.target;
			var id = cell.getAttribute('id');
			var coordinates = id.split('-').slice(1);
			var x = parseInt(coordinates[0]);
			var y = parseInt(coordinates[1]);
			if (board[x][y] !== '') {
				return;
			}
			board[x][y] = currentPlayer;
			cell.innerHTML = currentPlayer;
            cell.style.color = currentPlayer == 'X' ? 'red' : 'blue';
			
			var message = `check,${currentPlayer},${coordinates.join(',')}`;
			socket.send(message);
			cell.innerHTML = currentPlayer;
			if (currentPlayer == 'X'){
				document.getElementById('GamePlay-Select1').style.visibility = 'visible';
				document.getElementById('GamePlay-Select2').style.visibility = 'hidden';
			} else {
				document.getElementById('GamePlay-Select1').style.visibility = 'hidden';
				document.getElementById('GamePlay-Select2').style.visibility = 'visible';
			}
			if (checkWin(board, currentPlayer)) {
				console.log(currentPlayer + " wins!");
				socket.send(`endgame,${currentPlayer},win`);
				document.getElementById("Alert").innerHTML = `Player ${currentPlayer} wins!`;
				document.getElementById("Alert").style.scale = "1";
				gameOver = true;
			} else if (checkDraw(board)) {
				console.log("The game is a draw.");
				socket.send(`endgame,${currentPlayer},draw`);
				gameOver = true;
			}
            stopCountdown(currentPlayer);
	        startCountdown(currentPlayer);
			isMyTurn = false;
		}


		function createCaroBoard(size) {
			document.getElementById("cancel-match-btn").style.display = "none";
			document.getElementById("find-match-btn").style.display = "none";
			document.getElementById("match-status").style.display = "none";
			board = [];
			for (var i = 0; i < size; i++) {
				board[i] = [];
				for (var j = 0; j < size; j++) {
					board[i][j] = '';
				}
			}

			var caroBoard = document.createElement('table');
			caroBoard.setAttribute('id', 'caro-board');
			for (var i = 0; i < size; i++) {
				var row = document.createElement('tr');
				for (var j = 0; j < size; j++) {
					var cell = document.createElement('td');
					cell.setAttribute('id', 'cell-' + i + '-' + j);
					cell.setAttribute('class', 'caro-cell'); 
					cell.addEventListener('click', handleMove);
					row.appendChild(cell); 
				}
				caroBoard.appendChild(row);
			}

			var gameBoard = document.getElementById('game-board');
			gameBoard.innerHTML = ''; 
			gameBoard.appendChild(caroBoard);
			console.log(`Created Caro board with size ${size}x${size}.`);
		}
		
		function checkWin(board, currentPlayer) {
			var size = board.length;
			var winCondition = 5;

			for (var i = 0; i < size; i++) {
				for (var j = 0; j < size - winCondition + 1; j++) {
					var count = 0;
					for (var k = 0; k < winCondition; k++) {
						if (board[i][j + k] === currentPlayer) {
							count++;
						}
					}
					if (count === winCondition) {
						return true;
					}
				}
			}

			for (var j = 0; j < size; j++) {
				for (var i = 0; i < size - winCondition + 1; i++) {
					var count = 0;
					for (var k = 0; k < winCondition; k++) {
						if (board[i + k][j] === currentPlayer) {
							count++;
						}
					}
					if (count === winCondition) {
						return true;
					}
				}
			}

			for (var i = 0; i < size - winCondition + 1; i++) {
				for (var j = 0; j < size - winCondition + 1; j++) {
					var count = 0;
					for (var k = 0; k < winCondition; k++) {
						if (board[i + k][j + k] === currentPlayer) {
							count++;
						}
					}
					if (count === winCondition) {
						return true;
					}
				}
			}

			for (var i = 0; i < size - winCondition + 1; i++) {
				for (var j = winCondition - 1; j < size; j++) {
					var count = 0;
					for (var k = 0; k < winCondition; k++) {
						if (board[i + k][j - k] === currentPlayer) {
							count++;
						}
					}
					if (count === winCondition) {
						return true;
					}
				}
			}

			return false;
		}

		function checkDraw(board) {
			var size = board.length;

			for (var i = 0; i < size; i++) {
				for (var j = 0; j < size; j++) {
					if (board[i][j] === '') {
						return false;
					}
				}
			}

			return true;
		}
		
		
		function startCountdown(player) {
			var countdown = 60;
			var countdownElement = document.getElementById(player == 'O' ? 'Player1-Cooldown' : 'Player2-Cooldown');
			countdownElement.innerHTML = countdown + 's';
			countdownElement.style.visibility = 'visible';
			countdownInterval = setInterval(function () {
				countdown--;
				countdownElement.innerHTML = countdown + 's';
				if (countdown <= 0) {
					clearInterval(countdownInterval);
					countdownElement.style.visibility = 'hidden';
					gameOver = true;
					var winningPlayer = player == 'X' ? 'X' : 'O';
					socket.send(`endgame,${winningPlayer},win`);
				}
			}, 1000);
		}


		function stopCountdown(player) {
			var countdownElement = document.getElementById(player == 'O' ? 'Player1-Cooldown' : 'Player2-Cooldown');
			countdownElement.style.visibility = 'hidden';
			clearInterval(countdownInterval);
		}
		
    </script>
</body>
</html>
