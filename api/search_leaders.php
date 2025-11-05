<?php
require_once '../config/db.php';
start_secure_session();
header('Content-Type: application/json');

$name = '%' . (trim($_GET['name'] ?? '')) . '%';
$party = '%' . (trim($_GET['party'] ?? '')) . '%';
$position = '%' . (trim($_GET['position'] ?? '')) . '%';
$constituency = '%' . (trim($_GET['constituency'] ?? '')) . '%';

$sql = "SELECT 
            l.id, l.full_name, l.current_position, l.party_affiliation, l.constituency, l.photo_path,
            COALESCE(AVG(r.rating), 0) AS average_rating,
            COUNT(r.id) AS total_ratings
        FROM leaders l
        LEFT JOIN leader_reviews r ON l.id = r.leader_id
        WHERE l.full_name LIKE ? 
          AND l.party_affiliation LIKE ? 
          AND l.current_position LIKE ? 
          AND l.constituency LIKE ?
        GROUP BY l.id
        ORDER BY l.full_name ASC";

$response = [];

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ssss", $name, $party, $position, $constituency);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        echo json_encode($response);
    } else {
        http_response_code(500);
        $response = ["error" => "Database error: " . $stmt->error];
    }
    $stmt->close();
} else {
    http_response_code(500);
    $response = ["error" => "SQL Prepare error: " . $conn->error];
}

$conn->close();
if (isset($response['error'])) {
    echo json_encode($response);
}
?>

