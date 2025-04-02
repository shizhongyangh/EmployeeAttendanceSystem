<?php
// 数据库配置
$db_host = "154.84.61.112:3306";
$db_user = "root";
$db_pass = "Szy20130914";
$db_name = "kq";

// 创建数据库连接
function connectDB() {
    global $db_host, $db_user, $db_pass, $db_name;
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        die("连接失败: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8");
    return $conn;
}

// 关闭数据库连接
function closeDB($conn) {
    $conn->close();
}
?>
