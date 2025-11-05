<?php
require_once '../config/db.php';
start_secure_session();

// Access control
if (!is_admin()) {
    http_response_code(403);
    echo json_encode(["error" => "Access denied."]);
    exit;
}

header('Content-Type: application/json');

$search_term = '%' . (trim($_GET['search'] ?? '')) . '%';

$sql = "SELECT id, full_name, email, created_at FROM users 
        WHERE full_name LIKE ? OR email LIKE ? ORDER BY created_at DESC";

$response = [];

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ss", $search_term, $search_term);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        echo json_encode($response);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Database execution error: " . $stmt->error]);
    }
    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(["error" => "SQL Prepare error: " . $conn->error]);
}

$conn->close();
?>