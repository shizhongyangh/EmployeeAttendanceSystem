<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// 检查用户是否已登录
if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

// 检查是否为管理员
if (!isAdmin()) {
    header('Location: ../dashboard.php');
    exit;
}

// 获取当前用户信息
$user = getCurrentUser();

// 处理修改用户名
$username_success = '';
$username_error = '';

if (isset($_POST['change_username'])) {
    $new_username = $_POST['new_username'];
    
    $data = ['username' => $new_username];
    $result = updateUserInfo($user['id'], $data);
    
    if ($result) {
        $_SESSION['username'] = $new_username;
        $username_success = '用户名修改成功';
    } else {
        $username_error = '用户名修改失败';
    }
}

// 处理修改密码
$password_success = '';
$password_error = '';

if (isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $password_error = '新密码和确认密码不一致';
    } else {
        $result = changePassword($user['id'], $old_password, $new_password);
        
        if ($result) {
            $password_success = '密码修改成功';
        } else {
            $password_error = '原密码错误或修改失败';
        }
    }
}

// 重新获取用户信息（可能已更新）
$user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>个人信息 - SZY考勤系统</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>SZY考勤系统</h3>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">管理面板</a></li>
            <li><a href="users.php">员工管理</a></li>
            <li><a href="attendance.php">考勤管理</a></li>
            <li><a href="leaves.php">请假管理</a></li>
            <li><a href="business_trips.php">出差管理</a></li>
            <li><a href="reports.php">统计报表</a></li>
            <li class="active"><a href="profile.php">个人信息</a></li>
            <li><a href="../logout.php">退出登录</a></li>
        </ul>
    </div>
    
    <div class="content">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>个人信息</h2>
                </div>
                
                <div class="card-body">
                    <div class="profile-info">
                        <div class="form-group">
                            <label>用户名</label>
                            <p><?php echo $user['username']; ?></p>
                        </div>
                        
                        <div class="form-group">
                            <label>角色</label>
                            <p><?php echo $user['role'] == 'admin' ? '管理员' : '员工'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>修改用户名</h3>
                </div>
                
                <div class="card-body">
                    <?php if ($username_success): ?>
                    <div class="alert alert-success"><?php echo $username_success; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($username_error): ?>
                    <div class="alert alert-danger"><?php echo $username_error; ?></div>
                    <?php endif; ?>
                    
                    <form id="changeUsernameForm" method="post" action="">
                        <div class="form-group">
                            <label for="new_username">新用户名</label>
                            <input type="text" class="form-control" id="new_username" name="new_username" required>
                        </div>
                        
                        <button type="submit" name="change_username" class="btn btn-primary">修改用户名</button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>修改密码</h3>
                </div>
                
                <div class="card-body">
                    <?php if ($password_success): ?>
                    <div class="alert alert-success"><?php echo $password_success; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($password_error): ?>
                    <div class="alert alert-danger"><?php echo $password_error; ?></div>
                    <?php endif; ?>
                    
                    <form id="changePasswordForm" method="post" action="">
                        <div class="form-group">
                            <label for="old_password">原密码</label>
                            <input type="password" class="form-control" id="old_password" name="old_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">新密码</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">确认密码</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-primary">修改密码</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> SZY创新工作室. 保留所有权利.</p>
        </div>
    </div>
    
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
