<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../functions/booking_functions.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/views/auth/login.php');
    exit;
}

$page_title = 'Đơn đặt của tôi';
$active_tab = 'bookings';

// Lấy trạng thái filter
$status_filter = $_GET['status'] ?? 'all';

// Lấy danh sách bookings
$bookings = getUserBookings($_SESSION['user_id'], $status_filter === 'all' ? null : $status_filter);
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Đơn đặt của tôi</h4>
                    <div class="filter-section">
                        <select class="form-select form-select-sm" id="statusFilter">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Tất cả</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                            <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                            <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Đã hoàn thành</option>
                            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($bookings)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Homestay</th>
                                        <th>Ngày đặt</th>
                                        <th>Check-in / Check-out</th>
                                        <th>Số khách</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($booking['images'])): 
                                                        $images = json_decode($booking['images'], true); ?>
                                                        <img src="<?php echo getImageUrl($images[0] ?? ''); ?>" 
                                                             class="rounded me-3" 
                                                             width="60" 
                                                             height="60" 
                                                             alt="<?php echo htmlspecialchars($booking['title']); ?>"
                                                             style="object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center me-3" 
                                                             style="width: 60px; height: 60px;">
                                                            <i class="fas fa-home text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($booking['title']); ?></h6>
                                                        <small class="text-muted">
                                                            <i class="fas fa-map-marker-alt me-1"></i>
                                                            <?php echo htmlspecialchars($booking['address']); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <small><?php echo formatDate($booking['created_at']); ?></small>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <div><strong>Check-in:</strong> <?php echo formatDate($booking['check_in']); ?></div>
                                                    <div><strong>Check-out:</strong> <?php echo formatDate($booking['check_out']); ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo $booking['guests']; ?> khách</span>
                                            </td>
                                            <td>
                                                <strong class="text-primary"><?php echo formatPrice($booking['total_price']); ?></strong>
                                            </td>
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
                                                    <a href="<?php echo SITE_URL; ?>/views/booking/booking_detail.php?id=<?php echo $booking['id']; ?>" 
                                                       class="btn btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($booking['status'] === 'pending'): ?>
                                                        <button type="button" 
                                                                class="btn btn-outline-danger cancel-booking" 
                                                                data-booking-id="<?php echo $booking['id']; ?>"
                                                                data-booking-title="<?php echo htmlspecialchars($booking['title']); ?>">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có đơn đặt nào</h5>
                            <p class="text-muted">Hãy khám phá và đặt homestay đầu tiên của bạn!</p>
                            <a href="<?php echo SITE_URL; ?>/views/homestay/search.php" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>Tìm homestay
                            </a>
                        </div>
                    <?php endif; ?>
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
    // Status filter
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const status = this.value;
            const url = new URL(window.location.href);
            if (status === 'all') {
                url.searchParams.delete('status');
            } else {
                url.searchParams.set('status', status);
            }
            window.location.href = url.toString();
        });
    }
    
    // Cancel booking modal
    const cancelButtons = document.querySelectorAll('.cancel-booking');
    const cancelForm = document.getElementById('cancelForm');
    const cancelBookingId = document.getElementById('cancelBookingId');
    const bookingTitle = document.getElementById('bookingTitle');
    const cancelModal = new bootstrap.Modal(document.getElementById('cancelBookingModal'));
    
    cancelButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bookingId = this.getAttribute('data-booking-id');
            const title = this.getAttribute('data-booking-title');
            
            cancelBookingId.value = bookingId;
            bookingTitle.textContent = title;
            cancelModal.show();
        });
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>