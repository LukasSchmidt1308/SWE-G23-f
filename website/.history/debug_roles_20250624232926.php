<?php
require_once 'php/db.php';

echo "<h2>Current Users and Roles:</h2>";
try {
    $stmt = $pdo->query("SELECT benutzerid, name, rolle, benutzername FROM benutzer");
    $users = $stmt->fetchAll();
    
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Username</th><th>Role (raw)</th><th>Role (quoted)</th><th>Name</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['benutzerid']}</td>";
        echo "<td>{$user['benutzername']}</td>";
        echo "<td>{$user['rolle']}</td>";
        echo "<td>'" . $user['rolle'] . "'</td>";
        echo "<td>{$user['name']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>

<p><a href="index.php">Back to Login</a></p>
