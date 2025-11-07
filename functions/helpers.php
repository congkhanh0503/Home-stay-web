<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';

// Hàm chuyển hướng
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Hàm làm sạch dữ liệu đầu vào
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Hàm kiểm tra đã đăng nhập chưa
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Hàm kiểm tra role
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Hàm hiển thị thông báo
function showMessage($type, $message) {
    $class = '';
    switch ($type) {
        case MSG_SUCCESS:
            $class = 'alert alert-success';
            break;
        case MSG_ERROR:
            $class = 'alert alert-danger';
            break;
        case MSG_WARNING:
            $class = 'alert alert-warning';
            break;
        case MSG_INFO:
            $class = 'alert alert-info';
            break;
        default:
            $class = 'alert alert-secondary';
    }
    
    return '<div class="' . $class . '">' . $message . '</div>';
}

// Hàm flash message
function setFlashMessage($type, $message) {
    $_SESSION['flash_' . $type] = $message;
}

function getFlashMessage() {
    if (isset($_SESSION['flash_success'])) {
        $message = $_SESSION['flash_success'];
        unset($_SESSION['flash_success']);
        return showMessage(MSG_SUCCESS, $message);
    }
    
    if (isset($_SESSION['flash_error'])) {
        $message = $_SESSION['flash_error'];
        unset($_SESSION['flash_error']);
        return showMessage(MSG_ERROR, $message);
    }
    
    if (isset($_SESSION['flash_warning'])) {
        $message = $_SESSION['flash_warning'];
        unset($_SESSION['flash_warning']);
        return showMessage(MSG_WARNING, $message);
    }
    
    return '';
}

// Hàm định dạng giá tiền
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

// Hàm định dạng ngày tháng
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    $datetime = DateTime::createFromFormat('Y-m-d', $date);
    return $datetime ? $datetime->format($format) : $date;
}

// Hàm validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Hàm validate số điện thoại Việt Nam
function isValidPhone($phone) {
    $pattern = '/^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-9]|9[0-9])[0-9]{7}$/';
    return preg_match($pattern, $phone);
}

// Hàm tính số ngày giữa hai ngày
function calculateDays($check_in, $check_out) {
    $start = DateTime::createFromFormat('Y-m-d', $check_in);
    $end = DateTime::createFromFormat('Y-m-d', $check_out);
    
    if ($start && $end) {
        $interval = $start->diff($end);
        return $interval->days;
    }
    
    return 0;
}

// Hàm tạo slug từ tiêu đề
function createSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    
    if (empty($text)) {
        return 'n-a';
    }
    
    return $text;
}

// Hàm lấy giá trị từ POST hoặc GET
function getInput($key, $default = '') {
    if (isset($_POST[$key])) {
        return sanitizeInput($_POST[$key]);
    }
    if (isset($_GET[$key])) {
        return sanitizeInput($_GET[$key]);
    }
    return $default;
}

// Hàm kiểm tra request là AJAX
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// Hàm trả về JSON response
function jsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// Hàm debug
function debug($data, $exit = true) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    if ($exit) exit;
}
// Hàm lấy URL ảnh
function getImageUrl($file_path) {
    if (empty($file_path)) {
        return SITE_URL . '/assets/images/no-image.jpg';
    }
    
    return SITE_URL . '/uploads/' . $file_path;
}


// Hàm set flash message (alias cho set_flash_message)
function set_flash_message($type, $message) {
    setFlashMessage($type, $message);
}

// Hàm kiểm tra quyền truy cập
function checkAccess($allowed_roles = []) {
    if (!isLoggedIn()) {
        set_flash_message(MSG_ERROR, 'Vui lòng đăng nhập để tiếp tục.');
        redirect('login.php');
    }
    
    if (!empty($allowed_roles) && !in_array($_SESSION['user_role'], $allowed_roles)) {
        set_flash_message(MSG_ERROR, 'Bạn không có quyền truy cập trang này.');
        redirect('index.php');
    }
}

// Hàm kiểm tra role (alias cho has_role)
function has_role($role) {
    return hasRole($role);
}


?>