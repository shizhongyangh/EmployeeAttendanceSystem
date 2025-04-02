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

// 获取所有用户
$users = getAllUsers();

// 获取月度考勤统计
$conn = connectDB();
$start_date = "$year-$month-01";
$end_date = date('Y-m-t', strtotime($start_date));
$days_in_month = date('t', strtotime($start_date));
$working_days = getWorkingDays($start_date, $end_date);

$attendance_stats = [];
foreach ($users as $u) {
    if ($u['role'] == 'employee') {
        $user_id = $u['id'];
        
        // 获取出勤天数
        $sql = "SELECT COUNT(*) as total FROM attendance WHERE user_id = $user_id AND date BETWEEN '$start_date' AND '$end_date'";
        $result = $conn->query($sql);
        $attendance_days = $result->fetch_assoc()['total'];
        
        // 获取请假天数
        $sql = "SELECT SUM(DATEDIFF(end_date, start_date) + 1) as total FROM leaves 
                WHERE user_id = $user_id AND status = 'approved' 
                AND ((start_date BETWEEN '$start_date' AND '$end_date') 
                OR (end_date BETWEEN '$start_date' AND '$end_date'))";
        $result = $conn->query($sql);
        $leave_days = $result->fetch_assoc()['total'] ?: 0;
        
        // 获取出差天数
        $sql = "SELECT SUM(DATEDIFF(end_date, start_date) + 1) as total FROM business_trips 
                WHERE user_id = $user_id AND status = 'approved' 
                AND ((start_date BETWEEN '$start_date' AND '$end_date') 
                OR (end_date BETWEEN '$start_date' AND '$end_date'))";
        $result = $conn->query($sql);
        $trip_days = $result->fetch_assoc()['total'] ?: 0;
        
        // 计算缺勤天数
        $absent_days = $working_days - $attendance_days - $leave_days - $trip_days;
        $absent_days = max(0, $absent_days);
        
        // 计算出勤率
        $attendance_rate = ($working_days > 0) ? round(($attendance_days / $working_days) * 100, 2) : 0;
        
        $attendance_stats[] = [
            'user_id' => $user_id,
            'username' => $u['username'],
            'working_days' => $working_days,
            'attendance_days' => $attendance_days,
            'leave_days' => $leave_days,
            'trip_days' => $trip_days,
            'absent_days' => $absent_days,
            'attendance_rate' => $attendance_rate
        ];
    }
}

closeDB($conn);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>统计报表 - SZY考勤系统</title>
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
            <li class="active"><a href="reports.php">统计报表</a></li>
            <li><a href="profile.php">个人信息</a></li>
            <li><a href="../logout.php">退出登录</a></li>
        </ul>
    </div>
    
    <div class="content">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>统计报表</h2>
                </div>
                
                <div class="card-body">
                    <div class="filter-container">
                        <form action="" method="get" class="form-inline">
                            <div class="form-group">
                                <label for="year">年份：</label>
                                <select class="form-control" id="year" name="year">
                                    <?php for ($y = $current_year - 2; $y <= $current_year; $y++): ?>
                                    <option value="<?php echo $y; ?>" <?php echo ($y == $year) ? 'selected' : ''; ?>>
                                        <?php echo $y; ?>年
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="month">月份：</label>
                                <select class="form-control" id="month" name="month">
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?php echo sprintf('%02d', $m); ?>" <?php echo ($m == (int)$month) ? 'selected' : ''; ?>>
                                        <?php echo $m; ?>月
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">筛选</button>
                        </form>
                    </div>
                    
                    <div class="mt-4">
                        <h3><?php echo $year; ?>年<?php echo $month; ?>月考勤统计</h3>
                        
                        <?php if (count($attendance_stats) > 0): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>员工</th>
                                    <th>工作日</th>
                                    <th>出勤天数</th>
                                    <th>请假天数</th>
                                    <th>出差天数</th>
                                    <th>缺勤天数</th>
                                    <th>出勤率</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance_stats as $stat): ?>
                                <tr>
                                    <td><?php echo $stat['username']; ?></td>
                                    <td><?php echo $stat['working_days']; ?></td>
                                    <td><?php echo $stat['attendance_days']; ?></td>
                                    <td><?php echo $stat['leave_days']; ?></td>
                                    <td><?php echo $stat['trip_days']; ?></td>
                                    <td><?php echo $stat['absent_days']; ?></td>
                                    <td><?php echo $stat['attendance_rate']; ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p>暂无统计数据</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-4">
                        <button class="btn btn-primary" onclick="window.print()">打印报表</button>
                        <a href="export_report.php?year=<?php echo $year; ?>&month=<?php echo $month; ?>" class="btn btn-success">导出Excel</a>
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
