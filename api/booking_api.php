<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../controllers/BookingController.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để sử dụng API'
    ]);
    exit();
}

$bookingController = new BookingController();
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($bookingController);
            break;
            
        case 'POST':
            handlePostRequest($bookingController, $input);
            break;
            
        case 'PUT':
            handlePutRequest($bookingController, $input);
            break;
            
        case 'DELETE':
            handleDeleteRequest($bookingController);
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

function handleGetRequest($bookingController) {
    $action = $_GET['action'] ?? '';
    $booking_id = $_GET['id'] ?? null;
    
    switch ($action) {
        case 'user_bookings':
            $status = $_GET['status'] ?? null;
            $result = $bookingController->getUserBookings($status);
            echo json_encode($result);
            break;
            
        case 'host_bookings':
            if (!hasRole(ROLE_HOST)) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Bạn không có quyền truy cập'
                ]);
                return;
            }
            $status = $_GET['status'] ?? null;
            $result = $bookingController->getHostBookings($status);
            echo json_encode($result);
            break;
            
        case 'detail':
            if (!$booking_id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Thiếu ID booking'
                ]);
                return;
            }
            $result = $bookingController->getBookingDetail($booking_id);
            echo json_encode($result);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Action không hợp lệ'
            ]);
    }
}

function handlePostRequest($bookingController, $input) {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'create':
            if (empty($input)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Thiếu dữ liệu'
                ]);
                return;
            }
            
            // Validate required fields
            $required_fields = ['homestay_id', 'check_in', 'check_out', 'guests'];
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
            
            // Create booking using POST data
            $_POST = $input;
            $result = $bookingController->create();
            http_response_code($result['success'] ? 201 : 400);
            echo json_encode($result);
            break;
            
        case 'update_status':
            if (!hasRole(ROLE_HOST)) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Bạn không có quyền thực hiện hành động này'
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
            
            $_POST = $input;
            $result = $bookingController->updateStatus();
            http_response_code($result['success'] ? 200 : 400);
            echo json_encode($result);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Action không hợp lệ'
            ]);
    }
}

function handlePutRequest($bookingController, $input) {
    $booking_id = $_GET['id'] ?? null;
    
    if (!$booking_id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu ID booking'
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
    
    // Handle different update actions
    if (isset($input['action'])) {
        switch ($input['action']) {
            case 'cancel':
                $result = $bookingController->cancel($booking_id);
                http_response_code($result['success'] ? 200 : 400);
                echo json_encode($result);
                break;
                
            default:
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Action không hợp lệ'
                ]);
        }
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu action'
        ]);
    }
}

function handleDeleteRequest($bookingController) {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'DELETE method chưa được hỗ trợ'
    ]);
}
?>