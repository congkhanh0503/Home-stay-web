<?php
// Khởi tạo session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load config
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/functions/helpers.php';

// Kiểm tra nếu đã đăng nhập
if (isLoggedIn()) {
    // Redirect dựa trên role
    switch ($_SESSION['user_role']) {
        case ROLE_ADMIN:
            redirect(SITE_URL . '/admin.php');
            break;
        case ROLE_HOST:
            redirect(SITE_URL . '/views/host/dashboard.php');
            break;
        default:
            // User thông thường ở lại trang chủ
            break;
    }
}

// ... phần còn lại giữ nguyên

// Load models và controllers cần thiết cho trang chủ
require_once __DIR__ . '/models/Homestay.php';
require_once __DIR__ . '/controllers/HomestayController.php';

// Lấy dữ liệu cho trang chủ
$homestayModel = new Homestay();
$featured_homestays = $homestayModel->getPopular(6);

// Lấy thống kê
$conn = getDbConnection();
$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM homestays WHERE status = 'active') as total_homestays,
    (SELECT COUNT(*) FROM users WHERE role = 'host' AND status = 'active') as total_hosts,
    (SELECT COUNT(*) FROM bookings WHERE status = 'completed') as total_bookings";
$stats = fetchOne($conn, $stats_sql);
closeDbConnection($conn);

// Hiển thị trang chủ
$page_title = 'Trang chủ';
include __DIR__ . '/views/home/index.php';
?>