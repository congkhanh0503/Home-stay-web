<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../controllers/AuthController.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$authController = new AuthController();
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'POST':
            handlePostRequest($authController, $input);
            break;
            
        case 'GET':
            handleGetRequest($authController);
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

function handlePostRequest($authController, $input) {
    $action = $_GET['action'] ?? '';
    
    if (empty($input)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu dữ liệu'
        ]);
        return;
    }
    
    switch ($action) {
        case 'login':
            // Validate required fields
            $required_fields = ['email', 'password'];
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
            
            $_POST = $input;
            $result = $authController->login();
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => $result['message'],
                    'user' => [
                        'id' => $_SESSION['user_id'],
                        'name' => $_SESSION['user_name'],
                        'email' => $_SESSION['user_email'],
                        'role' => $_SESSION['user_role']
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }
            break;
            
        case 'register':
            // Validate required fields
            $required_fields = ['full_name', 'email', 'password', 'confirm_password'];
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
            
            $_POST = $input;
            $result = $authController->register();
            
            http_response_code($result['success'] ? 201 : 400);
            echo json_encode($result);
            break;
            
        case 'logout':
            $result = $authController->logout();
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

function handleGetRequest($authController) {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'check_auth':
            if (isLoggedIn()) {
                echo json_encode([
                    'success' => true,
                    'authenticated' => true,
                    'user' => [
                        'id' => $_SESSION['user_id'],
                        'name' => $_SESSION['user_name'],
                        'email' => $_SESSION['user_email'],
                        'role' => $_SESSION['user_role']
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'authenticated' => false
                ]);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Action không hợp lệ'
            ]);
    }
}
?>