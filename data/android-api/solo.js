var board = [];
var currentPlayer = '⭕';
var selectedCell = null;
var selectedCellIndex = null;
var winningCells = [];
var gameOver = false;
var timer;

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
    updateCurrentPlayer();
    startTimer();
}

function makeMove(i, j) {
    if (board[i][j] === '') {
        var currentCell = document.getElementById('board').children[i * 10 + j];
        if (selectedCell === currentCell) {
            board[i][j] = currentPlayer;
            var cell = currentCell;
            cell.innerHTML = currentPlayer;
            cell.className = 'cell player' + (currentPlayer === '⭕' ? '1' : '2');
            selectedCell = null;
            selectedCellIndex = null;
            if (checkWin(i, j)) {
                for (var k = 0; k < winningCells.length; k++) {
                    var cell = document.getElementById('board').children[winningCells[k][0] * 10 + winningCells[k][1]];
                    cell.className += ' win';
                }
                document.getElementById('result').innerHTML = 'Player ' + currentPlayer + ' wins! Please reset the game to play again.';
                gameOver = true;
            } else if (isDraw()) {
                document.getElementById('result').innerHTML = 'The game is a draw! Please reset the game to play again.';
                gameOver = true;
            }
            currentPlayer = currentPlayer === '⭕' ? '❌' : '⭕';
        } else {
            if (selectedCell !== null) {
                selectedCell.className = 'cell';
            }
            selectedCell = currentCell;
            selectedCell.className += ' selected';
            selectedCellIndex = i * 10 + j;
        }
    }
	updateCurrentPlayer();
    resetTimer();
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

function updateCurrentPlayer() {
    document.getElementById('currentPlayer').innerHTML = 'Current Player: ' + currentPlayer;
}

function startTimer() {
    var timeLeft = 60;
    timer = setInterval(function() {
		if (timeLeft <= 0) {
            clearInterval(timer);
            document.getElementById('timer').innerHTML = 'Time\'s up!';
            gameOver = true;
        } else {
            document.getElementById('timer').innerHTML = timeLeft + ' seconds remaining';
            timeLeft -= 1;
			if (gameOver == true) {
				clearInterval(timer);
				document.getElementById('timer').innerHTML = '';
			}
        }
    }, 1000);
	
}

function resetTimer() {
    clearInterval(timer);
    startTimer();
}

function resetBoard() {
    createBoard();
    currentPlayer = '⭕';
    winningCells = [];
    gameOver = false;
    document.getElementById('result').innerHTML = '';
}

createBoard();
