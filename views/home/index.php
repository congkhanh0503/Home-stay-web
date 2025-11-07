<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../models/Homestay.php';

$page_title = 'Trang chủ';

// Lấy homestays nổi bật
$homestayModel = new Homestay();
$featured_homestays = $homestayModel->getPopular(6);
$recent_homestays = $homestayModel->search(['limit' => 6]);

// Lấy thống kê (nếu có)
$conn = getDbConnection();
$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM homestays WHERE status = 'active') as total_homestays,
    (SELECT COUNT(*) FROM users WHERE role = 'host' AND status = 'active') as total_hosts,
    (SELECT COUNT(*) FROM bookings WHERE status = 'completed') as total_bookings";
$stats = fetchOne($conn, $stats_sql);
closeDbConnection($conn);
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section position-relative overflow-hidden">
    <!-- Background Video với overlay -->
    <div class="video-container position-absolute top-0 start-0 w-100 h-100">
        <video autoplay muted loop playsinline class="w-100 h-100 object-fit-cover">
            <source src="<?php echo SITE_URL; ?>/img/back1.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="video-overlay position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-50"></div>
    </div>
    
    <!-- Loading Spinner -->
    <div class="video-loading position-absolute top-50 start-50 translate-middle z-3">
        <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    
    <!-- Nội dung chính -->
    <div class="container position-relative z-2">
        <div class="row align-items-center min-vh-80 py-5">
            <div class="col-lg-6 text-white">
                <h1 class="display-4 fw-bold mb-4 text-shadow">Tìm Homestay Hoàn Hảo Cho Kỳ Nghỉ Của Bạn</h1>
                <p class="lead mb-4 fs-5 text-shadow">Khám phá hàng ngàn homestay độc đáo với giá cả phải chăng. Trải nghiệm như người bản địa tại mọi điểm đến.</p>
                
                <!-- Search Form -->
                <div class="search-card bg-white rounded-4 shadow-lg p-4">
                    <form action="<?php echo SITE_URL; ?>/views/homestay/search.php" method="GET" class="search-form">
                        <div class="row g-3 align-items-end">
                            <!-- Địa điểm -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label small fw-bold text-dark mb-2">
                                        <i class="fas fa-map-marker-alt me-1 text-primary"></i>Địa điểm
                                    </label>
                                    <input type="text" 
                                           name="location" 
                                           class="form-control border-primary border-2 py-3" 
                                           placeholder="Nhập thành phố, địa điểm..."
                                           required
                                           value="<?php echo isset($_GET['location']) ? htmlspecialchars($_GET['location']) : ''; ?>">
                                </div>
                            </div>
                            
                            <!-- Nhận phòng -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label small fw-bold text-dark mb-2">
                                        <i class="fas fa-calendar-alt me-1 text-primary"></i>Nhận phòng
                                    </label>
                                    <input type="date" 
                                           name="check_in" 
                                           class="form-control border-primary border-2 py-3" 
                                           placeholder="Chọn ngày"
                                           min="<?php echo date('Y-m-d'); ?>"
                                           required
                                           value="<?php echo isset($_GET['check_in']) ? htmlspecialchars($_GET['check_in']) : ''; ?>">
                                </div>
                            </div>
                            
                            <!-- Trả phòng -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label small fw-bold text-dark mb-2">
                                        <i class="fas fa-calendar-check me-1 text-primary"></i>Trả phòng
                                    </label>
                                    <input type="date" 
                                           name="check_out" 
                                           class="form-control border-primary border-2 py-3" 
                                           placeholder="Chọn ngày"
                                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                           required
                                           value="<?php echo isset($_GET['check_out']) ? htmlspecialchars($_GET['check_out']) : ''; ?>">
                                </div>
                            </div>
                            
                            <!-- Số khách -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label small fw-bold text-dark mb-2">
                                        <i class="fas fa-user-friends me-1 text-primary"></i>Số khách
                                    </label>
                                    <select name="guests" class="form-control border-primary border-2 py-3" required>
                                        <option value="">Chọn số khách</option>
                                        <?php for ($i = 1; $i <= 10; $i++): ?>
                                            <option value="<?php echo $i; ?>" 
                                                <?php echo (isset($_GET['guests']) && $_GET['guests'] == $i) ? 'selected' : ''; ?>>
                                                <?php echo $i; ?> khách
                                            </option>
                                        <?php endfor; ?>
                                        <option value="10+" <?php echo (isset($_GET['guests']) && $_GET['guests'] == '10+') ? 'selected' : ''; ?>>10+ khách</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Nút tìm kiếm -->
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold fs-5 rounded-3">
                                    <i class="fas fa-search me-2"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Quick locations -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="quick-locations">
                                    <span class="text-muted small me-2">Địa điểm phổ biến:</span>
                                    <?php 
                                    $popular_locations = ['Sapa', 'Đà Lạt', 'Hội An', 'Phú Quốc', 'Nha Trang'];
                                    foreach ($popular_locations as $location): 
                                    ?>
                                        <a href="#" class="quick-location-link badge bg-light text-dark text-decoration-none me-2 mb-1" 
                                           data-location="<?php echo htmlspecialchars($location); ?>">
                                            <?php echo htmlspecialchars($location); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Trust indicators -->
                <div class="trust-indicators mt-4">
                    <div class="d-flex flex-wrap gap-4">
                        <div class="d-flex align-items-center text-white-50">
                            <i class="fas fa-shield-alt me-2"></i>
                            <small>Bảo mật thanh toán</small>
                        </div>
                        <div class="d-flex align-items-center text-white-50">
                            <i class="fas fa-star me-2"></i>
                            <small>Đánh giá thực tế</small>
                        </div>
                        <div class="d-flex align-items-center text-white-50">
                            <i class="fas fa-headset me-2"></i>
                            <small>Hỗ trợ 24/7</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scroll indicator -->
    <div class="scroll-indicator position-absolute bottom-0 start-50 translate-middle-x mb-4 z-2">
        <a href="#stats-section" class="text-white text-decoration-none">
            <div class="d-flex flex-column align-items-center">
                <span class="small mb-1">Khám phá thêm</span>
                <i class="fas fa-chevron-down"></i>
            </div>
        </a>
    </div>
