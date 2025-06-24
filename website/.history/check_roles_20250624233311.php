<?php
require_once 'php/db.php';

echo "<h2>Checking actual role values in database:</h2>";
try {
    $stmt = $pdo->query("SELECT benutzername, rolle FROM benutzer");
    $users = $stmt->fetchAll();
    
    echo "<ul>";
    foreach ($users as $user) {
        echo "<li><strong>{$user['benutzername']}</strong>: role = '{$user['rolle']}'</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
