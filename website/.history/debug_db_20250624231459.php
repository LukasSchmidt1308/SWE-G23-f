<?php
require_once 'php/db.php';

echo "<h2>Database Debug Information</h2>";

try {
    // Check table structure
    echo "<h3>Benutzer Table Structure:</h3>";
    $stmt = $pdo->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'benutzer' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1'>";
    echo "<tr><th>Column Name</th><th>Data Type</th><th>Nullable</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>{$col['column_name']}</td><td>{$col['data_type']}</td><td>{$col['is_nullable']}</td></tr>";
    }
    echo "</table>";
    
    // Check existing users
    echo "<h3>Existing Users:</h3>";
    $stmt = $pdo->query("SELECT BenutzerID, Name, Rolle, Benutzername, LENGTH(Passwort) as password_length FROM Benutzer");
    $users = $stmt->fetchAll();
    
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Role</th><th>Username</th><th>Password Length</th></tr>";
    foreach ($users as $user) {
        echo "<tr><td>{$user['benutzerid']}</td><td>{$user['name']}</td><td>{$user['rolle']}</td><td>{$user['benutzername']}</td><td>{$user['password_length']}</td></tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
