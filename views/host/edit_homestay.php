<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../models/Homestay.php';

// Kiểm tra quyền host
if (!isLoggedIn() || !hasRole(ROLE_HOST)) {
    set_flash_message(MSG_ERROR, 'Bạn không có quyền truy cập trang này');
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$page_title = 'Chỉnh sửa Homestay';
$active_tab = 'manage_homestays';

$homestayModel = new Homestay();

// Lấy ID homestay từ URL
$homestay_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$homestay_id) {
    set_flash_message(MSG_ERROR, 'ID homestay không hợp lệ');
    header('Location: ' . SITE_URL . '/views/host/homestays.php');
    exit;
}

// Lấy thông tin homestay - sử dụng phương thức getById()
$homestay = $homestayModel->getById($homestay_id);

// Kiểm tra homestay có tồn tại và thuộc về host này không
if (!$homestay || $homestay['host_id'] != $_SESSION['user_id']) {
    set_flash_message(MSG_ERROR, 'Homestay không tồn tại hoặc bạn không có quyền chỉnh sửa');
    header('Location: ' . SITE_URL . '/views/host/homestays.php');
    exit;
}

// Xử lý form cập nhật homestay
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $homestay_data = [
        'title' => sanitizeInput($_POST['title']),
        'description' => sanitizeInput($_POST['description']),
        'address' => sanitizeInput($_POST['address']),
        'city' => sanitizeInput($_POST['city']),
        'district' => sanitizeInput($_POST['district']),
        'price_per_night' => intval($_POST['price_per_night']),
        'bedrooms' => intval($_POST['bedrooms']),
        'bathrooms' => intval($_POST['bathrooms']),
        'max_guests' => intval($_POST['max_guests']),
        'rules' => sanitizeInput($_POST['rules'])
    ];

    // Xử lý amenities
    $homestay_data['amenities'] = isset($_POST['amenities']) ? json_encode($_POST['amenities']) : '[]';

    // Xử lý upload ảnh mới (nếu có)
    $uploaded_images = [];
    $errors = [];
    
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        // Đường dẫn tuyệt đối đến thư mục uploads
        $upload_dir = __DIR__ . '/../../uploads/';
        
        // Tạo thư mục nếu chưa tồn tại
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                $errors[] = "Không thể tạo thư mục uploads. Vui lòng tạo thủ công thư mục: " . $upload_dir;
            }
        }
        
        // Kiểm tra quyền ghi
        if (!is_writable($upload_dir)) {
            $errors[] = "Thư mục uploads không có quyền ghi. Vui lòng cấp quyền ghi cho thư mục.";
        }
        
        if (empty($errors)) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    // Tạo tên file an toàn
                    $original_name = $_FILES['images']['name'][$key];
                    $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                    $safe_filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($original_name, PATHINFO_FILENAME));
                    $file_name = time() . '_' . $safe_filename . '.' . $file_extension;
                    $file_path = $upload_dir . $file_name;
                    
                    // Kiểm tra định dạng file
                    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (!in_array($file_extension, $allowed_types)) {
                        $errors[] = "File '$original_name' không đúng định dạng. Chỉ chấp nhận JPG, JPEG, PNG, GIF.";
                        continue;
                    }
                    
                    // Kiểm tra kích thước file (max 5MB)
                    if ($_FILES['images']['size'][$key] > 5 * 1024 * 1024) {
                        $errors[] = "File '$original_name' vượt quá kích thước cho phép (5MB).";
                        continue;
                    }
                    
                    // Di chuyển file
                    if (move_uploaded_file($tmp_name, $file_path)) {
                        $uploaded_images[] = $file_name;
                    } else {
                        $errors[] = "Không thể upload file '$original_name'.";
                    }
                } else {
                    $errors[] = "Lỗi khi upload file '{$_FILES['images']['name'][$key]}'.";
                }
            }
        }
    }
    
    // Xử lý kết quả upload
    if (!empty($errors)) {
        set_flash_message(MSG_ERROR, implode('<br>', $errors));
    }
    
    // Kết hợp ảnh cũ và ảnh mới
    $existing_images = isset($_POST['existing_images']) ? $_POST['existing_images'] : [];
    $all_images = array_merge($existing_images, $uploaded_images);
    
    if (!empty($all_images)) {
        $homestay_data['images'] = json_encode($all_images);
    } else {
        // Nếu không có ảnh nào, giữ nguyên ảnh cũ
        $homestay_data['images'] = json_encode($homestay['images']);
    }
    
    // Cập nhật homestay - sử dụng phương thức update()
    if ($homestayModel->update($homestay_id, $homestay_data)) {
        set_flash_message(MSG_SUCCESS, 'Cập nhật homestay thành công!');
        header('Location: ' . SITE_URL . '/views/host/homestays.php');
        exit;
    } else {
        set_flash_message(MSG_ERROR, 'Có lỗi xảy ra khi cập nhật homestay. Vui lòng thử lại.');
    }
}

