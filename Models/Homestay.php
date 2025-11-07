<?php
require_once __DIR__ . '/../config/db_connection.php';

class Homestay {
    private $conn;
    
    public function __construct() {
        $this->conn = getDbConnection();
    }
    
    public function __destruct() {
        closeDbConnection($this->conn);
    }
    
    // Tạo homestay mới
    public function create($homestay_data) {
        $sql = "INSERT INTO homestays (host_id, title, description, address, price_per_night, 
                max_guests, bedrooms, bathrooms, amenities, images, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        return insertQuery($this->conn, $sql, [
            $homestay_data['host_id'],
            $homestay_data['title'],
            $homestay_data['description'],
            $homestay_data['address'],
            $homestay_data['price_per_night'],
            $homestay_data['max_guests'],
            $homestay_data['bedrooms'],
            $homestay_data['bathrooms'],
            $homestay_data['amenities'],
            $homestay_data['images'],
            $homestay_data['status']
        ], 'isssdiiisss');
    }
    
    // Lấy homestay by ID
    public function getById($homestay_id) {
        $sql = "SELECT h.*, u.full_name as host_name, u.phone as host_phone, u.email as host_email
                FROM homestays h 
                LEFT JOIN users u ON h.host_id = u.id 
                WHERE h.id = ?";
        
        $homestay = fetchOne($this->conn, $sql, [$homestay_id], 'i');
        
        if ($homestay) {
            // Parse JSON fields
            $homestay['amenities'] = json_decode($homestay['amenities'], true) ?: [];
            $homestay['images'] = json_decode($homestay['images'], true) ?: [];
        }
        
        return $homestay;
    }
    
    // Cập nhật homestay
    public function update($homestay_id, $homestay_data) {
        $sql = "UPDATE homestays SET title = ?, description = ?, address = ?, 
                price_per_night = ?, max_guests = ?, bedrooms = ?, bathrooms = ?, 
                amenities = ?, images = ? WHERE id = ?";
        
        return executeUpdate($this->conn, $sql, [
            $homestay_data['title'],
            $homestay_data['description'],
            $homestay_data['address'],
            $homestay_data['price_per_night'],
            $homestay_data['max_guests'],
            $homestay_data['bedrooms'],
            $homestay_data['bathrooms'],
            $homestay_data['amenities'],
            $homestay_data['images'],
            $homestay_id
        ], 'sssdiiissi');
    }
    
    // Cập nhật trạng thái homestay
    public function updateStatus($homestay_id, $status) {
        $sql = "UPDATE homestays SET status = ? WHERE id = ?";
        return executeUpdate($this->conn, $sql, [$status, $homestay_id], 'si');
    }
    
    // Xóa homestay
    public function delete($homestay_id) {
        $sql = "DELETE FROM homestays WHERE id = ?";
        return executeUpdate($this->conn, $sql, [$homestay_id], 'i');
    }
    
    // Tìm kiếm homestays
    public function search($filters = []) {
        $sql = "SELECT h.*, u.full_name as host_name 
                FROM homestays h 
                LEFT JOIN users u ON h.host_id = u.id 
                WHERE h.status = 'active'";
        
        $params = [];
        $types = "";
        
        // Lọc theo địa điểm
        if (!empty($filters['location'])) {
            $sql .= " AND (h.address LIKE ? OR h.title LIKE ?)";
            $location = "%{$filters['location']}%";
            $params[] = $location;
            $params[] = $location;
            $types .= "ss";
        }
        
        // Lọc theo giá
        if (!empty($filters['min_price'])) {
            $sql .= " AND h.price_per_night >= ?";
            $params[] = floatval($filters['min_price']);
            $types .= "d";
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND h.price_per_night <= ?";
            $params[] = floatval($filters['max_price']);
            $types .= "d";
        }
        
        // Lọc theo số khách
        if (!empty($filters['guests'])) {
            $sql .= " AND h.max_guests >= ?";
            $params[] = intval($filters['guests']);
            $types .= "i";
        }
        
        // Lọc theo số phòng ngủ
        if (!empty($filters['bedrooms'])) {
            $sql .= " AND h.bedrooms >= ?";
            $params[] = intval($filters['bedrooms']);
            $types .= "i";
        }
        
        // Lọc theo tiện nghi
        if (!empty($filters['amenities'])) {
            $amenities = $filters['amenities'];
            if (is_array($amenities)) {
                foreach ($amenities as $amenity) {
                    $sql .= " AND JSON_CONTAINS(h.amenities, ?)";
                    $params[] = json_encode($amenity);
                    $types .= "s";
                }
            }
        }
        
        // Sắp xếp
        $sort = $filters['sort'] ?? 'created_at';
        $order = $filters['order'] ?? 'DESC';
        $sql .= " ORDER BY h.{$sort} {$order}";
        
        // Phân trang
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
            $types .= "i";
        }
        
