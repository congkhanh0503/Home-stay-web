<?php
require_once 'config.php';

function getDbConnection() {
    $servername = DB_HOST;
    $username = DB_USER;
    $password = DB_PASS;
    $dbname = DB_NAME;
    $port = DB_PORT;

    // Tạo kết nối
    $conn = mysqli_connect($servername, $username, $password, $dbname, $port);

    // Kiểm tra kết nối
    if (!$conn) {
        die("Kết nối database thất bại: " . mysqli_connect_error());
    }
    
    // Thiết lập charset cho kết nối (quan trọng để hiển thị tiếng Việt đúng)
    mysqli_set_charset($conn, "utf8mb4");
    
    return $conn;
}

// Hàm đóng kết nối
function closeDbConnection($conn) {
    if ($conn) {
        mysqli_close($conn);
    }
}

// Hàm thực hiện query an toàn
function executeQuery($conn, $sql, $params = [], $types = "") {
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return false;
    }
    
    if (!empty($params)) {
    // Đảm bảo tất cả parameters là chuỗi hoặc số
    $params = array_map(function($param) {
        if (is_array($param)) {
            // Nếu là mảng, chuyển thành JSON string
            return json_encode($param);
        }
        return $param;
    }, $params);
    
    $stmt->bind_param($types, ...$params);
}
    
    $success = mysqli_stmt_execute($stmt);
    if (!$success) {
        return false;
    }
    
    return $stmt;
}

// Hàm lấy tất cả kết quả
function fetchAll($conn, $sql, $params = [], $types = "") {
    $stmt = executeQuery($conn, $sql, $params, $types);
    if (!$stmt) {
        return [];
    }
    
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $rows;
}

// Hàm lấy một kết quả
function fetchOne($conn, $sql, $params = [], $types = "") {
    $stmt = executeQuery($conn, $sql, $params, $types);
    if (!$stmt) {
        return null;
    }
    
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

// Hàm thực hiện INSERT và trả về ID
function insertQuery($conn, $sql, $params = [], $types = "") {
    $stmt = executeQuery($conn, $sql, $params, $types);
    if (!$stmt) {
        return false;
    }
    
    $insert_id = mysqli_stmt_insert_id($stmt);
    mysqli_stmt_close($stmt);
    
    return $insert_id;
}

// Hàm thực hiện UPDATE/DELETE
function executeUpdate($conn, $sql, $params = [], $types = "") {
    $stmt = executeQuery($conn, $sql, $params, $types);
    if (!$stmt) {
        return false;
    }
    
    $affected_rows = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    
    return $affected_rows;
}
?>