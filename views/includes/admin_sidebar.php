<?php
// Kiểm tra xem file đã được include từ header chưa
if (!defined('SIDEBAR_INCLUDED')) {
    define('SIDEBAR_INCLUDED', true);
?>
<!-- Sidebar -->
<div class="col-md-3 col-lg-2 sidebar d-none d-md-block">
    <div class="position-sticky pt-3">
        <!-- User Info -->
        <div class="text-center text-white mb-4 px-3">
            <img src="<?php echo getImageUrl($_SESSION['user_avatar'] ?? ''); ?>" 
                 class="rounded-circle mb-3" 
                 width="80" 
                 height="80"
                 style="object-fit: cover; border: 3px solid rgba(255,255,255,0.3);"
                 alt="Admin Avatar">
            <h6 class="mb-1"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></h6>
            <small class="text-white-50">Quản trị viên</small>
        </div>

        <!-- Navigation -->
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_tab ?? '') === 'dashboard' ? 'active' : ''; ?>" 
                   href="<?php echo SITE_URL; ?>/views/admin/dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    Tổng quan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_tab ?? '') === 'users' ? 'active' : ''; ?>" 
                   href="<?php echo SITE_URL; ?>/views/admin/users.php">
                    <i class="fas fa-users"></i>
                    Người dùng
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_tab ?? '') === 'homestays' ? 'active' : ''; ?>" 
                   href="<?php echo SITE_URL; ?>/views/admin/homestays.php">
                    <i class="fas fa-home"></i>
                    Homestay
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_tab ?? '') === 'bookings' ? 'active' : ''; ?>" 
                   href="<?php echo SITE_URL; ?>/views/admin/bookings.php">
                    <i class="fas fa-calendar-alt"></i>
                    Đơn đặt
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_tab ?? '') === 'reports' ? 'active' : ''; ?>" 
                   href="<?php echo SITE_URL; ?>/views/admin/reports.php">
                    <i class="fas fa-chart-bar"></i>
                    Báo cáo
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_tab ?? '') === 'settings' ? 'active' : ''; ?>" 
                   href="<?php echo SITE_URL; ?>/views/admin/settings.php">
                    <i class="fas fa-cog"></i>
                    Cài đặt
                </a>
            </li>
        </ul>

        <!-- Bottom Links -->
        <div class="position-absolute bottom-0 start-0 w-100 p-3 border-top border-white-10">
            <a href="<?php echo SITE_URL; ?>/index.php" class="nav-link text-white-50">
                <i class="fas fa-globe me-2"></i>Về trang chủ
            </a>
            <a href="<?php echo SITE_URL; ?>/logout.php" class="nav-link text-white-50">
                <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
            </a>
        </div>
    </div>
</div>
<?php } ?>