<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../controllers/HomestayController.php';

// Kiểm tra đăng nhập và quyền host
if (!isLoggedIn() || !hasRole(ROLE_HOST)) {
    set_flash_message(MSG_ERROR, 'Bạn cần đăng nhập với tài khoản host để chỉnh sửa homestay');
    header('Location: ' . SITE_URL . '/views/auth/login.php');
    exit;
}

// Kiểm tra ID homestay
if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash_message(MSG_ERROR, 'Không tìm thấy homestay');
    header('Location: ' . SITE_URL . '/views/host/homestays.php');
    exit;
}

$homestay_id = intval($_GET['id']);
$homestayController = new HomestayController();
$homestay_result = $homestayController->getHomestayById($homestay_id);

if (!$homestay_result['success'] || $homestay_result['data']['host_id'] != $_SESSION['user_id']) {
    set_flash_message(MSG_ERROR, 'Bạn không có quyền chỉnh sửa homestay này');
    header('Location: ' . SITE_URL . '/views/host/homestays.php');
    exit;
}

$homestay = $homestay_result['data'];
$page_title = 'Chỉnh sửa: ' . $homestay['title'];

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $homestayController->update($homestay_id);
    
    if ($result['success']) {
        set_flash_message(MSG_SUCCESS, $result['message']);
        header('Location: ' . SITE_URL . '/views/host/homestays.php');
        exit;
    }
}

