<?php
session_start();
// Debug: Log session data
error_log("Patient.php - Session role: '" . ($_SESSION['role'] ?? 'NOT SET') . "'");
error_log("Patient.php - Full session: " . print_r($_SESSION, true));

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    error_log("Patient.php - Role check failed, redirecting to index.html");
    header('Location: ../index.html');
    exit();
}

// TODO: Replace dummy data with database queries.
// Dummy patient data for now; replace with database query later
$current = [
    'name' => 'Max Mustermann',
    'adresse' => 'Musterstraße 1<br>12345 Musterstadt',
    'geburtsdatum' => '01.01.1970',
    'kontakt' => 'max.mustermann@example.com<br>01234 567890',
    'blutdruck' => 120,
    'temperatur' => 36.8,
    'blutzucker' => 100,
    'warnhinweise' => ['Keine aktuellen Warnungen']
];
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