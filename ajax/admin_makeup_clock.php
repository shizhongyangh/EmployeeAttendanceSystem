<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// 检查用户是否已登录
if (!isLoggedIn()) {
    echo json_encode(['status' => false, 'message' => '未登录']);
    exit;
}

// 检查是否为管理员
if (!isAdmin()) {
    echo json_encode(['status' => false, 'message' => '无权限']);
    exit;
}

// 获取参数
$user_id = $_POST['user_id'];
$date = $_POST['date'];

// 管理员补卡
$result = adminMakeUpClock($user_id, $date);

echo json_encode($result);
?>
