<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../controllers/HomestayController.php';

$page_title = 'Tìm kiếm Homestay';

$homestayController = new HomestayController();

// Lấy filters từ URL
$filters = [
    'location' => $_GET['location'] ?? '',
    'min_price' => $_GET['min_price'] ?? '',
    'max_price' => $_GET['max_price'] ?? '',
    'guests' => $_GET['guests'] ?? '',
    'bedrooms' => $_GET['bedrooms'] ?? '',
    'amenities' => isset($_GET['amenities']) ? (is_array($_GET['amenities']) ? $_GET['amenities'] : [$_GET['amenities']]) : [],
    'check_in' => $_GET['check_in'] ?? '',
    'check_out' => $_GET['check_out'] ?? ''
];

// Tìm kiếm homestays
$search_result = $homestayController->search($filters);
$homestays = $search_result['data'] ?? [];

// Lấy tất cả amenities để hiển thị trong filter
$all_amenities = $GLOBALS['CONSTANTS']['AMENITIES'] ?? [];
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Bộ lọc</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="" id="searchForm">
                        <!-- Location -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Địa điểm</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="location" 
                                   value="<?php echo htmlspecialchars($filters['location']); ?>"
                                   placeholder="Thành phố, địa chỉ...">
                        </div>

                        <!-- Price Range -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Khoảng giá</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" 
                                           class="form-control" 
                                           name="min_price" 
                                           value="<?php echo htmlspecialchars($filters['min_price']); ?>"
                                           placeholder="Từ" 
                                           min="0">
                                </div>
                                <div class="col-6">
                                    <input type="number" 
                                           class="form-control" 
                                           name="max_price" 
                                           value="<?php echo htmlspecialchars($filters['max_price']); ?>"
                                           placeholder="Đến" 
                                           min="0">
                                </div>
                            </div>
                        </div>

                        <!-- Guests -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Số khách</label>
                            <select class="form-select" name="guests">
                                <option value="">Chọn số khách</option>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $filters['guests'] == $i ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> khách
                                    </option>
                                <?php endfor; ?>
                                <option value="11" <?php echo $filters['guests'] == '11' ? 'selected' : ''; ?>>Trên 10 khách</option>
                            </select>
                        </div>

                        <!-- Bedrooms -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Số phòng ngủ</label>
                            <select class="form-select" name="bedrooms">
                                <option value="">Chọn số phòng</option>
                                <option value="1" <?php echo $filters['bedrooms'] == '1' ? 'selected' : ''; ?>>1 phòng</option>
                                <option value="2" <?php echo $filters['bedrooms'] == '2' ? 'selected' : ''; ?>>2 phòng</option>
                                <option value="3" <?php echo $filters['bedrooms'] == '3' ? 'selected' : ''; ?>>3 phòng</option>
                                <option value="4" <?php echo $filters['bedrooms'] == '4' ? 'selected' : ''; ?>>4+ phòng</option>
                            </select>
                        </div>

                        <!-- Amenities -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Tiện nghi</label>
                            <div class="amenities-checkboxes">
                                <?php foreach ($all_amenities as $key => $label): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="amenities[]" 
                                               value="<?php echo $key; ?>" 
                                               id="amenity_<?php echo $key; ?>"
                                               <?php echo in_array($key, $filters['amenities']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="amenity_<?php echo $key; ?>">
                                            <?php echo $label; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Date Range (hidden but preserved) -->
                        <?php if (!empty($filters['check_in'])): ?>
                            <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($filters['check_in']); ?>">
                        <?php endif; ?>
                        <?php if (!empty($filters['check_out'])): ?>
                            <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($filters['check_out']); ?>">
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>Áp dụng bộ lọc
                            </button>
                            <a href="<?php echo SITE_URL; ?>/views/homestay/search.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Xóa bộ lọc
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Results -->
        <div class="col-lg-9">
            <!-- Search Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <?php if (!empty($filters['location'])): ?>
                            Homestay tại <?php echo htmlspecialchars($filters['location']); ?>
                        <?php else: ?>
                            Tất cả homestay
                        <?php endif; ?>
                    </h2>
                    <p class="text-muted mb-0">
                        Tìm thấy <strong><?php echo count($homestays); ?></strong> kết quả
                        <?php if (!empty(array_filter($filters))): ?>
                            với bộ lọc đã chọn
                        <?php endif; ?>
                    </p>
                </div>
                
                <!-- Sort Options -->
                <div class="sort-section">
                    <select class="form-select" id="sortResults">
                        <option value="created_at_desc">Mới nhất</option>
                        <option value="price_asc">Giá: Thấp đến cao</option>
                        <option value="price_desc">Giá: Cao đến thấp</option>
                        <option value="rating_desc">Đánh giá cao nhất</option>
                    </select>
                </div>
            </div>

            <!-- Results Grid -->
            <?php if (!empty($homestays)): ?>
                <div class="row">
                    <?php foreach ($homestays as $homestay): ?>
                        <div class="col-md-6 col-xl-4 mb-4">
                            <div class="card homestay-card shadow-sm h-100">
                                <!-- Image -->
                                <div class="position-relative">
                                    <?php if (!empty($homestay['images'])): ?>
                                        <img src="<?php echo getImageUrl($homestay['images'][0]); ?>" 
                                             class="card-img-top" 
                                             alt="<?php echo htmlspecialchars($homestay['title']); ?>"
                                             style="height: 200px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                             style="height: 200px;">
                                            <i class="fas fa-home fa-3x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Favorite Button -->
                                    <button type="button" class="btn btn-light btn-sm position-absolute top-0 end-0 m-2 favorite-btn">
                                        <i class="far fa-heart"></i>
                                    </button>
                                    
                                    <!-- Rating Badge -->
                                    <?php 
                                    // Tính rating trung bình (trong thực tế sẽ lấy từ database)
                                    $avg_rating = 4.5; // Placeholder
                                    ?>
                                    <div class="position-absolute top-0 start-0 m-2">
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-star me-1"></i><?php echo number_format($avg_rating, 1); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="card-body d-flex flex-column">
                                    <!-- Title and Location -->
                                    <h5 class="card-title"><?php echo htmlspecialchars($homestay['title']); ?></h5>
                                    <p class="card-text text-muted small mb-2">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php echo htmlspecialchars($homestay['address']); ?>
                                    </p>
                                    
                                    <!-- Host Info -->
                                    <div class="d-flex align-items-center mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            Chủ nhà: <?php echo htmlspecialchars($homestay['host_name']); ?>
                                        </small>
                                    </div>
                                    
                                    <!-- Amenities Preview -->
                                    <?php if (!empty($homestay['amenities'])): ?>
                                        <div class="mb-3">
                                            <?php $display_amenities = array_slice($homestay['amenities'], 0, 3); ?>
                                            <?php foreach ($display_amenities as $amenity): ?>
                                                <span class="badge bg-light text-dark me-1 mb-1 small">
                                                    <i class="fas fa-check me-1"></i><?php echo $amenity; ?>
                                                </span>
                                            <?php endforeach; ?>
                                            <?php if (count($homestay['amenities']) > 3): ?>
                                                <span class="badge bg-secondary small">+<?php echo count($homestay['amenities']) - 3; ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Details -->
                                    <div class="row text-center small text-muted mb-3">
                                        <div class="col-4">
                                            <i class="fas fa-user-friends"></i><br>
                                            <?php echo $homestay['max_guests']; ?> khách
                                        </div>
                                        <div class="col-4">
                                            <i class="fas fa-bed"></i><br>
                                            <?php echo $homestay['bedrooms']; ?> phòng
                                        </div>
                                        <div class="col-4">
                                            <i class="fas fa-bath"></i><br>
                                            <?php echo $homestay['bathrooms']; ?> tắm
                                        </div>
                                    </div>
                                    
                                    <!-- Price and Action -->
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
                </div>
            <?php else: ?>
                <!-- No Results -->
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Không tìm thấy homestay nào</h4>
                    <p class="text-muted mb-4">Hãy thử điều chỉnh bộ lọc tìm kiếm của bạn</p>
                    <a href="<?php echo SITE_URL; ?>/views/homestay/search.php" class="btn btn-primary">
                        <i class="fas fa-times me-1"></i>Xóa bộ lọc
                    </a>
                </div>
            <?php endif; ?>
            
            <!-- Pagination (placeholder) -->
            <?php if (count($homestays) > 0): ?>
                <nav aria-label="Page navigation" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Trước</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Tiếp</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sort functionality
    const sortSelect = document.getElementById('sortResults');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            // In a real application, this would reload the page with sort parameter
            console.log('Sort by:', this.value);
            // window.location.href = window.location.pathname + '?sort=' + this.value;
        });
    }
    
    // Favorite button functionality
    const favoriteButtons = document.querySelectorAll('.favorite-btn');
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas', 'text-danger');
            } else {
                icon.classList.remove('fas', 'text-danger');
                icon.classList.add('far');
            }
        });
    });
    
    // Price range validation
    const minPrice = document.querySelector('input[name="min_price"]');
    const maxPrice = document.querySelector('input[name="max_price"]');
    
    function validatePriceRange() {
        if (minPrice.value && maxPrice.value && parseInt(minPrice.value) > parseInt(maxPrice.value)) {
            maxPrice.setCustomValidity('Giá tối đa phải lớn hơn giá tối thiểu');
        } else {
            maxPrice.setCustomValidity('');
        }
    }
    
    if (minPrice && maxPrice) {
        minPrice.addEventListener('change', validatePriceRange);
        maxPrice.addEventListener('change', validatePriceRange);
    }
    
    // Auto-submit form when amenities are changed (optional)
    const amenityCheckboxes = document.querySelectorAll('input[name="amenities[]"]');
    amenityCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Uncomment to auto-submit when amenities change
            // document.getElementById('searchForm').submit();
        });
    });
});
</script>

<style>
.homestay-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.homestay-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
}

.favorite-btn {
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.sticky-top {
    z-index: 100;
}

.amenities-checkboxes {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #e9ecef;
    border-radius: 5px;
    padding: 10px;
}

.amenities-checkboxes .form-check {
    margin-bottom: 5px;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>