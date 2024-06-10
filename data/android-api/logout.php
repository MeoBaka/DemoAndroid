<?php
    // Bắt đầu phiên
    session_start();

    // Hủy tất cả các biến phiên
    $_SESSION = array();

    // Hủy phiên
    session_destroy();

    // Chuyển hướng người dùng đến trang đăng nhập
    header("location: login.php");
    exit;
?>
