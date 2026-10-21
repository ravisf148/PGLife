<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require "../includes/database_connect.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "is_logged_in" => false, "message" => "Please login to continue!"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$property_id = isset($_REQUEST['property_id']) ? intval($_REQUEST['property_id']) : 0;

if ($property_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid property ID!"]);
    exit;
}

// Check if already interested
$sql_check = "SELECT id FROM interested_users_properties WHERE user_id = ? AND property_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $user_id, $property_id);
$stmt_check->execute();
$res_check = $stmt_check->get_result();

if ($res_check->num_rows > 0) {
    // Remove interested
    $stmt_check->close();
    $sql_del = "DELETE FROM interested_users_properties WHERE user_id = ? AND property_id = ?";
    $stmt_del = $conn->prepare($sql_del);
    $stmt_del->bind_param("ii", $user_id, $property_id);
    if ($stmt_del->execute()) {
        echo json_encode(["success" => true, "is_interested" => false, "property_id" => $property_id]);
    } else {
        echo json_encode(["success" => false, "message" => "Could not update interest status."]);
    }
    $stmt_del->close();
} else {
    // Add interested
    $stmt_check->close();
    $sql_ins = "INSERT INTO interested_users_properties (user_id, property_id) VALUES (?, ?)";
    $stmt_ins = $conn->prepare($sql_ins);
    $stmt_ins->bind_param("ii", $user_id, $property_id);
    if ($stmt_ins->execute()) {
        echo json_encode(["success" => true, "is_interested" => true, "property_id" => $property_id]);
    } else {
        echo json_encode(["success" => false, "message" => "Could not update interest status."]);
    }
    $stmt_ins->close();
}

$conn->close();
?>
