<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';

// Nếu đã đăng nhập, redirect về trang chủ
if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$page_title = 'Đăng nhập';

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../controllers/AuthController.php';
    $authController = new AuthController();
    $result = $authController->login();
    
    if ($result['success']) {
        // Redirect dựa trên role
        switch ($_SESSION['user_role']) {
            case ROLE_ADMIN:
                header('Location: ' . SITE_URL . '/admin.php');
                exit;
            case ROLE_HOST:
                header('Location: ' . SITE_URL . '/views/host/dashboard.php');
                exit;
            default:
                header('Location: ' . SITE_URL . '/index.php');
                exit;
        }
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
                        <h2 class="card-title mb-2">Đăng nhập</h2>
                        <p class="text-muted">Chào mừng bạn trở lại</p>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
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
                        <div class="mb-4">
                            <label for="password" class="form-label">Mật khẩu</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Nhập mật khẩu"
                                       required>
                                <button type="button" class="input-group-text toggle-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label" for="remember">
                                    Ghi nhớ đăng nhập
                                </label>
                            </div>
                            <a href="<?php echo SITE_URL; ?>/views/auth/forgot_password.php" class="text-decoration-none">
                                Quên mật khẩu?
                            </a>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                            </button>
                        </div>

                        <!-- Register Link -->
                        <div class="text-center">
                            <p class="mb-0">Chưa có tài khoản? 
                                <a href="<?php echo SITE_URL; ?>/views/auth/register.php" class="text-decoration-none fw-bold">
                                    Đăng ký ngay
                                </a>
                            </p>
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
    const togglePassword = document.querySelector('.toggle-password');
    const passwordInput = document.getElementById('password');
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    }
    
    // Auto-focus on email field
    document.getElementById('email')?.focus();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>