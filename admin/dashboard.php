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

// 获取所有用户
$users = getAllUsers();

// 获取统计数据
$conn = connectDB();

// 总员工数
$sql = "SELECT COUNT(*) as total FROM users WHERE role = 'employee'";
$result = $conn->query($sql);
$employee_count = $result->fetch_assoc()['total'];

// 今日打卡人数
$today = date('Y-m-d');
$sql = "SELECT COUNT(DISTINCT user_id) as total FROM attendance WHERE date = '$today'";
$result = $conn->query($sql);
$today_attendance_count = $result->fetch_assoc()['total'];

// 待审批请假
$sql = "SELECT COUNT(*) as total FROM leaves WHERE status = 'pending'";
$result = $conn->query($sql);
$pending_leave_count = $result->fetch_assoc()['total'];

// 待审批出差
$sql = "SELECT COUNT(*) as total FROM business_trips WHERE status = 'pending'";
$result = $conn->query($sql);
$pending_trip_count = $result->fetch_assoc()['total'];

closeDB($conn);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理面板 - SZY考勤系统</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>SZY考勤系统</h3>
        </div>
        
        <ul class="sidebar-menu">
            <li class="active"><a href="dashboard.php">管理面板</a></li>
            <li><a href="users.php">员工管理</a></li>
            <li><a href="attendance.php">考勤管理</a></li>
            <li><a href="leaves.php">请假管理</a></li>
            <li><a href="business_trips.php">出差管理</a></li>
            <li><a href="reports.php">统计报表</a></li>
            <li><a href="profile.php">个人信息</a></li>
            <li><a href="../logout.php">退出登录</a></li>
        </ul>
    </div>
    
    <div class="content">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>管理面板</h2>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <div class="stats-card">
                                <h3>员工总数</h3>
                                <div class="number"><?php echo $employee_count; ?></div>
                                <p>系统中的员工数量</p>
                            </div>
                        </div>
                        
                        <div class="col">
                            <div class="stats-card">
                                <h3>今日打卡</h3>
                                <div class="number"><?php echo $today_attendance_count; ?></div>
                                <p>今日已打卡人数</p>
                            </div>
                        </div>
                        
                        <div class="col">
                            <div class="stats-card">
                                <h3>待审批请假</h3>
                                <div class="number"><?php echo $pending_leave_count; ?></div>
                                <p><a href="leaves.php?status=pending">查看详情</a></p>
                            </div>
                        </div>
                        
                        <div class="col">
                            <div class="stats-card">
                                <h3>待审批出差</h3>
                                <div class="number"><?php echo $pending_trip_count; ?></div>
                                <p><a href="business_trips.php?status=pending">查看详情</a></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-5">
                        <h3>员工列表</h3>
                        
                        <?php if (count($users) > 0): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>用户名</th>
                                    <th>角色</th>
                                    <th>积分</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                <?php if ($u['role'] == 'employee'): ?>
                                <tr>
                                    <td><?php echo $u['id']; ?></td>
                                    <td><?php echo $u['username']; ?></td>
                                    <td><?php echo $u['role'] == 'admin' ? '管理员' : '员工'; ?></td>
                                    <td><?php echo $u['points']; ?></td>
                                    <td><?php echo substr($u['created_at'], 0, 16); ?></td>
                                    <td>
                                        <a href="attendance.php?user_id=<?php echo $u['id']; ?>" class="btn btn-sm btn-primary">考勤记录</a>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p>暂无员工</p>
                        <?php endif; ?>
                        
                        <a href="users.php" class="btn btn-primary">管理员工</a>
                    </div>
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
