<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../functions/helpers.php';

class AdminController {
    
    public function getDashboardStats() {
        if (!isLoggedIn() || !hasRole(ROLE_ADMIN)) {
            return [
                'success' => false,
                'message' => 'Bạn không có quyền truy cập.'
            ];
        }
        
        $conn = getDbConnection();
        
        // Thống kê users
        $users_sql = "SELECT 
                COUNT(*) as total_users,
                COUNT(CASE WHEN role = 'user' THEN 1 END) as total_customers,
                COUNT(CASE WHEN role = 'host' THEN 1 END) as total_hosts,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users
            FROM users";
        $users_stats = fetchOne($conn, $users_sql);
        
        // Thống kê homestays
        $homestays_sql = "SELECT 
                COUNT(*) as total_homestays,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_homestays,
                COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive_homestays
            FROM homestays";
        $homestays_stats = fetchOne($conn, $homestays_sql);
        
        // Thống kê bookings
        $bookings_sql = "SELECT 
                COUNT(*) as total_bookings,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_bookings,
                COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_bookings,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_bookings,
                SUM(CASE WHEN status = 'completed' THEN total_price ELSE 0 END) as total_revenue
            FROM bookings";
        $bookings_stats = fetchOne($conn, $bookings_sql);
        
        // Bookings mới nhất
        $recent_bookings_sql = "SELECT b.*, u.full_name as user_name, h.title as homestay_title
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                JOIN homestays h ON b.homestay_id = h.id
                ORDER BY b.created_at DESC LIMIT 5";
        $recent_bookings = fetchAll($conn, $recent_bookings_sql);
        
        closeDbConnection($conn);
        
        return [
            'success' => true,
            'data' => [
                'users' => $users_stats,
                'homestays' => $homestays_stats,
                'bookings' => $bookings_stats,
                'recent_bookings' => $recent_bookings
            ]
        ];
    }
    
    public function getAllHomestays() {
        if (!isLoggedIn() || !hasRole(ROLE_ADMIN)) {
            return [
                'success' => false,
                'message' => 'Bạn không có quyền truy cập.'
            ];
        }
        
        $conn = getDbConnection();
        $sql = "SELECT h.*, u.full_name as host_name, u.email as host_email
                FROM homestays h
                LEFT JOIN users u ON h.host_id = u.id
                ORDER BY h.created_at DESC";
        $homestays = fetchAll($conn, $sql);
        closeDbConnection($conn);
        
        // Parse JSON fields
        foreach ($homestays as &$homestay) {
            $homestay['amenities'] = json_decode($homestay['amenities'], true) ?: [];
            $homestay['images'] = json_decode($homestay['images'], true) ?: [];
        }
        
        return [
            'success' => true,
            'data' => $homestays
        ];
    }
    
    public function updateHomestayStatus() {
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
        
        $homestay_id = intval($_POST['homestay_id']);
        $status = sanitizeInput($_POST['status']);
        
        $conn = getDbConnection();
        $sql = "UPDATE homestays SET status = ? WHERE id = ?";
        $result = executeUpdate($conn, $sql, [$status, $homestay_id], 'si');
        closeDbConnection($conn);
        
        if ($result) {
            $status_messages = [
                'active' => 'Kích hoạt homestay thành công!',
                'inactive' => 'Ẩn homestay thành công!',
                'blocked' => 'Khóa homestay thành công!'
            ];
            
            $message = $status_messages[$status] ?? 'Cập nhật trạng thái thành công!';
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
    
    public function getAllBookings() {
        if (!isLoggedIn() || !hasRole(ROLE_ADMIN)) {
            return [
                'success' => false,
                'message' => 'Bạn không có quyền truy cập.'
            ];
        }
        
        $conn = getDbConnection();
        $sql = "SELECT b.*, u.full_name as user_name, h.title as homestay_title, 
                       host.full_name as host_name
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                JOIN homestays h ON b.homestay_id = h.id
                JOIN users host ON h.host_id = host.id
                ORDER BY b.created_at DESC";
        $bookings = fetchAll($conn, $sql);
        closeDbConnection($conn);
        
        return [
            'success' => true,
            'data' => $bookings
        ];
    }
    
    public function getRevenueReport($start_date = null, $end_date = null) {
        if (!isLoggedIn() || !hasRole(ROLE_ADMIN)) {
            return [
                'success' => false,
                'message' => 'Bạn không có quyền truy cập.'
            ];
        }
        
        $conn = getDbConnection();
        
        $sql = "SELECT 
                DATE(b.created_at) as date,
                COUNT(*) as total_bookings,
                SUM(b.total_price) as daily_revenue,
                AVG(b.total_price) as avg_booking_value
            FROM bookings b
            WHERE b.status = 'completed'";
        
        $params = [];
        $types = "";
        
        if ($start_date) {
            $sql .= " AND b.created_at >= ?";
            $params[] = $start_date;
            $types .= "s";
        }
        
        if ($end_date) {
            $sql .= " AND b.created_at <= ?";
            $params[] = $end_date;
            $types .= "s";
        }
        
        $sql .= " GROUP BY DATE(b.created_at) ORDER BY date DESC";
        
        $revenue_data = fetchAll($conn, $sql, $params, $types);
        closeDbConnection($conn);
        
        return [
            'success' => true,
            'data' => $revenue_data
        ];
    }
}
?>