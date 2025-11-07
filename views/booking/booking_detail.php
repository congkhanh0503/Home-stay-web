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
$page_title = 'Chi tiết đơn đặt #' . $booking_id;
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?php echo SITE_URL; ?>/index.php" class="text-decoration-none">Trang chủ</a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?php echo SITE_URL; ?>/views/booking/bookings.php" class="text-decoration-none">Đơn đặt của tôi</a>
            </li>
            <li class="breadcrumb-item active">Chi tiết đơn đặt #<?php echo $booking_id; ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <!-- Booking Status -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">Đơn đặt #<?php echo $booking_id; ?></h4>
                            <?php
                            $status_class = '';
                            switch ($booking['status']) {
                                case 'pending': $status_class = 'bg-warning text-dark'; break;
                                case 'confirmed': $status_class = 'bg-success'; break;
                                case 'completed': $status_class = 'bg-info'; break;
                                case 'cancelled': $status_class = 'bg-danger'; break;
                                default: $status_class = 'bg-secondary';
                            }
                            ?>
                            <span class="badge <?php echo $status_class; ?> fs-6">
                                <?php echo getConstantLabel('BOOKING_STATUSES', $booking['status']); ?>
                            </span>
                        </div>
                        <div class="text-end">
                            <small class="text-muted">Ngày đặt: <?php echo formatDate($booking['created_at'], 'd/m/Y H:i'); ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Homestay Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-home me-2"></i>Thông tin Homestay</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <?php 
                            $images = json_decode($booking['images'], true) ?: [];
                            if (!empty($images)): 
                            ?>
                                <img src="<?php echo getImageUrl($images[0]); ?>" 
                                     class="img-fluid rounded" 
                                     alt="<?php echo htmlspecialchars($booking['title']); ?>">
                            <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 120px;">
                                    <i class="fas fa-home fa-2x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-9">
                            <h5><?php echo htmlspecialchars($booking['title']); ?></h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?php echo htmlspecialchars($booking['address']); ?>
                            </p>
                            <p class="text-muted mb-2">
                                <i class="fas fa-user me-1"></i>
                                Chủ nhà: <?php echo htmlspecialchars($booking['host_name']); ?>
                            </p>
                            <p class="text-muted mb-0">
                                <i class="fas fa-phone me-1"></i>
                                <?php echo htmlspecialchars($booking['host_phone']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Chi tiết đặt phòng</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ngày nhận phòng</label>
                            <div class="form-control bg-light">
                                <?php echo formatDate($booking['check_in']); ?>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ngày trả phòng</label>
                            <div class="form-control bg-light">
                                <?php echo formatDate($booking['check_out']); ?>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Số khách</label>
                            <div class="form-control bg-light">
                                <?php echo $booking['guests']; ?> khách
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Số đêm</label>
                            <div class="form-control bg-light">
                                <?php echo calculateDays($booking['check_in'], $booking['check_out']); ?> đêm
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Price Summary -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Chi phí</h5>
                </div>
                <div class="card-body">
                    <?php
                    $nights = calculateDays($booking['check_in'], $booking['check_out']);
                    $subtotal = $booking['price_per_night'] * $nights;
                    $service_fee = $subtotal * 0.1;
                    ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span><?php echo formatPrice($booking['price_per_night']); ?> x <?php echo $nights; ?> đêm</span>
                        <span><?php echo formatPrice($subtotal); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Phí dịch vụ (10%)</span>
                        <span><?php echo formatPrice($service_fee); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Tổng cộng</span>
                        <span class="text-primary"><?php echo formatPrice($booking['total_price']); ?></span>
                    </div>
                </div>
            </div>

            <!-- User Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Thông tin người đặt</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Họ tên:</strong> <?php echo htmlspecialchars($booking['user_name']); ?></p>
                    <p class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></p>
                    <p class="mb-0"><strong>Điện thoại:</strong> <?php echo htmlspecialchars($booking['user_phone']); ?></p>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Thao tác</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($booking['status'] === 'pending'): ?>
                            <button type="button" 
                                    class="btn btn-outline-danger cancel-booking" 
                                    data-booking-id="<?php echo $booking_id; ?>"
                                    data-booking-title="<?php echo htmlspecialchars($booking['title']); ?>">
                                <i class="fas fa-times me-1"></i>Hủy đơn đặt
                            </button>
                        <?php endif; ?>
                        
                        <a href="<?php echo SITE_URL; ?>/views/homestay/detail.php?id=<?php echo $booking['homestay_id']; ?>" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>Xem homestay
                        </a>
                        
                        <a href="<?php echo SITE_URL; ?>/views/user/bookings.php" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Booking Modal -->
<div class="modal fade" id="cancelBookingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận hủy đơn đặt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc muốn hủy đơn đặt tại <strong id="bookingTitle"></strong>?</p>
                <p class="text-danger"><small>Hành động này không thể hoàn tác.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <form method="POST" action="<?php echo SITE_URL; ?>/views/booking/cancel.php" id="cancelForm">
                    <input type="hidden" name="booking_id" id="cancelBookingId">
                    <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cancel booking modal
    const cancelButton = document.querySelector('.cancel-booking');
    const cancelForm = document.getElementById('cancelForm');
    const cancelBookingId = document.getElementById('cancelBookingId');
    const bookingTitle = document.getElementById('bookingTitle');
    const cancelModal = new bootstrap.Modal(document.getElementById('cancelBookingModal'));
    
    if (cancelButton) {
        cancelButton.addEventListener('click', function() {
            const bookingId = this.getAttribute('data-booking-id');
            const title = this.getAttribute('data-booking-title');
            
            cancelBookingId.value = bookingId;
            bookingTitle.textContent = title;
            cancelModal.show();
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>