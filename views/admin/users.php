<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../models/User.php';

// Kiểm tra quyền admin
if (!isLoggedIn() || !hasRole(ROLE_ADMIN)) {
    set_flash_message(MSG_ERROR, 'Bạn không có quyền truy cập trang này');
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$page_title = 'Quản lý người dùng';
$active_tab = 'users';

$userModel = new User();

// Xử lý filters
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$filters = [];
if (!empty($role_filter)) $filters['role'] = $role_filter;
if (!empty($status_filter)) $filters['status'] = $status_filter;
if (!empty($search)) $filters['search'] = $search;

// Lấy danh sách users
$users = $userModel->getAll($filters);

// Xử lý cập nhật trạng thái user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $user_id = intval($_POST['user_id']);
    $status = sanitizeInput($_POST['status']);
    
    if ($userModel->updateStatus($user_id, $status)) {
        set_flash_message(MSG_SUCCESS, 'Cập nhật trạng thái thành công');
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        set_flash_message(MSG_ERROR, 'Có lỗi xảy ra khi cập nhật trạng thái');
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Quản lý người dùng</h1>
                <div class="text-muted">
                    Tổng: <strong><?php echo count($users); ?></strong> người dùng
                </div>
            </div>

            <!-- Filters Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Vai trò</label>
                            <select class="form-select" name="role">
                                <option value="">Tất cả vai trò</option>
                                <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>Khách hàng</option>
                                <option value="host" <?php echo $role_filter === 'host' ? 'selected' : ''; ?>>Chủ nhà</option>
                                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Quản trị viên</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Trạng thái</label>
                            <select class="form-select" name="status">
                                <option value="">Tất cả trạng thái</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Đang hoạt động</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Đã khóa</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Tìm kiếm</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   placeholder="Tên hoặc email...">
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i>Lọc
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Danh sách người dùng</h6>
                    <a href="<?php echo SITE_URL; ?>/views/admin/user_add.php" class="btn btn-success btn-sm">
                        <i class="fas fa-plus me-1"></i>Thêm người dùng
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="usersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Thông tin</th>
                                    <th>Vai trò</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tham gia</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($users)): ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>#<?php echo $user['id']; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo getImageUrl($user['avatar']); ?>" 
                                                         class="rounded-circle me-3" 
                                                         width="40" 
                                                         height="40"
                                                         alt="Avatar"
                                                         style="object-fit: cover;">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-phone me-1"></i>
                                                            <?php echo $user['phone'] ? htmlspecialchars($user['phone']) : 'Chưa cập nhật'; ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    <?php echo $user['role'] === 'admin' ? 'bg-danger' : 
                                                           ($user['role'] === 'host' ? 'bg-success' : 'bg-primary'); ?>">
                                                    <?php echo getConstantLabel('ROLES', $user['role']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <input type="hidden" name="update_status" value="1">
                                                    <select name="status" 
                                                            class="form-select form-select-sm status-select 
                                                                <?php echo $user['status'] === 'active' ? 'border-success' : 'border-danger'; ?>"
                                                            onchange="this.form.submit()">
                                                        <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Đang hoạt động</option>
                                                        <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Khóa</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td>
                                                <small><?php echo formatDate($user['created_at']); ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?php echo SITE_URL; ?>/views/admin/user_detail.php?id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-outline-primary" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?php echo SITE_URL; ?>/views/admin/user_edit.php?id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-outline-warning" title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger delete-user" 
                                                            data-user-id="<?php echo $user['id']; ?>"
                                                            data-user-name="<?php echo htmlspecialchars($user['full_name']); ?>"
                                                            title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">Không tìm thấy người dùng nào</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Stats Summary -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center py-3">
                            <h5 class="mb-1"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'user')); ?></h5>
                            <small>Khách hàng</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center py-3">
                            <h5 class="mb-1"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'host')); ?></h5>
                            <small>Chủ nhà</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center py-3">
                            <h5 class="mb-1"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'admin')); ?></h5>
                            <small>Quản trị viên</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center py-3">
                            <h5 class="mb-1"><?php echo count(array_filter($users, fn($u) => $u['status'] === 'active')); ?></h5>
                            <small>Đang hoạt động</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Xác nhận xóa người dùng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc muốn xóa người dùng <strong id="deleteUserName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Cảnh báo:</strong> Hành động này sẽ xóa vĩnh viễn tất cả dữ liệu liên quan đến người dùng này.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form method="POST" action="<?php echo SITE_URL; ?>/views/admin/user_delete.php" id="deleteUserForm">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <button type="submit" class="btn btn-danger">Xác nhận xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete user modal
    const deleteButtons = document.querySelectorAll('.delete-user');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
    const deleteUserId = document.getElementById('deleteUserId');
    const deleteUserName = document.getElementById('deleteUserName');
    const deleteUserForm = document.getElementById('deleteUserForm');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');
            
            deleteUserId.value = userId;
            deleteUserName.textContent = userName;
            deleteModal.show();
        });
    });
    
    // Auto-submit filters when select changes
    const filterSelects = document.querySelectorAll('select[name="role"], select[name="status"]');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
});
</script>

<style>
.status-select {
    width: 140px;
    cursor: pointer;
}

.table th {
    border-bottom: 2px solid #e3e6f0;
    font-weight: 600;
}

.card {
    border: none;
    border-radius: 10px;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>