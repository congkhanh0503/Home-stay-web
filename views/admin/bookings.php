<?php 
require_once __DIR__ . '/../../config/config.php'; 
require_once __DIR__ . '/../../config/constants.php'; 
require_once __DIR__ . '/../../functions/helpers.php'; 
require_once __DIR__ . '/../../models/Booking.php'; 
require_once __DIR__ . '/../../models/User.php'; 
require_once __DIR__ . '/../../models/Homestay.php'; 

// Kiểm tra quyền admin 
if (!isLoggedIn() || !hasRole(ROLE_ADMIN)) { 
    set_flash_message(MSG_ERROR, 'Bạn không có quyền truy cập trang này'); 
    header('Location: ' . SITE_URL . '/index.php'); 
    exit; 
} 

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$page_title = 'Quản lý Đơn đặt'; 
$active_tab = 'bookings'; 
$bookingModel = new Booking(); 
$userModel = new User(); 
$homestayModel = new Homestay(); 

// Xử lý filters với sanitize
$status_filter = sanitizeInput($_GET['status'] ?? '');
$date_from = sanitizeInput($_GET['date_from'] ?? '');
$date_to = sanitizeInput($_GET['date_to'] ?? '');
$search = sanitizeInput($_GET['search'] ?? '');
$filters = []; 

if (!empty($status_filter)) $filters['status'] = $status_filter;
if (!empty($date_from)) $filters['date_from'] = $date_from;
if (!empty($date_to)) $filters['date_to'] = $date_to;
if (!empty($search)) $filters['search'] = $search;

// Validate date range
if (!empty($date_from) && !empty($date_to) && $date_from > $date_to) {
    set_flash_message(MSG_ERROR, 'Ngày bắt đầu không thể lớn hơn ngày kết thúc');
    $date_from = $date_to = '';
    $filters['date_from'] = $filters['date_to'] = '';
}

// Lấy danh sách bookings
$bookings = []; 
try { 
    $bookings_result = $bookingModel->getAll($filters); 
    
    // Đảm bảo $bookings luôn là mảng 
    if (is_array($bookings_result)) { 
        $bookings = $bookings_result; 
    } else { 
        $bookings = []; 
        error_log("Lỗi: bookingModel->getAll() không trả về mảng"); 
    } 
} catch (Exception $e) { 
    $bookings = []; 
    set_flash_message(MSG_ERROR, 'Lỗi khi tải danh sách đơn đặt: ' . $e->getMessage());
    error_log("Lỗi khi lấy danh sách bookings: " . $e->getMessage()); 
} 

// Xử lý cập nhật trạng thái booking 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) { 
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        set_flash_message(MSG_ERROR, 'Token bảo mật không hợp lệ');
        header('Location: ' . $_SERVER['REQUEST_URI']); 
        exit;
    }
    
    $booking_id = intval($_POST['booking_id']); 
    $status = sanitizeInput($_POST['status']); 
    
    if ($bookingModel->updateStatus($booking_id, $status)) { 
        set_flash_message(MSG_SUCCESS, 'Cập nhật trạng thái thành công'); 
        header('Location: ' . $_SERVER['REQUEST_URI']); 
        exit; 
    } else { 
        set_flash_message(MSG_ERROR, 'Có lỗi xảy ra khi cập nhật trạng thái'); 
    } 
} 

