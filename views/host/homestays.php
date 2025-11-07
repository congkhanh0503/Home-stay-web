<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../models/Homestay.php';

// Kiểm tra quyền host
if (!isLoggedIn() || !hasRole(ROLE_HOST)) {
    set_flash_message(MSG_ERROR, 'Bạn không có quyền truy cập trang này');
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$page_title = 'Quản lý Homestay';
$active_tab = 'homestays';

$homestayModel = new Homestay();
$host_id = $_SESSION['user_id'];

// Xử lý filters
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$filters = ['host_id' => $host_id];
if (!empty($status_filter)) $filters['status'] = $status_filter;
if (!empty($search)) $filters['search'] = $search;

// Lấy danh sách homestays
$homestays = $homestayModel->getByHost($host_id, $filters);

// Xử lý xóa homestay
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_homestay'])) {
    $homestay_id = intval($_POST['homestay_id']);
    
    // Kiểm tra homestay thuộc về host
    $homestay = $homestayModel->getById($homestay_id);
    if ($homestay && $homestay['host_id'] == $host_id) {
        if ($homestayModel->delete($homestay_id)) {
            set_flash_message(MSG_SUCCESS, 'Xóa homestay thành công');
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            set_flash_message(MSG_ERROR, 'Có lỗi xảy ra khi xóa homestay');
        }
    } else {
        set_flash_message(MSG_ERROR, 'Homestay không tồn tại hoặc không thuộc về bạn');
    }
}

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $homestay_id = intval($_POST['homestay_id']);
    $status = sanitizeInput($_POST['status']);
    
    // Kiểm tra homestay thuộc về host
    $homestay = $homestayModel->getById($homestay_id);
    if ($homestay && $homestay['host_id'] == $host_id) {
        if ($homestayModel->updateStatus($homestay_id, $status)) {
            set_flash_message(MSG_SUCCESS, 'Cập nhật trạng thái thành công');
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            set_flash_message(MSG_ERROR, 'Có lỗi xảy ra khi cập nhật trạng thái');
        }
    } else {
        set_flash_message(MSG_ERROR, 'Homestay không tồn tại hoặc không thuộc về bạn');
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../includes/host_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Quản lý Homestay</h1>
                <a href="<?php echo SITE_URL; ?>/views/host/add_homestay.php" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>Thêm homestay mới
                </a>
            </div>

            <!-- Flash Message -->
            <?php echo getFlashMessage(); ?>

            <!-- Filters -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Trạng thái</label>
                            <select class="form-select" name="status">
                                <option value="">Tất cả trạng thái</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Đang hoạt động</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Ngừng hoạt động</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Chờ duyệt</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Tìm kiếm</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   placeholder="Tìm theo tên homestay hoặc địa chỉ...">
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i>Lọc
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Homestays Grid -->
            <div class="row">
                <?php if (!empty($homestays)): ?>
                    <?php foreach ($homestays as $homestay): ?>
                        <?php 
                        // Xử lý images
                        $images = [];
                        if (!empty($homestay['images'])) {
                            if (is_string($homestay['images'])) {
                                $images = json_decode($homestay['images'], true) ?: [];
                            } elseif (is_array($homestay['images'])) {
                                $images = $homestay['images'];
                            }
                        }
                        $first_image = !empty($images) ? $images[0] : '';
                        ?>
                        
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card shadow-sm h-100">
                                <!-- Image -->
                                <?php if (!empty($first_image)): ?>
                                    <img src="<?php echo getImageUrl($first_image); ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($homestay['title']); ?>"
                                         style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                         style="height: 200px;">
                                        <i class="fas fa-home fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Status Badge -->
                                <div class="position-absolute top-0 end-0 m-2">
                                    <?php
                                    $status_class = '';
                                    $status_text = '';
                                    switch ($homestay['status']) {
                                        case 'active':
                                            $status_class = 'bg-success';
                                            $status_text = 'Đang hoạt động';
                                            break;
                                        case 'inactive':
                                            $status_class = 'bg-secondary';
                                            $status_text = 'Ngừng hoạt động';
                                            break;
                                        case 'pending':
                                            $status_class = 'bg-warning';
                                            $status_text = 'Chờ duyệt';
                                            break;
                                        case 'rejected':
                                            $status_class = 'bg-danger';
                                            $status_text = 'Từ chối';
                                            break;
                                        default:
                                            $status_class = 'bg-secondary';
                                            $status_text = $homestay['status'];
                                    }
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </div>
                                
                                <div class="card-body d-flex flex-column">
                                    <!-- Title -->
                                    <h5 class="card-title text-truncate" title="<?php echo htmlspecialchars($homestay['title']); ?>">
                                        <?php echo htmlspecialchars($homestay['title']); ?>
                                    </h5>
                                    
                                    <!-- Location -->
                                    <p class="card-text text-muted small mb-2">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php echo htmlspecialchars($homestay['address']); ?>
                                    </p>
                                    
                                    <!-- Price -->
                                    <div class="mb-2">
                                        <span class="h5 text-success"><?php echo formatPrice($homestay['price_per_night']); ?></span>
                                        <small class="text-muted">/đêm</small>
                                    </div>
                                    
                                    <!-- Details -->
                                    <div class="d-flex justify-content-between text-muted small mb-3">
                                        <span>
                                            <i class="fas fa-bed me-1"></i>
                                            <?php echo $homestay['bedrooms']; ?> phòng
                                        </span>
                                        <span>
                                            <i class="fas fa-bath me-1"></i>
                                            <?php echo $homestay['bathrooms']; ?> tắm
                                        </span>
                                        <span>
                                            <i class="fas fa-users me-1"></i>
                                            <?php echo $homestay['max_guests']; ?> khách
                                        </span>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="mt-auto">
                                        <div class="btn-group w-100" role="group">
                                            <a href="<?php echo SITE_URL; ?>/views/host/edit_homestay.php?id=<?php echo $homestay['id']; ?>" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <!-- Status Update Form -->
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="homestay_id" value="<?php echo $homestay['id']; ?>">
                                                <input type="hidden" name="update_status" value="1">
                                                <select name="status" 
                                                        class="form-select form-select-sm"
                                                        onchange="this.form.submit()"
                                                        style="width: auto;">
                                                    <option value="active" <?php echo $homestay['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                    <option value="inactive" <?php echo $homestay['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                </select>
                                            </form>
                                            
                                            <!-- Delete Form -->
                                            <form method="POST" action="" class="d-inline" 
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa homestay này?');">
                                                <input type="hidden" name="homestay_id" value="<?php echo $homestay['id']; ?>">
                                                <input type="hidden" name="delete_homestay" value="1">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-home fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có homestay nào</h5>
                            <p class="text-muted mb-3">Bắt đầu bằng cách thêm homestay đầu tiên của bạn</p>
                            <a href="<?php echo SITE_URL; ?>/views/host/add_homestay.php" class="btn btn-success">
                                <i class="fas fa-plus me-1"></i>Thêm homestay mới
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Stats Summary -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center py-3">
                            <h5 class="mb-1"><?php echo count(array_filter($homestays, fn($h) => $h['status'] === 'active')); ?></h5>
                            <small>Đang hoạt động</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center py-3">
                            <h5 class="mb-1"><?php echo count(array_filter($homestays, fn($h) => $h['status'] === 'pending')); ?></h5>
                            <small>Chờ duyệt</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body text-center py-3">
                            <h5 class="mb-1"><?php echo count(array_filter($homestays, fn($h) => $h['status'] === 'inactive')); ?></h5>
                            <small>Ngừng hoạt động</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center py-3">
                            <h5 class="mb-1"><?php echo count($homestays); ?></h5>
                            <small>Tổng số</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.btn-group .form-select {
    border-radius: 0;
    border-left: 1px solid #dee2e6;
    border-right: 1px solid #dee2e6;
}

.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>