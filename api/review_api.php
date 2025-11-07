<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../models/Review.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if user is logged in for POST, PUT, DELETE methods
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'OPTIONS']) && !isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để sử dụng API'
    ]);
    exit();
}

$reviewModel = new Review();
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($reviewModel);
            break;
            
        case 'POST':
            handlePostRequest($reviewModel, $input);
            break;
            
        case 'PUT':
            handlePutRequest($reviewModel, $input);
            break;
            
        case 'DELETE':
            handleDeleteRequest($reviewModel);
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Phương thức không được hỗ trợ'
            ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}

function handleGetRequest($reviewModel) {
    $action = $_GET['action'] ?? '';
    $homestay_id = $_GET['homestay_id'] ?? null;
    $review_id = $_GET['id'] ?? null;
    
    switch ($action) {
        case 'homestay_reviews':
            if (!$homestay_id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Thiếu ID homestay'
                ]);
                return;
            }
            
            $filters = [
                'rating' => $_GET['rating'] ?? null,
                'limit' => $_GET['limit'] ?? null
            ];
            
            $reviews = $reviewModel->getByHomestay($homestay_id, $filters);
            $stats = $reviewModel->getAverageRating($homestay_id);
            
            echo json_encode([
                'success' => true,
                'data' => $reviews,
                'stats' => $stats
            ]);
            break;
            
        case 'user_reviews':
            $user_id = $_SESSION['user_id'];
            $filters = [
                'limit' => $_GET['limit'] ?? null
            ];
            
            $reviews = $reviewModel->getByUser($user_id, $filters);
            echo json_encode([
                'success' => true,
                'data' => $reviews
            ]);
            break;
            
        case 'detail':
            if (!$review_id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Thiếu ID review'
                ]);
                return;
            }
            
            $review = $reviewModel->getById($review_id);
            if ($review) {
                echo json_encode([
                    'success' => true,
                    'data' => $review
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Không tìm thấy review'
                ]);
            }
            break;
            
        case 'stats':
            if (!$homestay_id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Thiếu ID homestay'
                ]);
                return;
            }
            
            $stats = $reviewModel->getAverageRating($homestay_id);
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Action không hợp lệ'
            ]);
    }
}

function handlePostRequest($reviewModel, $input) {
    if (empty($input)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu dữ liệu'
        ]);
        return;
    }
    
    // Validate required fields
    $required_fields = ['booking_id', 'homestay_id', 'rating'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "Thiếu trường bắt buộc: $field"
            ]);
            return;
        }
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Check if user has already reviewed this booking
    if ($reviewModel->userHasReviewed($input['booking_id'], $user_id)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Bạn đã đánh giá booking này rồi'
        ]);
        return;
    }
    
    // Check if user has stayed at this homestay
    if (!$reviewModel->userHasStayed($input['homestay_id'], $user_id)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Bạn chưa từng ở homestay này nên không thể đánh giá'
        ]);
        return;
    }
    
    // Validate rating
    $rating = intval($input['rating']);
    if ($rating < 1 || $rating > 5) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Rating phải từ 1 đến 5 sao'
        ]);
        return;
    }
    
    $review_data = [
        'booking_id' => $input['booking_id'],
        'user_id' => $user_id,
        'homestay_id' => $input['homestay_id'],
        'rating' => $rating,
        'comment' => $input['comment'] ?? ''
    ];
    
    $review_id = $reviewModel->create($review_data);
    
    if ($review_id) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'review_id' => $review_id,
            'message' => 'Đánh giá thành công!'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi tạo đánh giá'
        ]);
    }
}

function handlePutRequest($reviewModel, $input) {
    $review_id = $_GET['id'] ?? null;
    
    if (!$review_id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu ID review'
        ]);
        return;
    }
    
    if (empty($input)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu dữ liệu'
        ]);
        return;
    }
    
    // Check if review exists and belongs to user
    $review = $reviewModel->getById($review_id);
    if (!$review) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy review'
        ]);
        return;
    }
    
    if ($review['user_id'] != $_SESSION['user_id']) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Bạn không có quyền chỉnh sửa review này'
        ]);
        return;
    }
    
    // Validate rating if provided
    if (isset($input['rating'])) {
        $rating = intval($input['rating']);
        if ($rating < 1 || $rating > 5) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Rating phải từ 1 đến 5 sao'
            ]);
            return;
        }
    }
    
    $review_data = [
        'rating' => $input['rating'] ?? $review['rating'],
        'comment' => $input['comment'] ?? $review['comment']
    ];
    
    $result = $reviewModel->update($review_id, $review_data);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật đánh giá thành công!'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi cập nhật đánh giá'
        ]);
    }
}

function handleDeleteRequest($reviewModel) {
    $review_id = $_GET['id'] ?? null;
    
    if (!$review_id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu ID review'
        ]);
        return;
    }
    
    // Check if review exists and belongs to user
    $review = $reviewModel->getById($review_id);
    if (!$review) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy review'
        ]);
        return;
    }
    
    if ($review['user_id'] != $_SESSION['user_id'] && !hasRole(ROLE_ADMIN)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Bạn không có quyền xóa review này'
        ]);
        return;
    }
    
    $result = $reviewModel->delete($review_id);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Xóa đánh giá thành công!'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi xóa đánh giá'
        ]);
    }
}
?>