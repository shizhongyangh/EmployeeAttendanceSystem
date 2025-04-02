<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// 检查用户是否已登录
if (!isLoggedIn()) {
    echo json_encode(['status' => false, 'message' => '未登录']);
    exit;
}

// 获取参数
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$type = $_POST['type'];
$reason = $_POST['reason'];

// 申请请假
$user_id = $_SESSION['user_id'];
$result = applyLeave($user_id, $start_date, $end_date, $reason, $type);

echo json_encode($result);
?>
