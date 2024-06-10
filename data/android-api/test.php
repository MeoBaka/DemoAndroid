<?php
    // Bắt đầu phiên
    session_start();

    // Kiểm tra xem người dùng đã đăng nhập chưa
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        // Nếu chưa, chuyển hướng họ đến trang đăng nhập
        header("location: login.php");
        exit;
    }

    $conn = new mysqli("localhost", "Lap", "Lap@22002878", "db_lap");
	if ($conn->connect_error) {
	  die("Kết nối thất bại: " . $conn->connect_error);
	}
    $conn->close();
?>
<!DOCTYPE html>
<html> 
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel='stylesheet' href='css/game.css?v=<?php echo mt_rand(100000, 999999); ?>'>
    <script src="solo.js"></script>
</head>
<body>
	<div class="info">
		<div id="result"></div>
		<div id="currentPlayer"></div>
		<div id="timer"></div>
	</div>
    <div id="board"></div>
    <button onclick="resetBoard()" class="buttonReset">Reset</button>

    <form action="main.php" method="post">
        <input type="submit" value="Trở Về Main" class="back">
    </form>
    <script type="text/javascript">
        window.onload = resetBoard;
    </script>
      <div class="Caro-Rules">
            Luật chơi cờ caro khá đơn giản, nhiệm vụ của mỗi người chơi cờ caro đó là đạt được một đường thẳng, đường chéo, đường ngang với 5 ô nhanh nhất. Tuy nhiên, chỉ cần người chơi nào có thể đạt 4 nước mà bị chặn 1 đầu hoặc không bị chặn hai đầu là đã có thể chiến thắng.
        </div>
</body>
</html>
