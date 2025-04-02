<?php
// 数据库安装脚本
// 创建数据库和表结构

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 检查是否已安装
if (file_exists('config/installed.php')) {
    echo "系统已经安装，如需重新安装，请删除 config/installed.php 文件。";
    exit;
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];
    $db_name = $_POST['db_name'];
    $admin_username = $_POST['admin_username'];
    $admin_password = $_POST['admin_password'];
    
    // 创建数据库连接
    $conn = new mysqli($db_host, $db_user, $db_pass);
    
    // 检查连接
    if ($conn->connect_error) {
        die("连接失败: " . $conn->connect_error);
    }
    
    // 创建数据库
    $sql = "CREATE DATABASE IF NOT EXISTS $db_name DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
    if ($conn->query($sql) !== TRUE) {
        die("创建数据库失败: " . $conn->error);
    }
    
    // 选择数据库
    $conn->select_db($db_name);
    
    // 创建用户表
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) NOT NULL AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'employee') NOT NULL DEFAULT 'employee',
        points INT(11) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    
    if ($conn->query($sql) !== TRUE) {
        die("创建用户表失败: " . $conn->error);
    }
    
    // 创建考勤表
    $sql = "CREATE TABLE IF NOT EXISTS attendance (
        id INT(11) NOT NULL AUTO_INCREMENT,
        user_id INT(11) NOT NULL,
        date DATE NOT NULL,
        clock_in_time TIME NOT NULL,
        status ENUM('present', 'absent', 'leave', 'business_trip') NOT NULL DEFAULT 'present',
        is_makeup TINYINT(1) NOT NULL DEFAULT 0,
        is_admin_makeup TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY (user_id, date),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    
    if ($conn->query($sql) !== TRUE) {
        die("创建考勤表失败: " . $conn->error);
    }
    
    // 创建请假表
    $sql = "CREATE TABLE IF NOT EXISTS leaves (
        id INT(11) NOT NULL AUTO_INCREMENT,
        user_id INT(11) NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        type VARCHAR(50) NOT NULL,
        reason TEXT NOT NULL,
        status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
        comment TEXT,
        approved_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    
    if ($conn->query($sql) !== TRUE) {
        die("创建请假表失败: " . $conn->error);
    }
    
    // 创建出差表
    $sql = "CREATE TABLE IF NOT EXISTS business_trips (
        id INT(11) NOT NULL AUTO_INCREMENT,
        user_id INT(11) NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        destination VARCHAR(100) NOT NULL,
        purpose TEXT NOT NULL,
        status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
        comment TEXT,
        approved_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    
    if ($conn->query($sql) !== TRUE) {
        die("创建出差表失败: " . $conn->error);
    }
    
    // 创建管理员账户
    $admin_password_hash = md5($admin_password);
    $sql = "INSERT INTO users (username, password, role) VALUES ('$admin_username', '$admin_password_hash', 'admin')";
    
    if ($conn->query($sql) !== TRUE) {
        die("创建管理员账户失败: " . $conn->error);
    }
    
    // 创建示例员工账户
    $employee_password_hash = md5('123456');
    $sql = "INSERT INTO users (username, password, role, points) VALUES 
            ('张三', '$employee_password_hash', 'employee', 10),
            ('李四', '$employee_password_hash', 'employee', 8),
            ('王五', '$employee_password_hash', 'employee', 12)";
    
    if ($conn->query($sql) !== TRUE) {
        die("创建示例员工账户失败: " . $conn->error);
    }
    
    // 创建数据库配置文件
    $config_content = "<?php
\$db_host = \"$db_host\";
\$db_user = \"$db_user\";
\$db_pass = \"$db_pass\";
\$db_name = \"$db_name\";

function connectDB() {
    global \$db_host, \$db_user, \$db_pass, \$db_name;
    \$conn = new mysqli(\$db_host, \$db_user, \$db_pass, \$db_name);
    
    if (\$conn->connect_error) {
        die(\"连接失败: \" . \$conn->connect_error);
    }
    
    \$conn->set_charset(\"utf8\");
    return \$conn;
}

function closeDB(\$conn) {
    \$conn->close();
}
?>";
    
    file_put_contents('config/database.php', $config_content);
    
    // 创建安装标记文件
    file_put_contents('config/installed.php', '<?php // 安装时间: ' . date('Y-m-d H:i:s') . ' ?>');
    
    // 关闭连接
    $conn->close();
    
    // 重定向到登录页面
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安装 - SZY考勤系统</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-container" style="max-width: 600px;">
            <div class="login-logo">
                <h1>SZY考勤系统安装</h1>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <form method="post" action="">
                        <h3>数据库配置</h3>
                        
                        <div class="form-group">
                            <label for="db_host">数据库主机</label>
                            <input type="text" class="form-control" id="db_host" name="db_host" value="154.84.61.112:3306" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="db_user">数据库用户名</label>
                            <input type="text" class="form-control" id="db_user" name="db_user" value="root" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="db_pass">数据库密码</label>
                            <input type="password" class="form-control" id="db_pass" name="db_pass" value="Szy20130914">
                        </div>
                        
                        <div class="form-group">
                            <label for="db_name">数据库名称</label>
                            <input type="text" class="form-control" id="db_name" name="db_name" value="kq" required>
                        </div>
                        
                        <h3>管理员账户</h3>
                        
                        <div class="form-group">
                            <label for="admin_username">管理员用户名</label>
                            <input type="text" class="form-control" id="admin_username" name="admin_username" value="admin" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_password">管理员密码</label>
                            <input type="password" class="form-control" id="admin_password" name="admin_password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">安装系统</button>
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
</body>
</html>
