<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/helpers.php';

// Hàm upload ảnh
function uploadImage($file, $folder = 'general') {
    // Kiểm tra file
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'message' => 'Có lỗi xảy ra khi upload file.'
        ];
    }
    
    // Kiểm tra kích thước file
    if ($file['size'] > MAX_FILE_SIZE) {
        return [
            'success' => false,
            'message' => 'File quá lớn. Kích thước tối đa là 5MB.'
        ];
    }
    
    // Kiểm tra loại file
    $file_info = pathinfo($file['name']);
    $extension = strtolower($file_info['extension']);
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($extension, $allowed_types)) {
        return [
            'success' => false,
            'message' => 'Chỉ chấp nhận file ảnh (JPG, JPEG, PNG, GIF, WEBP).'
        ];
    }
    
    // Tạo thư mục nếu chưa tồn tại
    $upload_dir = UPLOAD_PATH . $folder . '/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Tạo tên file mới
    $file_name = uniqid() . '_' . time() . '.' . $extension;
    $file_path = $upload_dir . $file_name;
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        return [
            'success' => true,
            'file_name' => $file_name,
            'file_path' => $folder . '/' . $file_name,
            'message' => 'Upload ảnh thành công!'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Có lỗi xảy ra khi lưu file.'
        ];
    }
}

// Hàm upload nhiều ảnh
function uploadMultipleImages($files, $folder = 'homestays') {
    $results = [];
    
    foreach ($files['tmp_name'] as $key => $tmp_name) {
        if ($files['error'][$key] === UPLOAD_ERR_OK) {
            $file = [
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
                'tmp_name' => $tmp_name,
                'error' => $files['error'][$key],
                'size' => $files['size'][$key]
            ];
            
            $result = uploadImage($file, $folder);
            $results[] = $result;
        }
    }
    
    return $results;
}

// Hàm xóa ảnh
function deleteImage($file_path) {
    $full_path = UPLOAD_PATH . $file_path;
    
    if (file_exists($full_path) && is_file($full_path)) {
        if (unlink($full_path)) {
            return [
                'success' => true,
                'message' => 'Xóa ảnh thành công!'
            ];
        }
    }
    
    return [
        'success' => false,
        'message' => 'Không thể xóa ảnh.'
    ];
}
// Hàm xử lý upload ảnh homestay
function handleHomestayImages($files) {
    $upload_results = uploadMultipleImages($files, 'homestays');
    
    $successful_uploads = [];
    $errors = [];
    
    foreach ($upload_results as $result) {
        if ($result['success']) {
            $successful_uploads[] = $result['file_path'];
        } else {
            $errors[] = $result['message'];
        }
    }
    
    return [
        'success' => !empty($successful_uploads),
        'images' => $successful_uploads,
        'errors' => $errors,
        'message' => !empty($successful_uploads) ? 
                    'Upload ' . count($successful_uploads) . ' ảnh thành công!' :
                    'Upload ảnh thất bại!'
    ];
}

// Hàm xóa ảnh homestay
function deleteHomestayImages($image_paths) {
    if (is_string($image_paths)) {
        $image_paths = json_decode($image_paths, true) ?: [];
    }
    
    $results = [];
    foreach ($image_paths as $image_path) {
        $results[] = deleteImage($image_path);
    }
    
    return $results;
}
?>