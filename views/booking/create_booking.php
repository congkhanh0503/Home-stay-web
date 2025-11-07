<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../controllers/BookingController.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    set_flash_message(MSG_ERROR, 'Vui lòng đăng nhập để đặt homestay');
    header('Location: ' . SITE_URL . '/views/auth/login.php');
    exit;
}

$page_title = 'Đặt Homestay';

// Kiểm tra homestay_id
if (!isset($_POST['homestay_id']) || empty($_POST['homestay_id'])) {
    set_flash_message(MSG_ERROR, 'Không tìm thấy homestay');
    header('Location: ' . SITE_URL . '/views/homestay/search.php');
    exit;
}

$homestay_id = intval($_POST['homestay_id']);
$check_in = $_POST['check_in'] ?? '';
$check_out = $_POST['check_out'] ?? '';
$guests = intval($_POST['guests'] ?? 1);

// Lấy thông tin homestay
require_once __DIR__ . '/../../controllers/HomestayController.php';
$homestayController = new HomestayController();
$homestay_result = $homestayController->getHomestayById($homestay_id);

if (!$homestay_result['success']) {
    set_flash_message(MSG_ERROR, $homestay_result['message']);
    header('Location: ' . SITE_URL . '/views/homestay/search.php');
    exit;
}

$homestay = $homestay_result['data'];

// Xử lý đặt phòng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    $bookingController = new BookingController();
    $result = $bookingController->create();
    
    if ($result['success']) {
        header('Location: ' . SITE_URL . '/views/booking/booking_success.php?id=' . $result['booking_id']);
        exit;
    }
}

// Tính toán giá
$nights = 0;
$subtotal = 0;
$service_fee = 0;
$total = 0;

if (!empty($check_in) && !empty($check_out)) {
    $nights = calculateDays($check_in, $check_out);
    $subtotal = $homestay['price_per_night'] * $nights;
    $service_fee = $subtotal * 0.1; // 10% phí dịch vụ
    $total = $subtotal + $service_fee;
}
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
                <a href="<?php echo SITE_URL; ?>/views/homestay/search.php" class="text-decoration-none">Tìm homestay</a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?php echo SITE_URL; ?>/views/homestay/detail.php?id=<?php echo $homestay_id; ?>" class="text-decoration-none">
                    <?php echo htmlspecialchars($homestay['title']); ?>
                </a>
            </li>
            <li class="breadcrumb-item active">Xác nhận đặt phòng</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Xác nhận đặt phòng</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="homestay_id" value="<?php echo $homestay_id; ?>">
                        <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>">
                        <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>">
                        <input type="hidden" name="guests" value="<?php echo $guests; ?>">
                        
                        <!-- Homestay Info -->
                        <div class="mb-4">
                            <h5 class="text-primary mb-3">Thông tin Homestay</h5>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <?php if (!empty($homestay['images'])): ?>
                                                <img src="<?php echo getImageUrl($homestay['images'][0]); ?>" 
                                                     class="img-fluid rounded" 
                                                     alt="<?php echo htmlspecialchars($homestay['title']); ?>">
                                            <?php else: ?>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 100px;">
                                                    <i class="fas fa-home fa-2x text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-9">
                                            <h6><?php echo htmlspecialchars($homestay['title']); ?></h6>
                                            <p class="text-muted mb-2">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                <?php echo htmlspecialchars($homestay['address']); ?>
                                            </p>
                                            <p class="text-muted mb-2">
                                                <i class="fas fa-user me-1"></i>
                                                Chủ nhà: <?php echo htmlspecialchars($homestay['host_name']); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Details -->
                        <div class="mb-4">
                            <h5 class="text-primary mb-3">Chi tiết đặt phòng</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Ngày nhận phòng</label>
                                    <div class="form-control bg-light">
                                        <?php echo formatDate($check_in, 'd/m/Y'); ?>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Ngày trả phòng</label>
                                    <div class="form-control bg-light">
                                        <?php echo formatDate($check_out, 'd/m/Y'); ?>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Số khách</label>
                                    <div class="form-control bg-light">
                                        <?php echo $guests; ?> khách
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Số đêm</label>
                                    <div class="form-control bg-light">
                                        <?php echo $nights; ?> đêm
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="mb-4">
                            <h5 class="text-primary mb-3">Chi phí</h5>
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><?php echo formatPrice($homestay['price_per_night']); ?> x <?php echo $nights; ?> đêm</span>
                                        <span><?php echo formatPrice($subtotal); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Phí dịch vụ (10%)</span>
                                        <span><?php echo formatPrice($service_fee); ?></span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold fs-5">
                                        <span>Tổng cộng</span>
                                        <span class="text-primary"><?php echo formatPrice($total); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Info -->
                        <div class="mb-4">
                            <h5 class="text-primary mb-3">Thông tin người đặt</h5>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Họ tên</label>
                                            <div class="form-control bg-light">
                                                <?php echo htmlspecialchars($_SESSION['user_fullname'] ?? ''); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Email</label>
                                            <div class="form-control bg-light">
                                                <?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold">Số điện thoại</label>
                                            <div class="form-control bg-light">
                                                <?php echo htmlspecialchars($_SESSION['user_phone'] ?? ''); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                                <label class="form-check-label" for="agreeTerms">
                                    Tôi đồng ý với <a href="#" class="text-decoration-none">điều khoản và điều kiện</a> đặt phòng
                                </label>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?php echo SITE_URL; ?>/views/homestay/detail.php?id=<?php echo $homestay_id; ?>" 
                               class="btn btn-secondary me-md-2">
                                <i class="fas fa-arrow-left me-1"></i>Quay lại
                            </a>
                            <button type="submit" name="confirm_booking" class="btn btn-primary">
                                <i class="fas fa-check me-1"></i>Xác nhận đặt phòng
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>