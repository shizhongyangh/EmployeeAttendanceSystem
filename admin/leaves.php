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

// 获取请假申请列表
$status_filter = isset($_GET['status']) ? $_GET['status'] : null;
$leaves = getLeaveApplications(null, $status_filter);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>请假管理 - SZY考勤系统</title>
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
            <li class="active"><a href="leaves.php">请假管理</a></li>
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
                    <h2>请假管理</h2>
                </div>
                
                <div class="card-body">
                    <div class="alert-container"></div>
                    
                    <div class="filter-container">
                        <a href="leaves.php" class="btn <?php echo !$status_filter ? 'btn-primary' : 'btn-secondary'; ?>">全部</a>
                        <a href="leaves.php?status=pending" class="btn <?php echo $status_filter == 'pending' ? 'btn-primary' : 'btn-secondary'; ?>">待审批</a>
                        <a href="leaves.php?status=approved" class="btn <?php echo $status_filter == 'approved' ? 'btn-primary' : 'btn-secondary'; ?>">已批准</a>
                        <a href="leaves.php?status=rejected" class="btn <?php echo $status_filter == 'rejected' ? 'btn-primary' : 'btn-secondary'; ?>">已拒绝</a>
                    </div>
                    
                    <div class="mt-4">
                        <?php if (count($leaves) > 0): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>员工</th>
                                    <th>开始日期</th>
                                    <th>结束日期</th>
                                    <th>类型</th>
                                    <th>原因</th>
                                    <th>申请时间</th>
                                    <th>状态</th>
                                    <th>审批意见</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($leaves as $leave): ?>
                                <tr>
                                    <td><?php echo $leave['id']; ?></td>
                                    <td><?php echo $leave['username']; ?></td>
                                    <td><?php echo $leave['start_date']; ?></td>
                                    <td><?php echo $leave['end_date']; ?></td>
                                    <td><?php echo $leave['type']; ?></td>
                                    <td><?php echo $leave['reason']; ?></td>
                                    <td><?php echo substr($leave['created_at'], 0, 16); ?></td>
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
                                    <td>
                                        <?php if ($leave['status'] == 'pending'): ?>
                                        <button class="btn btn-sm btn-success approve-leave-btn" data-id="<?php echo $leave['id']; ?>">批准</button>
                                        <button class="btn btn-sm btn-danger reject-leave-btn" data-id="<?php echo $leave['id']; ?>">拒绝</button>
                                        <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>已处理</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p>暂无请假记录</p>
                        <?php endif; ?>
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
