<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/auth_functions.php';
require_once __DIR__ . '/../functions/helpers.php';

class UserController {
    
    public function getProfile() {
        if (!isLoggedIn()) {
            return [
                'success' => false,
                'message' => 'Vui lòng đăng nhập.'
            ];
        }
        
        $user_id = $_SESSION['user_id'];
        $user = getUserById($user_id);
        
        if ($user) {
            return [
                'success' => true,
                'data' => $user
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Không tìm thấy thông tin user.'
            ];
        }
    }
    
    public function getAllUsers() {
        // Chỉ admin mới được xem tất cả users
        if (!isLoggedIn() || !hasRole(ROLE_ADMIN)) {
            return [
                'success' => false,
                'message' => 'Bạn không có quyền truy cập.'
            ];
        }
        
        $conn = getDbConnection();
        $sql = "SELECT id, full_name, email, phone, role, status, created_at 
                FROM users ORDER BY created_at DESC";
        $users = fetchAll($conn, $sql);
        closeDbConnection($conn);
        
        return [
            'success' => true,
            'data' => $users
        ];
    }
    
    public function updateUserStatus() {
        if (!isLoggedIn() || !hasRole(ROLE_ADMIN)) {
            return [
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện hành động này.'
            ];
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'success' => false,
                'message' => 'Phương thức không hợp lệ.'
            ];
        }
        
        $user_id = intval($_POST['user_id']);
        $status = sanitizeInput($_POST['status']);
        
        $conn = getDbConnection();
        $sql = "UPDATE users SET status = ? WHERE id = ?";
        $result = executeUpdate($conn, $sql, [$status, $user_id], 'si');
        closeDbConnection($conn);
        
        if ($result) {
            $message = $status === 'active' ? 'Kích hoạt user thành công!' : 'Khóa user thành công!';
            setFlashMessage(MSG_SUCCESS, $message);
            return [
                'success' => true,
                'message' => $message
            ];
        } else {
            setFlashMessage(MSG_ERROR, 'Cập nhật trạng thái thất bại.');
            return [
                'success' => false,
                'message' => 'Cập nhật trạng thái thất bại.'
            ];
        }
    }
    
    public function getUsersByRole($role) {
        if (!isLoggedIn() || !hasRole(ROLE_ADMIN)) {
            return [
                'success' => false,
                'message' => 'Bạn không có quyền truy cập.'
            ];
        }
        
        $conn = getDbConnection();
        $sql = "SELECT id, full_name, email, phone, status, created_at 
                FROM users WHERE role = ? ORDER BY created_at DESC";
        $users = fetchAll($conn, $sql, [$role], 's');
        closeDbConnection($conn);
        
        return [
            'success' => true,
            'data' => $users
        ];
    }
}
?>