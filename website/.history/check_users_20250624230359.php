<?php
require 'php/db.php';
try {
    $result = $pdo->query('SELECT COUNT(*) FROM benutzer');
    echo 'Users in database: ' . $result->fetchColumn() . "\n";
    
    $result = $pdo->query('SELECT benutzername, rolle FROM benutzer');
    echo "Existing users:\n";
    while ($row = $result->fetch()) {
        echo "- " . $row['benutzername'] . " (" . $row['rolle'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
