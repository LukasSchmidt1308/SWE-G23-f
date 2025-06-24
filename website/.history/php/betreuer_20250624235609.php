<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'betreuer') {
    header('Location: ../index.html');
    exit();
}

require_once 'db.php';

// Get current betreuer ID
$stmt = $pdo->prepare("SELECT betreuerid FROM betreuer WHERE benutzerid = ?");
$stmt->execute([$_SESSION['user_id']]);
$betreuer = $stmt->fetch();

if (!$betreuer) {
    header('Location: ../index.html');
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
        if ($temperatur > 40) {
            $warnings[] = "Hohe Temperatur: {$temperatur}°C";
        }
        if (strpos($blutdruck, '/') !== false) {
            $parts = explode('/', $blutdruck);
            $systolic = (int)$parts[0];
            $diastolic = isset($parts[1]) ? (int)$parts[1] : 0;
            if ($systolic > 180 || $systolic < 90 || ($diastolic > 0 && ($diastolic > 120 || $diastolic < 60))) {
                $warnings[] = "Kritischer Blutdruck: {$blutdruck}";
            }
        }
        if ($blutzucker > 180 || $blutzucker < 70) {
            $warnings[] = "Kritischer Blutzucker: {$blutzucker} mg/dl";
        }
        
        // Insert warnings
        foreach ($warnings as $warning) {
            $stmt = $pdo->prepare("INSERT INTO warnhinweis (patientid, parameter, wert) VALUES (?, ?, ?)");
            $stmt->execute([$patientId, 'Gesundheitscheck', $warning]);
        }
        
        header("Location: betreuer.php?page=patient&patient={$patientId}");
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

// Load specific patient data if viewing patient details
$currentPatient = null;
$healthData = [];
$warnings = [];

if ($patientID && $page === 'patient') {
    // Verify patient belongs to this betreuer
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
</head>
<body>
    <div class="navbar">
        PflegePro – Betreuer
        <a href="logout.php" style="float:right; color:#fff; text-decoration:none; margin-right:20px;">Logout</a>
    </div>
    <div class="subnav">
        <a href="?page=overview" class="<?= $page === 'overview' ? 'active' : '' ?>">Übersicht</a>
        <?php if ($patientID): ?>
        <a href="?page=patient&patient=<?= $patientID ?>" class="<?= $page === 'patient' ? 'active' : '' ?>">Patient Details</a>
        <a href="?page=warn&patient=<?= $patientID ?>" class="<?= $page === 'warn' ? 'active' : '' ?>">Warnparameter setzen</a>
        <?php endif; ?>
    </div>
    <div class="container">
        <?php if ($page === 'overview'): ?>
            <h2>Ihre zugewiesenen Patienten (<?= count($patients) ?>)</h2>
            <?php if (empty($patients)): ?>
                <p>Sie haben derzeit keine zugewiesenen Patienten.</p>
            <?php else: ?>
                <div class="patient-list">
                    <?php foreach ($patients as $patient): ?>
                        <div class="patient-card" style="margin-bottom: 15px;">
                            <div class="patient-header">
                                <h3><a href="?page=patient&patient=<?= $patient['patientid'] ?>"><?= htmlspecialchars($patient['name']) ?></a></h3>
                            </div>
                            <div class="patient-info-row">
                                <div>
                                    <span class="label">Adresse:</span>
                                    <span><?= nl2br(htmlspecialchars($patient['adresse'])) ?></span>
                                </div>
                                <div>
                                    <span class="label">Geburtsdatum:</span>
                                    <span><?= date('d.m.Y', strtotime($patient['geburtsdatum'])) ?></span>
                                </div>
                                <div>
                                    <span class="label">Kontakt:</span>
                                    <span><?= nl2br(htmlspecialchars($patient['kontaktdaten'])) ?></span>
                                </div>
                                <div>
                                    <span class="label">Pflegeart:</span>
                                    <span><?= htmlspecialchars($patient['pflegeart']) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
        <?php elseif ($page === 'patient' && $currentPatient): ?>
            <div class="patient-card">
                <div class="patient-header">
                    <h3><?= htmlspecialchars($currentPatient['name']) ?></h3>
                    <a href="?page=overview" style="float: right; text-decoration: none;">← Zurück zur Übersicht</a>
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
                    <div>
                        <span class="label">Pflegeart:</span>
                        <span><?= htmlspecialchars($currentPatient['pflegeart']) ?></span>
                    </div>
                </div>
                
                <div class="patient-section-title">Aktuelle Gesundheitsparameter</div>
                <?php if ($healthData): ?>
                    <p><small>Letzte Messung: <?= date('d.m.Y H:i', strtotime($healthData['datum'])) ?></small></p>
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
                            <span><?= $healthData['blutzucker'] ?> mg/dl</span>
                        </div>
                    </div>
                <?php else: ?>
                    <p>Noch keine Gesundheitsdaten erfasst.</p>
                <?php endif; ?>
                
                <div class="patient-section-title">Neue Gesundheitsparameter erfassen</div>
                <form method="post" class="health-form">
                    <input type="hidden" name="action" value="update_health">
                    <input type="hidden" name="patient_id" value="<?= $currentPatient['patientid'] ?>">
                    <div class="patient-params-row">
                        <div>
                            <label class="label" for="blutdruck">Blutdruck (z.B. 120/80):</label>
                            <input type="text" id="blutdruck" name="blutdruck" class="input-field" placeholder="120/80" required>
                        </div>
                        <div>
                            <label class="label" for="temperatur">Temperatur (°C):</label>
                            <input type="number" id="temperatur" name="temperatur" class="input-field" step="0.1" min="30" max="45" placeholder="36.5" required>
                        </div>
                        <div>
                            <label class="label" for="blutzucker">Blutzucker (mg/dl):</label>
                            <input type="number" id="blutzucker" name="blutzucker" class="input-field" min="0" max="500" placeholder="100" required>
                        </div>
                    </div>
                    <button type="submit" class="submit-btn">Werte speichern</button>
                </form>
                
                <div class="warn-section">
                    <div class="patient-section-title">Aktuelle Warnhinweise</div>
                    <?php if (empty($warnings)): ?>
                        <p style="color: green;">Keine aktuellen Warnungen</p>
                    <?php else: ?>
                        <ul>
                        <?php foreach ($warnings as $warning): ?>
                            <li style="color: red;">
                                <strong><?= htmlspecialchars($warning['parameter']) ?>:</strong>
                                <?= htmlspecialchars($warning['wert']) ?>
                                <small>(<?= date('d.m.Y H:i', strtotime($warning['zeitstempel'])) ?>)</small>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            
        <?php else: ?>
            <p>Patient nicht gefunden oder Sie haben keine Berechtigung für diesen Patienten.</p>
            <a href="?page=overview">← Zurück zur Übersicht</a>
        <?php endif; ?>
    </div>
</body>
</html>