        $homestays = fetchAll($this->conn, $sql, $params, $types);
        
        // Parse JSON fields
        foreach ($homestays as &$homestay) {
            $homestay['amenities'] = json_decode($homestay['amenities'], true) ?: [];
            $homestay['images'] = json_decode($homestay['images'], true) ?: [];
        }
        
        return $homestays;
    }
    
    
    // Lấy homestays của host
    public function getByHost($host_id, $filters = []) {
        $sql = "SELECT * FROM homestays WHERE host_id = ?";
        
        $params = [$host_id];
        $types = "i";
        
        // Lọc theo status
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        // Phân trang
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
            $types .= "i";
        }
        
        $homestays = fetchAll($this->conn, $sql, $params, $types);
        
        // Parse JSON fields
        foreach ($homestays as &$homestay) {
            $homestay['amenities'] = json_decode($homestay['amenities'], true) ?: [];
            $homestay['images'] = json_decode($homestay['images'], true) ?: [];
        }
        
        return $homestays;
    }
    
    // Lấy tất cả homestays (cho admin)
    public function getAll($filters = []) {
        $sql = "SELECT h.*, u.full_name as host_name, u.email as host_email
                FROM homestays h
                LEFT JOIN users u ON h.host_id = u.id
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // Lọc theo status
        if (!empty($filters['status'])) {
            $sql .= " AND h.status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }
        
        // Lọc theo host
        if (!empty($filters['host_id'])) {
            $sql .= " AND h.host_id = ?";
            $params[] = $filters['host_id'];
            $types .= "i";
        }
        
        // Tìm kiếm
        if (!empty($filters['search'])) {
            $sql .= " AND (h.title LIKE ? OR h.address LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
            $types .= "ss";
        }
        
        $sql .= " ORDER BY h.created_at DESC";
        
        // Phân trang
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
            $types .= "i";
        }
        
        $homestays = fetchAll($this->conn, $sql, $params, $types);
        
        // Parse JSON fields
        foreach ($homestays as &$homestay) {
            $homestay['amenities'] = json_decode($homestay['amenities'], true) ?: [];
            $homestay['images'] = json_decode($homestay['images'], true) ?: [];
        }
        
        return $homestays;
    }
    
    // Đếm tổng số homestays
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM homestays WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }
        
        if (!empty($filters['host_id'])) {
            $sql .= " AND host_id = ?";
            $params[] = $filters['host_id'];
            $types .= "i";
        }
        
        $result = fetchOne($this->conn, $sql, $params, $types);
        return $result ? $result['total'] : 0;
    }
    
    // Lấy homestays phổ biến (nhiều booking nhất)
    public function getPopular($limit = 6) {
        $sql = "SELECT h.*, u.full_name as host_name,
                COUNT(b.id) as booking_count
                FROM homestays h
                LEFT JOIN users u ON h.host_id = u.id
                LEFT JOIN bookings b ON h.id = b.homestay_id
                WHERE h.status = 'active'
                GROUP BY h.id
                ORDER BY booking_count DESC, h.created_at DESC
                LIMIT ?";
        
        $homestays = fetchAll($this->conn, $sql, [$limit], 'i');
        
        // Parse JSON fields
        foreach ($homestays as &$homestay) {
            $homestay['amenities'] = json_decode($homestay['amenities'], true) ?: [];
            $homestay['images'] = json_decode($homestay['images'], true) ?: [];
        }
        
        return $homestays;
    }
    
    // Kiểm tra homestay có available trong khoảng thời gian không
    public function isAvailable($homestay_id, $check_in, $check_out) {
        $sql = "SELECT id FROM bookings 
                WHERE homestay_id = ? 
                AND status IN ('pending', 'confirmed')
                AND ((check_in BETWEEN ? AND ?) OR (check_out BETWEEN ? AND ?))";
        
        $conflict = fetchOne($this->conn, $sql, [
            $homestay_id,
            $check_in, $check_out,
            $check_in, $check_out
        ], 'issss');
        
        return empty($conflict);
    }
}
?>