</section>

<!-- Stats Section -->
<section id="stats-section" class="stats-section py-4 bg-white">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-3">
                <div class="stat-item">
                    <h3 class="text-primary fw-bold"><?php echo isset($stats['total_homestays']) ? number_format($stats['total_homestays']) : '0'; ?></h3>
                    <p class="text-muted mb-0">Homestay Chất Lượng</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-item">
                    <h3 class="text-primary fw-bold"><?php echo isset($stats['total_hosts']) ? number_format($stats['total_hosts']) : '0'; ?></h3>
                    <p class="text-muted mb-0">Chủ Nhà Thân Thiện</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-item">
                    <h3 class="text-primary fw-bold"><?php echo isset($stats['total_bookings']) ? number_format($stats['total_bookings']) : '0'; ?></h3>
                    <p class="text-muted mb-0">Đơn Đặt Thành Công</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Homestays -->
<section class="featured-section py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="section-title">Homestay Nổi Bật</h2>
                <p class="text-muted">Khám phá những homestay được yêu thích nhất</p>
            </div>
        </div>
        
        <div class="row">
            <?php if (!empty($featured_homestays)): ?>
                <?php foreach ($featured_homestays as $homestay): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card homestay-card shadow-sm h-100">
                            <!-- Image -->
                            <div class="position-relative">
                                <?php if (!empty($homestay['images'])): ?>
                                    <img src="<?php echo getImageUrl($homestay['images'][0]); ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($homestay['title']); ?>"
                                         style="height: 250px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="<?php echo getImageUrl(''); ?>" 
                                         class="card-img-top" 
                                         alt="No image"
                                         style="height: 250px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-star me-1"></i>
                                        <?php echo isset($homestay['avg_rating']) ? number_format($homestay['avg_rating'], 1) : '0.0'; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($homestay['title']); ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?php echo strlen($homestay['address']) > 50 ? substr($homestay['address'], 0, 50) . '...' : $homestay['address']; ?>
                                </p>
                                
                                <!-- Amenities -->
                                <?php if (!empty($homestay['amenities'])): ?>
                                    <div class="mb-2">
                                        <?php $display_amenities = array_slice($homestay['amenities'], 0, 2); ?>
                                        <?php foreach ($display_amenities as $amenity): ?>
                                            <span class="badge bg-light text-dark me-1 mb-1 small">
                                                <i class="fas fa-check me-1"></i><?php echo htmlspecialchars($amenity); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="text-primary mb-0"><?php echo formatPrice($homestay['price_per_night']); ?></h5>
                                            <small class="text-muted">/ đêm</small>
                                        </div>
                                        <a href="<?php echo SITE_URL; ?>/views/homestay/detail.php?id=<?php echo $homestay['id']; ?>" 
                                           class="btn btn-primary btn-sm">
                                            Xem chi tiết
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-home fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Chưa có homestay nào</h4>
                    <p class="text-muted">Hãy quay lại sau để khám phá những homestay mới</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="<?php echo SITE_URL; ?>/views/homestay/search.php" class="btn btn-outline-primary btn-lg">
                    Xem Tất Cả Homestay <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Recent Homestays -->
