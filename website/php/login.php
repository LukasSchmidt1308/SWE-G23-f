<?php
// PflegePro - Login Handler
// Handles user authentication and session management

session_start();
require_once 'db.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Debug: Log login attempts
error_log("Login attempt: username='$username', password length=" . strlen($password));

if (empty($username) || empty($password)) {
    error_log("Login failed: empty fields");
    header('Location: ../index.php?error=empty');
    exit();
}

try {
    // Get user from database
    $stmt = $pdo->prepare("SELECT benutzerid, name, rolle, passwort FROM benutzer WHERE benutzername = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($password, $user['passwort'])) {
        error_log("Login failed: invalid credentials for user '$username'");
        header('Location: ../index.php?error=invalid');
        exit();
    }
    
    error_log("Login successful for user '$username' with role '" . $user['rolle'] . "'");
    
    // Login successful - set session
    $_SESSION['user_id'] = $user['benutzerid'];
    $_SESSION['role'] = strtolower($user['rolle']);
    $_SESSION['username'] = $username;
    $_SESSION['name'] = $user['name'];
    
    // Redirect based on role
    switch ($_SESSION['role']) {
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
    
} catch (PDOException $e) {
    header('Location: ../index.php?error=db');
    exit();
}
?>