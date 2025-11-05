<?php
require_once '../config/db.php'; 
start_secure_session();

header('Content-Type: application/json');

$leader_id = $_GET['id'] ?? null;

if (!$leader_id || !is_numeric($leader_id)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid leader ID."]);
    exit;
}
if (!isset($_SESSION['id']) || (!is_citizen() && !is_admin())) {
    http_response_code(403);
    echo json_encode(['error' => 'User not logged in or unauthorized.']);
    exit;
}

$leader_id = (int)$leader_id;
$user_id = (int)$_SESSION['id'];

$response = [];

$stmt = $conn->prepare("SELECT id, full_name, age, dob, gender, photo_path, current_position, party_affiliation, past_positions, career_duration, constituency, declared_assets, annual_income, businesses_owned, investments, total_police_cases, case_types, court_case_status, num_children, qualifications FROM leaders WHERE id = ?");
$stmt->bind_param("i", $leader_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Leader not found']);
    exit;
}
$response['details'] = $result->fetch_assoc();
$stmt->close();

$sql_summary = "SELECT 
                    COALESCE(AVG(rating), 0) AS average_rating,
                    COUNT(id) AS total_ratings,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) AS 5_star_count,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) AS 4_star_count,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) AS 3_star_count,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) AS 2_star_count,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) AS 1_star_count
                FROM leader_reviews WHERE leader_id = ?";
$stmt = $conn->prepare($sql_summary);
$stmt->bind_param("i", $leader_id);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();

$summary_data = [
    'average_rating' => (float)$summary['average_rating'],
    'total_ratings' => (int)$summary['total_ratings'],
    'breakdown' => []
];

if ($summary['total_ratings'] > 0) {
    $total_ratings = $summary['total_ratings']; // Use this to avoid division by zero
    $summary_data['breakdown']['5'] = ($summary['5_star_count'] / $total_ratings) * 100;
    $summary_data['breakdown']['4'] = ($summary['4_star_count'] / $total_ratings) * 100;
    $summary_data['breakdown']['3'] = ($summary['3_star_count'] / $total_ratings) * 100;
    $summary_data['breakdown']['2'] = ($summary['2_star_count'] / $total_ratings) * 100;
    $summary_data['breakdown']['1'] = ($summary['1_star_count'] / $total_ratings) * 100;
} else {
    for ($i = 1; $i <= 5; $i++) $summary_data['breakdown'][$i] = 0;
}
$response['review_summary'] = $summary_data;
$stmt->close();


$sql_reviews = "SELECT 
                    r.rating, r.review_text, r.updated_at, u.full_name 
                FROM leader_reviews r
                JOIN users u ON r.user_id = u.id
                WHERE r.leader_id = ? AND r.review_text IS NOT NULL AND r.review_text != ''
                ORDER BY r.updated_at DESC
                LIMIT 10"; 
$stmt = $conn->prepare($sql_reviews);
$stmt->bind_param("i", $leader_id);
$stmt->execute();
$response['reviews_list'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();


$stmt = $conn->prepare("SELECT rating, review_text FROM leader_reviews WHERE leader_id = ? AND user_id = ?");
$stmt->bind_param("ii", $leader_id, $user_id);
$stmt->execute();
$response['user_review'] = $stmt->get_result()->fetch_assoc(); 
$stmt->close();

$conn->close();
echo json_encode($response);
?>

