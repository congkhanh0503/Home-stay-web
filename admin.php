
<?php
// Khởi tạo session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load config
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/functions/helpers.php';

// Kiểm tra đăng nhập và quyền admin
if (!isLoggedIn()) {
    set_flash_message(MSG_ERROR, 'Vui lòng đăng nhập để truy cập trang quản trị');
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

if (!hasRole(ROLE_ADMIN)) {
    set_flash_message(MSG_ERROR, 'Bạn không có quyền truy cập trang quản trị');
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

// Xử lý routing cho admin panel
$page = $_GET['page'] ?? 'dashboard';

// Danh sách các trang hợp lệ
$valid_pages = [
    'dashboard' => 'views/admin/dashboard.php',
    'users' => 'views/admin/users.php',
    'homestays' => 'views/admin/homestays.php',
    'bookings' => 'views/admin/bookings.php',
    'reports' => 'views/admin/reports.php',
    'settings' => 'views/admin/settings.php'
];

// Kiểm tra trang có hợp lệ không
if (!array_key_exists($page, $valid_pages)) {
    $page = 'dashboard';
}

// Include trang tương ứng
$page_file = __DIR__ . '/' . $valid_pages[$page];

if (file_exists($page_file)) {
    // Set page title dựa trên trang hiện tại
    $page_titles = [
        'dashboard' => 'Tổng quan',
        'users' => 'Quản lý người dùng',
        'homestays' => 'Quản lý Homestay',
        'bookings' => 'Quản lý đơn đặt',
        'reports' => 'Báo cáo & Thống kê',
        'settings' => 'Cài đặt hệ thống'
    ];
    
    $page_title = $page_titles[$page] ?? 'Admin Panel';
    $active_tab = $page;
    
    include $page_file;
} else {
    // Fallback: redirect đến dashboard nếu trang không tồn tại
    set_flash_message(MSG_ERROR, 'Trang không tồn tại');
    header('Location: ' . SITE_URL . '/admin.php?page=dashboard');
    exit;
}
?>
