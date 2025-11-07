<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';

// Nếu đã đăng nhập, redirect về trang chủ
if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$page_title = 'Đăng ký';

// Xử lý đăng ký
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../controllers/AuthController.php';
    $authController = new AuthController();
    $result = $authController->register();
    
    if ($result['success']) {
        // Redirect đến trang đăng nhập
        header('Location: ' . SITE_URL . '/views/auth/login.php');
        exit;
    }
}

// Get role from URL parameter (for host registration)
$default_role = isset($_GET['role']) && $_GET['role'] === 'host' ? ROLE_HOST : ROLE_USER;
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-4 border-0">
                    <div class="text-center">
                        <h2 class="card-title mb-2">Đăng ký tài khoản</h2>
                        <p class="text-muted">Tạo tài khoản mới để bắt đầu trải nghiệm</p>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <form method="POST" action="" id="registerForm">
                        <!-- Role Selection (hidden for normal users, visible for host registration) -->
                        <?php if ($default_role === ROLE_HOST): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Bạn đang đăng ký tài khoản Chủ nhà
                            </div>
                            <input type="hidden" name="role" value="<?php echo ROLE_HOST; ?>">
                        <?php else: ?>
                            <input type="hidden" name="role" value="<?php echo ROLE_USER; ?>">
                        <?php endif; ?>

                        <!-- Full Name -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           id="full_name" 
                                           name="full_name" 
                                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                                           placeholder="Nhập họ tên đầy đủ"
                                           required>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Số điện thoại</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-phone"></i>
                                    </span>
                                    <input type="tel" 
                                           class="form-control" 
                                           id="phone" 
                                           name="phone" 
                                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                           placeholder="Nhập số điện thoại">
                                </div>
                                <div class="form-text">Ví dụ: 0912345678</div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                       placeholder="Nhập email của bạn"
                                       required>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Nhập mật khẩu"
                                           minlength="6"
                                           required>
                                    <button type="button" class="input-group-text toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Mật khẩu ít nhất 6 ký tự</div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control" 
                                           id="confirm_password" 
                                           name="confirm_password" 
                                           placeholder="Nhập lại mật khẩu"
                                           required>
                                    <button type="button" class="input-group-text toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="agree_terms" id="agree_terms" required>
                                <label class="form-check-label" for="agree_terms">
                                    Tôi đồng ý với 
                                    <a href="#" class="text-decoration-none">Điều khoản dịch vụ</a> 
                                    và 
                                    <a href="#" class="text-decoration-none">Chính sách bảo mật</a>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Đăng ký
                            </button>
                        </div>

                        <!-- Login Link -->
                        <div class="text-center">
                            <p class="mb-0">Đã có tài khoản? 
                                <a href="<?php echo SITE_URL; ?>/views/auth/login.php" class="text-decoration-none fw-bold">
                                    Đăng nhập ngay
                                </a>
                            </p>
                        </div>

                        <!-- Host Registration Link -->
                        <?php if ($default_role === ROLE_USER): ?>
                            <div class="text-center mt-3">
                                <p class="mb-0">
                                    Bạn muốn trở thành chủ nhà?
                                    <a href="<?php echo SITE_URL; ?>/views/auth/register.php?role=host" class="text-decoration-none fw-bold">
                                        Đăng ký tài khoản Host
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>
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
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePassword() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Mật khẩu xác nhận không khớp');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    password.addEventListener('change', validatePassword);
    confirmPassword.addEventListener('keyup', validatePassword);
    
    // Phone number validation
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+]/g, '');
        });
    }
    
    // Auto-focus on first field
    document.getElementById('full_name')?.focus();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>