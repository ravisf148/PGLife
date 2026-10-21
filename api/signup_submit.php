<?php
require "../includes/database_connect.php";

header('Content-Type: application/json');

$full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$college_name = isset($_POST['college_name']) ? trim($_POST['college_name']) : '';
$gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';

if (empty($full_name) || empty($phone) || empty($email) || empty($password) || empty($college_name) || empty($gender)) {
    echo json_encode(["success" => false, "message" => "Please fill all required fields!"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Please enter a valid email address!"]);
    exit;
}

if (!preg_match('/^[0-9]{10}$/', $phone)) {
    echo json_encode(["success" => false, "message" => "Please enter a valid 10-digit phone number!"]);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(["success" => false, "message" => "Password must be at least 6 characters long!"]);
    exit;
}

// Check if email already registered
$sql_check = "SELECT id FROM users WHERE email = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $email);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "This email is already registered with us!"]);
    exit;
}
$stmt_check->close();

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$sql_insert = "INSERT INTO users (email, password, full_name, phone, gender, college_name) VALUES (?, ?, ?, ?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("ssssss", $email, $hashed_password, $full_name, $phone, $gender, $college_name);

if ($stmt_insert->execute()) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['user_id'] = $stmt_insert->insert_id;
    $_SESSION['full_name'] = $full_name;
    $_SESSION['email'] = $email;

    echo json_encode(["success" => true, "message" => "Your account has been registered successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Something went wrong! Error: " . $conn->error]);
}

$stmt_insert->close();
$conn->close();
?>
