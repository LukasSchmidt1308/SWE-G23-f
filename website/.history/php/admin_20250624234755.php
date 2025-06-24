<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.html');
    exit();
}

// TODO: Replace dummy lists with database queries for Betreuer, Patienten, Stationen.
// TODO: Implement backend logic for creating/editing/deleting entries.
// TODO: Implement backend logic for creating a new Betreuer.

// --- DEMO: Place for database connection and SQL queries ---
// Example: $betreuerList = ...; $patientList = ...; $stationList = ...;
require_once 'db.php';

$page = $_GET['page'] ?? 'betreuer';
// Handle create, delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if ($page === 'betreuer') {
        // Neuer Betreuer anlegen
        $name = $_POST['name'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $kontaktdaten = $_POST['email'] . ' / ' . $_POST['telefon'];
        $stationID = (int)$_POST['station_id'];
        $maxPatienten = (int)$_POST['max_patienten'] ?: 24;
        
        // Benutzer anlegen
        $stmt = $pdo->prepare("INSERT INTO Benutzer (Name, Rolle, Benutzername, Passwort, Kontaktdaten) VALUES (?, 'Betreuer', ?, ?, ?)");
        $stmt->execute([$name, $username, $password, $kontaktdaten]);
        $benutzerID = $pdo->lastInsertId();
        
        // Betreuer anlegen
        $stmt = $pdo->prepare("INSERT INTO Betreuer (BenutzerID, StationID, MaxPatienten) VALUES (?, ?, ?)");
        $stmt->execute([$benutzerID, $stationID, $maxPatienten]);
        header("Location: admin.php?page=betreuer");
        exit();
        
    } elseif ($page === 'patienten') {
        // Neuer Patient anlegen
        $name = $_POST['name'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $adresse = $_POST['adresse'];
        $geburtsdatum = $_POST['geburtsdatum'];
        $email = $_POST['email'];
        $telefon = $_POST['telefon'];
        $pflegeart = $_POST['pflegeart'];
        $stationID = !empty($_POST['station_id']) ? (int)$_POST['station_id'] : null;
        $betreuerID = !empty($_POST['betreuer_id']) ? (int)$_POST['betreuer_id'] : null;
        
        // Auto-assign betreuer if station is selected but no specific betreuer
        if ($stationID && !$betreuerID) {
            // Find betreuer with least patients in this station
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
            $availableBetreuer = $stmt->fetch();
            if ($availableBetreuer) {
                $betreuerID = $availableBetreuer['betreuerid'];
            }
        }
        
        // Benutzer anlegen (PostgreSQL string concatenation)
        $kontaktdaten = $email . ' / ' . $telefon;
        $stmt = $pdo->prepare("INSERT INTO Benutzer (Name, Rolle, Benutzername, Passwort, Kontaktdaten) VALUES (?, 'Patient', ?, ?, ?)");
        $stmt->execute([$name, $username, $password, $kontaktdaten]);
        $benutzerID = $pdo->lastInsertId();
        
        // Patient anlegen
        $stmt = $pdo->prepare("INSERT INTO Patient (BenutzerID, Adresse, Geburtsdatum, Pflegeart, StationID, BetreuerID) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$benutzerID, $adresse, $geburtsdatum, $pflegeart, $stationID, $betreuerID]);
        header("Location: admin.php?page=patienten");
        exit();
        
    } elseif ($page === 'stationen') {
        // Neue Station anlegen
        $name = $_POST['name'];
        $stmt = $pdo->prepare("INSERT INTO Station (Name, Adresse) VALUES (?, '')");
        $stmt->execute([$name]);
        header("Location: admin.php?page=stationen");
        exit();
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)$_GET['id'];
    if ($page === 'betreuer') {
        // Betreuer löschen - PostgreSQL syntax
        $stmt = $pdo->prepare("DELETE FROM Betreuer WHERE BetreuerID = ?");
        $stmt->execute([$id]);
        
    } elseif ($page === 'patienten') {
        $stmt = $pdo->prepare("DELETE FROM Patient WHERE PatientID = ?");
        $stmt->execute([$id]);
        
    } elseif ($page === 'stationen') {
        $stmt = $pdo->prepare("DELETE FROM Station WHERE StationID = ?");
        $stmt->execute([$id]);
    }
    header("Location: admin.php?page=$page");
    exit();
}

// Daten aus DB laden
$betreuerList = [];
$patientList = [];
$stationList = [];
$stationArray = [];        // für Stationen-Tabellenanzeige

// Stationen für Auswahl
$res = $pdo->query("SELECT stationid, name FROM station");
$stationData = $res->fetchAll();
foreach ($stationData as $row) {
    $stationList[$row['stationid']] = $row['name'];
    $stationArray[] = $row; // enthält ['stationid' => ..., 'name' => ...]
}

if ($page === 'betreuer') {
    $res = $pdo->query(
        "SELECT b.betreuerid, u.name, u.benutzername, s.name AS station"
      . " FROM betreuer b"
      . " JOIN benutzer u ON b.benutzerid = u.benutzerid"
      . " JOIN station s ON b.stationid = s.stationid"
    );
    $betreuerList = $res->fetchAll();
    
} elseif ($page === 'patienten') {
    // Load Betreuer for dropdown
    $res = $pdo->query(
        "SELECT b.betreuerid, u.name"
      . " FROM betreuer b"
      . " JOIN benutzer u ON b.benutzerid = u.benutzerid"
    );
    $betreuerList = $res->fetchAll();
    
    // Load Patients
    $res = $pdo->query(
        "SELECT p.patientid, u.name, u.benutzername, s.name AS station, bu.name AS betreuer"
      . " FROM patient p"
      . " JOIN benutzer u ON p.benutzerid = u.benutzerid"
      . " LEFT JOIN station s ON p.stationid = s.stationid"
      . " LEFT JOIN betreuer bt ON p.betreuerid = bt.betreuerid"
      . " LEFT JOIN benutzer bu ON bt.benutzerid = bu.benutzerid"
    );
    $patientList = $res->fetchAll();
    
} elseif ($page === 'stationen') {
    $res = $pdo->query("SELECT stationid, name FROM station");
    $stationList = $res->fetchAll();
}
    
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>PflegePro – Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="navbar">
        PflegePro – Admin
        <a href="logout.php" style="float:right; color:#fff; text-decoration:none; margin-right:20px;">Logout</a>
    </div>
    <div class="subnav">
        <a href="?page=betreuer" class="<?= $page === 'betreuer' ? 'active' : '' ?>">Betreuer verwalten</a>
        <a href="?page=patienten" class="<?= $page === 'patienten' ? 'active' : '' ?>">Patienten verwalten</a>
        <a href="?page=stationen" class="<?= $page === 'stationen' ? 'active' : '' ?>">Stationen verwalten</a>
    </div>
    <div class="container" style="max-width: 1100px; margin: 2rem auto;">

        <?php if ($page === 'betreuer'): ?>
        <section class="admin-section">
            <h2>Betreuer verwalten</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Benutzername</th>
                        <th>Station</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($betreuerList as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['name']) ?></td>
                        <td><?= htmlspecialchars($b['benutzername']) ?></td>
                        <td><?= htmlspecialchars($b['station']) ?></td>
                        <td>
                            <a href="?page=betreuer&action=delete&id=<?= $b['betreuerid'] ?>">Löschen</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h3>Neuen Betreuer anlegen</h3>
            <form class="admin-form" method="post">
                <input name="name" type="text" placeholder="Name" required>
                <input name="username" type="text" placeholder="Benutzername" required>
                <input name="password" type="password" placeholder="Passwort" required>
                <input name="email" type="email" placeholder="E-Mail" required>
                <input name="telefon" type="text" placeholder="Telefonnummer" required>
                <select name="station_id" required>
                    <option value="">Station wählen</option>
                    <?php foreach ($stationList as $id => $name): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
                <input name="max_patienten" type="number" placeholder="Max. Patienten (Standard: 24)" min="1" max="50" value="24">
                <button type="submit">Anlegen</button>
            </form>
        </section>
        <?php elseif ($page === 'patienten'): ?>
        <section class="admin-section">
            <h2>Patienten verwalten</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Benutzername</th>
                        <th>Station</th>
                        <th>Betreuer</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($patientList as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['benutzername']) ?></td>
                        <td><?= htmlspecialchars($p['station']) ?></td>
                        <td><?= htmlspecialchars($p['betreuer']) ?></td>
                        <td>
                            <a href="?page=patienten&action=delete&id=<?= $p['patientid'] ?>">Löschen</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h3>Neuen Patienten anlegen</h3>
            <form class="admin-form" method="post">
                <input name="name" type="text" placeholder="Name" required>
                <input name="username" type="text" placeholder="Benutzername" required>
                <input name="password" type="password" placeholder="Passwort" required>
                <input name="adresse" type="text" placeholder="Adresse" required>
                <input name="geburtsdatum" type="date" placeholder="Geburtsdatum" required>
                <input name="email" type="email" placeholder="E-Mail" required>
                <input name="telefon" type="text" placeholder="Telefonnummer" required>
                <select name="pflegeart" required>
                    <option value="">Pflegeart wählen</option>
                    <option value="Grundpflege">Grundpflege</option>
                    <option value="Behandlungspflege">Behandlungspflege</option>
                    <option value="Häusliche Pflege">Häusliche Pflege</option>
                </select>
                <select name="station_id" id="station_select">
                    <option value="">Station wählen (optional für häusliche Pflege)</option>
                    <?php foreach ($stationList as $id => $name): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                    <option value="0">Häusliche Pflege</option>
                </select>
                <select name="betreuer_id" id="betreuer_select">
                    <option value="">Betreuer automatisch zuweisen</option>
                    <?php foreach ($betreuerList as $b): ?>
                        <option value="<?= $b['betreuerid'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Anlegen</button>
            </form>
        </section>
        <?php elseif ($page === 'stationen'): ?>
        <section class="admin-section">
            <h2>Stationen verwalten</h2>
            <ul>
                <?php foreach ($stationArray as $s): ?>
                    <li>
                        <?= htmlspecialchars($s['name']) ?>
                        <a href="?page=stationen&action=delete&id=<?= $s['stationid'] ?>">Löschen</a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <h3>Neue Station anlegen</h3>
            <form class="admin-form" method="post">
                <input name="name" type="text" placeholder="Stationsname" required>
                <button type="submit">Anlegen</button>
            </form>
        </section>
        <?php endif; ?>
    </div>
</body>
</html>
