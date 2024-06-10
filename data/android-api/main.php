<?php
    session_start();
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        header("location: login.php");
        exit;
    }
    $username = $_SESSION["username"];

    $servername = "panel.mcsea.asia";
    $dbusername = "Lap";
    $dbpassword = "Lap@22002878";
    $dbname = "db_lap";

    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM caroinfo WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $elo = $row["elo"];
        $level = $row["level"];
        $coin = $row["coin"];
    } else {
        echo "No results found";
    }
    $conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Trang chính</title>
    <?php
      $randomNumber = mt_rand(100000, 999999);
      echo "<link rel='stylesheet' href='css/main.css?v=".$randomNumber."'>"
    ?>
</head>
<body>
    <div class="MainBorder">
        <div class="Profile-Main">
            <div class="Profile-Display">
                <div class="Profile-Infomation">
                    <img class="Profile-Img" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTRDUsQDpDYZTltD4JVCjpIYr2utsAYFDlAhO5qEWUHcQ&s">
                    <div class="Info-Display">
                        <div class="Profile-User"><?php echo $username; ?></div>
                        <div class="Profile-Score">Điểm: <?php echo $elo; ?></div>
                        <div class="Profile-Level">Cấp Độ: <?php echo $level; ?></div>
                    </div>
                </div>
                <div class="Profile-Coin">
                     <div class="Coin-Border">
                         <img class="Coin-Icon" src="https://cdn-icons-png.flaticon.com/512/217/217853.png">
                         <div class="Coin-Coin"><?php echo $coin; ?></div>
                     </div>
                </div>
            </div>
        </div>
        <div class="Button-Main">
            <form action="findgame.php" method="post">
                <input type="submit" value="CHƠI NHANH" class="buttonCaro">
            </form>
            <form action="test.php" method="post">
                <input type="submit" value="CHƠI 2 NGƯỜI" class="buttonCaro">
            </form>
            <form action="ai.php" method="post">
                <input type="submit" value="CHƠI VS MÁY" class="buttonCaro">
            </form>
            <form action="logout.php" method="post">
                <input type="submit" value="Đăng xuất" class="buttonLogOut">
            </form>
            <form action="" method="post">
                <input type="submit" value="Báo Cáo Lỗi" class="buttonLogOut">
            </form>
        </div>
        <div class="Caro-Rules">
            Luật chơi cờ caro khá đơn giản, nhiệm vụ của mỗi người chơi cờ caro đó là đạt được một đường thẳng, đường chéo, đường ngang với 5 ô nhanh nhất. Tuy nhiên, chỉ cần người chơi nào có thể đạt 4 nước mà bị chặn 1 đầu hoặc không bị chặn hai đầu là đã có thể chiến thắng.
        </div>
    </div>
</body>
</html>
