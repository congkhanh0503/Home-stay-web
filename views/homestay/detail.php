<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../controllers/HomestayController.php';

$page_title = 'Chi tiết Homestay';

// Kiểm tra ID homestay
if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash_message(MSG_ERROR, 'Không tìm thấy homestay');
    header('Location: ' . SITE_URL . '/views/homestay/search.php');
    exit;
}

$homestay_id = intval($_GET['id']);
$homestayController = new HomestayController();
$homestay_result = $homestayController->getHomestayById($homestay_id);

if (!$homestay_result['success']) {
    set_flash_message(MSG_ERROR, $homestay_result['message']);
    header('Location: ' . SITE_URL . '/views/homestay/search.php');
    exit;
}

$homestay = $homestay_result['data'];
$page_title = $homestay['title'] . ' - ' . SITE_NAME;

// Lấy reviews (placeholder - trong thực tế sẽ gọi Review model)
$reviews = [];
$average_rating = 4.5; // Placeholder

// Kiểm tra availability nếu có dates từ URL
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';
$guests = $_GET['guests'] ?? 1;

// Trong thực tế, bạn sẽ kiểm tra availability với các dates này
$is_available = true;
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
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($homestay['title']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Image Gallery -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-0">
                    <?php if (!empty($homestay['images'])): ?>
                        <div id="homestayCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php foreach ($homestay['images'] as $index => $image): ?>
                                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                        <img src="<?php echo getImageUrl($image); ?>" 
                                             class="d-block w-100" 
                                             alt="<?php echo htmlspecialchars($homestay['title']); ?>"
                                             style="height: 400px; object-fit: cover;">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($homestay['images']) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#homestayCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#homestayCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Thumbnail Gallery -->
                        <?php if (count($homestay['images']) > 1): ?>
                            <div class="p-3">
                                <div class="row g-2">
                                    <?php foreach ($homestay['images'] as $index => $image): ?>
                                        <div class="col-3">
                                            <img src="<?php echo getImageUrl($image); ?>" 
                                                 class="img-thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                                                 alt="Thumbnail <?php echo $index + 1; ?>"
                                                 style="height: 80px; width: 100%; object-fit: cover; cursor: pointer;"
                                                 data-bs-target="#homestayCarousel" 
                                                 data-bs-slide-to="<?php echo $index; ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-5 bg-light">
                            <i class="fas fa-home fa-4x text-muted mb-3"></i>
                            <p class="text-muted">Chưa có hình ảnh</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Homestay Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h1 class="h3 mb-2"><?php echo htmlspecialchars($homestay['title']); ?></h1>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?php echo htmlspecialchars($homestay['address']); ?>
                            </p>
                            <div class="d-flex align-items-center">
                                <div class="star-rating me-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= floor($average_rating) ? 'text-warning' : ($i <= $average_rating ? 'text-warning' : 'text-light'); ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-muted"><?php echo number_format($average_rating, 1); ?> · 15 đánh giá</span>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm favorite-btn">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>

                    <!-- Host Info -->
                    <div class="border-top pt-3 mt-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <img src="<?php echo getImageUrl(''); ?>" 
                                     class="rounded-circle" 
                                     width="60" 
                                     height="60" 
                                     alt="Host">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Chủ nhà: <?php echo htmlspecialchars($homestay['host_name']); ?></h6>
                                <p class="text-muted small mb-1">
                                    <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($homestay['host_phone']); ?>
                                </p>
                                <small class="text-success">
                                    <i class="fas fa-shield-alt me-1"></i>Đã xác minh danh tính
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Mô tả</h5>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($homestay['description'])); ?></p>
                </div>
            </div>

            <!-- Amenities -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Tiện nghi</h5>
                    <div class="row">
                        <?php if (!empty($homestay['amenities'])): ?>
                            <?php foreach ($homestay['amenities'] as $amenity): ?>
                                <div class="col-md-6 mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <?php echo $amenity; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <p class="text-muted">Chưa có thông tin tiện nghi</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Reviews -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Đánh giá</h5>
                    
                    <!-- Rating Summary -->
                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            <h2 class="text-primary mb-1"><?php echo number_format($average_rating, 1); ?></h2>
                            <div class="star-rating mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= floor($average_rating) ? 'text-warning' : ($i <= $average_rating ? 'text-warning' : 'text-light'); ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <small class="text-muted">15 đánh giá</small>
                        </div>
                        <div class="col-md-8">
                            <!-- Rating breakdown would go here -->
                        </div>
                    </div>

                    <!-- Reviews List -->
                    <div class="reviews-list">
                        <!-- Sample Review -->
                        <div class="review-item border-top pt-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <img src="<?php echo getImageUrl(''); ?>" 
                                         class="rounded-circle" 
                                         width="50" 
                                         height="50" 
                                         alt="Reviewer">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Nguyễn Văn A</h6>
                                    <div class="star-rating mb-2">
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                    </div>
                                    <p class="mb-2">Homestay rất tuyệt vời! View đẹp, chủ nhà thân thiện. Sẽ quay lại lần sau.</p>
                                    <small class="text-muted">Tháng 12, 2024</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Load More Reviews -->
                    <div class="text-center mt-4">
                        <button class="btn btn-outline-primary">Xem thêm đánh giá</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h3 class="text-primary"><?php echo formatPrice($homestay['price_per_night']); ?></h3>
                        <small class="text-muted">/ đêm</small>
                    </div>

                    <!-- Booking Form -->
                    <form method="POST" action="<?php echo SITE_URL; ?>/views/booking/create_booking.php">
                        <input type="hidden" name="homestay_id" value="<?php echo $homestay['id']; ?>">
                        
                        <!-- Date Picker -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ngày nhận phòng</label>
                            <input type="date" 
                                   class="form-control" 
                                   name="check_in" 
                                   value="<?php echo htmlspecialchars($check_in); ?>"
                                   min="<?php echo date('Y-m-d'); ?>"
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ngày trả phòng</label>
                            <input type="date" 
                                   class="form-control" 
                                   name="check_out" 
                                   value="<?php echo htmlspecialchars($check_out); ?>"
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                   required>
                        </div>
                        
                        <!-- Guests -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Số khách</label>
                            <select class="form-select" name="guests" required>
                                <?php for ($i = 1; $i <= $homestay['max_guests']; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $guests == $i ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> khách
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <small class="form-text text-muted">Tối đa <?php echo $homestay['max_guests']; ?> khách</small>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="price-breakdown border-top pt-3 mt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span><?php echo formatPrice($homestay['price_per_night']); ?> x <span id="nightsCount">0</span> đêm</span>
                                <span id="subtotal">0 VNĐ</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Phí dịch vụ</span>
                                <span id="serviceFee">0 VNĐ</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold border-top pt-2">
                                <span>Tổng cộng</span>
                                <span id="totalPrice">0 VNĐ</span>
                            </div>
                        </div>

                        <!-- Book Button -->
                        <div class="mt-4">
                            <?php if (isLoggedIn()): ?>
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-calendar-check me-2"></i>Đặt ngay
                                </button>
                            <?php else: ?>
                                <a href="<?php echo SITE_URL; ?>/views/auth/login.php" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập để đặt
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- Availability Notice -->
                        <?php if (!$is_available && !empty($check_in) && !empty($check_out)): ?>
                            <div class="alert alert-warning mt-3 mb-0">
                                <small>
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Homestay không khả dụng trong khoảng thời gian đã chọn
                                </small>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculate price based on dates
    const checkInInput = document.querySelector('input[name="check_in"]');
    const checkOutInput = document.querySelector('input[name="check_out"]');
    const nightsCount = document.getElementById('nightsCount');
    const subtotal = document.getElementById('subtotal');
    const serviceFee = document.getElementById('serviceFee');
    const totalPrice = document.getElementById('totalPrice');
    
    const pricePerNight = <?php echo $homestay['price_per_night']; ?>;
    
    function calculatePrice() {
        if (checkInInput.value && checkOutInput.value) {
            const checkIn = new Date(checkInInput.value);
            const checkOut = new Date(checkOutInput.value);
            const nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
            
            if (nights > 0) {
                const subtotalAmount = pricePerNight * nights;
                const serviceFeeAmount = subtotalAmount * 0.1; // 10% service fee
                const totalAmount = subtotalAmount + serviceFeeAmount;
                
                nightsCount.textContent = nights;
                subtotal.textContent = formatPrice(subtotalAmount);
                serviceFee.textContent = formatPrice(serviceFeeAmount);
                totalPrice.textContent = formatPrice(totalAmount);
            }
        }
    }
    
    function formatPrice(price) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(price);
    }
    
    checkInInput.addEventListener('change', function() {
        if (this.value) {
            const nextDay = new Date(this.value);
            nextDay.setDate(nextDay.getDate() + 1);
            checkOutInput.min = nextDay.toISOString().split('T')[0];
            
            if (checkOutInput.value && new Date(checkOutInput.value) < nextDay) {
                checkOutInput.value = '';
            }
        }
        calculatePrice();
    });
    
    checkOutInput.addEventListener('change', calculatePrice);
    
    // Initialize date inputs
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    checkInInput.min = today.toISOString().split('T')[0];
    checkOutInput.min = tomorrow.toISOString().split('T')[0];
    
    // Favorite button
    const favoriteBtn = document.querySelector('.favorite-btn');
    if (favoriteBtn) {
        favoriteBtn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas', 'text-danger');
                // Add to favorites (AJAX call)
            } else {
                icon.classList.remove('fas', 'text-danger');
                icon.classList.add('far');
                // Remove from favorites (AJAX call)
            }
        });
    }
    
    // Thumbnail click to change main image
    const thumbnails = document.querySelectorAll('.img-thumbnail');
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            const slideTo = this.getAttribute('data-bs-slide-to');
            const carousel = new bootstrap.Carousel(document.getElementById('homestayCarousel'));
            carousel.to(parseInt(slideTo));
            
            // Update active state
            thumbnails.forEach(t => t.classList.remove('active', 'border-primary'));
            this.classList.add('active', 'border-primary');
        });
    });
    
    // Calculate initial price if dates are pre-filled
    calculatePrice();
});
</script>

<style>
.carousel-item img {
    border-radius: 10px 10px 0 0;
}

.img-thumbnail.active {
    border-color: #0d6efd !important;
}

.sticky-top {
    z-index: 100;
}

.star-rating {
    color: #ffc107;
}

.price-breakdown {
    font-size: 0.9rem;
}

.favorite-btn {
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>