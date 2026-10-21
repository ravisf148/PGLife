<?php
require "../includes/database_connect.php";

header('Content-Type: application/json');

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Please enter both email and password!"]);
    exit;
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

        echo json_encode(["success" => true, "message" => "Login successful!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid email or password!"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid email or password!"]);
}

$stmt->close();
$conn->close();
?>
