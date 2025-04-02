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
$username = $_POST['username'];
$role = $_POST['role'];
$points = $_POST['points'];
$password = isset($_POST['password']) ? $_POST['password'] : null;

// 更新用户信息
$data = [
    'username' => $username,
    'role' => $role,
    'points' => $points
];

// 如果提供了新密码，则更新密码
if (!empty($password)) {
    $data['password'] = md5($password);
}

$result = updateUserInfo($user_id, $data);

if ($result) {
    echo json_encode(['status' => true, 'message' => '用户信息更新成功']);
} else {
    echo json_encode(['status' => false, 'message' => '用户信息更新失败']);
}
?>
