<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// 处理登录请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if (userLogin($username, $password)) {
        // 登录成功，根据角色重定向
        if (isAdmin()) {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: dashboard.php');
        }
        exit;
    } else {
        // 登录失败
        $_SESSION['error'] = '用户名或密码错误';
        header('Location: index.php');
        exit;
    }
}
?>
