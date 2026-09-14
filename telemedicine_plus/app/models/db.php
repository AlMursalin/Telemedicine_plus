<?php
session_start();

$host = 'localhost';
$dbname = 'telemedicine_plus';
$username = 'root';
$password = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

function require_login($allowed_roles = []) {
    if (!isset($_SESSION['user'])) {
        header("Location: login.php?msg=Please Log In");
        exit;
    }
    if (!empty($allowed_roles) && !in_array($_SESSION['user']['role'], $allowed_roles)) {
        die("<div class='alert'>Access Denied. Invalid Role.</div>");
    }
}
?>