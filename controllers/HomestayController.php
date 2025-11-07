<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/upload_functions.php';

class HomestayController {
    
    public function create() {
        if (!isLoggedIn() || !hasRole(ROLE_HOST)) {
            return [
                'success' => false,
                'message' => 'Chỉ chủ homestay mới có thể tạo homestay mới.'
            ];
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'success' => false,
                'message' => 'Phương thức không hợp lệ.'
            ];
        }
        
        // Lấy dữ liệu
        $title = sanitizeInput($_POST['title']);
        $description = sanitizeInput($_POST['description']);
        $address = sanitizeInput($_POST['address']);
        $price_per_night = floatval($_POST['price_per_night']);
        $max_guests = intval($_POST['max_guests']);
        $bedrooms = intval($_POST['bedrooms']);
        $bathrooms = intval($_POST['bathrooms']);
        $amenities = isset($_POST['amenities']) ? json_encode($_POST['amenities']) : '[]';
        
        // Validate
        $errors = [];
        if (empty($title)) $errors[] = 'Tiêu đề không được để trống.';
        if (empty($description)) $errors[] = 'Mô tả không được để trống.';
        if (empty($address)) $errors[] = 'Địa chỉ không được để trống.';
        if ($price_per_night <= 0) $errors[] = 'Giá phải lớn hơn 0.';
        if ($max_guests <= 0) $errors[] = 'Số khách tối đa phải lớn hơn 0.';
        if ($bedrooms < 0) $errors[] = 'Số phòng ngủ không hợp lệ.';
        if ($bathrooms < 0) $errors[] = 'Số phòng tắm không hợp lệ.';
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => implode(' ', $errors)
            ];
        }
        
        // Xử lý upload ảnh
        $images = [];
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $upload_result = handleHomestayImages($_FILES['images']);
            if ($upload_result['success']) {
                $images = $upload_result['images'];
            } else {
                return [
                    'success' => false,
                    'message' => 'Lỗi upload ảnh: ' . implode(', ', $upload_result['errors'])
                ];
            }
        }
        
        if (empty($images)) {
            return [
                'success' => false,
                'message' => 'Vui lòng upload ít nhất một ảnh cho homestay.'
            ];
        }
        
        // Lưu vào database
        $conn = getDbConnection();
        $sql = "INSERT INTO homestays (host_id, title, description, address, price_per_night, 
                max_guests, bedrooms, bathrooms, amenities, images, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
        
        $homestay_id = insertQuery($conn, $sql, [
            $_SESSION['user_id'],
            $title,
            $description,
            $address,
            $price_per_night,
            $max_guests,
            $bedrooms,
            $bathrooms,
            $amenities,
            json_encode($images)
        ], 'isssdiiiss');
        
        closeDbConnection($conn);
        
        if ($homestay_id) {
            setFlashMessage(MSG_SUCCESS, 'Tạo homestay thành công!');
            return [
                'success' => true,
                'homestay_id' => $homestay_id,
                'message' => 'Tạo homestay thành công!'
            ];
        } else {
            // Xóa ảnh đã upload nếu insert thất bại
            foreach ($images as $image) {
                deleteImage($image);
            }
            
            setFlashMessage(MSG_ERROR, 'Có lỗi xảy ra khi tạo homestay.');
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo homestay.'
            ];
        }
    }
    
    public function update($homestay_id) {
        if (!isLoggedIn() || !hasRole(ROLE_HOST)) {
            return [
                'success' => false,
                'message' => 'Bạn không có quyền chỉnh sửa homestay.'
            ];
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'success' => false,
                'message' => 'Phương thức không hợp lệ.'
            ];
        }
        
        // Kiểm tra homestay thuộc về user
        $homestay = $this->getHomestayById($homestay_id);
        if (!$homestay['success'] || $homestay['data']['host_id'] != $_SESSION['user_id']) {
            return [
                'success' => false,
                'message' => 'Homestay không tồn tại hoặc không thuộc về bạn.'
            ];
        }
        
        // Lấy dữ liệu
        $title = sanitizeInput($_POST['title']);
        $description = sanitizeInput($_POST['description']);
        $address = sanitizeInput($_POST['address']);
        $price_per_night = floatval($_POST['price_per_night']);
        $max_guests = intval($_POST['max_guests']);
        $bedrooms = intval($_POST['bedrooms']);
        $bathrooms = intval($_POST['bathrooms']);
        $amenities = isset($_POST['amenities']) ? json_encode($_POST['amenities']) : '[]';
        
        // Validate
        $errors = [];
        if (empty($title)) $errors[] = 'Tiêu đề không được để trống.';
        if (empty($description)) $errors[] = 'Mô tả không được để trống.';
        if (empty($address)) $errors[] = 'Địa chỉ không được để trống.';
        if ($price_per_night <= 0) $errors[] = 'Giá phải lớn hơn 0.';
        if ($max_guests <= 0) $errors[] = 'Số khách tối đa phải lớn hơn 0.';
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => implode(' ', $errors)
            ];
        }
        
        // Xử lý ảnh mới
        $current_images = json_decode($homestay['data']['images'], true) ?: [];
        $new_images = [];
        
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $upload_result = handleHomestayImages($_FILES['images']);
            if ($upload_result['success']) {
                $new_images = $upload_result['images'];
            }
        }
        
        // Kết hợp ảnh cũ và ảnh mới
        $all_images = array_merge($current_images, $new_images);
        
        if (empty($all_images)) {
            return [
                'success' => false,
                'message' => 'Homestay phải có ít nhất một ảnh.'
            ];
        }
        
        // Cập nhật database
        $conn = getDbConnection();
        $sql = "UPDATE homestays SET title = ?, description = ?, address = ?, 
                price_per_night = ?, max_guests = ?, bedrooms = ?, bathrooms = ?, 
                amenities = ?, images = ? WHERE id = ?";
        
        $result = executeUpdate($conn, $sql, [
            $title,
            $description,
            $address,
            $price_per_night,
            $max_guests,
            $bedrooms,
            $bathrooms,
            $amenities,
            json_encode($all_images),
            $homestay_id
        ], 'sssdiiissi');
        
        closeDbConnection($conn);
        
        if ($result) {
            setFlashMessage(MSG_SUCCESS, 'Cập nhật homestay thành công!');
            return [
                'success' => true,
                'message' => 'Cập nhật homestay thành công!'
            ];
        } else {
            // Xóa ảnh mới đã upload nếu update thất bại
            foreach ($new_images as $image) {
                deleteImage($image);
            }
            
            setFlashMessage(MSG_ERROR, 'Có lỗi xảy ra khi cập nhật homestay.');
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật homestay.'
            ];
        }
    }
    
    public function getHomestayById($homestay_id) {
        $conn = getDbConnection();
        $sql = "SELECT h.*, u.full_name as host_name, u.phone as host_phone 
                FROM homestays h 
                LEFT JOIN users u ON h.host_id = u.id 
                WHERE h.id = ?";
        $homestay = fetchOne($conn, $sql, [$homestay_id], 'i');
        closeDbConnection($conn);
        
        if ($homestay) {
            // Parse JSON fields
            $homestay['amenities'] = json_decode($homestay['amenities'], true) ?: [];
            $homestay['images'] = json_decode($homestay['images'], true) ?: [];
            
            return [
                'success' => true,
                'data' => $homestay
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Không tìm thấy homestay.'
            ];
        }
    }
    
    public function search($filters = []) {
        $conn = getDbConnection();
        
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
        
        $sql .= " ORDER BY h.created_at DESC";
        
        $homestays = fetchAll($conn, $sql, $params, $types);
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
    
    public function getHomestaysByHost($host_id = null) {
        if (!isLoggedIn() || !hasRole(ROLE_HOST)) {
            return [
                'success' => false,
                'message' => 'Bạn không có quyền truy cập.'
            ];
        }
        
        $host_id = $host_id ?: $_SESSION['user_id'];
        
        $conn = getDbConnection();
        $sql = "SELECT * FROM homestays WHERE host_id = ? ORDER BY created_at DESC";
        $homestays = fetchAll($conn, $sql, [$host_id], 'i');
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
    
    public function deleteImage($homestay_id, $image_index) {
        if (!isLoggedIn() || !hasRole(ROLE_HOST)) {
            return [
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện hành động này.'
            ];
        }
        
        // Kiểm tra homestay thuộc về user
        $homestay = $this->getHomestayById($homestay_id);
        if (!$homestay['success'] || $homestay['data']['host_id'] != $_SESSION['user_id']) {
            return [
                'success' => false,
                'message' => 'Homestay không tồn tại hoặc không thuộc về bạn.'
            ];
        }
        
        $images = $homestay['data']['images'];
        if (!isset($images[$image_index])) {
            return [
                'success' => false,
                'message' => 'Ảnh không tồn tại.'
            ];
        }
        
        // Xóa ảnh khỏi server
        $image_to_delete = $images[$image_index];
        deleteImage($image_to_delete);
        
        // Xóa ảnh khỏi mảng
        unset($images[$image_index]);
        $images = array_values($images); // Reset array keys
        
        // Cập nhật database
        $conn = getDbConnection();
        $sql = "UPDATE homestays SET images = ? WHERE id = ?";
        $result = executeUpdate($conn, $sql, [json_encode($images), $homestay_id], 'si');
        closeDbConnection($conn);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Xóa ảnh thành công!'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa ảnh.'
            ];
        }
    }
}
?>