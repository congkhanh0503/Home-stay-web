<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../controllers/BookingController.php';

checkAccess([ROLE_HOST, ROLE_ADMIN]);
$title = 'Quản lý booking';

$controller = new BookingController();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .booking-card {
            border-left: 4px solid #007bff;
            margin-bottom: 15px;
        }
        .booking-card.pending { border-left-color: #ffc107; }
        .booking-card.confirmed { border-left-color: #28a745; }
        .booking-card.cancelled { border-left-color: #dc3545; }
        .booking-card.completed { border-left-color: #6c757d; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="host_dashboard.php">Host Dashboard</a></li>
                        <li class="breadcrumb-item active">Quản lý booking</li>
                    </ol>
                </nav>

                <h2 class="mb-4">Quản lý Booking</h2>

                <?php echo getFlashMessage(); ?>

                <!-- Thống kê -->
                <div class="row">
                    <?php
                    $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
                    $colors = ['warning', 'success', 'secondary', 'danger'];
                    $icons = ['clock', 'check', 'check-double', 'times'];
                    
                    foreach ($statuses as $index => $status) {
                        $result = $controller->getHostBookings($status);
                        $count = $result['success'] ? count($result['data']) : 0;
                    ?>
                    <div class="col-md-3 col-6">
                        <div class="stats-card text-center">
                            <i class="fas fa-<?php echo $icons[$index]; ?> fa-2x mb-2"></i>
                            <h3><?php echo $count; ?></h3>
                            <p><?php echo getConstantLabel('BOOKING_STATUSES', $status); ?></p>
                        </div>
                    </div>
                    <?php } ?>
                </div>

                <!-- Danh sách booking -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Tất cả booking</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $result = $controller->getHostBookings();
                        
                        if ($result['success'] && !empty($result['data'])) {
                            foreach ($result['data'] as $booking) {
                        ?>
                        <div class="card booking-card <?php echo $booking['status']; ?>">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($booking['title']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($booking['user_name']); ?> • 
                                            <?php echo htmlspecialchars($booking['user_phone']); ?>
                                        </small>
                                    </div>
                                    <div class="col-md-2">
                                        <small class="text-muted">Check-in</small>
                                        <p class="mb-0"><?php echo formatDate($booking['check_in']); ?></p>
                                    </div>
                                    <div class="col-md-2">
                                        <small class="text-muted">Check-out</small>
                                        <p class="mb-0"><?php echo formatDate($booking['check_out']); ?></p>
                                    </div>
                                    <div class="col-md-2">
                                        <small class="text-muted">Số khách</small>
                                        <p class="mb-0"><?php echo $booking['guests']; ?> người</p>
                                    </div>
                                    <div class="col-md-1">
                                        <span class="badge bg-<?php echo $colors[array_search($booking['status'], $statuses)]; ?>">
                                            <?php echo getConstantLabel('BOOKING_STATUSES', $booking['status']); ?>
                                        </span>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <a href="booking_detail.php?id=<?php echo $booking['id']; ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i> Chi tiết
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                            }
                        } else {
                        ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có booking nào</h5>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>