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
$destination = $_POST['destination'];
$purpose = $_POST['purpose'];

// 申请出差
$user_id = $_SESSION['user_id'];
$result = applyBusinessTrip($user_id, $start_date, $end_date, $destination, $purpose);

echo json_encode($result);
?>
