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
$new_username = $_POST['username'];

// 修改用户名
$user_id = $_SESSION['user_id'];
$data = ['username' => $new_username];
$result = updateUserInfo($user_id, $data);

if ($result) {
    $_SESSION['username'] = $new_username;
    echo json_encode(['status' => true, 'message' => '用户名修改成功']);
} else {
    echo json_encode(['status' => false, 'message' => '用户名修改失败']);
}
?>
