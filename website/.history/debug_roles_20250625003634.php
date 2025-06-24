<?php
require_once 'php/db.php';

echo "<h2>Database Role Values Debug:</h2>";
try {
    $stmt = $pdo->query("SELECT benutzername, rolle, LENGTH(rolle) as role_length FROM benutzer ORDER BY benutzername");
    $users = $stmt->fetchAll();
    
    echo "<table border='1'>";
    echo "<tr><th>Username</th><th>Role</th><th>Role Length</th><th>Role Bytes</th></tr>";
    foreach ($users as $user) {
        $roleBytes = '';
        for ($i = 0; $i < strlen($user['rolle']); $i++) {
            $roleBytes .= ord($user['rolle'][$i]) . ' ';
        }
        echo "<tr>";
        echo "<td>{$user['benutzername']}</td>";
        echo "<td>'" . htmlspecialchars($user['rolle']) . "'</td>";
        echo "<td>{$user['role_length']}</td>";
        echo "<td>{$roleBytes}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>

<p><a href="index.php">Back to Login</a></p>
