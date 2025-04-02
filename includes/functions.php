<?php
session_start();

// 用户登录
function userLogin($username, $password) {
    $conn = connectDB();
    $password_hash = md5($password);
    
    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password_hash'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        closeDB($conn);
        return true;
    }
    
    closeDB($conn);
    return false;
}

// 检查用户是否已登录
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// 检查是否为管理员
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

// 获取当前用户信息
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $conn = connectDB();
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT * FROM users WHERE id = $user_id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        closeDB($conn);
        return $user;
    }
    
    closeDB($conn);
    return null;
}

// 打卡
function clockIn($user_id) {
    $conn = connectDB();
    $today = date('Y-m-d');
    $now = date('H:i:s');
    
    // 检查今天是否已打卡
    $sql = "SELECT * FROM attendance WHERE user_id = $user_id AND date = '$today'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        closeDB($conn);
        return ['status' => false, 'message' => '今天已经打卡'];
    }
    
    // 插入打卡记录
    $sql = "INSERT INTO attendance (user_id, date, clock_in_time) VALUES ($user_id, '$today', '$now')";
    
    if ($conn->query($sql) === TRUE) {
        // 增加积分
        $sql = "UPDATE users SET points = points + 2 WHERE id = $user_id";
        $conn->query($sql);
        
        closeDB($conn);
        return ['status' => true, 'message' => '打卡成功，+2积分'];
    } else {
        closeDB($conn);
        return ['status' => false, 'message' => '打卡失败：' . $conn->error];
    }
}

// 补卡
function makeUpClock($user_id, $date) {
    $conn = connectDB();
    $now = date('H:i:s');
    
    // 检查是否已打卡
    $sql = "SELECT * FROM attendance WHERE user_id = $user_id AND date = '$date'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        closeDB($conn);
        return ['status' => false, 'message' => '该日期已有打卡记录'];
    }
    
    // 检查是否为未来日期
    if (strtotime($date) > strtotime(date('Y-m-d'))) {
        closeDB($conn);
        return ['status' => false, 'message' => '不能为未来日期补卡'];
    }
    
    // 插入补卡记录
    $sql = "INSERT INTO attendance (user_id, date, clock_in_time, is_makeup) VALUES ($user_id, '$date', '$now', 1)";
    
    if ($conn->query($sql) === TRUE) {
        // 减少积分
        $sql = "UPDATE users SET points = points - 1 WHERE id = $user_id";
        $conn->query($sql);
        
        closeDB($conn);
        return ['status' => true, 'message' => '补卡成功，-1积分'];
    } else {
        closeDB($conn);
        return ['status' => false, 'message' => '补卡失败：' . $conn->error];
    }
}

// 管理员补卡
function adminMakeUpClock($user_id, $date) {
    $conn = connectDB();
    $now = date('H:i:s');
    
    // 检查是否已打卡
    $sql = "SELECT * FROM attendance WHERE user_id = $user_id AND date = '$date'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        closeDB($conn);
        return ['status' => false, 'message' => '该日期已有打卡记录'];
    }
    
    // 检查是否为未来日期
    if (strtotime($date) > strtotime(date('Y-m-d'))) {
        closeDB($conn);
        return ['status' => false, 'message' => '不能为未来日期补卡'];
    }
    
    // 插入补卡记录
    $sql = "INSERT INTO attendance (user_id, date, clock_in_time, is_makeup, is_admin_makeup) VALUES ($user_id, '$date', '$now', 1, 1)";
    
    if ($conn->query($sql) === TRUE) {
        closeDB($conn);
        return ['status' => true, 'message' => '管理员补卡成功'];
    } else {
        closeDB($conn);
        return ['status' => false, 'message' => '补卡失败：' . $conn->error];
    }
}

// 获取月度考勤记录
function getMonthlyAttendance($user_id, $year, $month) {
    $conn = connectDB();
    
    $start_date = "$year-$month-01";
    $end_date = date('Y-m-t', strtotime($start_date));
    
    $sql = "SELECT * FROM attendance WHERE user_id = $user_id AND date BETWEEN '$start_date' AND '$end_date' ORDER BY date ASC";
    $result = $conn->query($sql);
    
    $records = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
    }
    
    closeDB($conn);
    return $records;
}

// 获取所有用户
function getAllUsers() {
    $conn = connectDB();
    
    $sql = "SELECT * FROM users ORDER BY id ASC";
    $result = $conn->query($sql);
    
    $users = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    
    closeDB($conn);
    return $users;
}

// 添加用户
function addUser($username, $password, $role) {
    $conn = connectDB();
    
    // 检查用户名是否已存在
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        closeDB($conn);
        return ['status' => false, 'message' => '用户名已存在'];
    }
    
    // 插入用户
    $password_hash = md5($password);
    $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password_hash', '$role')";
    
    if ($conn->query($sql) === TRUE) {
        closeDB($conn);
        return ['status' => true, 'message' => '用户添加成功'];
    } else {
        closeDB($conn);
        return ['status' => false, 'message' => '用户添加失败：' . $conn->error];
    }
}

