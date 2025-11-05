<?php
require_once '../config/db.php';
start_secure_session();

header('Content-Type: application/json');

if (!is_citizen()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. You must be logged in as a citizen to review.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$leader_id = $data['leader_id'] ?? null;
$rating = $data['rating'] ?? null;
$review_text = $data['review_text'] ?? null; 
$user_id = $_SESSION['id'] ?? null;

if (empty($leader_id) || empty($rating) || empty($user_id) || !is_numeric($leader_id) || !is_numeric($rating)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid data. Rating and leader ID are required.']);
    exit;
}

if (is_string($review_text) && trim($review_text) === '') {
    $review_text = null;
}

$sql = "INSERT INTO leader_reviews (user_id, leader_id, rating, review_text) 
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            rating = VALUES(rating), 
            review_text = VALUES(review_text),
            updated_at = CURRENT_TIMESTAMP";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("iiis", $user_id, $leader_id, $rating, $review_text);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Review saved!']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error saving review: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'SQL Prepare error: ' . $conn->error]);
}

$conn->close();
?>

