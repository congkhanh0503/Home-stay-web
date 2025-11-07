    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-home me-2"></i><?php echo SITE_NAME; ?>
                    </h5>
                    <p class="text-light">Nền tảng đặt homestay uy tín, chất lượng với hàng ngàn lựa chọn phù hợp cho mọi nhu cầu của bạn.</p>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
                
                <div class="col-md-2 mb-4">
                    <h6 class="mb-3">Về chúng tôi</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-light text-decoration-none">Giới thiệu</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Điều khoản</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Chính sách bảo mật</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Liên hệ</a></li>
                    </ul>
                </div>
                
                <div class="col-md-3 mb-4">
                    <h6 class="mb-3">Hỗ trợ</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-light text-decoration-none">Trung tâm trợ giúp</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Hướng dẫn đặt phòng</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Câu hỏi thường gặp</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Phương thức thanh toán</a></li>
                    </ul>
                </div>
                
                <div class="col-md-3 mb-4">
                    <h6 class="mb-3">Liên hệ</h6>
                    <ul class="list-unstyled text-light">
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            123 Đường ABC, Quận 1, TP.HCM
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone me-2"></i>
                            +84 123 456 789
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            info@homestay.com
                        </li>
                    </ul>
                </div>
            </div>
            
            <hr class="bg-light">
            
            <div class="row">
                <div class="col-md-12 text-center">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
    
    <script>
        // Auto-hide flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });

        // Form validation
        function validateForm(form) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            return isValid;
        }

        // Price formatter
        function formatPrice(price) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(price);
        }

        // Date formatter
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('vi-VN');
        }
    </script>
</body>
</html>