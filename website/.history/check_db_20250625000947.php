<?php
require_once 'php/db.php';

echo "<h2>Database Check</h2>";

// Check stations
echo "<h3>Stations:</h3>";
$stmt = $pdo->query("SELECT stationid, name FROM station");
$stations = $stmt->fetchAll();
if (empty($stations)) {
    echo "No stations found!<br>";
} else {
    foreach ($stations as $station) {
        echo "ID: {$station['stationid']}, Name: {$station['name']}<br>";
    }
}

// Check users
echo "<h3>Users:</h3>";
$stmt = $pdo->query("SELECT benutzerid, name, rolle, benutzername FROM benutzer");
$users = $stmt->fetchAll();
foreach ($users as $user) {
    echo "ID: {$user['benutzerid']}, Name: {$user['name']}, Role: {$user['rolle']}, Username: {$user['benutzername']}<br>";
}
?>
