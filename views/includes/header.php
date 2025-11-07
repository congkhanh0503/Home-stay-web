<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
    
    <style>
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .navbar-brand {
            font-weight: bold;
            color: #2c3e50 !important;
        }
        .nav-link {
            font-weight: 500;
        }
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }
        .dropdown-menu {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* Tạo khoảng trống cho fixed header */
        body {
            padding-top: 40px; /* Chiều cao của header */
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding-top: 70px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation - Fixed Header -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container">
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>/index.php">
                <i class="fas fa-home me-2"></i><?php echo SITE_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/index.php">
                            <i class="fas fa-home me-1"></i>Trang chủ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/views/homestay/search.php">
                            <i class="fas fa-search me-1"></i>Tìm homestay
                        </a>
                    </li>
                    <?php if (isLoggedIn() && hasRole(ROLE_HOST)): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/views/host/dashboard.php">
                            <i class="fas fa-chart-line me-1"></i>Quản lý
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (isLoggedIn() && hasRole(ROLE_ADMIN)): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/admin.php">
                            <i class="fas fa-cog me-1"></i>Admin
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (!isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/views/auth/login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/views/auth/register.php">
                                <i class="fas fa-user-plus me-1"></i>Đăng ký
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <?php if (!empty($_SESSION['user_avatar'])): ?>
                                    <img src="<?php echo getImageUrl($_SESSION['user_avatar']); ?>" class="user-avatar me-1" alt="Avatar">
                                <?php else: ?>
                                    <i class="fas fa-user-circle me-1"></i>
                                <?php endif; ?>
                                <?php echo $_SESSION['user_name']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="<?php echo SITE_URL; ?>/views/user/profile.php">
                                        <i class="fas fa-user me-2"></i>Hồ sơ
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo SITE_URL; ?>/views/user/bookings.php">
                                        <i class="fas fa-calendar-alt me-2"></i>Đơn đặt của tôi
                                    </a>
                                </li>
                                <?php if (hasRole(ROLE_HOST)): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo SITE_URL; ?>/views/host/homestays.php">
                                        <i class="fas fa-home me-2"></i>Homestay của tôi
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo SITE_URL; ?>/views/host/bookings.php">
                                        <i class="fas fa-list-alt me-2"></i>Quản lý đơn đặt
                                    </a>
                                </li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>/views/auth/logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <!-- Flash Messages -->
        <?php echo getFlashMessage(); ?>