$all_amenities = $GLOBALS['CONSTANTS']['AMENITIES'] ?? [];
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Chỉnh sửa Homestay</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data" id="editHomestayForm">
                        <!-- Basic Information -->
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="mb-3 text-primary">Thông tin cơ bản</h5>
                                
                                <!-- Title -->
                                <div class="mb-3">
                                    <label for="title" class="form-label">Tiêu đề homestay <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="title" 
                                           name="title" 
                                           value="<?php echo htmlspecialchars($homestay['title']); ?>"
                                           required>
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label for="description" class="form-label">Mô tả chi tiết <span class="text-danger">*</span></label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="5"
                                              required><?php echo htmlspecialchars($homestay['description']); ?></textarea>
                                </div>

                                <!-- Address -->
                                <div class="mb-3">
                                    <label for="address" class="form-label">Địa chỉ đầy đủ <span class="text-danger">*</span></label>
                                    <textarea class="form-control" 
                                              id="address" 
                                              name="address" 
                                              rows="3"
                                              required><?php echo htmlspecialchars($homestay['address']); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <h5 class="mb-3 text-primary">Hình ảnh</h5>
                                
                                <!-- Current Images -->
                                <div class="mb-3">
                                    <label class="form-label">Hình ảnh hiện tại</label>
                                    <div id="currentImages" class="mb-3">
                                        <?php foreach ($homestay['images'] as $index => $image): ?>
                                            <div class="image-item position-relative d-inline-block me-2 mb-2">
                                                <img src="<?php echo getImageUrl($image); ?>" 
                                                     class="img-thumbnail" 
                                                     width="80" 
                                                     height="60"
                                                     style="object-fit: cover;">
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image"
                                                        data-image-index="<?php echo $index; ?>"
                                                        style="transform: translate(50%, -50%);">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <!-- New Images -->
                                <div class="mb-3">
                                    <label class="form-label">Thêm hình ảnh mới</label>
                                    <input type="file" 
                                           class="form-control" 
                                           name="images[]" 
                                           multiple 
                                           accept="image/*">
                                    <div class="form-text">Chọn thêm ảnh mới (tối đa 10 ảnh)</div>
                                </div>
                                
                                <!-- New Image Preview -->
                                <div id="newImagePreview" class="mt-3"></div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Pricing & Capacity -->
                        <h5 class="mb-3 text-primary">Giá cả & Sức chứa</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="price_per_night" class="form-label">Giá mỗi đêm (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       id="price_per_night" 
                                       name="price_per_night" 
                                       value="<?php echo $homestay['price_per_night']; ?>"
                                       min="100000"
                                       step="10000"
                                       required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="max_guests" class="form-label">Số khách tối đa <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       id="max_guests" 
                                       name="max_guests" 
                                       value="<?php echo $homestay['max_guests']; ?>"
                                       min="1"
                                       max="20"
                                       required>
                            </div>

                            <div class="col-md-2 mb-3">
                                <label for="bedrooms" class="form-label">Phòng ngủ</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="bedrooms" 
                                       name="bedrooms" 
                                       value="<?php echo $homestay['bedrooms']; ?>"
                                       min="0"
                                       max="10">
                            </div>

                            <div class="col-md-2 mb-3">
                                <label for="bathrooms" class="form-label">Phòng tắm</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="bathrooms" 
                                       name="bathrooms" 
                                       value="<?php echo $homestay['bathrooms']; ?>"
                                       min="0"
                                       max="10">
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Amenities -->
                        <h5 class="mb-3 text-primary">Tiện nghi</h5>
                        <div class="row">
                            <?php foreach ($all_amenities as $key => $label): ?>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="amenities[]" 
                                               value="<?php echo $key; ?>" 
                                               id="amenity_<?php echo $key; ?>"
                                               <?php echo in_array($key, $homestay['amenities']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="amenity_<?php echo $key; ?>">
                                            <i class="fas fa-check me-1 text-success"></i><?php echo $label; ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="<?php echo SITE_URL; ?>/views/host/homestays.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i>Quay lại
                                    </a>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Cập nhật
                                        </button>
                                        <a href="<?php echo SITE_URL; ?>/views/homestay/detail.php?id=<?php echo $homestay_id; ?>" 
                                           class="btn btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>Xem chi tiết
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Remove image functionality
    const removeButtons = document.querySelectorAll('.remove-image');
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const imageIndex = this.getAttribute('data-image-index');
            if (confirm('Bạn có chắc muốn xóa ảnh này?')) {
                // In a real application, you would make an AJAX call to remove the image
                this.closest('.image-item').remove();
                // Add hidden input to track removed images
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'removed_images[]';
                hiddenInput.value = imageIndex;
                document.getElementById('editHomestayForm').appendChild(hiddenInput);
            }
        });
    });
    
    // New image preview
    const newImageInput = document.querySelector('input[name="images[]"]');
    const newImagePreview = document.getElementById('newImagePreview');
    
    if (newImageInput && newImagePreview) {
        newImageInput.addEventListener('change', function(e) {
            newImagePreview.innerHTML = '';
            const files = e.target.files;
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'img-thumbnail me-2 mb-2';
                        img.style.width = '80px';
                        img.style.height = '60px';
                        img.style.objectFit = 'cover';
                        newImagePreview.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                }
            }
        });
    }
    
    // Form validation
    const form = document.getElementById('editHomestayForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const currentImages = document.querySelectorAll('#currentImages .image-item').length;
            const newImages = this.querySelector('input[name="images[]"]').files.length;
            
            if (currentImages === 0 && newImages === 0) {
                e.preventDefault();
                alert('Homestay phải có ít nhất một hình ảnh');
                return false;
            }
            
            if (newImages > 0) {
                const imageInput = this.querySelector('input[name="images[]"]');
                for (let file of imageInput.files) {
                    if (!file.type.startsWith('image/')) {
                        e.preventDefault();
                        alert('Chỉ được chọn file hình ảnh');
                        return false;
                    }
                    
                    if (file.size > 5 * 1024 * 1024) {
                        e.preventDefault();
                        alert('Kích thước file không được vượt quá 5MB');
                        return false;
                    }
                }
            }
        });
    }
});
</script>

<style>
.image-item {
    transition: opacity 0.3s ease;
}

.image-item:hover {
    opacity: 0.8;
}

.remove-image {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>