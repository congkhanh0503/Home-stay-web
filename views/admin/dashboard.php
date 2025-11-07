<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../controllers/AdminController.php';

// Kiểm tra quyền admin
if (!isLoggedIn() || !hasRole(ROLE_ADMIN)) {
    set_flash_message(MSG_ERROR, 'Bạn không có quyền truy cập trang này');
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$page_title = 'Admin Dashboard';
$active_tab = 'dashboard';

$adminController = new AdminController();
$stats_result = $adminController->getDashboardStats();
$stats = $stats_result['data'] ?? [];

// Lấy bookings gần đây
require_once __DIR__ . '/../../models/Booking.php';
$bookingModel = new Booking();
$recent_bookings = $bookingModel->getAll(['limit' => 5]);
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
                <h1 class="h3 mb-0">Tổng quan hệ thống</h1>
                <div class="text-muted">
                    <i class="fas fa-calendar-alt me-1"></i>
                    <?php echo date('d/m/Y'); ?>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <!-- Total Users -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow-sm h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Tổng người dùng
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo number_format($stats['users']['total_users'] ?? 0); ?>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-success">
                                            <i class="fas fa-users me-1"></i>
                                            <?php echo number_format($stats['users']['total_customers'] ?? 0); ?> khách hàng
                                        </small>
                                        <br>
                                        <small class="text-info">
                                            <i class="fas fa-user-tie me-1"></i>
                                            <?php echo number_format($stats['users']['total_hosts'] ?? 0); ?> chủ nhà
                                        </small>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Homestays -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow-sm h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Tổng homestay
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo number_format($stats['homestays']['total_homestays'] ?? 0); ?>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-success">
                                            <i class="fas fa-check-circle me-1"></i>
                                            <?php echo number_format($stats['homestays']['active_homestays'] ?? 0); ?> đang hoạt động
                                        </small>
                                        <br>
                                        <small class="text-warning">
                                            <i class="fas fa-pause-circle me-1"></i>
                                            <?php echo number_format($stats['homestays']['inactive_homestays'] ?? 0); ?> ngừng hoạt động
                                        </small>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-home fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Bookings -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow-sm h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Tổng đơn đặt
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo number_format($stats['bookings']['total_bookings'] ?? 0); ?>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-info">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo number_format($stats['bookings']['pending_bookings'] ?? 0); ?> chờ xác nhận
                                        </small>
                                        <br>
                                        <small class="text-success">
                                            <i class="fas fa-check me-1"></i>
                                            <?php echo number_format($stats['bookings']['confirmed_bookings'] ?? 0); ?> đã xác nhận
                                        </small>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Revenue -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow-sm h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Doanh thu
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo formatPrice($stats['bookings']['total_revenue'] ?? 0); ?>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-success">
                                            <i class="fas fa-check-circle me-1"></i>
                                            <?php echo number_format($stats['bookings']['completed_bookings'] ?? 0); ?> đơn hoàn thành
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-chart-line me-1"></i>
                                            Doanh thu hệ thống
                                        </small>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <!-- Revenue Chart -->
                <div class="col-xl-8 col-lg-7">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Doanh thu 7 ngày gần đây</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-area">
                                <canvas id="revenueChart" height="320"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pie Chart -->
                <div class="col-xl-4 col-lg-5">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Phân loại homestay</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-pie pt-4 pb-2">
                                <canvas id="homestayPieChart" height="300"></canvas>
                            </div>
                            <div class="mt-4 text-center small">
                                <span class="mr-2">
                                    <i class="fas fa-circle text-primary"></i> Đang hoạt động
                                </span>
                                <span class="mr-2">
                                    <i class="fas fa-circle text-warning"></i> Ngừng hoạt động
                                </span>
                                <span class="mr-2">
                                    <i class="fas fa-circle text-danger"></i> Bị khóa
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Đơn đặt gần đây</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="recentBookingsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Homestay</th>
                                    <th>Ngày đặt</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_bookings)): ?>
                                    <?php foreach ($recent_bookings as $booking): ?>
                                        <tr>
                                            <td>#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['homestay_title']); ?></td>
                                            <td><?php echo formatDate($booking['created_at']); ?></td>
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
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?php echo SITE_URL; ?>/views/booking/detail.php?id=<?php echo $booking['id']; ?>" 
                                                       class="btn btn-outline-primary" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-info" title="Gửi thông báo">
                                                        <i class="fas fa-bell"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">Chưa có đơn đặt nào</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <a href="<?php echo SITE_URL; ?>/admin.php?page=bookings" class="btn btn-primary btn-sm">
                            <i class="fas fa-list me-1"></i>Xem tất cả đơn đặt
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card bg-primary text-white shadow-sm">
                        <div class="card-body">
                            <div class="text-center">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <h6>Quản lý người dùng</h6>
                                <a href="<?php echo SITE_URL; ?>/admin.php?page=users" class="btn btn-light btn-sm mt-2">
                                    Truy cập <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card bg-success text-white shadow-sm">
                        <div class="card-body">
                            <div class="text-center">
                                <i class="fas fa-home fa-2x mb-2"></i>
                                <h6>Quản lý homestay</h6>
                                <a href="<?php echo SITE_URL; ?>/admin.php?page=homestays" class="btn btn-light btn-sm mt-2">
                                    Truy cập <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card bg-info text-white shadow-sm">
                        <div class="card-body">
                            <div class="text-center">
                                <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                <h6>Báo cáo & Thống kê</h6>
                                <a href="<?php echo SITE_URL; ?>/admin.php?page=reports" class="btn btn-light btn-sm mt-2">
                                    Truy cập <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card bg-warning text-white shadow-sm">
                        <div class="card-body">
                            <div class="text-center">
                                <i class="fas fa-cog fa-2x mb-2"></i>
                                <h6>Cài đặt hệ thống</h6>
                                <button class="btn btn-light btn-sm mt-2">
                                    Cấu hình <i class="fas fa-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['6 ngày trước', '5 ngày trước', '4 ngày trước', '3 ngày trước', '2 ngày trước', 'Hôm qua', 'Hôm nay'],
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: [1200000, 1900000, 1500000, 2500000, 2200000, 3000000, 2800000],
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    pointBackgroundColor: '#4e73df',
                    pointBorderColor: '#4e73df',
                    pointHoverBackgroundColor: '#2e59d9',
                    pointHoverBorderColor: '#2e59d9',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN', {
                                    style: 'currency',
                                    currency: 'VND'
                                }).format(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += new Intl.NumberFormat('vi-VN', {
                                    style: 'currency',
                                    currency: 'VND'
                                }).format(context.parsed.y);
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // Homestay Pie Chart
    const pieCtx = document.getElementById('homestayPieChart');
    if (pieCtx) {
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: ['Đang hoạt động', 'Ngừng hoạt động', 'Bị khóa'],
                datasets: [{
                    data: [<?php echo $stats['homestays']['active_homestays'] ?? 0; ?>, 
                           <?php echo $stats['homestays']['inactive_homestays'] ?? 0; ?>, 0],
                    backgroundColor: ['#4e73df', '#f6c23e', '#e74a3b'],
                    hoverBackgroundColor: ['#2e59d9', '#f4b619', '#e02d1b'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            },
        });
    }
});
</script>

<style>
.card {
    border: none;
    border-radius: 10px;
}

.border-left-primary {
    border-left: 4px solid #4e73df !important;
}

.border-left-success {
    border-left: 4px solid #1cc88a !important;
}

.border-left-info {
    border-left: 4px solid #36b9cc !important;
}

.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}

.text-xs {
    font-size: 0.7rem;
}

.font-weight-bold {
    font-weight: 700 !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>