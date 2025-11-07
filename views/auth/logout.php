<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';

// Xử lý đăng xuất
if (isLoggedIn()) {
    require_once __DIR__ . '/../../controllers/AuthController.php';
    $authController = new AuthController();
    $result = $authController->logout();
    
    if ($result['success']) {
        set_flash_message(MSG_SUCCESS, $result['message']);
    }
}

// Redirect về trang chủ
header('Location: ' . SITE_URL . '/index.php');
exit;
?>