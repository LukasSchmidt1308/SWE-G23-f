<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'betreuer') {
    header('Location: ../index.php');
    exit();
}

require_once 'db.php';

// Get current betreuer ID
$stmt = $pdo->prepare("SELECT betreuerid FROM betreuer WHERE benutzerid = ?");
$stmt->execute([$_SESSION['user_id']]);
$betreuer = $stmt->fetch();

if (!$betreuer) {
    header('Location: ../index.php');
    exit();
}

$betreuerID = $betreuer['betreuerid'];
$page = $_GET['page'] ?? 'overview';
$patientID = $_GET['patient'] ?? null;

// Handle health parameter updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_health') {
        $patientId = (int)$_POST['patient_id'];
        $blutdruck = $_POST['blutdruck'];
        $temperatur = (float)$_POST['temperatur'];
        $blutzucker = (float)$_POST['blutzucker'];
        
        // Insert new health parameters
        $stmt = $pdo->prepare("INSERT INTO gesundheitsparameter (patientid, datum, blutdruck, temperatur, blutzucker) VALUES (?, NOW(), ?, ?, ?)");
        $stmt->execute([$patientId, $blutdruck, $temperatur, $blutzucker]);
        
        // Check for warnings
        $warnings = [];
        if ($temperatur > 40 || $temperatur < 35) {
            if ($temperatur > 40) {
                $warnings[] = "Hohe Temperatur: {$temperatur}°C";
            } else {
                $warnings[] = "Niedrige Temperatur: {$temperatur}°C";
            }
        }
        if ($temperatur < 36.6) {
            $warnings[] = "Niedrige Temperatur: {$temperatur}°C";
        }
         // Check for both high and low blood pressure
        if ($blutdruck > 180 || $blutdruck < 90 ) {
                $warnings[] = "Kritischer Blutdruck: {$blutdruck}";
        }
        if ($blutzucker > 180 || $blutzucker < 70) {
            $warnings[] = "Kritischer Blutzucker: {$blutzucker} mg/dl";
        }
        
        // Clear all existing warnings for this patient
        $stmt = $pdo->prepare("DELETE FROM warnhinweis WHERE patientid = ?");
        $stmt->execute([$patientId]);
        
        // Insert new warnings
        foreach ($warnings as $warning) {
            $stmt = $pdo->prepare("INSERT INTO warnhinweis (patientid, parameter, wert) VALUES (?, ?, ?)");
            $stmt->execute([$patientId, 'Gesundheitscheck', $warning]);
        }
        
        header("Location: betreuer.php?page=overview&patient={$patientId}");
        exit();
    }
}