// 删除用户
function deleteUser($user_id) {
    $conn = connectDB();
    
    $sql = "DELETE FROM users WHERE id = $user_id";
    
    if ($conn->query($sql) === TRUE) {
        closeDB($conn);
        return ['status' => true, 'message' => '用户删除成功'];
    } else {
        closeDB($conn);
        return ['status' => false, 'message' => '用户删除失败：' . $conn->error];
    }
}

// 更新用户信息
function updateUserInfo($user_id, $data) {
    $conn = connectDB();
    
    $updates = [];
    foreach ($data as $key => $value) {
        $updates[] = "$key = '$value'";
    }
    
    $update_str = implode(', ', $updates);
    $sql = "UPDATE users SET $update_str WHERE id = $user_id";
    
    if ($conn->query($sql) === TRUE) {
        closeDB($conn);
        return true;
    } else {
        closeDB($conn);
        return false;
    }
}

// 修改密码
function changePassword($user_id, $old_password, $new_password) {
    $conn = connectDB();
    
    // 验证旧密码
    $old_password_hash = md5($old_password);
    $sql = "SELECT * FROM users WHERE id = $user_id AND password = '$old_password_hash'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 0) {
        closeDB($conn);
        return false;
    }
    
    // 更新密码
    $new_password_hash = md5($new_password);
    $sql = "UPDATE users SET password = '$new_password_hash' WHERE id = $user_id";
    
    if ($conn->query($sql) === TRUE) {
        closeDB($conn);
        return true;
    } else {
        closeDB($conn);
        return false;
    }
}

// 申请请假
function applyLeave($user_id, $start_date, $end_date, $reason, $type) {
    $conn = connectDB();
    
    $sql = "INSERT INTO leaves (user_id, start_date, end_date, reason, type) VALUES ($user_id, '$start_date', '$end_date', '$reason', '$type')";
    
    if ($conn->query($sql) === TRUE) {
        closeDB($conn);
        return ['status' => true, 'message' => '请假申请提交成功'];
    } else {
        closeDB($conn);
        return ['status' => false, 'message' => '请假申请提交失败：' . $conn->error];
    }
}

// 获取请假申请
function getLeaveApplications($user_id = null, $status = null) {
    $conn = connectDB();
    
    $where = [];
    if ($user_id !== null) {
        $where[] = "l.user_id = $user_id";
    }
    
    if ($status !== null) {
        $where[] = "l.status = '$status'";
    }
    
    $where_clause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $sql = "SELECT l.*, u.username FROM leaves l JOIN users u ON l.user_id = u.id $where_clause ORDER BY l.created_at DESC";
    $result = $conn->query($sql);
    
    $leaves = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $leaves[] = $row;
        }
    }
    
    closeDB($conn);
    return $leaves;
}

// 审批请假
function approveLeave($leave_id, $status, $comment) {
    $conn = connectDB();
    
    $sql = "UPDATE leaves SET status = '$status', comment = '$comment', approved_at = NOW() WHERE id = $leave_id";
    
    if ($conn->query($sql) === TRUE) {
        closeDB($conn);
        return ['status' => true, 'message' => '请假审批成功'];
    } else {
        closeDB($conn);
        return ['status' => false, 'message' => '请假审批失败：' . $conn->error];
    }
}

// 申请出差
function applyBusinessTrip($user_id, $start_date, $end_date, $destination, $purpose) {
    $conn = connectDB();
    
    $sql = "INSERT INTO business_trips (user_id, start_date, end_date, destination, purpose) VALUES ($user_id, '$start_date', '$end_date', '$destination', '$purpose')";
    
    if ($conn->query($sql) === TRUE) {
        closeDB($conn);
        return ['status' => true, 'message' => '出差申请提交成功'];
    } else {
        closeDB($conn);
        return ['status' => false, 'message' => '出差申请提交失败：' . $conn->error];
    }
}

// 获取出差申请
function getBusinessTripApplications($user_id = null, $status = null) {
    $conn = connectDB();
    
    $where = [];
    if ($user_id !== null) {
        $where[] = "b.user_id = $user_id";
    }
    
    if ($status !== null) {
        $where[] = "b.status = '$status'";
    }
    
    $where_clause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $sql = "SELECT b.*, u.username FROM business_trips b JOIN users u ON b.user_id = u.id $where_clause ORDER BY b.created_at DESC";
    $result = $conn->query($sql);
    
    $trips = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $trips[] = $row;
        }
    }
    
    closeDB($conn);
    return $trips;
}

// 审批出差
function approveBusinessTrip($trip_id, $status, $comment) {
    $conn = connectDB();
    
    $sql = "UPDATE business_trips SET status = '$status', comment = '$comment', approved_at = NOW() WHERE id = $trip_id";
    
    if ($conn->query($sql) === TRUE) {
        closeDB($conn);
        return ['status' => true, 'message' => '出差审批成功'];
    } else {
        closeDB($conn);
        return ['status' => false, 'message' => '出差审批失败：' . $conn->error];
    }
}

// 获取工作日天数
function getWorkingDays($start_date, $end_date) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start, $interval, $end->modify('+1 day'));
    
    $working_days = 0;
    foreach ($period as $day) {
        $weekday = $day->format('N');
        if ($weekday < 6) { // 1-5 是周一到周五
            $working_days++;
        }
    }
    
    return $working_days;
}
