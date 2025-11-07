<?php
// Sidebar for host dashboard
if (!isset($active_tab)) {
    $active_tab = 'dashboard';
}
?>

<div class="col-md-3">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Quản lý Host</h6>
        </div>
        <div class="list-group list-group-flush">
            <a href="<?php echo SITE_URL; ?>/views/host/dashboard.php" 
               class="list-group-item list-group-item-action <?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt me-2"></i>Tổng quan
            </a>
            <a href="<?php echo SITE_URL; ?>/views/host/homestays.php" 
               class="list-group-item list-group-item-action <?php echo $active_tab === 'homestays' ? 'active' : ''; ?>">
                <i class="fas fa-home me-2"></i>Homestay của tôi
            </a>
            <a href="<?php echo SITE_URL; ?>/views/host/bookings.php" 
               class="list-group-item list-group-item-action <?php echo $active_tab === 'bookings' ? 'active' : ''; ?>">
                <i class="fas fa-list-alt me-2"></i>Quản lý đơn đặt
            </a>
            <a href="<?php echo SITE_URL; ?>/views/host/add_homestay.php" 
               class="list-group-item list-group-item-action <?php echo $active_tab === 'add_homestay' ? 'active' : ''; ?>">
                <i class="fas fa-plus me-2"></i>Thêm homestay mới
            </a>
            <a href="<?php echo SITE_URL; ?>/views/host/revenue.php" 
               class="list-group-item list-group-item-action <?php echo $active_tab === 'revenue' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar me-2"></i>Doanh thu
            </a>
        </div>
    </div>
</div>