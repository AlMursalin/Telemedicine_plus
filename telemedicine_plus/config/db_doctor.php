<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$dbname = 'doctor_db';
$username = 'root';
$password = '';

try {
    $db_doctor = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Doctor Database Connection Failed: " . $e->getMessage());
}
?>