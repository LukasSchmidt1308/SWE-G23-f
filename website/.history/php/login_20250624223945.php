<?php
session_start();

// TODO: Replace dummy login check with database authentication.

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$users = [
    'admin' => ['password' => 'admin', 'role' => 'admin'],
];

if (isset($users[$username]) && $users[$username]['password'] === $password) {
    $_SESSION['role'] = $users[$username]['role'];
    $_SESSION['username'] = $username;
    header('Location: ' . $users[$username]['role'] . '.php');
    exit();
} else {
    // Optional: Add error handling (e.g., ?error=1 in URL)
    header('Location: ../index.html?error=1');
    exit();
}
?>