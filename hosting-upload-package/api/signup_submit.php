<?php
require "../includes/database_connect.php";

function send_signup_response($success, $message, $conn = null, $stmt = null) {
    if ($stmt) {
        $stmt->close();
    }
    if ($conn) {
        $conn->close();
    }

    $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(["success" => $success, "message" => $message]);
        exit;
    }

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $success ? 'success' : 'danger';

    $redirect_url = '../index.php';
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $referer_host = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
        $current_host = !empty($_SERVER['HTTP_HOST']) ? explode(':', $_SERVER['HTTP_HOST'])[0] : '';
        if ($referer_host === null || $referer_host === $current_host) {
            $redirect_url = $_SERVER['HTTP_REFERER'];
        }
    }
    header("Location: " . $redirect_url);
    exit;
}

$full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$college_name = isset($_POST['college_name']) ? trim($_POST['college_name']) : '';
$gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';

if (empty($full_name) || empty($phone) || empty($email) || empty($password) || empty($college_name) || empty($gender)) {
    send_signup_response(false, "Please fill all required fields!", $conn);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_signup_response(false, "Please enter a valid email address!", $conn);
}

if (!preg_match('/^[0-9]{10}$/', $phone)) {
    send_signup_response(false, "Please enter a valid 10-digit phone number!", $conn);
}

if (strlen($password) < 6) {
    send_signup_response(false, "Password must be at least 6 characters long!", $conn);
}

// Check if email already registered
$sql_check = "SELECT id FROM users WHERE email = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $email);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    send_signup_response(false, "This email is already registered with us!", $conn, $stmt_check);
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

    send_signup_response(true, "Your account has been registered successfully!", $conn, $stmt_insert);
} else {
    send_signup_response(false, "Something went wrong! Error: " . $conn->error, $conn, $stmt_insert);
}
?>
