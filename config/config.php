<?php
// Cấu hình cơ bản
define('SITE_NAME', 'Homestay Relax');
define('SITE_URL', 'http://localhost/HomestayManagementSystem');

// Cấu hình database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'khanh2005');
define('DB_NAME', 'homestay_management');
define('DB_PORT', 3306);

// Cấu hình upload
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Bắt đầu session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>