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
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>员工管理 - SZY考勤系统</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>SZY考勤系统</h3>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">管理面板</a></li>
            <li class="active"><a href="users.php">员工管理</a></li>
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
                    <h2>员工管理</h2>
                </div>
                
                <div class="card-body">
                    <div class="alert-container"></div>
                    
                    <button class="btn btn-primary" id="addUserBtn">添加员工</button>
                    
                    <div class="mt-4">
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
                                <tr>
                                    <td><?php echo $u['id']; ?></td>
                                    <td><?php echo $u['username']; ?></td>
                                    <td><?php echo $u['role'] == 'admin' ? '管理员' : '员工'; ?></td>
                                    <td><?php echo $u['points']; ?></td>
                                    <td><?php echo substr($u['created_at'], 0, 16); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-user-btn" data-id="<?php echo $u['id']; ?>" data-username="<?php echo $u['username']; ?>" data-role="<?php echo $u['role']; ?>" data-points="<?php echo $u['points']; ?>">编辑</button>
                                        <?php if ($u['id'] != $user['id']): ?>
                                        <button class="btn btn-sm btn-danger delete-user-btn" data-id="<?php echo $u['id']; ?>">删除</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p>暂无用户</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 添加用户模态框 -->
    <div class="modal" id="addUserModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">添加用户</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                
                <div class="modal-body">
                    <form id="addUserForm">
                        <div class="form-group">
                            <label for="username">用户名</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">密码</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="role">角色</label>
                            <select class="form-control" id="role" name="role">
                                <option value="employee">员工</option>
                                <option value="admin">管理员</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">添加</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 编辑用户模态框 -->
    <div class="modal" id="editUserModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">编辑用户</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                
                <div class="modal-body">
                    <form id="editUserForm">
                        <input type="hidden" id="edit_user_id" name="user_id">
                        
                        <div class="form-group">
                            <label for="edit_username">用户名</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_password">密码 (留空表示不修改)</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_role">角色</label>
                            <select class="form-control" id="edit_role" name="role">
                                <option value="employee">员工</option>
                                <option value="admin">管理员</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_points">积分</label>
                            <input type="number" class="form-control" id="edit_points" name="points" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">保存</button>
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
