<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../index.html');
    exit();
}

require_once 'db.php';

// Get current patient data
$stmt = $pdo->prepare("
    SELECT p.patientid, u.name, p.adresse, p.geburtsdatum, u.kontaktdaten, p.pflegeart, 
           bu.name as betreuer_name, s.name as station_name
    FROM patient p 
    JOIN benutzer u ON p.benutzerid = u.benutzerid 
    LEFT JOIN betreuer b ON p.betreuerid = b.betreuerid
    LEFT JOIN benutzer bu ON b.benutzerid = bu.benutzerid
    LEFT JOIN station s ON p.stationid = s.stationid
    WHERE u.benutzerid = ?
");
$stmt->execute([$_SESSION['user_id']]);
$patient = $stmt->fetch();

if (!$patient) {
    header('Location: ../index.html');
    exit();
}

// Load latest health parameters
$stmt = $pdo->prepare("
    SELECT blutdruck, temperatur, blutzucker, datum 
    FROM gesundheitsparameter 
    WHERE patientid = ? 
    ORDER BY datum DESC 
    LIMIT 1
");
$stmt->execute([$patient['patientid']]);
$healthData = $stmt->fetch();

// Load recent warnings
$stmt = $pdo->prepare("
    SELECT parameter, wert, zeitstempel 
    FROM warnhinweis 
    WHERE patientid = ? 
    ORDER BY zeitstempel DESC 
    LIMIT 5
");
$stmt->execute([$patient['patientid']]);
$warnings = $stmt->fetchAll();

// Set default values if no health data exists
if (!$healthData) {
    $healthData = [
        'blutdruck' => 'Noch nicht gemessen',
        'temperatur' => 0,
        'blutzucker' => 0,
        'datum' => null
    ];
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>PflegePro – Patient</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="navbar">
        PflegePro – Patient
        <a href="logout.php" style="float:right; color:#fff; text-decoration:none; margin-right:20px;">Logout</a>
    </div>
    <div class="subnav">
        <a href="#" class="active">Übersicht</a>
    </div>
    <div class="container">
        <div class="right" style="margin:auto;">
            <div class="patient-card">
                <div class="patient-header">
                    <h3><?php echo $current['name']; ?></h3>
                </div>
                <div class="patient-info-row">
                    <div>
                        <span class="label">Adresse:</span>
                        <span><?php echo $current['adresse']; ?></span>
                    </div>
                    <div>
                        <span class="label">Geburtsdatum:</span>
                        <span><?php echo $current['geburtsdatum']; ?></span>
                    </div>
                    <div>
                        <span class="label">Kontakt:</span>
                        <span><?php echo $current['kontakt']; ?></span>
                    </div>
                </div>
                <div class="patient-section-title">Gesundheitsparameter</div>
                <div class="patient-params-row">
                    <div>
                        <span class="label">Blutdruck:</span>
                        <span><?php echo $current['blutdruck']; ?></span>
                    </div>
                    <div>
                        <span class="label">Temperatur:</span>
                        <span><?php echo $current['temperatur']; ?>°C</span>
                    </div>
                    <div>
                        <span class="label">Blutzucker:</span>
                        <span><?php echo $current['blutzucker']; ?></span>
                    </div>
                </div>
                <div class="warn-section">
                    <div class="patient-section-title">Warnhinweise</div>
                    <ul>
                        <?php foreach ($current['warnhinweise'] as $warn): ?>
                            <li><?php echo $warn; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>