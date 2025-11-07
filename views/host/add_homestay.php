
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

$page_title = 'Thêm Homestay mới';
$active_tab = 'add_homestay';

$homestayModel = new Homestay();

// Xử lý form thêm homestay
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $homestay_data = [
        'host_id' => $_SESSION['user_id'],
        'title' => sanitizeInput($_POST['title']),
        'description' => sanitizeInput($_POST['description']),
        'address' => sanitizeInput($_POST['address']),
        'city' => sanitizeInput($_POST['city']),
        'district' => sanitizeInput($_POST['district']),
        'price_per_night' => intval($_POST['price_per_night']),
        'bedrooms' => intval($_POST['bedrooms']),
        'bathrooms' => intval($_POST['bathrooms']),
        'max_guests' => intval($_POST['max_guests']),
        'amenities' => isset($_POST['amenities']) ? $_POST['amenities'] : [],
        'rules' => sanitizeInput($_POST['rules']),
        'status' => 'pending' // Chờ duyệt khi mới tạo
    ];

    // Xử lý upload ảnh
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
    $uploaded_images = [];
    $errors = [];
    
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
                    error_log("Upload successful: $file_path"); // Log để debug
                } else {
                    $error_msg = "Không thể upload file '$original_name'. ";
                    $error_msg .= "Lỗi: " . (error_get_last()['message'] ?? 'Unknown error');
                    $errors[] = $error_msg;
                    error_log("Upload failed: $file_path - " . (error_get_last()['message'] ?? 'Unknown error'));
                }
            } else {
                $errors[] = "Lỗi khi upload file '{$_FILES['images']['name'][$key]}'. Mã lỗi: {$_FILES['images']['error'][$key]}";
            }
        }
    }
}
    
    // Xử lý kết quả upload
    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
    }
    
    if (!empty($uploaded_images)) {
        $_POST['images'] = $uploaded_images;
        $_SESSION['flash_success'] = 'Upload ảnh thành công!';
    } else {
        $_SESSION['flash_error'] = 'Không có ảnh nào được upload thành công.';
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
                <h1 class="h3 mb-0">Thêm Homestay mới</h1>
                <a href="<?php echo SITE_URL; ?>/views/host/homestays.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Quay lại
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data" id="addHomestayForm">
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
                                       value="<?php echo $_POST['title'] ?? ''; ?>"
                                       placeholder="Nhập tên homestay..."
                                       required>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Mô tả <span class="text-danger">*</span></label>
                                <textarea class="form-control" 
                                          name="description" 
                                          rows="4"
                                          placeholder="Mô tả chi tiết về homestay của bạn..."
                                          required><?php echo $_POST['description'] ?? ''; ?></textarea>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Thành phố <span class="text-danger">*</span></label>
                                <select class="form-select" name="city" required>
                                    <option value="">Chọn thành phố</option>
                                    <option value="hanoi">Hà Nội</option>
                                    <option value="hcm">TP. Hồ Chí Minh</option>
                                    <option value="danang">Đà Nẵng</option>
                                    <option value="halong">Hạ Long</option>
                                    <option value="nhatrang">Nha Trang</option>
                                    <option value="dalat">Đà Lạt</option>
                                    <option value="hoian">Hội An</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quận/Huyện <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       name="district" 
                                       value="<?php echo $_POST['district'] ?? ''; ?>"
                                       placeholder="Ví dụ: Quận 1, Ba Đình..."
                                       required>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Địa chỉ chi tiết <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       name="address" 
                                       value="<?php echo $_POST['address'] ?? ''; ?>"
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
                                       value="<?php echo $_POST['price_per_night'] ?? ''; ?>"
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
                                       value="<?php echo $_POST['bedrooms'] ?? 1; ?>"
                                       min="1"
                                       max="10"
                                       required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Số phòng tắm <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       name="bathrooms" 
                                       value="<?php echo $_POST['bathrooms'] ?? 1; ?>"
                                       min="1"
                                       max="10"
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số khách tối đa <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       name="max_guests" 
                                       value="<?php echo $_POST['max_guests'] ?? 2; ?>"
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
                                    ?>
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       name="amenities[]" 
                                                       value="<?php echo $amenity; ?>"
                                                       id="amenity_<?php echo $key; ?>">
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
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Tải lên hình ảnh (Tối đa 5 ảnh) <span class="text-danger">*</span></label>
                                <input type="file" 
                                       class="form-control" 
                                       name="images[]" 
                                       multiple
                                       accept="image/*"
                                       required>
                                <div class="form-text">Chọn ít nhất 1 ảnh và tối đa 5 ảnh để hiển thị homestay của bạn.</div>
                            </div>
                            
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
                                          placeholder="Ví dụ: Không hút thuốc, không tổ chức tiệc, giữ im lặng sau 22h..."><?php echo $_POST['rules'] ?? ''; ?></textarea>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="reset" class="btn btn-secondary me-md-2">
                                        <i class="fas fa-redo me-1"></i>Nhập lại
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-plus me-1"></i>Thêm homestay
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
    // Image preview
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
    const form = document.getElementById('addHomestayForm');
    form.addEventListener('submit', function(e) {
        const price = document.querySelector('input[name="price_per_night"]').value;
        if (price < 100000) {
            e.preventDefault();
            alert('Giá mỗi đêm phải từ 100,000 VNĐ trở lên');
            return false;
        }
        
        const images = document.querySelector('input[name="images[]"]').files;
        if (images.length === 0) {
            e.preventDefault();
            alert('Vui lòng chọn ít nhất 1 ảnh');
            return false;
        }
        
        if (images.length > 5) {
            e.preventDefault();
            alert('Chỉ được chọn tối đa 5 ảnh');
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
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>