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
            $diastolic = (int)$parts[1];
            if ($systolic > 180 || $systolic < 90 || $diastolic > 120 || $diastolic < 60) {
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

// Dummy patient data for demonstration  Replace with database query later 

$patients = [
    'p1' => [
        'name' => 'Max Mustermann',
        'adresse' => 'Musterstraße 1<br>12345 Musterstadt',
        'geburtsdatum' => '01.01.1970',
        'kontakt' => 'max.mustermann@example.com<br>01234 567890',
        'blutdruck' => 120,
        'temperatur' => 36.8,
        'blutzucker' => 100,
        'warnhinweise' => ['Keine aktuellen Warnungen']
    ],
    'p2' => [
        'name' => 'Erika Musterfrau',
        'adresse' => 'Beispielweg 2<br>54321 Beispielstadt',
        'geburtsdatum' => '15.05.1980',
        'kontakt' => 'erika.musterfrau@example.com<br>09876 543210',
        'blutdruck' => 135,
        'temperatur' => 37.2,
        'blutzucker' => 110,
        'warnhinweise' => ['Keine aktuellen Warnungen']
    ],
    'p3' => [
        'name' => 'John Doe',
        'adresse' => 'Sample Street 3<br>98765 Sampletown',
        'geburtsdatum' => '22.09.1965',
        'kontakt' => 'john.doe@example.com<br>01122 334455',
        'blutdruck' => 140,
        'temperatur' => 38.0,
        'blutzucker' => 130,
        'warnhinweise' => ['Blutdruck zu hoch am 18.06.2025, 10:30 Uhr']
    ]
];

$current = $patients[$patient];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>PflegePro – Betreuer</title>
    <link rel="stylesheet" href="../style.css">
    <script src="../script.js"></script>
</head>
<body>
    <div class="navbar">
        PflegePro – Betreuer
        <a href="logout.php" style="float:right; color:#fff; text-decoration:none; margin-right:20px;">Logout</a>
    </div>
    <div class="subnav">
        <a href="?page=overview&patient=<?php echo htmlspecialchars($patient); ?>" class="<?php echo $page === 'overview' ? 'active' : ''; ?>">Übersicht</a>
        <a href="?page=warn&patient=<?php echo htmlspecialchars($patient); ?>" class="<?php echo $page === 'warn' ? 'active' : ''; ?>">Warnparameter setzen</a>
        <form style="display:inline;" method="get">
            <input type="hidden" name="page" value="<?php echo htmlspecialchars($page); ?>">
            <select name="patient" onchange="this.form.submit()">
                <option value="p1" <?php if($patient === 'p1') echo 'selected'; ?>>Max Mustermann</option>
                <option value="p2" <?php if($patient === 'p2') echo 'selected'; ?>>Erika Musterfrau</option>
                <option value="p3" <?php if($patient === 'p3') echo 'selected'; ?>>John Doe</option>
            </select>
        </form>
    </div>
    <div class="container">
        <div class="right" style="margin:auto;">
            <?php if ($page === 'overview'): ?>
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
            <?php elseif ($page === 'warn'): ?>
                <div class="patient-card">
                    <div class="patient-header">
                        <h3>Warnparameter setzen für <?php echo $current['name']; ?></h3>
                    </div>
                    <form>
                        <div class="patient-params-row">
                            <div>
                                <label class="label" for="blutdruck">Blutdruck (mmHg):</label>
                                <input type="number" id="blutdruck" name="blutdruck" class="input-field" value="<?php echo $current['blutdruck']; ?>">
                            </div>
                            <div>
                                <label class="label" for="temperatur">Temperatur (°C):</label>
                                <input type="number" id="temperatur" name="temperatur" step="0.1" class="input-field" value="<?php echo $current['temperatur']; ?>">
                            </div>
                            <div>
                                <label class="label" for="blutzucker">Blutzucker (mg/dl):</label>
                                <input type="number" id="blutzucker" name="blutzucker" step="0.1" class="input-field" value="<?php echo $current['blutzucker']; ?>">
                            </div>
                        </div>
                        <button type="submit" class="submit-btn">Speichern</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>