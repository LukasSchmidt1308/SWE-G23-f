<?php
require_once 'php/db.php';

echo "<h1>Quick Database Check</h1>";

try {
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT * FROM Benutzer WHERE Rolle = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p>✓ Admin user found: " . htmlspecialchars($admin['benutzername']) . "</p>";
    } else {
        echo "<p>❌ No admin user found!</p>";
        
        // Create admin user
        echo "<p>Creating admin user...</p>";
        $password = password_hash('admin', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO Benutzer (Name, Rolle, Benutzername, Passwort, Kontaktdaten) VALUES ('Administrator', 'admin', 'admin', ?, 'admin@pflegepro.de')");
        $stmt->execute([$password]);
        echo "<p>✓ Admin user created!</p>";
    }
    
    // Check tables
    $tables = ['benutzer', 'station', 'betreuer', 'patient'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "<p>Table '$table': $count records</p>";
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='index.php'>Go to Login</a></p>";
?>
