 <!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="user-scalable=no" />
    <title>Caro Game Client</title>
	<style>
	#game-board {
		border-collapse: collapse;
		margin-left: 30px;
	}
	#player-role1{
		visibility: hidden;
	}
	#player-role2{
		visibility: hidden;
	}
	.caro-cell {
		width: 50px;
		height: 50px;
		border: 1px solid #000;
        font-size: 35px;
        color: black;
	}

	#game-board {
		text-align: center;
		margin-top: 20px;
	}
	.GamePlay {
		display: flex;
		width: auto;
		justify-content: space-between;
		visibility: hidden;
		font-size: 40px;
	}

	.GamePlay-Title {
		font-weight: bold;
		display: flex;
	}
	.GamePlay-Title2 {
		font-weight: bold;
		display: flex;
        justify-content: flex-end;
	}

	body {
		font-family: system-ui;
	}

	.GamePLay-isMyTurn {
		color: crimson;
		font-weight: bold;
		visibility: hidden;
		
	}
	.GamePLay-isMyTurn2 {
		color: crimson;
		font-weight: bold;
		visibility: hidden;
	}

	.GamePlay-Cooldown {
		font-weight: bold;
		color: darkgoldenrod;
		visibility: hidden;
	}
	.GamePlay-Cooldown2 {
		font-weight: bold;
		color: darkgoldenrod;
		visibility: hidden;
	}

	.GamePlay-Player2 {
		text-align: right;
	}
	.GameMenu {
		height: 60px;
	}
	.GameMenu-BtnFind{
		padding: 10px 20px 10px 20px;
		background-color: deepskyblue;
		border-radius: 10px;
		border: 0px;
		color: white;
		font-weight: bold;
		font-family: monospace;
		font-size: 20px;
	}
	.GameMenu-BreakFind{
		padding: 30px 60px 30px 60px;
		background-color: indianred;
		color: white;
		font-weight: bold;
		font-family: monospace;
		font-size: 40px;
		border: 0px;
		border-radius: 10px;
		margin-left: 40%;
	}
	.game-alert{
		color: red;
		font-weight: bold;
		font-family: monospace;
		font-size: 40px;
		position: absolute;
		top: 25%;
		left: 10%;
		background-color: #ffe036a6;
		padding: 40px 80px 40px 80px;
		border-radius: 10px;
		scale: 0;
		animation-duration: 1s;
		transition: 0.3s ease;
	}
	.TimTran {
		font-weight: bold;
		color: darkgoldenrod;
		text-align: center;
        font-size: 40px;
	}
	</style>
</head>
<body>
	<div class="GameMenu" id="MenuGame">
		<button class="GameMenu-BtnFind" id="find-match-btn" onclick="findMatch()">Find Match</button>
		<button class="GameMenu-BreakFind" id="cancel-match-btn" style="display: none;" onclick="cancelMatch()">Cancel</button>
		<div id="match-status"></div>
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
				<div id="player-role2">❌</div> Bot Play  
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
            currentPlayer = 'O'; 
            isMyTurn = true; 
            createCaroBoard(16);
            document.getElementById("GameStarted").style.visibility = "visible";
			document.getElementById("MenuGame").style.height = "0px";
        }

		function cancelMatch() {
			cancelRequested = true;
			document.getElementById("cancel-match-btn").style.display = "none";
			document.getElementById("find-match-btn").style.display = "inline";
			document.getElementById("match-status").innerHTML = "";
			if (socket && socket.readyState === WebSocket.OPEN) {
				socket.send("cancelfind");
			} else {
				console.log("Socket is not available or closed.");
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
            if (checkWin(board, currentPlayer)) {
                document.getElementById("Alert").innerHTML = `${currentPlayer} Win`;
				document.getElementById("Alert").style.scale = "1";
                gameOver = true;
            } else if (checkDraw(board)) {
                alert("The game is a draw.");
                gameOver = true;
            }
            isMyTurn = false;
            botMove(); // Let the bot make a move after the player
        }

		function botMove() {
			if (gameOver) {
				return;
			}
			var aiMove = getAIMove(board);
			console.log(aiMove);
			board[aiMove[0]][aiMove[1]] = 'X';
			var cell = document.getElementById('cell-' + aiMove[0] + '-' + aiMove[1]);
			cell.innerHTML = 'X';
			if (checkWin(board, 'X')) {
				console.log("X wins!");
				document.getElementById("Alert").innerHTML = "X Win";
				document.getElementById("Alert").style.scale = "1";
				gameOver = true;
			} else if (checkDraw(board)) {
				console.log("The game is a draw.");
				document.getElementById("Alert").innerHTML = "Game Draw";
				document.getElementById("Alert").style.scale = "1";
				gameOver = true;
			}
			isMyTurn = true;
		}

		function getAIMove(board) {
			var size = board.length;
			var player = 'O';
			var bot = 'X';
			var availableMoves = [];
			for (var i = 0; i < size; i++) {
				for (var j = 0; j < size; j++) {
					if (board[i][j] === '') {
						availableMoves.push([i, j]);
					}
				}
			}
			return availableMoves[Math.floor(Math.random() * availableMoves.length)];
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
