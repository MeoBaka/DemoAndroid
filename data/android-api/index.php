<?php
    // Bắt đầu phiên
    session_start();

    // Thông tin kết nối đến cơ sở dữ liệu MySQL
    $servername = "panel.mcsea.asia";
    $dbusername = "Lap";
    $dbpassword = "Lap@22002878";
    $dbname = "db_lap";

    // Tạo kết nối đến cơ sở dữ liệu
    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

    // Kiểm tra kết nối
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }

    // Kiểm tra xem người dùng đã đăng nhập chưa
    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        // Người dùng đã đăng nhập, chuyển hướng đến trang main.php
        header("Location: main.php");
        exit;
    }

    // Kiểm tra xem form có được gửi không
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST["username"];
        $password = $_POST["password"];

        // Tạo câu truy vấn SQL
        $sql = "SELECT * FROM users WHERE username = ? AND password = ?";

        // Chuẩn bị câu truy vấn
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password);

        // Thực hiện câu truy vấn
        $stmt->execute();

        // Lấy kết quả
        $result = $stmt->get_result();

        // Kiểm tra xem có bản ghi nào không
        if ($result->num_rows > 0) {
            // Đăng nhập thành công, đặt biến phiên và chuyển hướng đến trang main.php
            $_SESSION["loggedin"] = true;
            header("Location: main.php");
            exit;
        } else {
            // Đăng nhập thất bại, hiển thị thông báo lỗi
            echo "Tên đăng nhập hoặc mật khẩu không đúng.";
        }
    }
?>