<section class="recent-section py-5 bg-light">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="section-title">Homestay Mới Nhất</h2>
                <p class="text-muted">Khám phá những homestay vừa được thêm vào</p>
            </div>
        </div>
        
        <div class="row">
            <?php if (!empty($recent_homestays['data'])): ?>
                <?php foreach (array_slice($recent_homestays['data'], 0, 3) as $homestay): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card homestay-card shadow-sm h-100">
                            <?php if (!empty($homestay['images'])): ?>
                                <img src="<?php echo getImageUrl($homestay['images'][0]); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($homestay['title']); ?>"
                                     style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <img src="<?php echo getImageUrl(''); ?>" 
                                     class="card-img-top" 
                                     alt="No image"
                                     style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($homestay['title']); ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?php echo strlen($homestay['address']) > 60 ? substr($homestay['address'], 0, 60) . '...' : $homestay['address']; ?>
                                </p>
                                
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="text-primary mb-0"><?php echo formatPrice($homestay['price_per_night']); ?></h5>
                                            <small class="text-muted">/ đêm</small>
                                        </div>
                                        <span class="badge bg-success">
                                            <i class="fas fa-plus me-1"></i>Mới
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-4">
                    <p class="text-muted">Chưa có homestay mới</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="how-it-works py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="section-title">Cách Thức Hoạt Động</h2>
                <p class="text-muted">Đặt homestay chỉ với 3 bước đơn giản</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <div class="step-item">
                    <div class="step-icon bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-search fa-2x"></i>
                    </div>
                    <h4>1. Tìm Kiếm</h4>
                    <p class="text-muted">Tìm homestay phù hợp với nhu cầu và ngân sách của bạn</p>
                </div>
            </div>
            
            <div class="col-md-4 text-center mb-4">
                <div class="step-item">
                    <div class="step-icon bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                    <h4>2. Đặt Phòng</h4>
                    <p class="text-muted">Chọn ngày và đặt homestay với vài cú click</p>
                </div>
            </div>
            
            <div class="col-md-4 text-center mb-4">
                <div class="step-item">
                    <div class="step-icon bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-home fa-2x"></i>
                    </div>
                    <h4>3. Tận Hưởng</h4>
                    <p class="text-muted">Trải nghiệm kỳ nghỉ tuyệt vời tại homestay</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Become Host CTA -->
