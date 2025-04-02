<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// 检查用户是否已登录
if (!isLoggedIn()) {
    echo json_encode(['status' => false, 'message' => '未登录']);
    exit;
}

// 获取日期参数
$date = $_POST['date'];

// 补卡
$user_id = $_SESSION['user_id'];
$result = makeUpClock($user_id, $date);

echo json_encode($result);
?>
