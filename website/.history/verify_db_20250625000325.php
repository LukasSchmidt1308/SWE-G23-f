<?php
require_once 'php/db.php';

echo "<h2>Database Verification</h2>";

// Check users
echo "<h3>Users:</h3>";
$stmt = $pdo->query("SELECT name, rolle, benutzername FROM benutzer ORDER BY rolle, name");
$users = $stmt->fetchAll();
foreach ($users as $user) {
    echo "- {$user['name']} ({$user['rolle']}) - Username: {$user['benutzername']}<br>";
}

// Check stations
echo "<h3>Stations:</h3>";
$stmt = $pdo->query("SELECT name FROM station ORDER BY name");
$stations = $stmt->fetchAll();
foreach ($stations as $station) {
    echo "- {$station['name']}<br>";
}

// Check betreuer
echo "<h3>Betreuer:</h3>";
$stmt = $pdo->query("
    SELECT u.name, s.name as station, b.maxpatienten, 
           COUNT(p.patientid) as current_patients
    FROM betreuer b
    JOIN benutzer u ON b.benutzerid = u.benutzerid
    JOIN station s ON b.stationid = s.stationid
    LEFT JOIN patient p ON b.betreuerid = p.betreuerid
    GROUP BY b.betreuerid, u.name, s.name, b.maxpatienten
    ORDER BY u.name
");
$betreuer = $stmt->fetchAll();
foreach ($betreuer as $b) {
    echo "- {$b['name']} (Station: {$b['station']}) - {$b['current_patients']}/{$b['maxpatienten']} Patienten<br>";
}

// Check patients
echo "<h3>Patients:</h3>";
$stmt = $pdo->query("
    SELECT u.name, p.pflegeart, s.name as station, bu.name as betreuer
    FROM patient p
    JOIN benutzer u ON p.benutzerid = u.benutzerid
    LEFT JOIN station s ON p.stationid = s.stationid
    LEFT JOIN betreuer b ON p.betreuerid = b.betreuerid
    LEFT JOIN benutzer bu ON b.benutzerid = bu.benutzerid
    ORDER BY u.name
");
$patients = $stmt->fetchAll();
foreach ($patients as $patient) {
    $station = $patient['station'] ?? 'Häusliche Pflege';
    $betreuer = $patient['betreuer'] ?? 'Nicht zugewiesen';
    echo "- {$patient['name']} ({$patient['pflegeart']}) - Station: {$station}, Betreuer: {$betreuer}<br>";
}
?>
