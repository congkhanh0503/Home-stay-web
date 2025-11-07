<?php
// Vai trò người dùng
define('ROLE_USER', 'user');
define('ROLE_HOST', 'host');
define('ROLE_ADMIN', 'admin');

// Trạng thái homestay
define('HOMESTAY_ACTIVE', 'active');
define('HOMESTAY_INACTIVE', 'inactive');
define('HOMESTAY_BLOCKED', 'blocked');

// Trạng thái booking
define('BOOKING_PENDING', 'pending');
define('BOOKING_CONFIRMED', 'confirmed');
define('BOOKING_CANCELLED', 'cancelled');
define('BOOKING_COMPLETED', 'completed');

// Thông báo
define('MSG_SUCCESS', 'success');
define('MSG_ERROR', 'error');
define('MSG_WARNING', 'warning');
define('MSG_INFO', 'info');

// Mảng constants để dễ sử dụng
$ROLES = [
    ROLE_USER => 'Khách hàng',
    ROLE_HOST => 'Chủ homestay', 
    ROLE_ADMIN => 'Quản trị viên'
];

$HOMESTAY_STATUSES = [
    HOMESTAY_ACTIVE => 'Đang hoạt động',
    HOMESTAY_INACTIVE => 'Ngừng hoạt động',
    HOMESTAY_BLOCKED => 'Bị khóa'
];

$BOOKING_STATUSES = [
    BOOKING_PENDING => 'Chờ xác nhận',
    BOOKING_CONFIRMED => 'Đã xác nhận',
    BOOKING_CANCELLED => 'Đã hủy',
    BOOKING_COMPLETED => 'Đã hoàn thành'
];

// XÓA CÁC HÀM HELPER Ở ĐÂY - CHỈ ĐỂ LẠI CÁC CONSTANTS
// Tiện ích nhỏ
// function formatPrice($price) {
//     return number_format($price, 0, ',', '.') . ' VNĐ';
// }
// 
// function redirect($url) {
//     header("Location: " . $url);
//     exit();
// }
// 
// function sanitizeInput($data) {
//     if (is_array($data)) {
//         return array_map('sanitizeInput', $data);
//     }
//     return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
// }

// Hàm helper để lấy constant label
function getConstantLabel($type, $value) {
    global $ROLES, $HOMESTAY_STATUSES, $BOOKING_STATUSES;
    
    switch ($type) {
        case 'ROLES':
            return $ROLES[$value] ?? $value;
        case 'HOMESTAY_STATUSES':
            return $HOMESTAY_STATUSES[$value] ?? $value;
        case 'BOOKING_STATUSES':
            return $BOOKING_STATUSES[$value] ?? $value;
        default:
            return $value;
    }
}
?>