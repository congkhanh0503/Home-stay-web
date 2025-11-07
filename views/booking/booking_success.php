<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../controllers/BookingController.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/views/auth/login.php');
    exit;
}

// Kiểm tra booking_id
if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash_message(MSG_ERROR, 'Không tìm thấy booking');
    header('Location: ' . SITE_URL . '/views/booking/bookings.php');
    exit;
}

$booking_id = intval($_GET['id']);
$bookingController = new BookingController();
$booking_result = $bookingController->getBookingDetail($booking_id);

if (!$booking_result['success']) {
    set_flash_message(MSG_ERROR, $booking_result['message']);
    header('Location: ' . SITE_URL . '/views/booking/bookings.php');
    exit;
}

$booking = $booking_result['data'];
$page_title = 'Đặt phòng thành công';
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-success">
                <div class="card-header bg-success text-white text-center">
                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                    <h3 class="mb-0">Đặt phòng thành công!</h3>
                </div>
                <div class="card-body text-center">
                    <p class="lead mb-4">Cảm ơn bạn đã đặt phòng. Đơn đặt của bạn đang chờ chủ homestay xác nhận.</p>
                    
                    <!-- Booking Summary -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title text-primary mb-3">Thông tin đặt phòng</h5>
                            <div class="row text-start">
                                <div class="col-md-6 mb-2">
                                    <strong>Mã đặt phòng:</strong> #<?php echo $booking['id']; ?>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong>Trạng thái:</strong> 
                                    <span class="badge bg-warning text-dark"><?php echo getConstantLabel('BOOKING_STATUSES', $booking['status']); ?></span>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong>Homestay:</strong> <?php echo htmlspecialchars($booking['title']); ?>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong>Chủ nhà:</strong> <?php echo htmlspecialchars($booking['host_name']); ?>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong>Check-in:</strong> <?php echo formatDate($booking['check_in']); ?>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong>Check-out:</strong> <?php echo formatDate($booking['check_out']); ?>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong>Số khách:</strong> <?php echo $booking['guests']; ?> khách
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong>Tổng tiền:</strong> <?php echo formatPrice($booking['total_price']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Next Steps -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Bước tiếp theo:</h6>
                        <ul class="mb-0 ps-3">
                            <li>Chủ homestay sẽ xác nhận đơn đặt của bạn trong thời gian sớm nhất</li>
                            <li>Bạn sẽ nhận được email thông báo khi đơn đặt được xác nhận</li>
                            <li>Bạn có thể theo dõi trạng thái đơn đặt trong mục "Đơn đặt của tôi"</li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 d-md-flex justify-content-center">
                        <a href="<?php echo SITE_URL; ?>/views/booking/booking_detail.php?id=<?php echo $booking_id; ?>" 
                           class="btn btn-primary me-md-2">
                            <i class="fas fa-eye me-1"></i>Xem chi tiết
                        </a>
                        <a href="<?php echo SITE_URL; ?>/views/booking/bookings.php" 
                           class="btn btn-outline-primary me-md-2">
                            <i class="fas fa-list me-1"></i>Đơn đặt của tôi
                        </a>
                        <a href="<?php echo SITE_URL; ?>/views/homestay/search.php" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-search me-1"></i>Tìm homestay khác
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>