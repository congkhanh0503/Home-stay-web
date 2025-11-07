<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../models/Homestay.php';
require_once __DIR__ . '/../../models/Booking.php';

// Kiểm tra quyền host
if (!isLoggedIn() || !hasRole(ROLE_HOST)) {
    set_flash_message(MSG_ERROR, 'Bạn không có quyền truy cập trang này');
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$page_title = 'Host Dashboard';
$active_tab = 'dashboard';

$homestayModel = new Homestay();
$bookingModel = new Booking();

$host_id = $_SESSION['user_id'];

$stats = [
    'total_bookings' => 0,
    'pending_bookings' => 0,
    'confirmed_bookings' => 0,
    'completed_bookings' => 0,
    'total_revenue' => 0,
    'active_homestays' => 0,
    'total_homestays' => 0
];

// Lấy thống kê bookings
$all_bookings = $bookingModel->getByHost($host_id);
foreach ($all_bookings as $booking) {
    $stats['total_bookings']++;
    
    switch ($booking['status']) {
        case 'pending':
            $stats['pending_bookings']++;
            break;
        case 'confirmed':
            $stats['confirmed_bookings']++;
            break;
        case 'completed':
            $stats['completed_bookings']++;
            $stats['total_revenue'] += $booking['total_price'];
            break;
    }
}

// Lấy thống kê homestays
$host_homestays = $homestayModel->getByHost($host_id);
$stats['total_homestays'] = count($host_homestays);
$stats['active_homestays'] = 0;

foreach ($host_homestays as $homestay) {
    if ($homestay['status'] === 'active') {
        $stats['active_homestays']++;
    }
}

// Lấy bookings gần đây (5 bookings mới nhất)
$recent_bookings = array_slice($all_bookings, 0, 5);

$recent_homestays = array_slice($host_homestays, 0, 5); // Lấy 5 homestay mới nhất
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
                <h1 class="h3 mb-0">Tổng quan Host</h1>
                <div class="text-muted">
                    <i class="fas fa-calendar-alt me-1"></i>
                    <?php echo date('d/m/Y'); ?>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <!-- Total Homestays -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow-sm h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Tổng homestay
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $stats['total_homestays']; ?>
                                    </div>
                                    <small class="text-success">
                                        <i class="fas fa-check-circle me-1"></i>
                                        <?php echo $stats['active_homestays']; ?> đang hoạt động
                                    </small>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-home fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Bookings -->
                <div class="col-md-3 mb-4">
    <div class="card bg-primary text-white">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <h4 class="mb-0"><?php echo $stats['total_bookings'] ?? 0; ?></h4>
                    <p class="mb-0">Tổng đơn đặt</p>
                </div>
                <div class="align-self-center">
                    <i class="fas fa-calendar-check fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-md-3 mb-4">
    <div class="card bg-warning text-white">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <h4 class="mb-0"><?php echo $stats['pending_bookings'] ?? 0; ?></h4>
                    <p class="mb-0">Đơn chờ xác nhận</p>
                </div>
                <div class="align-self-center">
                    <i class="fas fa-clock fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-md-3 mb-4">
    <div class="card bg-success text-white">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <h4 class="mb-0"><?php echo $stats['completed_bookings'] ?? 0; ?></h4>
                    <p class="mb-0">Đơn hoàn thành</p>
                </div>
                <div class="align-self-center">
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-md-3 mb-4">
    <div class="card bg-info text-white">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <h4 class="mb-0"><?php echo formatPrice($stats['total_revenue'] ?? 0); ?></h4>
                    <p class="mb-0">Tổng doanh thu</p>
                </div>
                <div class="align-self-center">
                    <i class="fas fa-dollar-sign fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>


            <div class="row">
                <!-- Recent Bookings -->
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Đơn đặt gần đây</h6>
                            <a href="<?php echo SITE_URL; ?>/views/host/bookings.php" class="btn btn-sm btn-outline-primary">
                                Xem tất cả
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recent_bookings)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Mã đơn</th>
                                                <th>Khách hàng</th>
                                                <th>Homestay</th>
                                                <th>Ngày nhận</th>
                                                <th>Tổng tiền</th>
                                                <th>Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_bookings as $booking): ?>
                                                <tr>
                                                    <td>#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                                    <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($booking['homestay_title']); ?></td>
                                                    <td><?php echo formatDate($booking['check_in']); ?></td>
                                                    <td class="text-success fw-bold"><?php echo formatPrice($booking['total_price']); ?></td>
                                                    <td>
                                                        <?php
                                                        $status_class = '';
                                                        switch ($booking['status']) {
                                                            case 'pending': $status_class = 'status-pending'; break;
                                                            case 'confirmed': $status_class = 'status-confirmed'; break;
                                                            case 'completed': $status_class = 'status-completed'; break;
                                                            case 'cancelled': $status_class = 'status-cancelled'; break;
                                                            default: $status_class = 'bg-secondary';
                                                        }
                                                        ?>
                                                        <span class="status-badge <?php echo $status_class; ?>">
                                                            <?php echo getConstantLabel('BOOKING_STATUSES', $booking['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Chưa có đơn đặt nào</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

<!-- Recent Homestays -->
<div class="col-lg-4 mb-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Homestay của tôi</h6>
            <a href="<?php echo SITE_URL; ?>/views/host/homestays.php" class="btn btn-sm btn-outline-primary">
                Xem tất cả
            </a>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($recent_homestays)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recent_homestays as $homestay): ?>
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
                        
                        <div class="list-group-item p-3">
                            <div class="d-flex align-items-start">
                                <!-- Ảnh homestay -->
                                <div class="flex-shrink-0 me-3">
                                    <?php if (!empty($first_image)): ?>
                                        <img src="<?php echo getImageUrl($first_image); ?>" 
                                             class="rounded" 
                                             width="60" 
                                             height="60"
                                             alt="<?php echo htmlspecialchars($homestay['title']); ?>"
                                             style="object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="width: 60px; height: 60px;">
                                            <i class="fas fa-home text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Thông tin homestay -->
                                <div class="flex-grow-1" style="min-width: 0;"> <!-- min-width: 0 để fix text overflow -->
                                    <h6 class="mb-1 text-truncate" title="<?php echo htmlspecialchars($homestay['title']); ?>">
                                        <?php echo htmlspecialchars($homestay['title']); ?>
                                    </h6>
                                    
                                    <div class="d-flex align-items-center mb-1">
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <span class="text-truncate d-inline-block" style="max-width: 120px;" title="<?php echo htmlspecialchars($homestay['city'] ?? 'Đà Lạt'); ?>">
                                                <?php echo htmlspecialchars($homestay['city'] ?? 'Đà Lạt'); ?>
                                            </span>
                                        </small>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-success fw-bold">
                                            <?php echo formatPrice($homestay['price_per_night']); ?>
                                            <small class="text-muted">/đêm</small>
                                        </span>
                                        <span class="badge <?php echo $homestay['status'] === 'active' ? 'bg-success' : ($homestay['status'] === 'pending' ? 'bg-warning' : 'bg-secondary'); ?>">
                                            <?php 
                                            $status_labels = [
                                                'active' => 'Đang hoạt động',
                                                'inactive' => 'Ngừng hoạt động', 
                                                'pending' => 'Chờ duyệt',
                                                'rejected' => 'Từ chối'
                                            ];
                                            echo $status_labels[$homestay['status']] ?? $homestay['status'];
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-home fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-2">Chưa có homestay nào</p>
                    <a href="<?php echo SITE_URL; ?>/views/host/add_homestay.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Thêm homestay đầu tiên
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white">
            <h6 class="mb-0">Thao tác nhanh</h6>
        </div>
        <div class="card-body">
            <div class="d-grid gap-2">
                <a href="<?php echo SITE_URL; ?>/views/host/add_homestay.php" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Thêm homestay mới
                </a>
                <a href="<?php echo SITE_URL; ?>/views/host/bookings.php" class="btn btn-outline-primary">
                    <i class="fas fa-list-alt me-2"></i>Quản lý đơn đặt
                </a>
                <a href="<?php echo SITE_URL; ?>/views/host/revenue.php" class="btn btn-outline-info">
                    <i class="fas fa-chart-bar me-2"></i>Xem doanh thu
                </a>
            </div>
        </div>
    </div>
</div>
<style>
/* CSS cho phần homestay */
.list-group-item {
    border: none;
    border-bottom: 1px solid #eee;
    transition: background-color 0.2s;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}

.list-group-item:last-child {
    border-bottom: none;
}

.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Badge styles */
.badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

.bg-success {
    background-color: #198754 !important;
}

.bg-warning {
    background-color: #ffc107 !important;
    color: #000 !important;
}

.bg-secondary {
    background-color: #6c757d !important;
}

/* Price styling */
.text-success {
    color: #198754 !important;
    font-weight: 600;
}

/* Icon styling */
.fas.fa-map-marker-alt {
    font-size: 0.8rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .list-group-item .d-flex {
        flex-direction: column;
        text-align: center;
    }
    
    .list-group-item .flex-shrink-0 {
        margin-bottom: 1rem;
    }
    
    .list-group-item .d-flex.justify-content-between {
        flex-direction: row;
    }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>