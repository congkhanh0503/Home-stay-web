<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';

// Nếu đã đăng nhập, redirect về trang chủ
if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$page_title = 'Đặt lại mật khẩu';
$token = $_GET['token'] ?? '';

// TODO: Kiểm tra token hợp lệ
if (empty($token)) {
    set_flash_message(MSG_ERROR, 'Token reset không hợp lệ.');
    header('Location: ' . SITE_URL . '/views/auth/forgot_password.php');
    exit;
}

// Xử lý reset password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate
    if ($new_password !== $confirm_password) {
        set_flash_message(MSG_ERROR, 'Mật khẩu xác nhận không khớp.');
    } elseif (strlen($new_password) < 6) {
        set_flash_message(MSG_ERROR, 'Mật khẩu phải có ít nhất 6 ký tự.');
    } else {
        // Cập nhật mật khẩu mới
        // Trong thực tế, bạn sẽ verify token và cập nhật password
        
        set_flash_message(MSG_SUCCESS, 'Mật khẩu đã được đặt lại thành công.');
        header('Location: ' . SITE_URL . '/views/auth/login.php');
        exit;
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-4 border-0">
                    <div class="text-center">
                        <h2 class="card-title mb-2">Đặt lại mật khẩu</h2>
                        <p class="text-muted">Tạo mật khẩu mới cho tài khoản của bạn</p>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        
                        <!-- New Password -->
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Mật khẩu mới</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       id="new_password" 
                                       name="new_password" 
                                       placeholder="Nhập mật khẩu mới"
                                       minlength="6"
                                       required>
                                <button type="button" class="input-group-text toggle-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Mật khẩu ít nhất 6 ký tự</div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       placeholder="Nhập lại mật khẩu mới"
                                       required>
                                <button type="button" class="input-group-text toggle-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Đặt lại mật khẩu
                            </button>
                        </div>

                        <!-- Back to Login -->
                        <div class="text-center">
                            <a href="<?php echo SITE_URL; ?>/views/auth/login.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Quay lại đăng nhập
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    });
    
    // Password confirmation validation
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePassword() {
        if (newPassword.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Mật khẩu xác nhận không khớp');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    newPassword.addEventListener('change', validatePassword);
    confirmPassword.addEventListener('keyup', validatePassword);
    
    // Auto-focus on password field
    document.getElementById('new_password')?.focus();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>