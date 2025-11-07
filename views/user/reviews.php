<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../models/Review.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/views/auth/login.php');
    exit;
}

$page_title = 'Đánh giá của tôi';
$active_tab = 'reviews';

// Lấy danh sách reviews của user
$reviewModel = new Review();
$reviews = $reviewModel->getByUser($_SESSION['user_id']);
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0"><i class="fas fa-star me-2"></i>Đánh giá của tôi</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($reviews)): ?>
                        <div class="reviews-list">
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-item border-bottom pb-4 mb-4">
                                    <div class="row">
                                        <div class="col-md-2 text-center">
                                            <!-- Star Rating -->
                                            <div class="star-rating mb-2">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-light'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <small class="text-muted"><?php echo formatDate($review['created_at']); ?></small>
                                        </div>
                                        
                                        <div class="col-md-10">
                                            <!-- Homestay Info -->
                                            <div class="homestay-info mb-3">
                                                <h6 class="mb-1">
                                                    <a href="<?php echo SITE_URL; ?>/views/homestay/detail.php?id=<?php echo $review['homestay_id']; ?>" 
                                                       class="text-decoration-none">
                                                        <?php echo htmlspecialchars($review['homestay_title']); ?>
                                                    </a>
                                                </h6>
                                                <?php if (!empty($review['homestay_images'])): 
                                                    $images = json_decode($review['homestay_images'], true); ?>
                                                    <img src="<?php echo getImageUrl($images[0] ?? ''); ?>" 
                                                         class="rounded me-2" 
                                                         width="80" 
                                                         height="60" 
                                                         alt="<?php echo htmlspecialchars($review['homestay_title']); ?>"
                                                         style="object-fit: cover;">
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Review Comment -->
                                            <div class="review-comment">
                                                <?php if (!empty($review['comment'])): ?>
                                                    <p class="mb-2"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                                <?php else: ?>
                                                    <p class="text-muted mb-2"><i>Không có nhận xét</i></p>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Actions -->
                                            <div class="review-actions mt-2">
                                                <button type="button" 
                                                        class="btn btn-outline-primary btn-sm edit-review"
                                                        data-review-id="<?php echo $review['id']; ?>"
                                                        data-rating="<?php echo $review['rating']; ?>"
                                                        data-comment="<?php echo htmlspecialchars($review['comment']); ?>">
                                                    <i class="fas fa-edit me-1"></i>Sửa
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-outline-danger btn-sm delete-review"
                                                        data-review-id="<?php echo $review['id']; ?>"
                                                        data-homestay-title="<?php echo htmlspecialchars($review['homestay_title']); ?>">
                                                    <i class="fas fa-trash me-1"></i>Xóa
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-star fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có đánh giá nào</h5>
                            <p class="text-muted">Hãy đánh giá các homestay bạn đã trải nghiệm!</p>
                            <a href="<?php echo SITE_URL; ?>/views/user/bookings.php" class="btn btn-primary">
                                <i class="fas fa-calendar-alt me-1"></i>Xem đơn đặt
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Review Modal -->
<div class="modal fade" id="editReviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo SITE_URL; ?>/api/review_api.php">
                <input type="hidden" name="id" id="editReviewId">
                <input type="hidden" name="_method" value="PUT">
                
                <div class="modal-header">
                    <h5 class="modal-title">Chỉnh sửa đánh giá</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Star Rating -->
                    <div class="mb-3">
                        <label class="form-label">Đánh giá sao</label>
                        <div class="star-rating-input">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star star-icon" data-rating="<?php echo $i; ?>" style="cursor: pointer; font-size: 1.5rem;"></i>
                            <?php endfor; ?>
                            <input type="hidden" name="rating" id="editRating" value="5">
                        </div>
                    </div>
                    
                    <!-- Comment -->
                    <div class="mb-3">
                        <label for="editComment" class="form-label">Nhận xét</label>
                        <textarea class="form-control" 
                                  id="editComment" 
                                  name="comment" 
                                  rows="4" 
                                  placeholder="Chia sẻ trải nghiệm của bạn..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Review Modal -->
<div class="modal fade" id="deleteReviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa đánh giá</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc muốn xóa đánh giá cho <strong id="deleteHomestayTitle"></strong>?</p>
                <p class="text-danger"><small>Hành động này không thể hoàn tác.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <form method="POST" action="<?php echo SITE_URL; ?>/api/review_api.php" id="deleteReviewForm">
                    <input type="hidden" name="id" id="deleteReviewId">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">Xác nhận xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit review modal
    const editButtons = document.querySelectorAll('.edit-review');
    const editModal = new bootstrap.Modal(document.getElementById('editReviewModal'));
    const editReviewId = document.getElementById('editReviewId');
    const editRating = document.getElementById('editRating');
    const editComment = document.getElementById('editComment');
    const starIcons = document.querySelectorAll('.star-icon');
    
    // Star rating functionality
    starIcons.forEach(star => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.getAttribute('data-rating'));
            editRating.value = rating;
            
            // Update star display
            starIcons.forEach((s, index) => {
                if (index < rating) {
                    s.classList.add('text-warning');
                    s.classList.remove('text-light');
                } else {
                    s.classList.remove('text-warning');
                    s.classList.add('text-light');
                }
            });
        });
    });
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const reviewId = this.getAttribute('data-review-id');
            const rating = this.getAttribute('data-rating');
            const comment = this.getAttribute('data-comment');
            
            editReviewId.value = reviewId;
            editRating.value = rating;
            editComment.value = comment;
            
            // Set star rating display
            starIcons.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('text-warning');
                    star.classList.remove('text-light');
                } else {
                    star.classList.remove('text-warning');
                    star.classList.add('text-light');
                }
            });
            
            editModal.show();
        });
    });
    
    // Delete review modal
    const deleteButtons = document.querySelectorAll('.delete-review');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteReviewModal'));
    const deleteReviewId = document.getElementById('deleteReviewId');
    const deleteHomestayTitle = document.getElementById('deleteHomestayTitle');
    const deleteReviewForm = document.getElementById('deleteReviewForm');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const reviewId = this.getAttribute('data-review-id');
            const homestayTitle = this.getAttribute('data-homestay-title');
            
            deleteReviewId.value = reviewId;
            deleteHomestayTitle.textContent = homestayTitle;
            deleteModal.show();
        });
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>