// Xử lý xóa booking 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_booking'])) { 
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        set_flash_message(MSG_ERROR, 'Token bảo mật không hợp lệ');
        header('Location: ' . $_SERVER['REQUEST_URI']); 
        exit;
    }
    
    $booking_id = intval($_POST['booking_id']); 
    if ($bookingModel->delete($booking_id)) { 
        set_flash_message(MSG_SUCCESS, 'Xóa đơn đặt thành công'); 
        header('Location: ' . $_SERVER['REQUEST_URI']); 
        exit; 
    } else { 
        set_flash_message(MSG_ERROR, 'Có lỗi xảy ra khi xóa đơn đặt'); 
    } 
} 
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid mt-4"> 
    <div class="row">
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Quản lý Đơn đặt</h1>
                <div class="text-muted">
                    Tổng: <strong><?php echo count($bookings); ?></strong> đơn đặt
                </div>
            </div>

            <!-- Filters Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Trạng thái</label>
                            <select class="form-select" name="status">
                                <option value="">Tất cả trạng thái</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                                <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Từ ngày</label>
                            <input type="date" 
                                   class="form-control" 
                                   name="date_from" 
                                   value="<?php echo htmlspecialchars($date_from); ?>">
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Đến ngày</label>
                            <input type="date" 
                                   class="form-control" 
                                   name="date_to" 
                                   value="<?php echo htmlspecialchars($date_to); ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Tìm kiếm</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   placeholder="Mã đơn, tên khách hoặc homestay...">
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 me-2">
                                <i class="fas fa-filter me-1"></i>Lọc
                            </button>
                            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-redo me-1"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bookings Table -->
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Danh sách Đơn đặt</h6>
                    <div class="btn-group">
                        <button type="button" class="btn btn-success btn-sm">
                            <i class="fas fa-download me-1"></i>Xuất Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="bookingsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Homestay</th>
                                    <th>Ngày nhận/phòng</th>
                                    <th>Số đêm</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày đặt</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($bookings)): ?>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td>
                                                <strong>#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($booking['user_name'] ?? 'N/A'); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($booking['user_email'] ?? 'N/A'); ?></small>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-phone me-1"></i>
                                                        <?php echo isset($booking['user_phone']) && $booking['user_phone'] ? htmlspecialchars($booking['user_phone']) : 'N/A'; ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php 
                                                    // Lấy ảnh homestay
                                                    $homestay_images = $booking['images'] ?? '';
                                                    $first_image = '';
                                                    
                                                    if (!empty($homestay_images)) {
                                                        if (is_string($homestay_images)) {
                                                            $images = json_decode($homestay_images, true) ?: [];
                                                            $first_image = $images[0] ?? '';
                                                        } else if (is_array($homestay_images)) {
                                                            $first_image = $homestay_images[0] ?? '';
                                                        }
                                                    }
                                                    
                                                    if (!empty($first_image)): ?>
                                                        <img src="<?php echo getImageUrl($first_image); ?>" 
                                                             class="rounded me-3" 
                                                             width="50" 
                                                             height="50"
                                                             alt="<?php echo htmlspecialchars($booking['title'] ?? ''); ?>"
                                                             style="object-fit: cover;"
                                                             onerror="this.src='<?php echo SITE_URL; ?>/assets/img/no-image.jpg'">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center me-3" 
                                                             style="width: 50px; height: 50px;">
                                                            <i class="fas fa-home text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($booking['title'] ?? 'N/A'); ?></h6>
                                                        <small class="text-muted">
                                                            <i class="fas fa-user-tie me-1"></i>
                                                            <?php echo htmlspecialchars($booking['host_name'] ?? 'N/A'); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-center">
                                                    <div class="fw-bold text-primary">
                                                        <?php echo isset($booking['check_in']) ? formatDate($booking['check_in'], 'd/m/Y') : 'N/A'; ?>
                                                    </div>
                                                    <small class="text-muted">đến</small>
                                                    <div class="fw-bold text-primary">
                                                        <?php echo isset($booking['check_out']) ? formatDate($booking['check_out'], 'd/m/Y') : 'N/A'; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <?php if (isset($booking['check_in']) && isset($booking['check_out'])): ?>
                                                    <?php 
                                                    try {
                                                        $check_in = new DateTime($booking['check_in']);
                                                        $check_out = new DateTime($booking['check_out']);
                                                        $nights = $check_in->diff($check_out)->days;
                                                        echo '<span class="badge bg-info">' . $nights . ' đêm</span>';
                                                    } catch (Exception $e) {
                                                        echo '<span class="badge bg-secondary">N/A</span>';
                                                    }
                                                    ?>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-success fw-bold">
                                                <?php echo isset($booking['total_price']) ? formatPrice($booking['total_price']) : '0 ₫'; ?>
                                            </td>
                                            <td>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="update_status" value="1">
                                                    <select name="status" 
                                                            class="form-select form-select-sm status-select 
                                                                <?php 
                                                                $status = $booking['status'] ?? '';
                                                                switch($status) {
                                                                    case 'pending': echo 'border-warning'; break;
                                                                    case 'confirmed': echo 'border-info'; break;
                                                                    case 'completed': echo 'border-success'; break;
                                                                    case 'cancelled': echo 'border-danger'; break;
                                                                    default: echo 'border-secondary';
                                                                }
                                                                ?>"
                                                            onchange="this.form.submit()">
                                                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                                                        <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                                                        <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                                                        <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td>
                                                <small><?php echo isset($booking['created_at']) ? formatDate($booking['created_at']) : 'N/A'; ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?php echo SITE_URL; ?>/views/booking/booking_detail.php?id=<?php echo $booking['id']; ?>" 
                                                       class="btn btn-outline-primary" title="Xem chi tiết" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-info send-notification" 
                                                            data-booking-id="<?php echo $booking['id']; ?>"
                                                            title="Gửi thông báo">
                                                        <i class="fas fa-bell"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger delete-booking" 
                                                            data-booking-id="<?php echo $booking['id']; ?>"
                                                            data-booking-code="#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?>"
                                                            title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">Không tìm thấy đơn đặt nào</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center py-3">
                            <h5 class="mb-1"><?php echo count(array_filter($bookings, fn($b) => ($b['status'] ?? '') === 'pending')); ?></h5>
                            <small>Chờ xác nhận</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center py-3">
                            <h5 class="mb-1"><?php echo count(array_filter($bookings, fn($b) => ($b['status'] ?? '') === 'confirmed')); ?></h5>
                            <small>Đã xác nhận</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center py-3">
                            <h5 class="mb-1"><?php echo count(array_filter($bookings, fn($b) => ($b['status'] ?? '') === 'completed')); ?></h5>
                            <small>Hoàn thành</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center py-3">
                            <h5 class="mb-1"><?php echo count(array_filter($bookings, fn($b) => ($b['status'] ?? '') === 'cancelled')); ?></h5>
                            <small>Đã hủy</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Cards -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center py-3">
                            <h5 class="mb-1">
                                <?php 
                                $total_revenue = 0;
                                foreach ($bookings as $booking) {
                                    $total_revenue += floatval($booking['total_price'] ?? 0);
                                }
                                echo formatPrice($total_revenue);
                                ?>
                            </h5>
                            <small>Tổng doanh thu</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center py-3">
                            <h5 class="mb-1">
                                <?php 
                                $completed_revenue = 0;
                                foreach ($bookings as $booking) {
                                    if (($booking['status'] ?? '') === 'completed') {
                                        $completed_revenue += floatval($booking['total_price'] ?? 0);
                                    }
                                }
                                echo formatPrice($completed_revenue);
                                ?>
                            </h5>
                            <small>Doanh thu đã hoàn thành</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Booking Modal -->
