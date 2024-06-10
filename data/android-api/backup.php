<?php
    $conn = new mysqli("localhost", "Lap", "Lap@22002878", "db_lap");
	if ($conn->connect_error) {
	  die("Kết nối thất bại: " . $conn->connect_error);
	}
    $conn->close();
?>
<?php
    // Bắt đầu phiên
    session_start();

    // Kiểm tra xem người dùng đã đăng nhập chưa
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        // Nếu chưa, chuyển hướng họ đến trang đăng nhập
        header("location: login.php");
        exit;
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <?php
      $randomNumber = mt_rand(100000, 999999);
      echo "<link rel='stylesheet' href='css/game.css?v=".$randomNumber."'>"
    ?>
</head>
<body>
    <div id="result"></div>
    <div id="board"></div>
    <button onclick="resetBoard()" class="buttonReset">Reset</button>

    <form action="main.php" method="post">
        <input type="submit" value="Trở Về Main" class="back">
    </form>

    <script>
        var board = [];
        var currentPlayer = '⭕';
        var winningCells = [];
        var gameOver = false;

        function createBoard() {
            var boardElement = document.getElementById('board');
            boardElement.innerHTML = '';
            for (var i = 0; i < 10; i++) {
                board[i] = [];
                for (var j = 0; j < 10; j++) {
                    board[i][j] = '';
                    var cell = document.createElement('div');
                    cell.innerHTML = board[i][j];
                    cell.className = 'cell';
                    cell.addEventListener('click', (function(i, j) {
                        return function() {
                            if (!gameOver) {
                                makeMove(i, j);
                            }
                        }
                    })(i, j));
                    boardElement.appendChild(cell);
                }
            }
        }

        function makeMove(i, j) {
            if (board[i][j] === '') {
                board[i][j] = currentPlayer;
                var cell = document.getElementById('board').children[i * 10 + j];
                cell.innerHTML = currentPlayer;
                cell.className += ' player' + (currentPlayer === '⭕' ? '1' : '2');
                if (checkWin(i, j)) {
                    for (var k = 0; k < winningCells.length; k++) {
                        var cell = document.getElementById('board').children[winningCells[k][0] * 10 + winningCells[k][1]];
                        cell.className += ' win';
                    }
                    document.getElementById('result').innerHTML = '          Player ' + currentPlayer + ' wins! Please reset the game to play again.';
                    gameOver = true;
                } else if (isDraw()) {
                    document.getElementById('result').innerHTML = '          The game is a draw! Please reset the game to play again.';
                    gameOver = true;
                }
                currentPlayer = currentPlayer === '⭕' ? '❌' : '⭕';
            }
        }

        function checkWin(i, j) {
            return checkLine(i, j, -1, 0) || checkLine(i, j, 1, 0) || checkLine(i, j, 0, -1) || checkLine(i, j, 0, 1) || checkLine(i, j, -1, -1) || checkLine(i, j, 1, 1) || checkLine(i, j, -1, 1) || checkLine(i, j, 1, -1);
        }

        function checkLine(i, j, di, dj) {
            var count = 0;
            winningCells = [];
            for (var k = -4; k <= 4; k++) {
                var ni = i + k * di, nj = j + k * dj;
                if (ni >= 0 && ni < 10 && nj >= 0 && nj < 10 && board[ni][nj] === currentPlayer) {
                    count++;
                    winningCells.push([ni, nj]);
                    if (count === 5) return true;
                } else {
                    count = 0;
                    winningCells = [];
                }
            }
            return false;
        }

        function isDraw() {
            for (var i = 0; i < 10; i++) {
                for (var j = 0; j < 10; j++) {
                    if (board[i][j] === '') {
                        return false;
                    }
                }
            }
            return true;
        }

        function resetBoard() {
            createBoard();
            currentPlayer = '⭕';
            winningCells = [];
            gameOver = false;
            document.getElementById('result').innerHTML = '';
        }

        createBoard();
    </script>
</body>
</html>
