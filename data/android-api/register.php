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

$status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $enteredUsername = $_POST["username"];
    $enteredPassword = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];

    // Kiểm tra xác nhận mật khẩu
    if ($enteredPassword !== $confirmPassword) {
        $status = "Xác nhận mật khẩu không khớp!";
    } else {
        // Kiểm tra xem tài khoản đã tồn tại hay chưa
        $sql_check = "SELECT * FROM account WHERE username = '$enteredUsername'";
        $result_check = $conn->query($sql_check);
        if ($result_check->num_rows > 0) {
            $status = "Tài khoản đã tồn tại!";
        } else {
            // Thêm dữ liệu vào cơ sở dữ liệu
            $rank = "member";
            $sql = "INSERT INTO `account`(`username`, `password`, `rank`) VALUES ('$enteredUsername','$enteredPassword','$rank')";
            if ($conn->query($sql) === TRUE) {
                $status = "Đăng ký thành công!";
            } else {
                $status = "Đã xảy ra lỗi: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Đăng ký</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <?php
      $randomNumber = mt_rand(100000, 999999);
      echo "<link rel='stylesheet' href='css/styles.css?v=".$randomNumber."'>"
    ?>
</head>
<body>
    <div class="Khung">
        <h2>Đăng ký</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="group">
                <label class="title-label" for="username">Tài khoản:</label>
                <input class="inputbox" type="text" id="username" name="username" required>
            </div>
            <div class="group">
                <label class="title-label" for="password">Mật khẩu:</label>
                <input class="inputbox" type="password" id="password" name="password" required>
            </div>
            <div class="group">
                <label class="title-label" for="confirm_password">Nhập lại mật khẩu:</label>
                <input class="inputbox" type="password" id="confirm_password" name="confirm_password" required>
            </div><br>
            <div class="group">
                <input class="loginbutton" type="submit" value="Đăng ký">
               <p class="large-text">Nếu đã có tài khoản, vui lòng <a href="login.php">đăng nhập</a>.</p>
            </div>
        
        </form>
        <p><?php echo $status; ?></p>
    </div>
</body>
</html>
