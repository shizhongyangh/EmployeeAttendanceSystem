<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// 检查用户是否已登录
if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// 检查是否为管理员，如果是则重定向到管理员面板
if (isAdmin()) {
    header('Location: admin/dashboard.php');
    exit;
}

// 获取当前用户信息
$user = getCurrentUser();

// 处理出差申请提交
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $destination = $_POST['destination'];
    $purpose = $_POST['purpose'];
    
    $result = applyBusinessTrip($user['id'], $start_date, $end_date, $destination, $purpose);
    
    if ($result['status']) {
        $success_message = $result['message'];
    } else {
        $error_message = $result['message'];
    }
}

// 获取出差记录
$business_trips = getBusinessTripApplications($user['id']);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>出差申请 - SZY考勤系统</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>SZY考勤系统</h3>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">考勤打卡</a></li>
            <li><a href="attendance.php">考勤记录</a></li>
            <li><a href="leave.php">请假申请</a></li>
            <li class="active"><a href="business_trip.php">出差申请</a></li>
            <li><a href="profile.php">个人信息</a></li>
            <li><a href="logout.php">退出登录</a></li>
        </ul>
    </div>
    
    <div class="content">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>出差申请</h2>
                </div>
                
                <div class="card-body">
                    <div class="alert-container">
                        <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <form id="businessTripForm" method="post" action="">
                        <div class="form-group">
                            <label for="start_date">开始日期</label>
                            <input type="date" class="form-control datepicker" id="start_date" name="start_date" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="end_date">结束日期</label>
                            <input type="date" class="form-control datepicker" id="end_date" name="end_date" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="destination">目的地</label>
                            <input type="text" class="form-control" id="destination" name="destination" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="purpose">出差目的</label>
                            <textarea class="form-control" id="purpose" name="purpose" rows="3" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">提交申请</button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>出差记录</h3>
                </div>
                
                <div class="card-body">
                    <?php if (count($business_trips) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>申请日期</th>
                                <th>开始日期</th>
                                <th>结束日期</th>
                                <th>目的地</th>
                                <th>出差目的</th>
                                <th>状态</th>
                                <th>审批意见</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($business_trips as $trip): ?>
                            <tr>
                                <td><?php echo substr($trip['created_at'], 0, 10); ?></td>
                                <td><?php echo $trip['start_date']; ?></td>
                                <td><?php echo $trip['end_date']; ?></td>
                                <td><?php echo $trip['destination']; ?></td>
                                <td><?php echo $trip['purpose']; ?></td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    $status_text = '';
                                    
                                    switch ($trip['status']) {
                                        case 'pending':
                                            $status_class = 'warning';
                                            $status_text = '待审批';
                                            break;
                                        case 'approved':
                                            $status_class = 'success';
                                            $status_text = '已批准';
                                            break;
                                        case 'rejected':
                                            $status_class = 'danger';
                                            $status_text = '已拒绝';
                                            break;
                                    }
                                    ?>
                                    <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                                <td><?php echo $trip['comment']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p>暂无出差记录</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> SZY创新工作室. 保留所有权利.</p>
        </div>
    </div>
    
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
