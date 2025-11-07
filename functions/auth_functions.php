<?php
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/helpers.php';

// Hàm đăng ký user
function registerUser($user_data) {
    $conn = getDbConnection();
    
    // Kiểm tra email đã tồn tại chưa
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $existing_user = fetchOne($conn, $check_sql, [$user_data['email']], 's');
    
    if ($existing_user) {
        closeDbConnection($conn);
        return [
            'success' => false,
            'message' => 'Email đã được sử dụng. Vui lòng chọn email khác.'
        ];
    }
    
    // Mã hóa mật khẩu
    $hashed_password = password_hash($user_data['password'], PASSWORD_DEFAULT);
    
    // Thêm user mới
    $sql = "INSERT INTO users (full_name, email, password, phone, role, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    
    $user_id = insertQuery($conn, $sql, [
        $user_data['full_name'],
        $user_data['email'],
        $hashed_password,
        $user_data['phone'],
        $user_data['role']
    ], 'sssss');
    
    closeDbConnection($conn);
    
    if ($user_id) {
        return [
            'success' => true,
            'user_id' => $user_id,
            'message' => 'Đăng ký thành công!'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Có lỗi xảy ra khi đăng ký. Vui lòng thử lại.'
        ];
    }
}

// Hàm đăng nhập
function loginUser($email, $password) {
    $conn = getDbConnection();
    
    $sql = "SELECT id, full_name, email, password, role, status FROM users WHERE email = ?";
    $user = fetchOne($conn, $sql, [$email], 's');
    
    closeDbConnection($conn);
    
    if ($user) {
        // Kiểm tra mật khẩu
        if (password_verify($password, $user['password'])) {
            // Kiểm tra trạng thái tài khoản
            if ($user['status'] == 'active') {
                // Lưu thông tin user vào session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                return [
                    'success' => true,
                    'message' => 'Đăng nhập thành công!'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.'
                ];
            }
        }
    }
    
    return [
        'success' => false,
        'message' => 'Email hoặc mật khẩu không đúng.'
    ];
}

// Hàm đăng xuất
function logoutUser() {
    // Xóa tất cả session
    session_unset();
    session_destroy();
    
    return [
        'success' => true,
        'message' => 'Đăng xuất thành công!'
    ];
}

// Hàm lấy thông tin user
function getUserById($user_id) {
    $conn = getDbConnection();
    
    $sql = "SELECT id, full_name, email, phone, role, avatar, status, created_at 
            FROM users WHERE id = ?";
    $user = fetchOne($conn, $sql, [$user_id], 'i');
    
    closeDbConnection($conn);
    return $user;
}

// Hàm cập nhật thông tin user
function updateUserProfile($user_id, $data) {
    $conn = getDbConnection();
    
    $sql = "UPDATE users SET full_name = ?, phone = ?, avatar = ? WHERE id = ?";
    $result = executeUpdate($conn, $sql, [
        $data['full_name'],
        $data['phone'],
        $data['avatar'],
        $user_id
    ], 'sssi');
    
    closeDbConnection($conn);
    
    if ($result) {
        // Cập nhật session
        $_SESSION['user_name'] = $data['full_name'];
        
        return [
            'success' => true,
            'message' => 'Cập nhật thông tin thành công!'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Có lỗi xảy ra khi cập nhật thông tin.'
        ];
    }
}

// Hàm đổi mật khẩu
function changePassword($user_id, $current_password, $new_password) {
    $conn = getDbConnection();
    
    // Lấy mật khẩu hiện tại
    $sql = "SELECT password FROM users WHERE id = ?";
    $user = fetchOne($conn, $sql, [$user_id], 'i');
    
    if (!$user || !password_verify($current_password, $user['password'])) {
        closeDbConnection($conn);
        return [
            'success' => false,
            'message' => 'Mật khẩu hiện tại không đúng.'
        ];
    }
    
    // Cập nhật mật khẩu mới
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET password = ? WHERE id = ?";
    $result = executeUpdate($conn, $sql, [$hashed_password, $user_id], 'si');
    
    closeDbConnection($conn);
    
    if ($result) {
        return [
            'success' => true,
            'message' => 'Đổi mật khẩu thành công!'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Có lỗi xảy ra khi đổi mật khẩu.'
        ];
    }
}
?>