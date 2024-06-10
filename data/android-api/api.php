<?php
    if (!isset($_POST["db_config"]) || empty($_POST["db_config"]) || !isset($_POST["sql_query"]) || empty($_POST["sql_query"])) {
      exit;
    }
    $db_config = explode(",", $_POST["db_config"]);
    $conn = new mysqli($db_config[0], $db_config[1], $db_config[2], $db_config[3]);
    if ($conn->connect_error) {
      die("Kết nối thất bại: " . $conn->connect_error);
    }
    $sql = $_POST["sql_query"];
    if (!$result = $conn->query($sql)) {
      die("Có lỗi xảy ra khi thực hiện truy vấn: " . $conn->error);
    }
    $data = array();
    if ($result->num_rows > 0) {
      while($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $data[] = $row;
      }
    } else {
      die("Không tìm thấy dữ liệu trong cơ sở dữ liệu.");
    }
    echo json_encode($data);
    $conn->close();
?>
