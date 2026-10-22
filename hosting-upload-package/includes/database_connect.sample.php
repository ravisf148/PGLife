<?php
// InfinityFree MySQL Database Connection Template
// Duplicate or rename this file to database_connect.php and fill in your InfinityFree MySQL credentials.
$host = "sql109.infinityfree.com"; // Your MySQL Hostname from InfinityFree Control Panel
$username = "your_username";       // e.g. if0_xxxxxxxx
$password = "your_password";       // Your InfinityFree account password
$database = "your_database";       // e.g. if0_xxxxxxxx_pglife

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
