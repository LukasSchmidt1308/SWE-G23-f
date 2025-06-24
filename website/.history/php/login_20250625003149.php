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
    
    if (!$user || !password_verify($password, $user['passwort'])) {
        header('Location: ../index.php?error=invalid');
        exit();
    }
    
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