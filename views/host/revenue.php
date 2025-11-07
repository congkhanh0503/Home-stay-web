
<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../models/Booking.php';

// Kiểm tra quyền host
if (!isLoggedIn() || !hasRole(ROLE_HOST)) {
    set_flash_message(MSG_ERROR, 'Bạn không có quyền truy cập trang này');
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$page_title = 'Quản lý Doanh thu';
$active_tab = 'revenue';

$bookingModel = new Booking();
$host_id = $_SESSION['user_id'];

// Xử lý filters
$month = $_GET['month'] ?? date('Y-m');
$year = $_GET['year'] ?? date('Y');

// Lấy bookings hoàn thành trong tháng
$completed_bookings = $bookingModel->getByHost($host_id, [
    'status' => 'completed',
    'month' => $month
]);

$revenue_stats = [
    'total_revenue' => 0,
    'total_bookings' => 0,
    'total_nights' => 0,
    'avg_booking' => 0,
    'top_homestays' => []
];

// Tính tổng doanh thu từ các booking hoàn thành
foreach ($completed_bookings as $booking) {
    $revenue_stats['total_revenue'] += $booking['total_price'];
    $revenue_stats['total_bookings']++;
    $revenue_stats['total_nights'] += calculateDays($booking['check_in'], $booking['check_out']);
}

// Tính trung bình mỗi đơn
if ($revenue_stats['total_bookings'] > 0) {
    $revenue_stats['avg_booking'] = $revenue_stats['total_revenue'] / $revenue_stats['total_bookings'];
}

// Lấy top homestays có doanh thu cao nhất
$top_homestays = [];
foreach ($completed_bookings as $booking) {
    $homestay_id = $booking['homestay_id'];
    if (!isset($top_homestays[$homestay_id])) {
        $top_homestays[$homestay_id] = [
            'title' => $booking['homestay_title'],
            'revenue' => 0,
            'booking_count' => 0
        ];
    }
    $top_homestays[$homestay_id]['revenue'] += $booking['total_price'];
    $top_homestays[$homestay_id]['booking_count']++;
}

// Sắp xếp theo doanh thu giảm dần và lấy top 5
usort($top_homestays, function($a, $b) {
    return $b['revenue'] - $a['revenue'];
});
$revenue_stats['top_homestays'] = array_slice($top_homestays, 0, 5);

// Dữ liệu cho biểu đồ doanh thu theo tháng
$monthly_revenue = array_fill(0, 12, 0); // Khởi tạo mảng 12 tháng với giá trị 0

// Lấy doanh thu cho cả năm
$yearly_bookings = $bookingModel->getByHost($host_id, [
    'status' => 'completed',
    'year' => $year
]);

// Tính doanh thu theo tháng
foreach ($yearly_bookings as $booking) {
    $booking_month = (int)date('m', strtotime($booking['check_in']));
    $monthly_revenue[$booking_month - 1] += $booking['total_price']; // -1 vì mảng bắt đầu từ 0
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
                <h1 class="h3 mb-0">Quản lý Doanh thu</h1>
                <div class="text-muted">
                    <i class="fas fa-chart-line me-1"></i>
                    Phân tích tài chính
                </div>
            </div>

            <!-- Filters -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tháng</label>
                            <select class="form-select" name="month">
                                <?php 
                                $current_year = date('Y');
                                $selected_year = $year;
                                for ($i = 1; $i <= 12; $i++): 
                                    $month_value = $selected_year . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                                    $month_name = "Tháng $i/$selected_year";
                                ?>
                                    <option value="<?php echo $month_value; ?>" <?php echo $month === $month_value ? 'selected' : ''; ?>>
                                        <?php echo $month_name; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Năm</label>
                            <select class="form-select" name="year" id="yearSelect">
                                <?php for ($i = date('Y'); $i >= 2020; $i--): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $year == $i ? 'selected' : ''; ?>>
                                        Năm <?php echo $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i>Xem báo cáo
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Revenue Stats -->
            <div class="row mb-4">
                <div class="col-md-3 mb-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center py-4">
                            <i class="fas fa-dollar-sign fa-2x mb-3"></i>
                            <h4 class="mb-1"><?php echo formatPrice($revenue_stats['total_revenue'] ?? 0); ?></h4>
                            <p class="mb-0">Tổng doanh thu</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center py-4">
                            <i class="fas fa-calendar-check fa-2x mb-3"></i>
                            <h4 class="mb-1"><?php echo $revenue_stats['total_bookings'] ?? 0; ?></h4>
                            <p class="mb-0">Tổng đơn hàng</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center py-4">
                            <i class="fas fa-bed fa-2x mb-3"></i>
                            <h4 class="mb-1"><?php echo $revenue_stats['total_nights'] ?? 0; ?></h4>
                            <p class="mb-0">Tổng số đêm</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center py-4">
                            <i class="fas fa-chart-line fa-2x mb-3"></i>
                            <h4 class="mb-1"><?php echo formatPrice($revenue_stats['avg_booking'] ?? 0); ?></h4>
                            <p class="mb-0">Trung bình/đơn</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Revenue Chart -->
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">Doanh thu theo tháng (<?php echo $year; ?>)</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueChart" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top Homestays -->
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">Homestay có doanh thu cao nhất</h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($revenue_stats['top_homestays'])): ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($revenue_stats['top_homestays'] as $homestay): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($homestay['title']); ?></h6>
                                                <small class="text-muted"><?php echo $homestay['booking_count']; ?> đơn</small>
                                            </div>
                                            <span class="text-success fw-bold">
                                                <?php echo formatPrice($homestay['revenue']); ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-chart-bar fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Chưa có dữ liệu</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed Bookings -->
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Đơn hàng hoàn thành (<?php echo date('m/Y', strtotime($month)); ?>)</h6>
                    <span class="badge bg-success"><?php echo count($completed_bookings); ?> đơn</span>
                </div>
                <div class="card-body">
                    <?php if (!empty($completed_bookings)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Khách hàng</th>
                                        <th>Homestay</th>
                                        <th>Ngày</th>
                                        <th>Số đêm</th>
                                        <th>Doanh thu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($completed_bookings as $booking): ?>
                                        <tr>
                                            <td>#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['homestay_title']); ?></td>
                                            <td><?php echo formatDate($booking['check_in']); ?> - <?php echo formatDate($booking['check_out']); ?></td>
                                            <td><?php echo calculateDays($booking['check_in'], $booking['check_out']); ?></td>
                                            <td class="text-success fw-bold"><?php echo formatPrice($booking['total_price']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-receipt fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Không có đơn hàng hoàn thành trong tháng này</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Biểu đồ doanh thu
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        const monthlyRevenue = <?php echo json_encode($monthly_revenue); ?>;
        const months = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 
                       'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
        
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Doanh thu (VND)',
                    data: monthlyRevenue,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN', {
                                    style: 'currency',
                                    currency: 'VND'
                                }).format(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return (value / 1000000).toFixed(1) + ' tr';
                                }
                                if (value >= 1000) {
                                    return (value / 1000).toFixed(0) + ' k';
                                }
                                return value;
                            }
                        }
                    }
                }
            }
        });
    }

    // Cập nhật danh sách tháng khi thay đổi năm
    const yearSelect = document.getElementById('yearSelect');
    if (yearSelect) {
        yearSelect.addEventListener('change', function() {
            // Form sẽ tự động submit khi thay đổi năm
            this.form.submit();
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>