<div class="modal fade" id="deleteBookingModal" tabindex="-1"> 
    <div class="modal-dialog"> 
        <div class="modal-content"> 
            <div class="modal-header"> 
                <h5 class="modal-title text-danger">Xác nhận xóa đơn đặt</h5> 
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button> 
            </div> 
            <div class="modal-body"> 
                <p>Bạn có chắc muốn xóa đơn đặt <strong id="deleteBookingCode"></strong>?</p> 
                <div class="alert alert-danger"> 
                    <i class="fas fa-exclamation-triangle me-2"></i> 
                    <strong>Cảnh báo:</strong> Hành động này sẽ xóa vĩnh viễn đơn đặt và không thể khôi phục. 
                </div> 
            </div> 
            <div class="modal-footer"> 
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button> 
                <form method="POST" action="" id="deleteBookingForm"> 
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="booking_id" id="deleteBookingId"> 
                    <input type="hidden" name="delete_booking" value="1"> 
                    <button type="submit" class="btn btn-danger">Xác nhận xóa</button> 
                </form> 
            </div> 
        </div> 
    </div> 
</div>

<!-- Send Notification Modal -->
<div class="modal fade" id="sendNotificationModal" tabindex="-1"> 
    <div class="modal-dialog"> 
        <div class="modal-content"> 
            <div class="modal-header"> 
                <h5 class="modal-title">Gửi thông báo</h5> 
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button> 
            </div> 
            <div class="modal-body"> 
                <form id="notificationForm"> 
                    <input type="hidden" name="booking_id" id="notificationBookingId"> 
                    <div class="mb-3"> 
                        <label class="form-label">Tiêu đề</label> 
                        <input type="text" class="form-control" name="title" value="Thông báo về đơn đặt" required> 
                    </div> 
                    <div class="mb-3"> 
                        <label class="form-label">Nội dung</label> 
                        <textarea class="form-control" name="message" rows="4" placeholder="Nhập nội dung thông báo..." required></textarea> 
                    </div> 
                    <div class="mb-3"> 
                        <label class="form-label">Loại thông báo</label> 
                        <select class="form-select" name="type"> 
                            <option value="info">Thông tin</option> 
                            <option value="warning">Cảnh báo</option> 
                            <option value="success">Thành công</option> 
                            <option value="error">Lỗi</option> 
                        </select> 
                    </div> 
                </form> 
            </div> 
            <div class="modal-footer"> 
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button> 
                <button type="button" class="btn btn-primary" id="sendNotificationBtn">Gửi thông báo</button> 
            </div> 
        </div> 
    </div> 
