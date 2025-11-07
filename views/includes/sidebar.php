<?php
// Sidebar for user dashboard
if (!isset($active_tab)) {
    $active_tab = 'profile';
}
?>

<div class="col-md-3">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="fas fa-user-circle me-2"></i>Tài khoản</h6>
        </div>
        <div class="list-group list-group-flush">
            <a href="<?php echo SITE_URL; ?>/views/user/profile.php" 
               class="list-group-item list-group-item-action <?php echo $active_tab === 'profile' ? 'active' : ''; ?>">
                <i class="fas fa-user me-2"></i>Thông tin cá nhân
            </a>
            <a href="<?php echo SITE_URL; ?>/views/user/bookings.php" 
               class="list-group-item list-group-item-action <?php echo $active_tab === 'bookings' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt me-2"></i>Đơn đặt của tôi
            </a>
            <a href="<?php echo SITE_URL; ?>/views/user/reviews.php" 
               class="list-group-item list-group-item-action <?php echo $active_tab === 'reviews' ? 'active' : ''; ?>">
                <i class="fas fa-star me-2"></i>Đánh giá của tôi
            </a>
            <a href="<?php echo SITE_URL; ?>/views/user/security.php" 
               class="list-group-item list-group-item-action <?php echo $active_tab === 'security' ? 'active' : ''; ?>">
                <i class="fas fa-lock me-2"></i>Bảo mật
            </a>
        </div>
    </div>
</div>