<?php
require_once 'php/db.php';

echo "<h2>Test Users:</h2>";
try {
    $stmt = $pdo->query("SELECT benutzerid, name, rolle, benutzername FROM benutzer");
    $users = $stmt->fetchAll();
    
    echo "<ul>";
    foreach ($users as $user) {
        echo "<li><strong>{$user['benutzername']}</strong> ({$user['rolle']}) - {$user['name']}</li>";
    }
    echo "</ul>";
    
    echo "<p>Test these credentials:</p>";
    echo "<ul>";
    echo "<li>admin / admin123</li>";
    echo "<li>patient / patient123</li>"; 
    echo "<li>betreuer / betreuer123</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>

<p><a href="index.php">Back to Login</a></p>
