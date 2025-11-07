<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../models/Homestay.php';

// Kiểm tra quyền admin
if (!isLoggedIn() || !hasRole(ROLE_ADMIN)) {
    set_flash_message(MSG_ERROR, 'Bạn không có quyền truy cập trang này');
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$page_title = 'Quản lý Homestay';
$active_tab = 'homestays';

$homestayModel = new Homestay();

// Xử lý filters
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$filters = [];
if (!empty($status_filter)) $filters['status'] = $status_filter;
if (!empty($search)) $filters['search'] = $search;

// Lấy danh sách homestays
$homestays = $homestayModel->getAll($filters);

// Xử lý cập nhật trạng thái homestay
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $homestay_id = intval($_POST['homestay_id']);
    $status = sanitizeInput($_POST['status']);
    
    if ($homestayModel->updateStatus($homestay_id, $status)) {
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
                <h1 class="h3 mb-0">Quản lý Homestay</h1>
                <div class="text-muted">
                    Tổng: <strong><?php echo count($homestays); ?></strong> homestay
                </div>
            </div>

            <!-- Filters Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Trạng thái</label>
                            <select class="form-select" name="status">
                                <option value="">Tất cả trạng thái</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Đang hoạt động</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Ngừng hoạt động</option>
                                <option value="blocked" <?php echo $status_filter === 'blocked' ? 'selected' : ''; ?>>Bị khóa</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Tìm kiếm</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   placeholder="Tên homestay hoặc địa chỉ...">
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i>Lọc
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Homestays Table -->
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Danh sách Homestay</h6>
                    <div class="btn-group">
                        <button type="button" class="btn btn-success btn-sm">
                            <i class="fas fa-download me-1"></i>Xuất Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="homestaysTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Homestay</th>
                                    <th>Chủ nhà</th>
                                    <th>Giá/đêm</th>
                                    <th>Đánh giá</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($homestays)): ?>
                                    <?php foreach ($homestays as $homestay): ?>
                                        <tr>
                                            <td>#<?php echo $homestay['id']; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php 
                                                    // Xử lý images - kiểm tra nếu đã là mảng hay cần decode
                                                    $images = $homestay['images'];
                                                    if (is_string($images)) {
                                                        $images = json_decode($images, true) ?: [];
                                                    }
                                                    if (!is_array($images)) {
                                                        $images = [];
                                                    }
                                                    
                                                    if (!empty($images)): ?>
                                                        <img src="<?php echo getImageUrl($images[0] ?? ''); ?>" 
                                                             class="rounded me-3" 
                                                             width="60" 
                                                             height="60"
                                                             alt="<?php echo htmlspecialchars($homestay['title']); ?>"
                                                             style="object-fit: cover;"
                                                             onerror="this.src='<?php echo SITE_URL; ?>/assets/img/no-image.jpg'">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center me-3" 
                                                             style="width: 60px; height: 60px;">
                                                            <i class="fas fa-home text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($homestay['title']); ?></h6>
                                                        <small class="text-muted">
                                                            <i class="fas fa-map-marker-alt me-1"></i>
                                                            <?php echo strlen($homestay['address']) > 50 ? 
                                                                substr($homestay['address'], 0, 50) . '...' : 
                                                                $homestay['address']; ?>
                                                        </small>
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-bed me-1"></i>
                                                            <?php echo $homestay['bedrooms']; ?> phòng · 
                                                            <?php echo $homestay['max_guests']; ?> khách
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($homestay['host_name'] ?? 'N/A'); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($homestay['host_email'] ?? 'N/A'); ?></small>
                                                </div>
                                            </td>
                                            <td class="text-success fw-bold">
                                                <?php echo formatPrice($homestay['price_per_night']); ?>
                                            </td>
                                            <td>
                                                <div class="star-rating small">
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="fas fa-star-half-alt text-warning"></i>
                                                </div>
                                                <small class="text-muted">4.5 (15)</small>
                                            </td>
                                            <td>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="homestay_id" value="<?php echo $homestay['id']; ?>">
                                                    <input type="hidden" name="update_status" value="1">
                                                    <select name="status" 
                                                            class="form-select form-select-sm status-select 
                                                                <?php echo $homestay['status'] === 'active' ? 'border-success' : 
                                                                       ($homestay['status'] === 'inactive' ? 'border-warning' : 'border-danger'); ?>"
                                                            onchange="this.form.submit()">
                                                        <option value="active" <?php echo $homestay['status'] === 'active' ? 'selected' : ''; ?>>Đang hoạt động</option>
                                                        <option value="inactive" <?php echo $homestay['status'] === 'inactive' ? 'selected' : ''; ?>>Ngừng hoạt động</option>
                                                        <option value="blocked" <?php echo $homestay['status'] === 'blocked' ? 'selected' : ''; ?>>Khóa</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td>
                                                <small><?php echo formatDate($homestay['created_at']); ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?php echo SITE_URL; ?>/views/homestay/detail.php?id=<?php echo $homestay['id']; ?>" 
                                                       class="btn btn-outline-primary" title="Xem chi tiết" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?php echo SITE_URL; ?>/views/admin/homestay_edit.php?id=<?php echo $homestay['id']; ?>" 
                                                       class="btn btn-outline-warning" title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger delete-homestay" 
                                                            data-homestay-id="<?php echo $homestay['id']; ?>"
                                                            data-homestay-title="<?php echo htmlspecialchars($homestay['title']); ?>"
                                                            title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-home fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">Không tìm thấy homestay nào</p>
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
                    <div class="card bg-success text-white">
                        <div class="card-body text-center py-3">
                            <h5 class="mb-1"><?php echo count(array_filter($homestays, fn($h) => ($h['status'] ?? '') === 'active')); ?></h5>
                            <small>Đang hoạt động</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center py-3">
                            <h5 class="mb-1"><?php echo count(array_filter($homestays, fn($h) => ($h['status'] ?? '') === 'inactive')); ?></h5>
                            <small>Ngừng hoạt động</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center py-3">
                            <h5 class="mb-1"><?php echo count(array_filter($homestays, fn($h) => ($h['status'] ?? '') === 'blocked')); ?></h5>
                            <small>Bị khóa</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center py-3">
                            <h5 class="mb-1"><?php echo count($homestays); ?></h5>
                            <small>Tổng homestay</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Homestay Modal -->
<div class="modal fade" id="deleteHomestayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Xác nhận xóa homestay</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc muốn xóa homestay <strong id="deleteHomestayTitle"></strong>?</p>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Cảnh báo:</strong> Hành động này sẽ xóa vĩnh viễn homestay và tất cả dữ liệu liên quan (đơn đặt, đánh giá).
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form method="POST" action="<?php echo SITE_URL; ?>/views/admin/homestay_delete.php" id="deleteHomestayForm">
                    <input type="hidden" name="homestay_id" id="deleteHomestayId">
                    <button type="submit" class="btn btn-danger">Xác nhận xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete homestay modal
    const deleteButtons = document.querySelectorAll('.delete-homestay');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteHomestayModal'));
    const deleteHomestayId = document.getElementById('deleteHomestayId');
    const deleteHomestayTitle = document.getElementById('deleteHomestayTitle');
    const deleteHomestayForm = document.getElementById('deleteHomestayForm');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const homestayId = this.getAttribute('data-homestay-id');
            const homestayTitle = this.getAttribute('data-homestay-title');
            
            deleteHomestayId.value = homestayId;
            deleteHomestayTitle.textContent = homestayTitle;
            deleteModal.show();
        });
    });
    
    // Auto-submit filters when select changes
    const filterSelects = document.querySelectorAll('select[name="status"]');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
});
</script>

<style>
.status-select {
    width: 150px;
    cursor: pointer;
}

.star-rating {
    color: #ffc107;
}

.table th {
    border-bottom: 2px solid #e3e6f0;
    font-weight: 600;
}

.card {
    border: none;
    border-radius: 10px;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