// Load assigned patients
$stmt = $pdo->prepare("
    SELECT p.patientid, u.name, p.adresse, p.geburtsdatum, u.kontaktdaten, p.pflegeart
    FROM patient p 
    JOIN benutzer u ON p.benutzerid = u.benutzerid 
    WHERE p.betreuerid = ?
    ORDER BY u.name
");
$stmt->execute([$betreuerID]);
$patients = $stmt->fetchAll();

// Auto-select first patient if none selected
if (!$patientID && !empty($patients)) {
    $patientID = $patients[0]['patientid'];
}

// Load specific patient data for the selected/auto-selected patient
$currentPatient = null;
$healthData = [];
$warnings = [];

if ($patientID) {
    // Verify patient belongs to this betreuer and load data
    $stmt = $pdo->prepare("
        SELECT p.patientid, u.name, p.adresse, p.geburtsdatum, u.kontaktdaten, p.pflegeart
        FROM patient p 
        JOIN benutzer u ON p.benutzerid = u.benutzerid 
        WHERE p.patientid = ? AND p.betreuerid = ?
    ");
    $stmt->execute([$patientID, $betreuerID]);
    $currentPatient = $stmt->fetch();
    
    if ($currentPatient) {
        // Load latest health parameters
        $stmt = $pdo->prepare("
            SELECT blutdruck, temperatur, blutzucker, datum 
            FROM gesundheitsparameter 
            WHERE patientid = ? 
            ORDER BY datum DESC 
            LIMIT 1
        ");
        $stmt->execute([$patientID]);
        $healthData = $stmt->fetch();
        
        // Load recent warnings
        $stmt = $pdo->prepare("
            SELECT parameter, wert, zeitstempel 
            FROM warnhinweis 
            WHERE patientid = ? 
            ORDER BY zeitstempel DESC 
            LIMIT 5
        ");
        $stmt->execute([$patientID]);
        $warnings = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>PflegePro – Betreuer</title>
    <link rel="stylesheet" href="../style.css">
    <script>
        function checkWarningsAndPrompt() {
            <?php if (!empty($warnings)): ?>
                alert("Warnungen erkannt! E-Mail wird automatisch an den Arzt gesendet.");
            <?php endif; ?>
        }
        
        // Check for warnings when page loads and is overview page
        window.onload = function() {
            <?php if ($page === 'overview' && !empty($warnings)): ?>
                checkWarningsAndPrompt();
            <?php endif; ?>
        }
    </script>
</head>
<body>
    <div class="navbar">
        PflegePro – Betreuer
        <a href="logout.php" style="float:right; color:#fff; text-decoration:none; margin-right:20px;">Logout</a>
    </div>
    <div class="subnav">
        <a href="?page=overview&patient=<?= $patientID ?>" class="<?= $page === 'overview' ? 'active' : '' ?>" onclick="checkWarningsAndPrompt()">Übersicht</a>
        <?php if ($patientID): ?>
        <a href="?page=warn&patient=<?= $patientID ?>" class="<?= $page === 'warn' ? 'active' : '' ?>">Gesundheitsparameter setzen</a>
        <?php endif; ?>
        <?php if (!empty($patients)): ?>
        <form style="display:inline;" method="get">
            <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
            <select name="patient" onchange="this.form.submit()">
                <?php foreach ($patients as $patient): ?>
                    <option value="<?= $patient['patientid'] ?>" <?= $patientID == $patient['patientid'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($patient['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <?php endif; ?>
    </div>
    <div class="container">
        <div class="right" style="margin:auto;">
            <?php if ($page === 'overview' && $currentPatient): ?>
                <div class="patient-card">
                    <div class="patient-header">
                        <h3><?= htmlspecialchars($currentPatient['name']) ?></h3>
                    </div>
                    <div class="patient-info-row">
                        <div>
                            <span class="label">Adresse:</span>
                            <span><?= nl2br(htmlspecialchars($currentPatient['adresse'])) ?></span>
                        </div>
                        <div>
                            <span class="label">Geburtsdatum:</span>
                            <span><?= date('d.m.Y', strtotime($currentPatient['geburtsdatum'])) ?></span>
                        </div>
                        <div>
                            <span class="label">Kontakt:</span>
                            <span><?= nl2br(htmlspecialchars($currentPatient['kontaktdaten'])) ?></span>
                        </div>
                    </div>
                    <div class="patient-section-title">Gesundheitsparameter</div>
                    <?php if ($healthData): ?>
                        <div class="patient-params-row">
                            <div>
                                <span class="label">Blutdruck:</span>
                                <span><?= htmlspecialchars($healthData['blutdruck']) ?></span>
                            </div>
                            <div>
                                <span class="label">Temperatur:</span>
                                <span><?= $healthData['temperatur'] ?>°C</span>
                            </div>
                            <div>
                                <span class="label">Blutzucker:</span>
                                <span><?= $healthData['blutzucker'] ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="patient-params-row">
                            <div>
                                <span class="label">Blutdruck:</span>
                                <span>Noch nicht gemessen</span>
                            </div>
                            <div>
                                <span class="label">Temperatur:</span>
                                <span>Noch nicht gemessen</span>
                            </div>
                            <div>
                                <span class="label">Blutzucker:</span>
                                <span>Noch nicht gemessen</span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="warn-section">
                        <div class="patient-section-title">Warnhinweise</div>
                        <?php if (empty($warnings)): ?>
                            <ul><li>Keine aktuellen Warnungen</li></ul>
                        <?php else: ?>
                            <ul>
                            <?php foreach ($warnings as $warning): ?>
                                <li><?= htmlspecialchars($warning['wert']) ?></li>
                            <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($page === 'warn' && $currentPatient): ?>
                <div class="patient-card">
                    <div class="patient-header">
                        <h3>Gesundheitsparameter setzen für <?= htmlspecialchars($currentPatient['name']) ?></h3>
                    </div>
                    <form method="post">
                        <input type="hidden" name="action" value="update_health">
                        <input type="hidden" name="patient_id" value="<?= $currentPatient['patientid'] ?>">
                        <div class="patient-params-row">
                            <div>
                                <label class="label" for="blutdruck">Blutdruck (mmHg):</label>
                                <input type="text" id="blutdruck" name="blutdruck" class="input-field" value="<?= $healthData ? htmlspecialchars($healthData['blutdruck']) : '' ?>">
                            </div>
                            <div>
                                <label class="label" for="temperatur">Temperatur (°C):</label>
                                <input type="number" id="temperatur" name="temperatur" step="0.1" class="input-field" value="<?= $healthData ? $healthData['temperatur'] : '' ?>">
                            </div>
                            <div>
                                <label class="label" for="blutzucker">Blutzucker:</label>
                                <input type="number" id="blutzucker" name="blutzucker" class="input-field" value="<?= $healthData ? $healthData['blutzucker'] : '' ?>">
                            </div>
                        </div>
                        <button type="submit" class="submit-btn">Speichern und E-Mail senden</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="patient-card">
                    <div class="patient-header">
                        <h3>Keine Patienten zugewiesen</h3>
                    </div>
                    <p>Sie haben derzeit keine zugewiesenen Patienten.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
