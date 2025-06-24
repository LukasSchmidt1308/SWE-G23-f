<?php
require_once 'php/db.php';

echo "<h2>Warning Debug</h2>";

// Show all warnings in database
echo "<h3>All Warnings in Database:</h3>";
$stmt = $pdo->query("SELECT w.*, u.name as patient_name FROM warnhinweis w JOIN patient p ON w.patientid = p.patientid JOIN benutzer u ON p.benutzerid = u.benutzerid ORDER BY w.zeitstempel DESC");
$warnings = $stmt->fetchAll();

if (empty($warnings)) {
    echo "<p>No warnings found</p>";
} else {
    echo "<table border='1'>";
    echo "<tr><th>Patient</th><th>Parameter</th><th>Wert</th><th>Zeitstempel</th></tr>";
    foreach ($warnings as $w) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($w['patient_name']) . "</td>";
        echo "<td>" . htmlspecialchars($w['parameter']) . "</td>";
        echo "<td>" . htmlspecialchars($w['wert']) . "</td>";
        echo "<td>" . $w['zeitstempel'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Test DELETE operation
if (isset($_GET['clear']) && isset($_GET['patient_id'])) {
    $patientId = (int)$_GET['patient_id'];
    echo "<h3>Clearing warnings for patient ID: $patientId</h3>";
    
    $stmt = $pdo->prepare("DELETE FROM warnhinweis WHERE patientid = ?");
    $result = $stmt->execute([$patientId]);
    $affected = $stmt->rowCount();
    
    echo "<p>Delete result: " . ($result ? 'SUCCESS' : 'FAILED') . "</p>";
    echo "<p>Rows affected: $affected</p>";
    
    echo "<p><a href='debug_warnings.php'>Refresh to see result</a></p>";
}

// Show patients for testing
echo "<h3>Available Patients:</h3>";
$stmt = $pdo->query("SELECT p.patientid, u.name FROM patient p JOIN benutzer u ON p.benutzerid = u.benutzerid");
$patients = $stmt->fetchAll();
foreach ($patients as $p) {
    echo "<p>ID: {$p['patientid']}, Name: {$p['name']} ";
    echo "<a href='?clear=1&patient_id={$p['patientid']}'>[Clear Warnings]</a></p>";
}
?>
