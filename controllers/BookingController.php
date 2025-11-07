<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../functions/booking_functions.php';
require_once __DIR__ . '/../functions/helpers.php';

class BookingController {
    
    public function create() {
        if (!isLoggedIn()) {
            return [
                'success' => false,
                'message' => 'Vui lòng đăng nhập để đặt homestay.'
            ];
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'success' => false,
                'message' => 'Phương thức không hợp lệ.'
            ];
        }
        
        // Lấy dữ liệu
        $homestay_id = intval($_POST['homestay_id']);
        $check_in = sanitizeInput($_POST['check_in']);
        $check_out = sanitizeInput($_POST['check_out']);
        $guests = intval($_POST['guests']);
        
        // Validate
        $errors = [];
        if (empty($homestay_id)) $errors[] = 'Homestay không hợp lệ.';
        if (empty($check_in)) $errors[] = 'Vui lòng chọn ngày check-in.';
        if (empty($check_out)) $errors[] = 'Vui lòng chọn ngày check-out.';
        if ($guests <= 0) $errors[] = 'Số khách phải lớn hơn 0.';
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => implode(' ', $errors)
            ];
        }
        
        // Validate ngày
        $today = date('Y-m-d');
        if ($check_in < $today) {
            return [
                'success' => false,
                'message' => 'Ngày check-in không được ở trong quá khứ.'
            ];
        }
        
        if ($check_out <= $check_in) {
            return [
                'success' => false,
                'message' => 'Ngày check-out phải sau ngày check-in.'
            ];
        }
        
        $booking_data = [
            'user_id' => $_SESSION['user_id'],
            'homestay_id' => $homestay_id,
            'check_in' => $check_in,
            'check_out' => $check_out,
            'guests' => $guests
        ];
        
        $result = createBooking($booking_data);
        
        if ($result['success']) {
            setFlashMessage(MSG_SUCCESS, $result['message']);
        } else {
            setFlashMessage(MSG_ERROR, $result['message']);
        }
        
        return $result;
    }
    
    public function getUserBookings($status = null) {
        if (!isLoggedIn()) {
            return [
                'success' => false,
                'message' => 'Vui lòng đăng nhập.'
            ];
        }
        
        $bookings = getUserBookings($_SESSION['user_id'], $status);
        
        return [
            'success' => true,
            'data' => $bookings
        ];
    }
    
    public function getHostBookings($status = null) {
        if (!isLoggedIn() || !hasRole(ROLE_HOST)) {
            return [
                'success' => false,
                'message' => 'Bạn không có quyền truy cập.'
            ];
        }
        
        $bookings = getHostBookings($_SESSION['user_id'], $status);
        
        return [
            'success' => true,
            'data' => $bookings
        ];
    }
    
    public function getBookingDetail($booking_id) {
        if (!isLoggedIn()) {
            return [
                'success' => false,
                'message' => 'Vui lòng đăng nhập.'
            ];
        }
        
        $booking = getBookingById($booking_id);
        
        if (!$booking) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy booking.'
            ];
        }
        
        // Kiểm tra quyền xem booking
        $user_id = $_SESSION['user_id'];
        $user_role = $_SESSION['user_role'];
        
        $is_owner = $booking['user_id'] == $user_id;
        $is_host = $user_role == ROLE_HOST;
        $is_admin = $user_role == ROLE_ADMIN;
        
        if (!$is_owner && !$is_host && !$is_admin) {
            return [
                'success' => false,
                'message' => 'Bạn không có quyền xem booking này.'
            ];
        }
        
        return [
            'success' => true,
            'data' => $booking
        ];
    }
    
    public function updateStatus() {
        if (!isLoggedIn() || !hasRole(ROLE_HOST)) {
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
        
        $booking_id = intval($_POST['booking_id']);
        $status = sanitizeInput($_POST['status']);
        
        // Kiểm tra booking thuộc về host
        $booking = getBookingById($booking_id);
        if (!$booking) {
            return [
                'success' => false,
                'message' => 'Booking không tồn tại.'
            ];
        }
        
        // Lấy homestay để kiểm tra chủ sở hữu
        require_once __DIR__ . '/../config/db_connection.php';
        $conn = getDbConnection();
        $sql = "SELECT host_id FROM homestays WHERE id = ?";
        $homestay = fetchOne($conn, $sql, [$booking['homestay_id']], 'i');
        closeDbConnection($conn);
        
        if (!$homestay || $homestay['host_id'] != $_SESSION['user_id']) {
            return [
                'success' => false,
                'message' => 'Bạn không có quyền cập nhật booking này.'
            ];
        }
        
        $result = updateBookingStatus($booking_id, $status);
        
        if ($result) {
            $status_messages = [
                'confirmed' => 'Xác nhận booking thành công!',
                'cancelled' => 'Hủy booking thành công!'
            ];
            
            $message = $status_messages[$status] ?? 'Cập nhật trạng thái thành công!';
            setFlashMessage(MSG_SUCCESS, $message);
            
            return [
                'success' => true,
                'message' => $message
            ];
        } else {
            setFlashMessage(MSG_ERROR, 'Có lỗi xảy ra khi cập nhật trạng thái.');
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật trạng thái.'
            ];
        }
    }
    
    public function cancel($booking_id) {
        if (!isLoggedIn()) {
            return [
                'success' => false,
                'message' => 'Vui lòng đăng nhập.'
            ];
        }
        
        $result = cancelBooking($booking_id, $_SESSION['user_id']);
        
        if ($result['success']) {
            setFlashMessage(MSG_SUCCESS, $result['message']);
        } else {
            setFlashMessage(MSG_ERROR, $result['message']);
        }
        
        return $result;
    }
}
?>