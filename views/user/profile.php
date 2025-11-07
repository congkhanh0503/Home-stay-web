<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../functions/auth_functions.php';

// Kiểm tra đăng nhập
checkAccess();

$page_title = 'Hồ sơ cá nhân';
$active_tab = 'profile';

// Lấy thông tin user
$user = getUserById($_SESSION['user_id']);

// Xử lý cập nhật profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    require_once __DIR__ . '/../../controllers/AuthController.php';
    $authController = new AuthController();
    $result = $authController->updateProfile();
    
    if ($result['success']) {
        // Reload user data
        $user = getUserById($_SESSION['user_id']);
    }
}

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    require_once __DIR__ . '/../../controllers/AuthController.php';
    $authController = new AuthController();
    $result = $authController->changePassword();
}
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
                    <h4 class="mb-0"><i class="fas fa-user-circle me-2"></i>Thông tin cá nhân</h4>
                </div>
                <div class="card-body">
                    <!-- Profile Update Form -->
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="update_profile" value="1">
                        
                        <div class="row">
                            <!-- Avatar -->
                            <div class="col-md-3 text-center mb-4">
                                <div class="avatar-section">
                                    <div class="avatar-preview mb-3">
                                        <img src="<?php echo getImageUrl($user['avatar']); ?>" 
                                             class="rounded-circle shadow" 
                                             width="150" 
                                             height="150"
                                             alt="Avatar"
                                             id="avatarPreview">
                                    </div>
                                    <div class="avatar-upload">
                                        <input type="file" 
                                               name="avatar" 
                                               id="avatarInput" 
                                               class="d-none" 
                                               accept="image/*">
                                        <label for="avatarInput" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-camera me-1"></i>Đổi ảnh
                                        </label>
                                        <?php if (!empty($user['avatar'])): ?>
                                        <button type="button" class="btn btn-outline-danger btn-sm mt-1" id="removeAvatar">
                                            <i class="fas fa-trash me-1"></i>Xóa ảnh
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- User Info -->
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="full_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="full_name" 
                                               name="full_name" 
                                               value="<?php echo htmlspecialchars($user['full_name']); ?>"
                                               required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="email" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>"
                                               disabled>
                                        <div class="form-text">Email không thể thay đổi</div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Số điện thoại</label>
                                        <input type="tel" 
                                               class="form-control" 
                                               id="phone" 
                                               name="phone" 
                                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                               placeholder="Nhập số điện thoại">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Vai trò</label>
                                        <input type="text" 
                                               class="form-control" 
                                               value="<?php echo getConstantLabel('ROLES', $user['role']); ?>"
                                               disabled>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Trạng thái</label>
                                        <input type="text" 
                                               class="form-control" 
                                               value="<?php echo $user['status'] === 'active' ? 'Đang hoạt động' : 'Đã khóa'; ?>"
                                               disabled>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Ngày tham gia</label>
                                        <input type="text" 
                                               class="form-control" 
                                               value="<?php echo formatDate($user['created_at']); ?>"
                                               disabled>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Cập nhật thông tin
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <hr class="my-5">
                    
                    <!-- Change Password Form -->
                    <div class="password-section">
                        <h5 class="mb-4"><i class="fas fa-lock me-2"></i>Đổi mật khẩu</h5>
                        
                        <form method="POST" action="" class="row g-3">
                            <input type="hidden" name="change_password" value="1">
                            
                            <div class="col-md-6">
                                <label for="current_password" class="form-label">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control" 
                                           id="current_password" 
                                           name="current_password" 
                                           placeholder="Nhập mật khẩu hiện tại"
                                           required>
                                    <button type="button" class="input-group-text toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-md-6"></div>
                            
                            <div class="col-md-6">
                                <label for="new_password" class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                                <div class="input-group">
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
                            
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                <div class="input-group">
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
                            
                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-key me-1"></i>Đổi mật khẩu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Avatar preview
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const removeAvatar = document.getElementById('removeAvatar');
    
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Remove avatar
    if (removeAvatar) {
        removeAvatar.addEventListener('click', function() {
            if (confirm('Bạn có chắc muốn xóa ảnh đại diện?')) {
                avatarPreview.src = '<?php echo getImageUrl(""); ?>';
                // You might want to add a hidden field to indicate avatar removal
            }
        });
    }
    
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
    
    if (newPassword && confirmPassword) {
        function validatePassword() {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Mật khẩu xác nhận không khớp');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
        
        newPassword.addEventListener('change', validatePassword);
        confirmPassword.addEventListener('keyup', validatePassword);
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>