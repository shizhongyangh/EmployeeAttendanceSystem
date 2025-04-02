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
$old_password = $_POST['old_password'];
$new_password = $_POST['new_password'];

// 修改密码
$user_id = $_SESSION['user_id'];
$result = changePassword($user_id, $old_password, $new_password);

if ($result) {
    echo json_encode(['status' => true, 'message' => '密码修改成功']);
} else {
    echo json_encode(['status' => false, 'message' => '原密码错误或修改失败']);
}
?>
