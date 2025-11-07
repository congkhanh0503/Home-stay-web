<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../models/Booking.php';

// Kiểm tra quyền host
if (!isLoggedIn() || !hasRole(ROLE_HOST)) {
    set_flash_message(MSG_ERROR, 'Bạn không có quyền truy cập trang này');
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$page_title = 'Quản lý Đơn đặt';
$active_tab = 'bookings';

$bookingModel = new Booking();
$host_id = $_SESSION['user_id'];

// DEBUG: Hiển thị host_id để kiểm tra
error_log("Host ID: " . $host_id);

// Xử lý filters
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Lấy tất cả bookings của host
$all_bookings = $bookingModel->getByHost($host_id);

// DEBUG: Hiển thị số lượng bookings
error_log("Total bookings found: " . count($all_bookings));

// Filter theo status
if (!empty($status_filter)) {
    $all_bookings = array_filter($all_bookings, function($booking) use ($status_filter) {
        return $booking['status'] === $status_filter;
    });
}

// Filter theo search
if (!empty($search)) {
    $search_lower = strtolower($search);
    $all_bookings = array_filter($all_bookings, function($booking) use ($search_lower) {
        return strpos(strtolower($booking['user_name'] ?? ''), $search_lower) !== false || 
               strpos(strtolower($booking['homestay_title'] ?? ''), $search_lower) !== false;
    });
}

$bookings = $all_bookings;

// Xử lý cập nhật trạng thái booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_booking_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $status = sanitizeInput($_POST['status']);
    
    // Kiểm tra booking thuộc về host
    $booking = $bookingModel->getById($booking_id);
    if ($booking) {
        // Lấy homestay để kiểm tra host_id
        require_once __DIR__ . '/../../models/Homestay.php';
        $homestayModel = new Homestay();
        $homestay = $homestayModel->getById($booking['homestay_id']);
        
        if ($homestay && $homestay['host_id'] == $host_id) {
            if ($bookingModel->updateStatus($booking_id, $status)) {
                set_flash_message(MSG_SUCCESS, 'Cập nhật trạng thái thành công');
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit;
            } else {
                set_flash_message(MSG_ERROR, 'Có lỗi xảy ra khi cập nhật trạng thái');
            }
        } else {
            set_flash_message(MSG_ERROR, 'Booking không thuộc về bạn');
        }
    } else {
        set_flash_message(MSG_ERROR, 'Booking không tồn tại');
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../includes/host_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Quản lý Đơn đặt</h1>
                <div class="text-muted">
                    Tổng: <strong><?php echo count($bookings); ?></strong> đơn đặt
                    <!-- DEBUG: Hiển thị host_id -->
                    <small class="d-block">Host ID: <?php echo $host_id; ?></small>
                </div>
            </div>

            <!-- Flash Message -->
            <?php echo getFlashMessage(); ?>

            <!-- Filters -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Trạng thái</label>
                            <select class="form-select" name="status">
                                <option value="">Tất cả trạng thái</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                                <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Tìm kiếm</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   placeholder="Tên khách hàng hoặc homestay...">
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i>Lọc
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bookings Table -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if (!empty($bookings)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Khách hàng</th>
                                        <th>Homestay</th>
                                        <th>Ngày đặt</th>
                                        <th>Check-in/out</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td>#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($booking['user_name'] ?? 'N/A'); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($booking['user_email'] ?? 'N/A'); ?></small>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($booking['user_phone'] ?? 'N/A'); ?></small>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($booking['homestay_title'] ?? 'N/A'); ?></td>
                                            <td>
                                                <small><?php echo formatDate($booking['created_at'] ?? ''); ?></small>
                                            </td>
                                            <td>
                                                <small>
                                                    <strong>Vào:</strong> <?php echo formatDate($booking['check_in'] ?? ''); ?><br>
                                                    <strong>Ra:</strong> <?php echo formatDate($booking['check_out'] ?? ''); ?>
                                                </small>
                                            </td>
                                            <td class="text-success fw-bold"><?php echo formatPrice($booking['total_price'] ?? 0); ?></td>
                                            <td>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="update_booking_status" value="1">
                                                    <select name="status" 
                                                            class="form-select form-select-sm"
                                                            onchange="this.form.submit()"
                                                            <?php echo in_array($booking['status'] ?? '', ['completed', 'cancelled']) ? 'disabled' : ''; ?>>
                                                        <option value="pending" <?php echo ($booking['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                                                        <option value="confirmed" <?php echo ($booking['status'] ?? '') === 'confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                                                        <option value="completed" <?php echo ($booking['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                                                        <option value="cancelled" <?php echo ($booking['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Hủy</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?php echo SITE_URL; ?>/views/booking/detail.php?id=<?php echo $booking['id']; ?>" 
                                                       class="btn btn-outline-primary" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if (!empty($booking['user_phone'])): ?>
                                                        <a href="tel:<?php echo htmlspecialchars($booking['user_phone']); ?>" 
                                                           class="btn btn-outline-info" title="Liên hệ">
                                                            <i class="fas fa-phone"></i>
                                                        </a>
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
                            <i class="fas fa-calendar-times fa-2x text-muted mb-3"></i>
                            <h5 class="text-muted">Không tìm thấy đơn đặt nào</h5>
                            <p class="text-muted">Các đơn đặt homestay của bạn sẽ hiển thị tại đây.</p>
                            <div class="mt-3">
                                <small class="text-info">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Host ID: <?php echo $host_id; ?> | 
                                    Tổng bookings: <?php echo count($all_bookings); ?>
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>