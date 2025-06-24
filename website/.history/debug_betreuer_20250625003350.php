<?php
require_once 'php/db.php';

echo "<h2>Debug: Automatic Betreuer Assignment</h2>";

// Check available betreuer
echo "<h3>Available Betreuer:</h3>";
$stmt = $pdo->query("SELECT b.betreuerid, u.name, b.stationid, s.name as station_name, b.maxpatienten 
                     FROM betreuer b 
                     JOIN benutzer u ON b.benutzerid = u.benutzerid 
                     LEFT JOIN station s ON b.stationid = s.stationid");
$betreuer = $stmt->fetchAll();
foreach ($betreuer as $b) {
    echo "<p>ID: {$b['betreuerid']}, Name: {$b['name']}, Station: {$b['station_name']}, Max: {$b['maxpatienten']}</p>";
}

// Check current patient assignments
echo "<h3>Current Patient Assignments:</h3>";
$stmt = $pdo->query("SELECT b.betreuerid, u.name as betreuer_name, COUNT(p.patientid) as patient_count, b.maxpatienten
                     FROM betreuer b 
                     JOIN benutzer u ON b.benutzerid = u.benutzerid
                     LEFT JOIN patient p ON b.betreuerid = p.betreuerid
                     GROUP BY b.betreuerid, u.name, b.maxpatienten");
$assignments = $stmt->fetchAll();
foreach ($assignments as $a) {
    echo "<p>Betreuer: {$a['betreuer_name']}, Current Patients: {$a['patient_count']}, Max: {$a['maxpatienten']}</p>";
}

// Test assignment logic for different scenarios
echo "<h3>Testing Assignment Logic:</h3>";

// Test 1: For station 1
echo "<h4>Test 1: For Station 1</h4>";
$stationID = 1;
$stmt = $pdo->prepare("
    SELECT b.betreuerid, COUNT(p.patientid) as patient_count, b.maxpatienten
    FROM betreuer b 
    LEFT JOIN patient p ON b.betreuerid = p.betreuerid
    WHERE b.stationid = ?
    GROUP BY b.betreuerid, b.maxpatienten
    HAVING COUNT(p.patientid) < b.maxpatienten
    ORDER BY patient_count ASC
    LIMIT 1
");
$stmt->execute([$stationID]);
$result = $stmt->fetch();
if ($result) {
    echo "<p>✓ Found available betreuer: ID {$result['betreuerid']}, Current: {$result['patient_count']}, Max: {$result['maxpatienten']}</p>";
} else {
    echo "<p>✗ No available betreuer found for station 1</p>";
}

// Test 2: For any station (häusliche Pflege)
echo "<h4>Test 2: For Häusliche Pflege (any available betreuer)</h4>";
$stmt = $pdo->prepare("
    SELECT b.betreuerid, COUNT(p.patientid) as patient_count, b.maxpatienten
    FROM betreuer b 
    LEFT JOIN patient p ON b.betreuerid = p.betreuerid
    GROUP BY b.betreuerid, b.maxpatienten
    HAVING COUNT(p.patientid) < b.maxpatienten
    ORDER BY patient_count ASC
    LIMIT 1
");
$stmt->execute();
$result = $stmt->fetch();
if ($result) {
    echo "<p>✓ Found available betreuer: ID {$result['betreuerid']}, Current: {$result['patient_count']}, Max: {$result['maxpatienten']}</p>";
} else {
    echo "<p>✗ No available betreuer found</p>";
}

// Show all stations
echo "<h3>Available Stations:</h3>";
$stmt = $pdo->query("SELECT stationid, name FROM station");
$stations = $stmt->fetchAll();
foreach ($stations as $s) {
    echo "<p>ID: {$s['stationid']}, Name: {$s['name']}</p>";
}
?>
