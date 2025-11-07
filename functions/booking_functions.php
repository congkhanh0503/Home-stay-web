<?php
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/helpers.php';

// Hàm tạo booking mới
function createBooking($booking_data) {
    $conn = getDbConnection();
    
    // Kiểm tra homestay có tồn tại và available không
    $homestay_sql = "SELECT price_per_night, max_guests, status FROM homestays WHERE id = ?";
    $homestay = fetchOne($conn, $homestay_sql, [$booking_data['homestay_id']], 'i');
    
    if (!$homestay) {
        closeDbConnection($conn);
        return [
            'success' => false,
            'message' => 'Homestay không tồn tại.'
        ];
    }
    
    if ($homestay['status'] != 'active') {
        closeDbConnection($conn);
        return [
            'success' => false,
            'message' => 'Homestay hiện không khả dụng.'
        ];
    }
    
    // Kiểm tra số lượng khách
    if ($booking_data['guests'] > $homestay['max_guests']) {
        closeDbConnection($conn);
        return [
            'success' => false,
            'message' => 'Số lượng khách vượt quá giới hạn của homestay.'
        ];
    }
    
    // Kiểm tra ngày check-in/check-out
    $check_in = $booking_data['check_in'];
    $check_out = $booking_data['check_out'];
    
    if ($check_in >= $check_out) {
        closeDbConnection($conn);
        return [
            'success' => false,
            'message' => 'Ngày check-out phải sau ngày check-in.'
        ];
    }
    
    // Kiểm tra homestay có bị trùng booking không
    $conflict_sql = "SELECT id FROM bookings 
                     WHERE homestay_id = ? 
                     AND status IN ('pending', 'confirmed')
                     AND ((check_in BETWEEN ? AND ?) OR (check_out BETWEEN ? AND ?))";
    $conflict = fetchOne($conn, $conflict_sql, [
        $booking_data['homestay_id'],
        $check_in, $check_out,
        $check_in, $check_out
    ], 'issss');
    
    if ($conflict) {
        closeDbConnection($conn);
        return [
            'success' => false,
            'message' => 'Homestay đã có người đặt trong khoảng thời gian này.'
        ];
    }
    
    // Tính tổng tiền
    $days = calculateDays($check_in, $check_out);
    $total_price = $days * $homestay['price_per_night'];
    
    // Tạo booking
    $sql = "INSERT INTO bookings (user_id, homestay_id, check_in, check_out, guests, total_price, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";
    
    $booking_id = insertQuery($conn, $sql, [
        $booking_data['user_id'],
        $booking_data['homestay_id'],
        $check_in,
        $check_out,
        $booking_data['guests'],
        $total_price
    ], 'iissid');
    
    closeDbConnection($conn);
    
    if ($booking_id) {
        return [
            'success' => true,
            'booking_id' => $booking_id,
            'total_price' => $total_price,
            'message' => 'Đặt homestay thành công! Vui lòng chờ chủ homestay xác nhận.'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Có lỗi xảy ra khi đặt homestay. Vui lòng thử lại.'
        ];
    }
}

// Hàm lấy danh sách booking của user
function getUserBookings($user_id, $status = null) {
    $conn = getDbConnection();
    
    $sql = "SELECT b.*, h.title, h.images, h.address, u.full_name as host_name
            FROM bookings b
            JOIN homestays h ON b.homestay_id = h.id
            JOIN users u ON h.host_id = u.id
            WHERE b.user_id = ?";
    
    $params = [$user_id];
    $types = "i";
    
    if ($status) {
        $sql .= " AND b.status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    $sql .= " ORDER BY b.created_at DESC";
    
    $bookings = fetchAll($conn, $sql, $params, $types);
    closeDbConnection($conn);
    
    return $bookings;
}

// Hàm lấy chi tiết booking
function getBookingById($booking_id) {
    $conn = getDbConnection();
    
    $sql = "SELECT b.*, h.title, h.images, h.address, h.price_per_night, 
                   u.full_name as user_name, u.phone as user_phone,
                   host.full_name as host_name, host.phone as host_phone
            FROM bookings b
            JOIN homestays h ON b.homestay_id = h.id
            JOIN users u ON b.user_id = u.id
            JOIN users host ON h.host_id = host.id
            WHERE b.id = ?";
    
    $booking = fetchOne($conn, $sql, [$booking_id], 'i');
    closeDbConnection($conn);
    
    return $booking;
}

// Hàm cập nhật trạng thái booking
function updateBookingStatus($booking_id, $status) {
    $conn = getDbConnection();
    
    $sql = "UPDATE bookings SET status = ? WHERE id = ?";
    $result = executeUpdate($conn, $sql, [$status, $booking_id], 'si');
    
    closeDbConnection($conn);
    
    return $result > 0;
}

// Hàm lấy booking của host
function getHostBookings($host_id, $status = null) {
    $conn = getDbConnection();
    
    $sql = "SELECT b.*, h.title, h.images, u.full_name as user_name, u.phone as user_phone
            FROM bookings b
            JOIN homestays h ON b.homestay_id = h.id
            JOIN users u ON b.user_id = u.id
            WHERE h.host_id = ?";
    
    $params = [$host_id];
    $types = "i";
    
    if ($status) {
        $sql .= " AND b.status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    $sql .= " ORDER BY b.created_at DESC";
    
    $bookings = fetchAll($conn, $sql, $params, $types);
    closeDbConnection($conn);
    
    return $bookings;
}

// Hàm hủy booking
function cancelBooking($booking_id, $user_id) {
    $conn = getDbConnection();
    
    // Kiểm tra booking thuộc về user
    $check_sql = "SELECT id, status FROM bookings WHERE id = ? AND user_id = ?";
    $booking = fetchOne($conn, $check_sql, [$booking_id, $user_id], 'ii');
    
    if (!$booking) {
        closeDbConnection($conn);
        return [
            'success' => false,
            'message' => 'Booking không tồn tại hoặc không thuộc về bạn.'
        ];
    }
    
    if ($booking['status'] != 'pending') {
        closeDbConnection($conn);
        return [
            'success' => false,
            'message' => 'Chỉ có thể hủy booking đang chờ xác nhận.'
        ];
    }
    
    // Cập nhật trạng thái
    $sql = "UPDATE bookings SET status = 'cancelled' WHERE id = ?";
    $result = executeUpdate($conn, $sql, [$booking_id], 'i');
    
    closeDbConnection($conn);
    
    if ($result) {
        return [
            'success' => true,
            'message' => 'Hủy booking thành công!'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Có lỗi xảy ra khi hủy booking.'
        ];
    }
}
?>