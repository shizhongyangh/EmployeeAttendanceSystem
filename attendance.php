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

// 获取年月参数
$current_year = date('Y');
$current_month = date('m');

if (isset($_GET['year']) && isset($_GET['month'])) {
    $year = $_GET['year'];
    $month = $_GET['month'];
} else {
    $year = $current_year;
    $month = $current_month;
}

// 获取月度考勤记录
$user_id = $_SESSION['user_id'];
$attendance_records = getMonthlyAttendance($user_id, $year, $month);

// 生成日历数据
$first_day = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = date('t', $first_day);
$day_of_week = date('N', $first_day);
$today = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>考勤记录 - SZY考勤系统</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>SZY考勤系统</h3>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">考勤打卡</a></li>
            <li class="active"><a href="attendance.php">考勤记录</a></li>
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
                    <h2>考勤记录</h2>
                </div>
                
                <div class="card-body">
                    <div class="alert-container"></div>
                    
                    <div class="date-selector">
                        <select id="yearSelector" class="form-control">
                            <?php for ($y = $current_year - 2; $y <= $current_year; $y++): ?>
                            <option value="<?php echo $y; ?>" <?php echo ($y == $year) ? 'selected' : ''; ?>><?php echo $y; ?>年</option>
                            <?php endfor; ?>
                        </select>
                        
                        <select id="monthSelector" class="form-control">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo sprintf('%02d', $m); ?>" <?php echo ($m == (int)$month) ? 'selected' : ''; ?>><?php echo $m; ?>月</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="calendar-container">
                        <div class="calendar-header">
                            <h4><?php echo $year; ?>年<?php echo $month; ?>月考勤日历</h4>
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
                                            if ($attendance['is_makeup']) {
                                                echo '<br><span class="badge badge-warning">补卡</span>';
                                            }
                                            if (isset($attendance['is_admin_makeup']) && $attendance['is_admin_makeup']) {
                                                echo '<br><span class="badge badge-info">管理员补卡</span>';
                                            }
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
                    
                    <h3 class="mt-5">考勤详细记录</h3>
                    
                    <table class="table">
                        <thead>
                            <tr>
                                <th>日期</th>
                                <th>打卡时间</th>
                                <th>状态</th>
                                <th>类型</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($attendance_records) > 0): ?>
                                <?php foreach ($attendance_records as $record): ?>
                                <tr>
                                    <td><?php echo $record['date']; ?></td>
                                    <td><?php echo $record['clock_in_time']; ?></td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        $status_text = '';
                                        
                                        switch ($record['status']) {
                                            case 'present':
                                                $status_class = 'success';
                                                $status_text = '出勤';
                                                break;
                                            case 'absent':
                                                $status_class = 'danger';
                                                $status_text = '缺勤';
                                                break;
                                            case 'leave':
                                                $status_class = 'warning';
                                                $status_text = '请假';
                                                break;
                                            case 'business_trip':
                                                $status_class = 'info';
                                                $status_text = '出差';
                                                break;
                                        }
                                        ?>
                                        <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($record['is_makeup']): ?>
                                        <span class="badge badge-warning">补卡</span>
                                        <?php else: ?>
                                        <span class="badge badge-primary">正常打卡</span>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($record['is_admin_makeup']) && $record['is_admin_makeup']): ?>
                                        <span class="badge badge-info">管理员补卡</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">暂无考勤记录</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
