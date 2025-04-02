<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

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

// 获取当前日期
$today = date('Y-m-d');

// 检查今天是否已打卡
$conn = connectDB();
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM attendance WHERE user_id = $user_id AND date = '$today'";
$result = $conn->query($sql);
$already_clocked_in = ($result->num_rows > 0);
closeDB($conn);

// 获取当前月份的考勤记录
$current_year = date('Y');
$current_month = date('m');

// 如果有GET参数，则使用GET参数的年月
if (isset($_GET['year']) && isset($_GET['month'])) {
    $year = $_GET['year'];
    $month = $_GET['month'];
} else {
    $year = $current_year;
    $month = $current_month;
}

$attendance_records = getMonthlyAttendance($user_id, $year, $month);

// 生成日历数据
$first_day = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = date('t', $first_day);
$day_of_week = date('N', $first_day);

// 获取请假和出差记录
$leaves = getLeaveApplications($user_id);
$business_trips = getBusinessTripApplications($user_id);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>员工面板 - SZY考勤系统</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>SZY考勤系统</h3>
        </div>
        
        <ul class="sidebar-menu">
            <li class="active"><a href="dashboard.php">考勤打卡</a></li>
            <li><a href="attendance.php">考勤记录</a></li>
            <li><a href="leave.php">请假申请</a></li>
            <li><a href="business_trip.php">出差申请</a></li>
            <li><a href="profile.php">个人信息</a></li>
            <li><a href="logout.php">退出登录</a></li>
        </ul>
    </div>
    
    <div class="content">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>欢迎, <?php echo $user['username']; ?></h2>
                </div>
                
                <div class="card-body">
                    <div class="alert-container"></div>
                    
                    <div class="row">
                        <div class="col">
                            <div class="stats-card">
                                <h3>当前积分</h3>
                                <div class="number"><?php echo $user['points']; ?></div>
                                <p>累计积分</p>
                            </div>
                        </div>
                        
                        <div class="col">
                            <div class="stats-card">
                                <h3>今日状态</h3>
                                <div class="number">
                                    <?php echo $already_clocked_in ? '已打卡' : '未打卡'; ?>
                                </div>
                                <p><?php echo date('Y-m-d'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!$already_clocked_in): ?>
                    <div class="text-center mt-4">
                        <button id="clockInBtn" class="btn btn-primary btn-lg">立即打卡</button>
                    </div>
                    <?php endif; ?>
                    
                    <h3 class="mt-5">本月考勤日历</h3>
                    
                    <div class="calendar-container">
                        <div class="calendar-header">
                            <h4><?php echo $year; ?>年<?php echo $month; ?>月</h4>
                        </div>
                        
                        <table class="calendar">
                            <thead>
                                <tr>
                                    <th>周一</th>
                                    <th>周二</th>
                                    <th>周三</th>
                                    <th>周四</th>
                                    <th>周五</th>
                                    <th>周六</th>
                                    <th>周日</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <?php
                                    // 填充月初的空白
                                    for ($i = 1; $i < $day_of_week; $i++) {
                                        echo '<td></td>';
                                    }
                                    
                                    // 填充日期
                                    for ($day = 1; $day <= $days_in_month; $day++) {
                                        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                        $is_today = ($date == $today);
                                        $attendance = null;
                                        
                                        // 查找当天的考勤记录
                                        foreach ($attendance_records as $record) {
                                            if ($record['date'] == $date) {
                                                $attendance = $record;
                                                break;
                                            }
                                        }
                                        
                                        $class = '';
                                        if ($is_today) {
                                            $class = 'today';
                                        }
                                        
                                        if ($attendance) {
                                            if ($attendance['is_makeup']) {
                                                $class .= ' makeup';
                                            } else {
                                                $class .= ' present';
                                            }
                                        }
                                        
                                        echo '<td class="' . $class . '">';
                                        echo $day;
                                        
                                        if ($attendance) {
                                            echo '<br><small>' . substr($attendance['clock_in_time'], 0, 5) . '</small>';
                                        } else {
                                            // 如果是过去的日期且没有打卡记录，显示补卡按钮
                                            $past_date = strtotime($date) < strtotime($today);
                                            if ($past_date) {
                                                echo '<br><button class="btn btn-sm btn-warning makeup-btn" data-date="' . $date . '">补卡</button>';
                                            }
                                        }
                                        
                                        echo '</td>';
                                        
                                        // 如果是周日或月末最后一天，结束当前行并开始新行
                                        if (($day_of_week + $day - 1) % 7 == 0 || $day == $days_in_month) {
                                            echo '</tr>';
                                            if ($day != $days_in_month) {
                                                echo '<tr>';
                                            }
                                        }
                                    }
                                    
                                    // 填充月末的空白
                                    $last_day_of_week = ($day_of_week + $days_in_month - 1) % 7;
                                    if ($last_day_of_week != 0) {
                                        for ($i = $last_day_of_week; $i < 7; $i++) {
                                            echo '<td></td>';
                                        }
                                    }
                                    ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="calendar-legend mt-3">
                        <span class="badge present">已打卡</span>
                        <span class="badge makeup">已补卡</span>
                        <span class="badge today">今天</span>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>最近请假记录</h3>
                </div>
                
                <div class="card-body">
                    <?php if (count($leaves) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>开始日期</th>
                                <th>结束日期</th>
                                <th>类型</th>
                                <th>原因</th>
                                <th>状态</th>
                                <th>审批意见</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($leaves, 0, 5) as $leave): ?>
                            <tr>
                                <td><?php echo $leave['start_date']; ?></td>
                                <td><?php echo $leave['end_date']; ?></td>
                                <td><?php echo $leave['type']; ?></td>
                                <td><?php echo $leave['reason']; ?></td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    $status_text = '';
                                    
                                    switch ($leave['status']) {
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
                                <td><?php echo $leave['comment']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p>暂无请假记录</p>
                    <?php endif; ?>
                    
                    <a href="leave.php" class="btn btn-primary">查看全部</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>最近出差记录</h3>
                </div>
                
                <div class="card-body">
                    <?php if (count($business_trips) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>开始日期</th>
                                <th>结束日期</th>
                                <th>目的地</th>
                                <th>目的</th>
                                <th>状态</th>
                                <th>审批意见</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($business_trips, 0, 5) as $trip): ?>
                            <tr>
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
                    
                    <a href="business_trip.php" class="btn btn-primary">查看全部</a>
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
