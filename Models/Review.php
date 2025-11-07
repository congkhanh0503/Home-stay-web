<?php
require_once __DIR__ . '/../config/db_connection.php';

class Review {
    private $conn;
    
    public function __construct() {
        $this->conn = getDbConnection();
    }
    
    public function __destruct() {
        closeDbConnection($this->conn);
    }
    
    // Tạo review mới
    public function create($review_data) {
        $sql = "INSERT INTO reviews (booking_id, user_id, homestay_id, rating, comment, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        return insertQuery($this->conn, $sql, [
            $review_data['booking_id'],
            $review_data['user_id'],
            $review_data['homestay_id'],
            $review_data['rating'],
            $review_data['comment']
        ], 'iiiis');
    }
    
    // Lấy review by ID
    public function getById($review_id) {
        $sql = "SELECT r.*, u.full_name as user_name, u.avatar as user_avatar
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                WHERE r.id = ?";
        
        return fetchOne($this->conn, $sql, [$review_id], 'i');
    }
    
    // Lấy reviews của homestay
    public function getByHomestay($homestay_id, $filters = []) {
        $sql = "SELECT r.*, u.full_name as user_name, u.avatar as user_avatar
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                WHERE r.homestay_id = ?";
        
        $params = [$homestay_id];
        $types = "i";
        
        // Lọc theo rating
        if (!empty($filters['rating'])) {
            $sql .= " AND r.rating = ?";
            $params[] = $filters['rating'];
            $types .= "i";
        }
        
        $sql .= " ORDER BY r.created_at DESC";
        
        // Phân trang
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
            $types .= "i";
        }
        
        return fetchAll($this->conn, $sql, $params, $types);
    }
    
    // Lấy reviews của user
    public function getByUser($user_id, $filters = []) {
        $sql = "SELECT r.*, h.title as homestay_title, h.images as homestay_images
                FROM reviews r
                JOIN homestays h ON r.homestay_id = h.id
                WHERE r.user_id = ?";
        
        $params = [$user_id];
        $types = "i";
        
        $sql .= " ORDER BY r.created_at DESC";
        
        // Phân trang
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
            $types .= "i";
        }
        
        return fetchAll($this->conn, $sql, $params, $types);
    }
    
    // Cập nhật review
    public function update($review_id, $review_data) {
        $sql = "UPDATE reviews SET rating = ?, comment = ? WHERE id = ?";
        
        return executeUpdate($this->conn, $sql, [
            $review_data['rating'],
            $review_data['comment'],
            $review_id
        ], 'isi');
    }
    
    // Xóa review
    public function delete($review_id) {
        $sql = "DELETE FROM reviews WHERE id = ?";
        return executeUpdate($this->conn, $sql, [$review_id], 'i');
    }
    
    // Tính rating trung bình của homestay
    public function getAverageRating($homestay_id) {
        $sql = "SELECT 
                AVG(rating) as average_rating,
                COUNT(*) as total_reviews,
                COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
                COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
                COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
                COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
                COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
            FROM reviews 
            WHERE homestay_id = ?";
        
        return fetchOne($this->conn, $sql, [$homestay_id], 'i');
    }
    
    // Kiểm tra user đã review booking này chưa
    public function userHasReviewed($booking_id, $user_id) {
        $sql = "SELECT id FROM reviews WHERE booking_id = ? AND user_id = ?";
        $result = fetchOne($this->conn, $sql, [$booking_id, $user_id], 'ii');
        return !empty($result);
    }
    
    // Kiểm tra user đã từng ở homestay này chưa (để review)
    public function userHasStayed($homestay_id, $user_id) {
        $sql = "SELECT id FROM bookings 
                WHERE homestay_id = ? AND user_id = ? AND status = 'completed'";
        $result = fetchOne($this->conn, $sql, [$homestay_id, $user_id], 'ii');
        return !empty($result);
    }
    
    // Lấy tất cả reviews (cho admin)
    public function getAll($filters = []) {
        $sql = "SELECT r.*, u.full_name as user_name, h.title as homestay_title
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                JOIN homestays h ON r.homestay_id = h.id
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // Lọc theo rating
        if (!empty($filters['rating'])) {
            $sql .= " AND r.rating = ?";
            $params[] = $filters['rating'];
            $types .= "i";
        }
        
        // Lọc theo homestay
        if (!empty($filters['homestay_id'])) {
            $sql .= " AND r.homestay_id = ?";
            $params[] = $filters['homestay_id'];
            $types .= "i";
        }
        
        // Tìm kiếm
        if (!empty($filters['search'])) {
            $sql .= " AND (u.full_name LIKE ? OR h.title LIKE ? OR r.comment LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= "sss";
        }
        
        $sql .= " ORDER BY r.created_at DESC";
        
        // Phân trang
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
            $types .= "i";
        }
        
        return fetchAll($this->conn, $sql, $params, $types);
    }
    
    // Đếm tổng số reviews
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM reviews WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if (!empty($filters['homestay_id'])) {
            $sql .= " AND homestay_id = ?";
            $params[] = $filters['homestay_id'];
            $types .= "i";
        }
        
        if (!empty($filters['rating'])) {
            $sql .= " AND rating = ?";
            $params[] = $filters['rating'];
            $types .= "i";
        }
        
        $result = fetchOne($this->conn, $sql, $params, $types);
        return $result ? $result['total'] : 0;
    }
    
    // Lấy reviews gần đây
    public function getRecent($limit = 10) {
        $sql = "SELECT r.*, u.full_name as user_name, u.avatar as user_avatar,
                       h.title as homestay_title
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                JOIN homestays h ON r.homestay_id = h.id
                ORDER BY r.created_at DESC
                LIMIT ?";
        
        return fetchAll($this->conn, $sql, [$limit], 'i');
    }
}
?>