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
    $stmt = $pdo->prepare("SELECT BenutzerID, Name, Rolle, Passwort FROM Benutzer WHERE Benutzername = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    // Debug: Check if user exists and what columns are available
    if (!$user) {
        header('Location: ../index.php?error=invalid');
        exit();
    }
    
    // Check if password column exists and is not null
    if (!isset($user['Passwort']) || $user['Passwort'] === null) {
        error_log("Password column missing or null for user: " . $username);
        header('Location: ../index.php?error=db');
        exit();
    }
    
    if (password_verify($password, $user['Passwort'])) {
        // Login successful
        $_SESSION['user_id'] = $user['BenutzerID'];
        $_SESSION['role'] = $user['Rolle'];
        $_SESSION['username'] = $username;
        $_SESSION['name'] = $user['Name'];
        
        // Redirect based on role
        switch ($user['Rolle']) {
            case 'admin':
                header('Location: admin.php');
                break;
            case 'Betreuer':
                header('Location: betreuer.php');
                break;
            case 'Patient':
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