<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../controllers/BookingController.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/views/auth/login.php');
    exit;
}

// Kiểm tra phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash_message(MSG_ERROR, 'Phương thức không hợp lệ');
    header('Location: ' . SITE_URL . '/views/booking/bookings.php');
    exit;
}

// Kiểm tra booking_id
if (!isset($_POST['booking_id']) || empty($_POST['booking_id'])) {
    set_flash_message(MSG_ERROR, 'Không tìm thấy booking');
    header('Location: ' . SITE_URL . '/views/booking/bookings.php');
    exit;
}

$booking_id = intval($_POST['booking_id']);
$bookingController = new BookingController();
$result = $bookingController->cancel($booking_id);

// Chuyển hướng về trang danh sách booking
header('Location: ' . SITE_URL . '/views/user/bookings.php');
exit;