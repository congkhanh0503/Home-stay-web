<?php
require_once __DIR__ . '/../config/db_connection.php';

class User {
    private $conn;
    
    public function __construct() {
        $this->conn = getDbConnection();
    }
    
    public function __destruct() {
        closeDbConnection($this->conn);
    }
    
    // Lấy user by ID
    public function getById($user_id) {
        $sql = "SELECT id, full_name, email, phone, role, avatar, status, created_at 
                FROM users WHERE id = ?";
        return fetchOne($this->conn, $sql, [$user_id], 'i');
    }
    
    // Lấy user by email
    public function getByEmail($email) {
        $sql = "SELECT id, full_name, email, password, phone, role, avatar, status, created_at 
                FROM users WHERE email = ?";
        return fetchOne($this->conn, $sql, [$email], 's');
    }
    
    // Tạo user mới
    public function create($user_data) {
        $sql = "INSERT INTO users (full_name, email, password, phone, role, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        return insertQuery($this->conn, $sql, [
            $user_data['full_name'],
            $user_data['email'],
            $user_data['password'],
            $user_data['phone'],
            $user_data['role']
        ], 'sssss');
    }
    
    // Cập nhật thông tin user
    public function update($user_id, $user_data) {
        $sql = "UPDATE users SET full_name = ?, phone = ?, avatar = ? WHERE id = ?";
        
        return executeUpdate($this->conn, $sql, [
            $user_data['full_name'],
            $user_data['phone'],
            $user_data['avatar'],
            $user_id
        ], 'sssi');
    }
    
    // Đổi mật khẩu
    public function updatePassword($user_id, $hashed_password) {
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        return executeUpdate($this->conn, $sql, [$hashed_password, $user_id], 'si');
    }
    
    // Cập nhật trạng thái user
    public function updateStatus($user_id, $status) {
        $sql = "UPDATE users SET status = ? WHERE id = ?";
        return executeUpdate($this->conn, $sql, [$status, $user_id], 'si');
    }
    
    // Lấy tất cả users (cho admin)
    public function getAll($filters = []) {
        $sql = "SELECT id, full_name, email, phone, role, avatar, status, created_at 
                FROM users WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // Lọc theo role
        if (!empty($filters['role'])) {
            $sql .= " AND role = ?";
            $params[] = $filters['role'];
            $types .= "s";
        }
        
        // Lọc theo status
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }
        
        // Tìm kiếm theo tên hoặc email
        if (!empty($filters['search'])) {
            $sql .= " AND (full_name LIKE ? OR email LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
            $types .= "ss";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        // Phân trang
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
            $types .= "i";
        }
        
        return fetchAll($this->conn, $sql, $params, $types);
    }
    
    // Đếm tổng số users
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM users WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if (!empty($filters['role'])) {
            $sql .= " AND role = ?";
            $params[] = $filters['role'];
            $types .= "s";
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }
        
        $result = fetchOne($this->conn, $sql, $params, $types);
        return $result ? $result['total'] : 0;
    }
    
    // Kiểm tra email đã tồn tại chưa
    public function emailExists($email, $exclude_user_id = null) {
        $sql = "SELECT id FROM users WHERE email = ?";
        $params = [$email];
        $types = "s";
        
        if ($exclude_user_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_user_id;
            $types .= "i";
        }
        
        $result = fetchOne($this->conn, $sql, $params, $types);
        return !empty($result);
    }
    
    // Lấy users theo role
    public function getByRole($role, $limit = null) {
        $sql = "SELECT id, full_name, email, phone, avatar, status, created_at 
                FROM users WHERE role = ? ORDER BY created_at DESC";
        
        $params = [$role];
        $types = "s";
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
            $types .= "i";
        }
        
        return fetchAll($this->conn, $sql, $params, $types);
    }
}
?>