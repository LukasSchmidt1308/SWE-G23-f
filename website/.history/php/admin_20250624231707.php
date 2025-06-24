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
        $stationID = (int)$_POST['station_id'];
        
        // Benutzer anlegen
        $stmt = $pdo->prepare("INSERT INTO Benutzer (Name, Rolle, Benutzername, Passwort, Kontaktdaten) VALUES (?, 'Betreuer', ?, ?, '')");
        $stmt->execute([$name, $username, $password]);
        $benutzerID = $pdo->lastInsertId();
        
        // Betreuer anlegen
        $stmt = $pdo->prepare("INSERT INTO Betreuer (BenutzerID, StationID) VALUES (?, ?)");
        $stmt->execute([$benutzerID, $stationID]);
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
        $stationID = (int)$_POST['station_id'];
        $betreuerID = (int)$_POST['betreuer_id'];
        
        // Benutzer anlegen (PostgreSQL string concatenation)
        $kontaktdaten = $email . ' / ' . $telefon;
        $stmt = $pdo->prepare("INSERT INTO Benutzer (Name, Rolle, Benutzername, Passwort, Kontaktdaten) VALUES (?, 'Patient', ?, ?, ?)");
        $stmt->execute([$name, $username, $password, $kontaktdaten]);
        $benutzerID = $pdo->lastInsertId();
        
        // Patient anlegen
        $stmt = $pdo->prepare("INSERT INTO Patient (BenutzerID, Adresse, Geburtsdatum, Pflegeart, StationID, BetreuerID) VALUES (?, ?, ?, '', ?, ?)");
        $stmt->execute([$benutzerID, $adresse, $geburtsdatum, $stationID, $betreuerID]);
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
$res = $pdo->query("SELECT StationID, Name FROM Station");
$stationData = $res->fetchAll();
foreach ($stationData as $row) {
    $stationList[$row['StationID']] = $row['Name'];
    $stationArray[] = $row; // enthält ['StationID' => ..., 'Name' => ...]
}

if ($page === 'betreuer') {
    $res = $pdo->query(
        "SELECT b.BetreuerID, u.Name, u.Benutzername, s.Name AS Station"
      . " FROM Betreuer b"
      . " JOIN Benutzer u ON b.BenutzerID = u.BenutzerID"
      . " JOIN Station s ON b.StationID = s.StationID"
    );
    $betreuerList = $res->fetchAll();
    
} elseif ($page === 'patienten') {
    // Load Betreuer for dropdown
    $res = $mysqli->query(
        "SELECT b.BetreuerID, u.Name"
      . " FROM Betreuer b"
      . " JOIN Benutzer u ON b.BenutzerID = u.BenutzerID"
    );
    $betreuerList = $res->fetchAll();
    
    // Load Patients
    $res = $mysqli->query(
        "SELECT p.PatientID, u.Name, u.Benutzername, s.Name AS Station, bu.Name AS Betreuer"
      . " FROM Patient p"
      . " JOIN Benutzer u ON p.BenutzerID = u.BenutzerID"
      . " LEFT JOIN Station s ON p.StationID = s.StationID"
      . " LEFT JOIN Betreuer bt ON p.BetreuerID = bt.BetreuerID"
      . " LEFT JOIN Benutzer bu ON bt.BenutzerID = bu.BenutzerID"
    );
    $patientList = $res->fetchAll();
    
} elseif ($page === 'stationen') {
    $res = $mysqli->query("SELECT StationID, Name FROM Station");
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
                        <td><?= htmlspecialchars($b['Name']) ?></td>
                        <td><?= htmlspecialchars($b['Benutzername']) ?></td>
                        <td><?= htmlspecialchars($b['Station']) ?></td>
                        <td>
                            <a href="?page=betreuer&action=delete&id=<?= $b['BetreuerID'] ?>">Löschen</a>
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
                <select name="station_id" required>
                    <option value="">Station wählen</option>
                    <?php foreach ($stationList as $id => $name): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
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
                        <td><?= htmlspecialchars($p['Name']) ?></td>
                        <td><?= htmlspecialchars($p['Benutzername']) ?></td>
                        <td><?= htmlspecialchars($p['Station']) ?></td>
                        <td><?= htmlspecialchars($p['Betreuer']) ?></td>
                        <td>
                            <a href="?page=patienten&action=delete&id=<?= $p['PatientID'] ?>">Löschen</a>
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
                <select name="station_id" required>
                    <option value="">Station wählen</option>
                    <?php foreach ($stationList as $id => $name): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="betreuer_id" required>
                    <option value="">Betreuer wählen</option>
                    <?php foreach ($betreuerList as $b): ?>
                        <option value="<?= $b['BetreuerID'] ?>"><?= htmlspecialchars($b['Name']) ?></option>
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
                        <?= htmlspecialchars($s['Name']) ?>
                        <a href="?page=stationen&action=delete&id=<?= $s['StationID'] ?>">Löschen</a>
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
