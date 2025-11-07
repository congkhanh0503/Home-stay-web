<?php
require_once __DIR__ . '/../config/db_connection.php';

class Booking {
    private $conn;
    
    public function __construct() {
        $this->conn = getDbConnection();
    }
    
    public function __destruct() {
        closeDbConnection($this->conn);
    }
    
    // Tạo booking mới
    public function create($booking_data) {
        $sql = "INSERT INTO bookings (user_id, homestay_id, check_in, check_out, guests, 
                total_price, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";
        
        return insertQuery($this->conn, $sql, [
            $booking_data['user_id'],
            $booking_data['homestay_id'],
            $booking_data['check_in'],
            $booking_data['check_out'],
            $booking_data['guests'],
            $booking_data['total_price']
        ], 'iissid');
    }
    
    // Lấy booking by ID
    public function getById($booking_id) {
        $sql = "SELECT b.*, h.title, h.images, h.address, h.price_per_night, 
                       u.full_name as user_name, u.phone as user_phone, u.email as user_email,
                       host.full_name as host_name, host.phone as host_phone, host.email as host_email
                FROM bookings b
                JOIN homestays h ON b.homestay_id = h.id
                JOIN users u ON b.user_id = u.id
                JOIN users host ON h.host_id = host.id
                WHERE b.id = ?";
        
        return fetchOne($this->conn, $sql, [$booking_id], 'i');
    }
    
    // Cập nhật trạng thái booking
    public function updateStatus($booking_id, $status) {
        $sql = "UPDATE bookings SET status = ? WHERE id = ?";
        return executeUpdate($this->conn, $sql, [$status, $booking_id], 'si');
    }
    
    // Lấy bookings của user
    public function getByUser($user_id, $filters = []) {
        $sql = "SELECT b.*, h.title, h.images, h.address, u.full_name as host_name
                FROM bookings b
                JOIN homestays h ON b.homestay_id = h.id
                JOIN users u ON h.host_id = u.id
                WHERE b.user_id = ?";
        
        $params = [$user_id];
        $types = "i";
        
        // Lọc theo status
        if (!empty($filters['status'])) {
            $sql .= " AND b.status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }
        
        // Lọc theo khoảng thời gian
        if (!empty($filters['start_date'])) {
            $sql .= " AND b.check_in >= ?";
            $params[] = $filters['start_date'];
            $types .= "s";
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND b.check_out <= ?";
            $params[] = $filters['end_date'];
            $types .= "s";
        }
        
        $sql .= " ORDER BY b.created_at DESC";
        
        // Phân trang
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
            $types .= "i";
        }
        
        return fetchAll($this->conn, $sql, $params, $types);
    }
    
    // Lấy bookings của host
    public function getByHost($host_id, $filters = []) {
    $sql = "SELECT b.*, h.title as homestay_title, u.full_name as user_name, 
                   u.email as user_email, u.phone as user_phone
            FROM bookings b
            JOIN homestays h ON b.homestay_id = h.id
            JOIN users u ON b.user_id = u.id
            WHERE h.host_id = ?";
    
    $params = [$host_id];
    $types = "i";
    
    // Lọc theo status
    if (!empty($filters['status'])) {
        $sql .= " AND b.status = ?";
        $params[] = $filters['status'];
        $types .= "s";
    }
    
    // Lọc theo tháng
    if (!empty($filters['month'])) {
        $sql .= " AND MONTH(b.check_in) = MONTH(?) AND YEAR(b.check_in) = YEAR(?)";
        $params[] = $filters['month'];
        $params[] = $filters['month'];
        $types .= "ss";
    }
    
    // Lọc theo năm
    if (!empty($filters['year'])) {
        $sql .= " AND YEAR(b.check_in) = ?";
        $params[] = $filters['year'];
        $types .= "s";
    }
    
    $sql .= " ORDER BY b.created_at DESC";
    
    return fetchAll($this->conn, $sql, $params, $types);
}
    public function delete($booking_id) {
    $sql = "DELETE FROM bookings WHERE id = ?";
    return executeUpdate($this->conn, $sql, [$booking_id], 'i');
}
    // Lấy tất cả bookings (cho admin)
    public function getAll($filters = []) {
        $sql = "SELECT b.*, h.title as homestay_title, u.full_name as user_name, 
                       host.full_name as host_name
                FROM bookings b
                JOIN homestays h ON b.homestay_id = h.id
                JOIN users u ON b.user_id = u.id
                JOIN users host ON h.host_id = host.id
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // Lọc theo status
        if (!empty($filters['status'])) {
            $sql .= " AND b.status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }
        
        // Lọc theo user
        if (!empty($filters['user_id'])) {
            $sql .= " AND b.user_id = ?";
            $params[] = $filters['user_id'];
            $types .= "i";
        }
        
        // Lọc theo host
        if (!empty($filters['host_id'])) {
            $sql .= " AND h.host_id = ?";
            $params[] = $filters['host_id'];
            $types .= "i";
        }
        
        // Lọc theo khoảng thời gian
        if (!empty($filters['start_date'])) {
            $sql .= " AND b.check_in >= ?";
            $params[] = $filters['start_date'];
            $types .= "s";
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND b.check_out <= ?";
            $params[] = $filters['end_date'];
            $types .= "s";
        }
        
        $sql .= " ORDER BY b.created_at DESC";
        
        // Phân trang
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
            $types .= "i";
        }
        
        return fetchAll($this->conn, $sql, $params, $types);
    }
    
    // Đếm tổng số bookings
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM bookings WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = ?";
            $params[] = $filters['user_id'];
            $types .= "i";
        }
        
        $result = fetchOne($this->conn, $sql, $params, $types);
        return $result ? $result['total'] : 0;
    }
    
    // Thống kê revenue
    public function getRevenueStats($filters = []) {
        $sql = "SELECT 
                COUNT(*) as total_bookings,
                SUM(total_price) as total_revenue,
                AVG(total_price) as avg_booking_value,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_bookings,
                SUM(CASE WHEN status = 'completed' THEN total_price ELSE 0 END) as completed_revenue
            FROM bookings WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // Lọc theo khoảng thời gian
        if (!empty($filters['start_date'])) {
            $sql .= " AND created_at >= ?";
            $params[] = $filters['start_date'];
            $types .= "s";
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND created_at <= ?";
            $params[] = $filters['end_date'];
            $types .= "s";
        }
        
        return fetchOne($this->conn, $sql, $params, $types);
    }
    
    // Lấy bookings sắp hết hạn (cho reminder)
    public function getUpcomingBookings($days = 7) {
        $sql = "SELECT b.*, u.full_name as user_name, u.email as user_email,
                       h.title as homestay_title, host.full_name as host_name
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                JOIN homestays h ON b.homestay_id = h.id
                JOIN users host ON h.host_id = host.id
                WHERE b.status = 'confirmed'
                AND b.check_in BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                ORDER BY b.check_in ASC";
        
        return fetchAll($this->conn, $sql, [$days], 'i');
    }
    
    // Cập nhật booking expired
    public function updateExpiredBookings() {
        $sql = "UPDATE bookings 
                SET status = 'expired' 
                WHERE status = 'pending' 
                AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        
        return executeUpdate($this->conn, $sql);
    }
}
?>