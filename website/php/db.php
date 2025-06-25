<?php
// PflegePro - University Praktikum Project
// Database connection using PDO and PostgreSQL

// Database connection parameters
$dbHost = "localhost";
$dbUser = "pflegepro_user";  
$dbPass = "secure_password123";
$dbName = "pflegepro";
$dbPort = "5432";

try {
    $pdo = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Database connection failed - set to null for setup_check to handle
    $pdo = null;
}
?>