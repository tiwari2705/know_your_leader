<?php
require_once '../config/db.php';
start_secure_session();

if (!is_admin()) {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Access denied."]);
    exit;
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? null;
$leader_id = $_POST['leader_id'] ?? null;
$response = ["success" => false, "error" => ""];

function upload_photo($file, $current_path = null) {
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ["path" => $current_path]; 
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ["error" => "Upload failed with error code: " . $file['error']];
    }
    
    $target_dir = "../uploads/leaders/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_file_name = uniqid('leader_') . '.' . $imageFileType;
    $target_file = $target_dir . $new_file_name;
    $db_path = "uploads/leaders/" . $new_file_name;

    if (!getimagesize($file["tmp_name"])) {
        return ["error" => "File is not an image."];
    }
    
    if ($file["size"] > 5000000) { 
        return ["error" => "File is too large (max 5MB)."];
    }

    if(!in_array($imageFileType, ["jpg", "png", "jpeg"])) {
        return ["error" => "Only JPG, JPEG, & PNG files are allowed."];
    }

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        if ($current_path && file_exists('../' . $current_path)) {
            unlink('../' . $current_path);
        }
        return ["path" => $db_path];
    } else {
        return ["error" => "There was an error moving the uploaded file."];
    }
}

if ($action === 'delete') {
    if (!is_numeric($leader_id)) {
        $response['error'] = "Invalid leader ID for deletion.";
        echo json_encode($response);
        exit;
    }
    $sql_photo = "SELECT photo_path FROM leaders WHERE id = ?";
    if ($stmt_photo = $conn->prepare($sql_photo)) {
        $stmt_photo->bind_param("i", $leader_id);
        $stmt_photo->execute();
        $result_photo = $stmt_photo->get_result();
        if ($row = $result_photo->fetch_assoc()) {
            if ($row['photo_path'] && file_exists('../' . $row['photo_path'])) {
                unlink('../' . $row['photo_path']);
            }
        }
        $stmt_photo->close();
    }
    
    $sql = "DELETE FROM leaders WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $leader_id);
        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['error'] = "Failed to delete from database: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $response['error'] = "Delete SQL prepare failed: " . $conn->error;
    }
} 
elseif ($action === 'add' || $action === 'edit') {
    
    $fields = [
        'full_name', 'age', 'dob', 'gender', 'current_position', 'party_affiliation', 'past_positions', 
        'career_duration', 'constituency', 'declared_assets', 'annual_income', 'businesses_owned', 
        'investments', 'total_police_cases', 'case_types', 'court_case_status', 'num_children', 'qualifications'
    ];
    
    $bind_types = "";
    $bind_values = [];
    $set_params = [];
    $current_photo_path = null;

    if ($action === 'edit' && is_numeric($leader_id)) {
        $sql_path = "SELECT photo_path FROM leaders WHERE id = ?";
        $stmt_path = $conn->prepare($sql_path);
        $stmt_path->bind_param("i", $leader_id);
        $stmt_path->execute();
        $result_path = $stmt_path->get_result();
        $current_photo_path = $result_path->fetch_assoc()['photo_path'] ?? null;
        $stmt_path->close();
    }

    $upload_result = upload_photo($_FILES['photo'] ?? ['error' => UPLOAD_ERR_NO_FILE], $current_photo_path);
    
    if (isset($upload_result['error'])) {
        $response['error'] = "Photo upload error: " . $upload_result['error'];
        echo json_encode($response);
        exit;
    }
    $photo_path = $upload_result['path'];

    foreach ($fields as $field) {
        $value = $_POST[$field] ?? null;
        
        if (in_array($field, ['age', 'career_duration', 'total_police_cases', 'num_children'])) {
            $bind_types .= 'i';
            $bind_values[] = empty($value) ? null : (int)$value;
        } elseif (in_array($field, ['declared_assets', 'annual_income'])) {
            $bind_types .= 's';
            $bind_values[] = empty($value) ? null : 'Rs ' . trim($value);} 
        else {
            $bind_types .= 's';
            $bind_values[] = empty($value) ? null : trim($value);
        }
        $set_params[] = "$field = ?";
    }
    
    $set_params[] = "photo_path = ?";
    $bind_types .= 's';
    $bind_values[] = $photo_path;

    if ($action === 'add') {
        $columns = implode(', ', array_merge($fields, ['photo_path']));
        $placeholders = implode(', ', array_fill(0, count($fields) + 1, '?'));
        $sql = "INSERT INTO leaders ($columns) VALUES ($placeholders)";
        
    } elseif ($action === 'edit' && is_numeric($leader_id)) {
        $sql = "UPDATE leaders SET " . implode(', ', $set_params) . " WHERE id = ?";
        $bind_types .= 'i';
        $bind_values[] = (int)$leader_id;
    }

    if (isset($sql)) {
        if ($stmt = $conn->prepare($sql)) {
            $ref_values = [];
            foreach ($bind_values as $key => $value) {
                $ref_values[$key] = &$bind_values[$key];
            }
            array_unshift($ref_values, $bind_types);
            
            call_user_func_array([$stmt, 'bind_param'], $ref_values);
            
            if ($stmt->execute()) {
                $response['success'] = true;
            } else {
                $response['error'] = "Database execution failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['error'] = "SQL Prepare failed: " . $conn->error;
        }
    }
}

echo json_encode($response);
$conn->close();
?>