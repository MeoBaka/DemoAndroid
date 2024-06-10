<?php
    session_start();

    $servername = "panel.mcsea.asia";
    $dbusername = "Lap";
    $dbpassword = "Lap@22002878";
    $dbname = "db_lap";

    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }

    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        header("Location: main.php");
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST["username"];
        $password = $_POST["password"];
        $sql = "SELECT * FROM account WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $sql = "SELECT * FROM account WHERE username = ? AND password = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $password);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $_SESSION["loggedin"] = true;
                $_SESSION["username"] = $username;
                header("Location: main.php");
                exit;
            } else {
                echo '<div class="login-message error">Sai mật khẩu!</div>';
            }
        } else {
            echo '<div class="login-message error">Tài khoản không tồn tại!</div>';
        }
    }
?>


<!DOCTYPE html>
<html>
<head>
    <title>Đăng nhập</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <?php
      $randomNumber = mt_rand(100000, 999999);
      echo "<link rel='stylesheet' href='css/styles.css?v=".$randomNumber."'>"
    ?>
</head>
<body>
	<div class="Khung">
		<p class="title">Đăng nhập</p>
		<form method="post" action="">
			<div class="group">
				<label for="username" class="title-label">Tên đăng nhập:</label>
				<input type="text" id="username" name="username" required class="inputbox">
			</div><br>
		    <div class="group">
				<label for="password" class="title-label">Mật khẩu:</label>
				<input type="password" id="password" name="password" required class="inputbox">
			</div><br>
			<input type="submit" value="Đăng nhập" class="loginbutton">
		</form>
		<div class="buttonadd">
            <form method="get" action="register.php" class="regis">
		    	<div class="form-group">
		    		<input type="submit" class="registerbutton" value="Đăng ký">
		    	</div>
	    	</form>
    		<button id="exitButton" class="exitButton">Thoát</button>
    		<button id="forgotPasswordButton" class="forgotPasswordButton">Quên mật khẩu</button>
    	</div>
	</div>
    <script type="text/javascript">
    document.getElementById("exitButton").addEventListener("click", function() {
        if (window.JSInterface) {
            window.JSInterface.exitApp();
        }
    });
    document.getElementById("forgotPasswordButton").addEventListener("click", function() {
        if (window.JSInterface) {
            window.JSInterface.forgotPassword();
        }
    });
    </script>

</body>
</html>
