<?php
require "../includes/database_connect.php";

function send_login_response($success, $message, $conn = null, $stmt = null) {
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

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($email) || empty($password)) {
    send_login_response(false, "Please enter both email and password!", $conn);
}

$sql = "SELECT id, full_name, email, password FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $verified = false;

    if (password_verify($password, $row['password'])) {
        $verified = true;
    } elseif (sha1($password) === $row['password']) {
        // Support legacy hash and seamlessly upgrade to bcrypt password_hash
        $verified = true;
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $new_hash, $row['id']);
        $update_stmt->execute();
        $update_stmt->close();
    }

    if ($verified) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['full_name'] = $row['full_name'];
        $_SESSION['email'] = $row['email'];

        send_login_response(true, "Login successful!", $conn, $stmt);
    } else {
        send_login_response(false, "Invalid email or password!", $conn, $stmt);
    }
} else {
    send_login_response(false, "Invalid email or password!", $conn, $stmt);
}
?>