</div>

<script>
document.addEventListener('DOMContentLoaded', function() { 
    // Delete booking modal
    const deleteButtons = document.querySelectorAll('.delete-booking'); 
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteBookingModal')); 
    const deleteBookingId = document.getElementById('deleteBookingId'); 
    const deleteBookingCode = document.getElementById('deleteBookingCode'); 
    const deleteBookingForm = document.getElementById('deleteBookingForm'); 
    
    deleteButtons.forEach(button => { 
        button.addEventListener('click', function() { 
            const bookingId = this.getAttribute('data-booking-id'); 
            const bookingCode = this.getAttribute('data-booking-code'); 
            deleteBookingId.value = bookingId; 
            deleteBookingCode.textContent = bookingCode; 
            deleteModal.show(); 
        }); 
    }); 

    // Send notification modal
    const notificationButtons = document.querySelectorAll('.send-notification'); 
    const notificationModal = new bootstrap.Modal(document.getElementById('sendNotificationModal')); 
    const notificationBookingId = document.getElementById('notificationBookingId'); 
    const sendNotificationBtn = document.getElementById('sendNotificationBtn'); 
    
    notificationButtons.forEach(button => { 
        button.addEventListener('click', function() { 
            const bookingId = this.getAttribute('data-booking-id'); 
            notificationBookingId.value = bookingId; 
            notificationModal.show(); 
        }); 
    }); 

    // Handle sending notification 
    sendNotificationBtn.addEventListener('click', function() { 
        const form = document.getElementById('notificationForm'); 
        const formData = new FormData(form); 
        
        // AJAX request to send notification
        fetch('<?php echo SITE_URL; ?>/api/send-notification.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Thông báo đã được gửi thành công!');
                notificationModal.hide();
                form.reset();
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể gửi thông báo'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Lỗi kết nối: ' + error);
        });
    }); 

    // Auto-submit filters when select changes 
    const filterSelects = document.querySelectorAll('select[name="status"]'); 
    filterSelects.forEach(select => { 
        select.addEventListener('change', function() { 
            this.form.submit(); 
        }); 
    }); 
}); 
</script>

<style>
.status-select { 
    width: 140px; 
    cursor: pointer; 
} 
.table th { 
    border-bottom: 2px solid #e3e6f0; 
    font-weight: 600; 
} 
.card { 
    border: none; 
    border-radius: 10px; 
} 
.btn-group-sm > .btn { 
    padding: 0.25rem 0.5rem; 
} 
.status-badge { 
    padding: 0.25rem 0.5rem; 
    border-radius: 0.25rem; 
    font-size: 0.75rem; 
    font-weight: 600; 
} 
.status-pending { 
    background-color: #fff3cd; 
    color: #856404; 
} 
.status-confirmed { 
    background-color: #d1ecf1; 
    color: #0c5460; 
} 
.status-completed { 
    background-color: #d4edda; 
    color: #155724; 
} 
.status-cancelled { 
    background-color: #f8d7da; 
    color: #721c24; 
} 
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>