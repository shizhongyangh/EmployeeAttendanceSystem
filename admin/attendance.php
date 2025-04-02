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

// 获取用户ID参数
$filter_user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

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

// 获取考勤记录
$conn = connectDB();

$where = [];
if ($filter_user_id) {
    $where[] = "a.user_id = $filter_user_id";
}

$start_date = "$year-$month-01";
$end_date = date('Y-m-t', strtotime($start_date));
$where[] = "a.date BETWEEN '$start_date' AND '$end_date'";

$where_clause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT a.*, u.username FROM attendance a JOIN users u ON a.user_id = u.id $where_clause ORDER BY a.date DESC, a.user_id ASC";
$result = $conn->query($sql);

$attendance_records = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $attendance_records[] = $row;
    }
}

closeDB($conn);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>考勤管理 - SZY考勤系统</title>
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
            <li class="active"><a href="attendance.php">考勤管理</a></li>
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
                    <h2>考勤管理</h2>
                </div>
                
                <div class="card-body">
                    <div class="alert-container"></div>
                    
                    <div class="filter-container">
                        <form action="" method="get" class="form-inline">
                            <div class="form-group">
                                <label for="user_id">员工：</label>
                                <select class="form-control" id="user_id" name="user_id">
                                    <option value="">全部员工</option>
                                    <?php foreach ($users as $u): ?>
                                    <?php if ($u['role'] == 'employee'): ?>
                                    <option value="<?php echo $u['id']; ?>" <?php echo ($filter_user_id == $u['id']) ? 'selected' : ''; ?>>
                                        <?php echo $u['username']; ?>
                                    </option>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
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
                        <?php if (count($attendance_records) > 0): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>员工</th>
                                    <th>日期</th>
                                    <th>打卡时间</th>
                                    <th>状态</th>
                                    <th>是否补卡</th>
                                    <th>是否管理员补卡</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance_records as $record): ?>
                                <tr>
                                    <td><?php echo $record['id']; ?></td>
                                    <td><?php echo $record['username']; ?></td>
                                    <td><?php echo $record['date']; ?></td>
                                    <td><?php echo $record['clock_in_time']; ?></td>
                                    <td><?php echo $record['status']; ?></td>
                                    <td><?php echo $record['is_makeup'] ? '是' : '否'; ?></td>
                                    <td><?php echo $record['is_admin_makeup'] ? '是' : '否'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p>暂无考勤记录</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-4">
                        <h3>帮员工补卡</h3>
                        
                        <form id="adminMakeupForm" class="form-inline">
                            <div class="form-group">
                                <label for="makeup_user_id">员工：</label>
                                <select class="form-control" id="makeup_user_id" name="user_id" required>
                                    <option value="">选择员工</option>
                                    <?php foreach ($users as $u): ?>
                                    <?php if ($u['role'] == 'employee'): ?>
                                    <option value="<?php echo $u['id']; ?>">
                                        <?php echo $u['username']; ?>
                                    </option>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="makeup_date">日期：</label>
                                <input type="date" class="form-control" id="makeup_date" name="date" required max="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">补卡</button>
                        </form>
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
