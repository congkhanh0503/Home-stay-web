<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/auth_functions.php';
require_once __DIR__ . '/../functions/helpers.php';

class AuthController {
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'success' => false,
                'message' => 'Phương thức không hợp lệ.'
            ];
        }
        
        // Lấy và validate dữ liệu
        $full_name = sanitizeInput($_POST['full_name']);
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $phone = sanitizeInput($_POST['phone']);
        $role = sanitizeInput($_POST['role'] ?? ROLE_USER);
        
        // Validate dữ liệu
        if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
            return [
                'success' => false,
                'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc.'
            ];
        }
        
        if (!isValidEmail($email)) {
            return [
                'success' => false,
                'message' => 'Email không hợp lệ.'
            ];
        }
        
        if ($password !== $confirm_password) {
            return [
                'success' => false,
                'message' => 'Mật khẩu xác nhận không khớp.'
            ];
        }
        
        if (strlen($password) < 6) {
            return [
                'success' => false,
                'message' => 'Mật khẩu phải có ít nhất 6 ký tự.'
            ];
        }
        
        if (!empty($phone) && !isValidPhone($phone)) {
            return [
                'success' => false,
                'message' => 'Số điện thoại không hợp lệ.'
            ];
        }
        
        // Tạo user data
        $user_data = [
            'full_name' => $full_name,
            'email' => $email,
            'password' => $password,
            'phone' => $phone,
            'role' => $role
        ];
        
        // Gọi hàm register
        $result = registerUser($user_data);
        
        if ($result['success']) {
            setFlashMessage(MSG_SUCCESS, $result['message']);
        } else {
            setFlashMessage(MSG_ERROR, $result['message']);
        }
        
        return $result;
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'success' => false,
                'message' => 'Phương thức không hợp lệ.'
            ];
        }
        
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        
        // Validate
        if (empty($email) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập email và mật khẩu.'
            ];
        }
        
        if (!isValidEmail($email)) {
            return [
                'success' => false,
                'message' => 'Email không hợp lệ.'
            ];
        }
        
        // Gọi hàm login
        $result = loginUser($email, $password);
        
        if ($result['success']) {
            setFlashMessage(MSG_SUCCESS, $result['message']);
        } else {
            setFlashMessage(MSG_ERROR, $result['message']);
        }
        
        return $result;
    }
    
    public function logout() {
        $result = logoutUser();
        setFlashMessage(MSG_SUCCESS, $result['message']);
        return $result;
    }
    
    public function updateProfile() {
        if (!isLoggedIn()) {
            return [
                'success' => false,
                'message' => 'Vui lòng đăng nhập để cập nhật thông tin.'
            ];
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'success' => false,
                'message' => 'Phương thức không hợp lệ.'
            ];
        }
        
        $user_id = $_SESSION['user_id'];
        $full_name = sanitizeInput($_POST['full_name']);
        $phone = sanitizeInput($_POST['phone']);
        
        // Validate
        if (empty($full_name)) {
            return [
                'success' => false,
                'message' => 'Họ tên không được để trống.'
            ];
        }
        
        if (!empty($phone) && !isValidPhone($phone)) {
            return [
                'success' => false,
                'message' => 'Số điện thoại không hợp lệ.'
            ];
        }
        
        // Xử lý upload avatar
        $avatar = '';
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            require_once __DIR__ . '/../functions/upload_functions.php';
            $upload_result = uploadImage($_FILES['avatar'], 'avatars');
            
            if ($upload_result['success']) {
                $avatar = $upload_result['file_path'];
                
                // Xóa avatar cũ nếu có
                $user = getUserById($user_id);
                if (!empty($user['avatar'])) {
                    deleteImage($user['avatar']);
                }
            }
        }
        
        $data = [
            'full_name' => $full_name,
            'phone' => $phone,
            'avatar' => $avatar
        ];
        
        $result = updateUserProfile($user_id, $data);
        
        if ($result['success']) {
            setFlashMessage(MSG_SUCCESS, $result['message']);
        } else {
            setFlashMessage(MSG_ERROR, $result['message']);
        }
        
        return $result;
    }
    
    public function changePassword() {
        if (!isLoggedIn()) {
            return [
                'success' => false,
                'message' => 'Vui lòng đăng nhập để đổi mật khẩu.'
            ];
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'success' => false,
                'message' => 'Phương thức không hợp lệ.'
            ];
        }
        
        $user_id = $_SESSION['user_id'];
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            return [
                'success' => false,
                'message' => 'Vui lòng điền đầy đủ thông tin.'
            ];
        }
        
        if ($new_password !== $confirm_password) {
            return [
                'success' => false,
                'message' => 'Mật khẩu mới và xác nhận không khớp.'
            ];
        }
        
        if (strlen($new_password) < 6) {
            return [
                'success' => false,
                'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự.'
            ];
        }
        
        $result = changePassword($user_id, $current_password, $new_password);
        
        if ($result['success']) {
            setFlashMessage(MSG_SUCCESS, $result['message']);
        } else {
            setFlashMessage(MSG_ERROR, $result['message']);
        }
        
        return $result;
    }
}
?>