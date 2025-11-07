<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';

// Nếu đã đăng nhập, redirect về trang chủ
if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$page_title = 'Quên mật khẩu';

// Xử lý gửi email reset password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    
    // Kiểm tra email tồn tại
    require_once __DIR__ . '/../../config/db_connection.php';
    $conn = getDbConnection();
    $sql = "SELECT id, full_name FROM users WHERE email = ? AND status = 'active'";
    $user = fetchOne($conn, $sql, [$email], 's');
    closeDbConnection($conn);
    
    if ($user) {
        //  Gửi email reset password
        // Trong thực tế, bạn sẽ tạo token, lưu vào database và gửi email
        
        set_flash_message(MSG_SUCCESS, 'Hướng dẫn reset mật khẩu đã được gửi đến email của bạn.');
        header('Location: ' . SITE_URL . '/views/auth/login.php');
        exit;
    } else {
        set_flash_message(MSG_ERROR, 'Email không tồn tại hoặc tài khoản đã bị khóa.');
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
                        <h2 class="card-title mb-2">Quên mật khẩu</h2>
                        <p class="text-muted">Nhập email để nhận hướng dẫn reset mật khẩu</p>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <!-- Email -->
                        <div class="mb-4">
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

                        <!-- Submit Button -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Gửi hướng dẫn
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
    // Auto-focus on email field
    document.getElementById('email')?.focus();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>