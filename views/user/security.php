<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/views/auth/login.php');
    exit;
}

$page_title = 'Bảo mật';
$active_tab = 'security';
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Cài đặt bảo mật</h4>
                </div>
                <div class="card-body">
                    <!-- Session Management -->
                    <div class="security-section mb-5">
                        <h5 class="mb-3"><i class="fas fa-laptop me-2"></i>Quản lý phiên đăng nhập</h5>
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="mb-3">Dưới đây là các thiết bị đang đăng nhập vào tài khoản của bạn:</p>
                                
                                <!-- Current Session -->
                                <div class="session-item border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">Phiên hiện tại</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-desktop me-1"></i>
                                                <?php echo $_SERVER['HTTP_USER_AGENT']; ?>
                                            </small>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                Đăng nhập lúc: <?php echo date('H:i d/m/Y'); ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-success">Đang hoạt động</span>
                                    </div>
                                </div>
                                
                                <div class="text-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm" id="logoutAllSessions">
                                        <i class="fas fa-sign-out-alt me-1"></i>Đăng xuất tất cả thiết bị
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Privacy Settings -->
                    <div class="privacy-section mb-5">
                        <h5 class="mb-3"><i class="fas fa-user-secret me-2"></i>Cài đặt quyền riêng tư</h5>
                        
                        <form method="POST" action="">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="profileVisibility" checked>
                                        <label class="form-check-label" for="profileVisibility">
                                            Cho phép người khác xem hồ sơ của tôi
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="emailVisibility">
                                        <label class="form-check-label" for="emailVisibility">
                                            Ẩn địa chỉ email
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="reviewVisibility" checked>
                                        <label class="form-check-label" for="reviewVisibility">
                                            Hiển thị đánh giá của tôi công khai
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Lưu cài đặt
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Account Actions -->
                    <div class="account-actions">
                        <h5 class="mb-3"><i class="fas fa-cog me-2"></i>Hành động tài khoản</h5>
                        
                        <div class="card border-warning">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6 class="text-warning">Tải xuống dữ liệu cá nhân</h6>
                                        <p class="small text-muted mb-0">
                                            Tải xuống bản sao dữ liệu cá nhân của bạn bao gồm thông tin hồ sơ, đơn đặt và đánh giá.
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <button type="button" class="btn btn-outline-warning">
                                            <i class="fas fa-download me-1"></i>Yêu cầu dữ liệu
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card border-danger mt-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6 class="text-danger">Xóa tài khoản</h6>
                                        <p class="small text-muted mb-0">
                                            Xóa vĩnh viễn tài khoản của bạn và tất cả dữ liệu liên quan. Hành động này không thể hoàn tác.
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                            <i class="fas fa-trash me-1"></i>Xóa tài khoản
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Xác nhận xóa tài khoản</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Cảnh báo:</strong> Đây là hành động không thể hoàn tác.
                </div>
                
                <p>Khi xóa tài khoản:</p>
                <ul>
                    <li>Tất cả thông tin cá nhân sẽ bị xóa vĩnh viễn</li>
                    <li>Các đơn đặt đang hoạt động sẽ bị hủy</li>
                    <li>Đánh giá của bạn sẽ bị xóa</li>
                    <li>Bạn không thể khôi phục tài khoản sau khi xóa</li>
                </ul>
                
                <div class="mb-3">
                    <label for="confirmEmail" class="form-label">
                        Nhập email của bạn để xác nhận: <strong><?php echo $_SESSION['user_email']; ?></strong>
                    </label>
                    <input type="email" class="form-control" id="confirmEmail" placeholder="Nhập email của bạn">
                </div>
                
                <div class="mb-3">
                    <label for="confirmText" class="form-label">Nhập "TÔI MUỐN XÓA TÀI KHOẢN" để xác nhận:</label>
                    <input type="text" class="form-control" id="confirmText" placeholder="TÔI MUỐN XÓA TÀI KHOẢN">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteAccount" disabled>
                    <i class="fas fa-trash me-1"></i>Xóa tài khoản vĩnh viễn
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete account confirmation
    const confirmEmail = document.getElementById('confirmEmail');
    const confirmText = document.getElementById('confirmText');
    const confirmDeleteBtn = document.getElementById('confirmDeleteAccount');
    
    function validateDeleteConfirmation() {
        const emailMatch = confirmEmail.value === '<?php echo $_SESSION['user_email']; ?>';
        const textMatch = confirmText.value === 'TÔI MUỐN XÓA TÀI KHOẢN';
        confirmDeleteBtn.disabled = !(emailMatch && textMatch);
    }
    
    confirmEmail.addEventListener('input', validateDeleteConfirmation);
    confirmText.addEventListener('input', validateDeleteConfirmation);
    
    // Logout all sessions
    const logoutAllBtn = document.getElementById('logoutAllSessions');
    if (logoutAllBtn) {
        logoutAllBtn.addEventListener('click', function() {
            if (confirm('Bạn có chắc muốn đăng xuất khỏi tất cả thiết bị?')) {
                // Redirect to logout all endpoint
                window.location.href = '<?php echo SITE_URL; ?>/views/auth/logout_all.php';
            }
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>