<?php
session_start();
require_once 'db.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header('Location: ../index.php?error=empty');
    exit();
}

try {
    // Get user from database
    $stmt = $pdo->prepare("SELECT benutzerid, name, rolle, passwort FROM benutzer WHERE benutzername = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    // Debug: Check if user exists and what columns are available
    if (!$user) {
        header('Location: ../index.php?error=invalid');
        exit();
    }
    
    // Check if password column exists and is not null (PostgreSQL converts to lowercase)
    if (!isset($user['passwort']) || $user['passwort'] === null) {
        error_log("Password column missing or null for user: " . $username);
        header('Location: ../index.php?error=db');
        exit();
    }
    
    if (password_verify($password, $user['passwort'])) {
        // Login successful
        $_SESSION['user_id'] = $user['benutzerid'];
        $_SESSION['role'] = strtolower($user['rolle']); // Normalize role to lowercase
        $_SESSION['username'] = $username;
        $_SESSION['name'] = $user['name'];
        
        // Redirect based on role (case-insensitive)
        $role = strtolower($user['rolle']);
        switch ($role) {
            case 'admin':
                header('Location: admin.php');
                break;
            case 'betreuer':
                header('Location: betreuer.php');
                break;
            case 'patient':
                header('Location: patient.php');
                break;
            default:
                header('Location: ../index.php?error=role');
        }
        exit();
        
    } else {
        // Login failed
        header('Location: ../index.php?error=invalid');
        exit();
    }
    
} catch (PDOException $e) {
    // Database error
    header('Location: ../index.php?error=db');
    exit();
}
?>