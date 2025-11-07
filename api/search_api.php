<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../controllers/HomestayController.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$homestayController = new HomestayController();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        handleSearchRequest($homestayController);
    } else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Chỉ hỗ trợ phương thức GET'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}

function handleSearchRequest($homestayController) {
    $action = $_GET['action'] ?? 'search';
    
    switch ($action) {
        case 'search':
            $filters = [
                'location' => $_GET['location'] ?? '',
                'min_price' => $_GET['min_price'] ?? '',
                'max_price' => $_GET['max_price'] ?? '',
                'guests' => $_GET['guests'] ?? '',
                'bedrooms' => $_GET['bedrooms'] ?? '',
                'amenities' => isset($_GET['amenities']) ? explode(',', $_GET['amenities']) : []
            ];
            
            // Remove empty filters
            $filters = array_filter($filters, function($value) {
                return $value !== '' && $value !== [];
            });
            
            $result = $homestayController->search($filters);
            echo json_encode($result);
            break;
            
        case 'detail':
            $homestay_id = $_GET['id'] ?? null;
            if (!$homestay_id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Thiếu ID homestay'
                ]);
                return;
            }
            
            $result = $homestayController->getHomestayById($homestay_id);
            echo json_encode($result);
            break;
            
        case 'popular':
            $limit = $_GET['limit'] ?? 6;
            $result = getPopularHomestays($limit);
            echo json_encode($result);
            break;
            
        case 'check_availability':
            $homestay_id = $_GET['homestay_id'] ?? null;
            $check_in = $_GET['check_in'] ?? null;
            $check_out = $_GET['check_out'] ?? null;
            
            if (!$homestay_id || !$check_in || !$check_out) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Thiếu thông tin bắt buộc'
                ]);
                return;
            }
            
            $result = checkHomestayAvailability($homestay_id, $check_in, $check_out);
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

function getPopularHomestays($limit = 6) {
    $conn = getDbConnection();
    
    $sql = "SELECT h.*, u.full_name as host_name,
            COUNT(b.id) as booking_count,
            (SELECT AVG(rating) FROM reviews WHERE homestay_id = h.id) as avg_rating
            FROM homestays h
            LEFT JOIN users u ON h.host_id = u.id
            LEFT JOIN bookings b ON h.id = b.homestay_id AND b.status = 'completed'
            WHERE h.status = 'active'
            GROUP BY h.id
            ORDER BY booking_count DESC, h.created_at DESC
            LIMIT ?";
    
    $homestays = fetchAll($conn, $sql, [$limit], 'i');
    closeDbConnection($conn);
    
    // Parse JSON fields
    foreach ($homestays as &$homestay) {
        $homestay['amenities'] = json_decode($homestay['amenities'], true) ?: [];
        $homestay['images'] = json_decode($homestay['images'], true) ?: [];
    }
    
    return [
        'success' => true,
        'data' => $homestays
    ];
}

function checkHomestayAvailability($homestay_id, $check_in, $check_out) {
    $conn = getDbConnection();
    
    $sql = "SELECT COUNT(*) as conflict_count
            FROM bookings 
            WHERE homestay_id = ? 
            AND status IN ('pending', 'confirmed')
            AND ((check_in BETWEEN ? AND ?) OR (check_out BETWEEN ? AND ?))";
    
    $result = fetchOne($conn, $sql, [
        $homestay_id,
        $check_in, $check_out,
        $check_in, $check_out
    ], 'issss');
    
    closeDbConnection($conn);
    
    $is_available = $result['conflict_count'] == 0;
    
    return [
        'success' => true,
        'available' => $is_available,
        'message' => $is_available ? 'Homestay có sẵn' : 'Homestay đã có người đặt'
    ];
}
?>