<?php if (!isLoggedIn() || (isLoggedIn() && !hasRole(ROLE_HOST))): ?>
<section class="cta-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 class="mb-3">Bạn muốn trở thành chủ nhà?</h3>
                <p class="mb-4">Kiếm thu nhập thụ động bằng cách cho thuê không gian của bạn. Tham gia cộng đồng chủ nhà của chúng tôi ngay hôm nay!</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo SITE_URL; ?>/views/host/register.php" class="btn btn-warning btn-lg">
                        <i class="fas fa-plus me-2"></i>Đăng Ký Làm Host
                    </a>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/views/auth/register.php?role=host" class="btn btn-warning btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Đăng Ký Ngay
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Testimonials -->
<section class="testimonials-section py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="section-title">Khách Hàng Nói Gì</h2>
                <p class="text-muted">Những trải nghiệm thực tế từ khách hàng của chúng tôi</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card testimonial-card h-100">
                    <div class="card-body text-center">
                        <div class="star-rating mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="card-text mb-4">"Homestay tuyệt vời! Chủ nhà rất thân thiện, view đẹp và đầy đủ tiện nghi. Sẽ quay lại!"</p>
                        <div class="client-info">
                            <img src="<?php echo getImageUrl(''); ?>" 
                                 class="rounded-circle mb-2" 
                                 width="60" 
                                 height="60" 
                                 alt="Client">
                            <h6 class="mb-1">Nguyễn Thị A</h6>
                            <small class="text-muted">Đà Lạt</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card testimonial-card h-100">
                    <div class="card-body text-center">
                        <div class="star-rating mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <p class="card-text mb-4">"Trải nghiệm tốt từ A đến Z. Homestay sạch sẽ, giá cả hợp lý. Rất đáng để thử!"</p>
                        <div class="client-info">
                            <img src="<?php echo getImageUrl(''); ?>" 
                                 class="rounded-circle mb-2" 
                                 width="60" 
                                 height="60" 
                                 alt="Client">
                            <h6 class="mb-1">Trần Văn B</h6>
                            <small class="text-muted">Hà Nội</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card testimonial-card h-100">
                    <div class="card-body text-center">
                        <div class="star-rating mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="card-text mb-4">"Dịch vụ chuyên nghiệp, homestay đúng như mô tả. Cảm ơn đội ngũ hỗ trợ rất nhiệt tình!"</p>
                        <div class="client-info">
                            <img src="<?php echo getImageUrl(''); ?>" 
                                 class="rounded-circle mb-2" 
                                 width="60" 
                                 height="60" 
                                 alt="Client">
                            <h6 class="mb-1">Lê Thị C</h6>
                            <small class="text-muted">TP.HCM</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set min date for check-out
    const checkInInput = document.querySelector('input[name="check_in"]');
    const checkOutInput = document.querySelector('input[name="check_out"]');
    
    if (checkInInput && checkOutInput) {
        checkInInput.addEventListener('change', function() {
            const checkInDate = new Date(this.value);
            checkInDate.setDate(checkInDate.getDate() + 1);
            checkOutInput.min = checkInDate.toISOString().split('T')[0];
            
            // If check-out date is before new min date, clear it
            if (checkOutInput.value && new Date(checkOutInput.value) < checkInDate) {
                checkOutInput.value = '';
            }
        });
    }
    
    // Initialize date inputs
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    if (checkInInput) checkInInput.min = new Date().toISOString().split('T')[0];
    if (checkOutInput) checkOutInput.min = tomorrow.toISOString().split('T')[0];
    
    // Video loading handler
    const video = document.querySelector('video');
    const loading = document.querySelector('.video-loading');
    
    if (video) {
        video.addEventListener('loadeddata', function() {
            if (loading) {
                loading.classList.add('fade-out');
                setTimeout(() => {
                    loading.style.display = 'none';
                }, 500);
            }
        });
        
        // Fallback: hide loading after 3s
        setTimeout(() => {
            if (loading && loading.style.display !== 'none') {
                loading.classList.add('fade-out');
                setTimeout(() => {
                    loading.style.display = 'none';
                }, 500);
            }
        }, 3000);
    }
    
    // Quick location links
    const quickLocationLinks = document.querySelectorAll('.quick-location-link');
    quickLocationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const location = this.getAttribute('data-location');
            const locationInput = document.querySelector('input[name="location"]');
            if (locationInput) {
                locationInput.value = location;
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>