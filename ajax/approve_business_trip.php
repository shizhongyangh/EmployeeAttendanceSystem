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
$trip_id = $_POST['trip_id'];
$status = $_POST['status'];
$comment = isset($_POST['comment']) ? $_POST['comment'] : '';

// 审批出差
$result = approveBusinessTrip($trip_id, $status, $comment);

echo json_encode($result);
?>