// Parse JSON fields từ database (đã được xử lý trong getById() nhưng đảm bảo an toàn)
if (is_string($homestay['amenities'])) {
    $homestay['amenities'] = json_decode($homestay['amenities'], true);
}
if (!is_array($homestay['amenities'])) {
    $homestay['amenities'] = [];
}

if (is_string($homestay['images'])) {
    $homestay['images'] = json_decode($homestay['images'], true);
}
if (!is_array($homestay['images'])) {
    $homestay['images'] = [];
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
                <h1 class="h3 mb-0">Chỉnh sửa Homestay</h1>
                <a href="<?php echo SITE_URL; ?>/views/host/homestays.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Quay lại
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data" id="editHomestayForm">
                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="mb-3 text-primary">
                                    <i class="fas fa-info-circle me-2"></i>Thông tin cơ bản
                                </h5>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Tên homestay <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       name="title" 
                                       value="<?php echo htmlspecialchars($homestay['title'] ?? ''); ?>"
                                       placeholder="Nhập tên homestay..."
                                       required>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Mô tả <span class="text-danger">*</span></label>
                                <textarea class="form-control" 
                                          name="description" 
                                          rows="4"
                                          placeholder="Mô tả chi tiết về homestay của bạn..."
                                          required><?php echo htmlspecialchars($homestay['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Thành phố</label>
                                <input type="text" 
                                       class="form-control" 
                                       name="city" 
                                       value="<?php echo htmlspecialchars($homestay['city'] ?? ''); ?>"
                                       placeholder="Ví dụ: Hà Nội, TP.HCM...">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quận/Huyện</label>
                                <input type="text" 
                                       class="form-control" 
                                       name="district" 
                                       value="<?php echo htmlspecialchars($homestay['district'] ?? ''); ?>"
                                       placeholder="Ví dụ: Quận 1, Ba Đình...">
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Địa chỉ chi tiết <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       name="address" 
                                       value="<?php echo htmlspecialchars($homestay['address'] ?? ''); ?>"
                                       placeholder="Số nhà, đường, phường/xã..."
                                       required>
                            </div>
                        </div>

                        <!-- Property Details -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="mb-3 text-primary">
                                    <i class="fas fa-home me-2"></i>Thông tin phòng
                                </h5>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Giá mỗi đêm (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       name="price_per_night" 
                                       value="<?php echo $homestay['price_per_night'] ?? ''; ?>"
                                       min="100000"
                                       step="10000"
                                       placeholder="500000"
                                       required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Số phòng ngủ <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       name="bedrooms" 
                                       value="<?php echo $homestay['bedrooms'] ?? 1; ?>"
                                       min="1"
                                       max="10"
                                       required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Số phòng tắm <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       name="bathrooms" 
                                       value="<?php echo $homestay['bathrooms'] ?? 1; ?>"
                                       min="1"
                                       max="10"
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số khách tối đa <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       name="max_guests" 
                                       value="<?php echo $homestay['max_guests'] ?? 2; ?>"
                                       min="1"
                                       max="20"
                                       required>
                            </div>
                        </div>

                        <!-- Amenities -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="mb-3 text-primary">
                                    <i class="fas fa-concierge-bell me-2"></i>Tiện nghi
                                </h5>
                            </div>
                            
                            <div class="col-12">
                                <div class="row">
                                    <?php
                                    $common_amenities = [
                                        'wifi' => 'Wi-Fi miễn phí',
                                        'air_conditioner' => 'Máy lạnh',
                                        'tv' => 'TV',
                                        'kitchen' => 'Bếp',
                                        'parking' => 'Chỗ đỗ xe',
                                        'pool' => 'Hồ bơi',
                                        'garden' => 'Vườn',
                                        'bbq' => 'Khu vực BBQ',
                                        'washing_machine' => 'Máy giặt',
                                        'hot_water' => 'Nước nóng',
                                        'breakfast' => 'Bữa sáng',
                                        'security' => 'An ninh 24/7'
                                    ];
                                    
                                    foreach ($common_amenities as $key => $amenity):
                                        $is_checked = in_array($amenity, $homestay['amenities']) ? 'checked' : '';
                                    ?>
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       name="amenities[]" 
                                                       value="<?php echo $amenity; ?>"
                                                       id="amenity_<?php echo $key; ?>"
                                                       <?php echo $is_checked; ?>>
                                                <label class="form-check-label" for="amenity_<?php echo $key; ?>">
                                                    <?php echo $amenity; ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Images -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="mb-3 text-primary">
                                    <i class="fas fa-images me-2"></i>Hình ảnh
                                </h5>
                            </div>
                            
                            <!-- Hiển thị ảnh hiện tại -->
                            <?php if (!empty($homestay['images'])): ?>
                            <div class="col-12 mb-3">
                                <label class="form-label">Ảnh hiện tại</label>
                                <div class="row" id="existingImages">
                                    <?php foreach ($homestay['images'] as $image): ?>
                                    <div class="col-md-3 mb-3 image-item">
                                        <div class="card position-relative">
                                            <img src="<?php echo SITE_URL . '/uploads/' . $image; ?>" 
                                                 class="card-img-top" 
                                                 style="height: 150px; object-fit: cover;"
                                                 alt="Homestay image"
                                                 onerror="this.src='<?php echo SITE_URL; ?>/assets/img/no-image.jpg'">
                                            <div class="card-body p-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           name="existing_images[]" 
                                                           value="<?php echo $image; ?>" 
                                                           id="img_<?php echo $image; ?>" 
                                                           checked>
                                                    <label class="form-check-label small" for="img_<?php echo $image; ?>">
                                                        Giữ lại
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Upload ảnh mới -->
                            <div class="col-12 mb-3">
                                <label class="form-label">Thêm ảnh mới (Tối đa 5 ảnh)</label>
                                <input type="file" 
                                       class="form-control" 
                                       name="images[]" 
                                       multiple
                                       accept="image/*">
                                <div class="form-text">Chọn ảnh mới để thêm vào homestay (tối đa 5 ảnh).</div>
                            </div>
                            
                            <!-- Preview ảnh mới -->
                            <div class="col-12">
                                <div id="imagePreview" class="row"></div>
                            </div>
                        </div>

                        <!-- House Rules -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="mb-3 text-primary">
                                    <i class="fas fa-clipboard-list me-2"></i>Nội quy nhà
                                </h5>
                            </div>
                            
                            <div class="col-12">
                                <textarea class="form-control" 
                                          name="rules" 
                                          rows="3"
                                          placeholder="Ví dụ: Không hút thuốc, không tổ chức tiệc, giữ im lặng sau 22h..."><?php echo htmlspecialchars($homestay['rules'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="<?php echo SITE_URL; ?>/views/host/homestays.php" class="btn btn-secondary me-md-2">
                                        <i class="fas fa-times me-1"></i>Hủy
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Cập nhật homestay
                                    </button>
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
    // Image preview for new images
    const imageInput = document.querySelector('input[name="images[]"]');
    const imagePreview = document.getElementById('imagePreview');
    
    imageInput.addEventListener('change', function() {
        imagePreview.innerHTML = '';
        
        if (this.files) {
            Array.from(this.files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-md-3 mb-3';
                        col.innerHTML = `
                            <div class="card">
                                <img src="${e.target.result}" class="card-img-top" style="height: 150px; object-fit: cover;">
                                <div class="card-body p-2 text-center">
                                    <small class="text-muted">Ảnh mới</small>
                                </div>
                            </div>
                        `;
                        imagePreview.appendChild(col);
                    }
                    
                    reader.readAsDataURL(file);
                }
            });
        }
    });
    
    // Form validation
    const form = document.getElementById('editHomestayForm');
    form.addEventListener('submit', function(e) {
        const price = document.querySelector('input[name="price_per_night"]').value;
        if (price < 100000) {
            e.preventDefault();
            alert('Giá mỗi đêm phải từ 100,000 VNĐ trở lên');
            return false;
        }
        
        // Kiểm tra xem có ít nhất một ảnh được chọn (cũ hoặc mới)
        const existingImages = document.querySelectorAll('input[name="existing_images[]"]:checked');
        const newImages = document.querySelector('input[name="images[]"]').files;
        
        if (existingImages.length === 0 && newImages.length === 0) {
            e.preventDefault();
            alert('Vui lòng chọn ít nhất 1 ảnh (giữ lại ảnh cũ hoặc tải lên ảnh mới)');
            return false;
        }
        
        if (newImages.length > 5) {
            e.preventDefault();
            alert('Chỉ được chọn tối đa 5 ảnh mới');
            return false;
        }
    });
});
</script>

<style>
.form-check-label {
    font-weight: normal;
}
.card {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}
.image-item .card:hover {
    border-color: #0d6efd;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
