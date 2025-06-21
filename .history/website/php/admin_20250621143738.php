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
// require_once 'db.php';
// Example: $betreuerList = ...; $patientList = ...; $stationList = ...;

// Dummy data for demonstration
$betreuerList = [
    ['name' => 'Anna Pfleger', 'benutzername' => 'betreuer1', 'station' => 'Station A'],
    ['name' => 'Bernd Helfer', 'benutzername' => 'betreuer2', 'station' => 'Station B'],
];
$patientList = [
    ['name' => 'Max Mustermann', 'benutzername' => 'p1', 'station' => 'Station A', 'betreuer' => 'Anna Pfleger'],
    ['name' => 'Erika Musterfrau', 'benutzername' => 'p2', 'station' => 'Station B', 'betreuer' => 'Bernd Helfer'],
];
$stationList = ['Station A', 'Station B', 'Station C'];

$page = $_GET['page'] ?? 'betreuer';
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
        <a href="?page=betreuer" class="<?php echo $page === 'betreuer' ? 'active' : ''; ?>">Betreuer verwalten</a>
        <a href="?page=patienten" class="<?php echo $page === 'patienten' ? 'active' : ''; ?>">Patienten verwalten</a>
        <a href="?page=stationen" class="<?php echo $page === 'stationen' ? 'active' : ''; ?>">Stationen verwalten</a>
    </div>
    <div class="container" style="max-width: 1100px; margin: 2rem auto;">

        <?php if ($page === 'betreuer'): ?>
        <!-- Betreuer verwalten -->
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
                        <td><?php echo htmlspecialchars($b['name']); ?></td>
                        <td><?php echo htmlspecialchars($b['benutzername']); ?></td>
                        <td><?php echo htmlspecialchars($b['station']); ?></td>
                        <td>
                            <button>Bearbeiten</button>
                            <button>Löschen</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h3>Neuen Betreuer anlegen</h3>
            <form class="admin-form">
                <input type="text" placeholder="Name" required>
                <input type="text" placeholder="Benutzername" required>
                <input type="password" placeholder="Passwort" required>
                <select required>
                    <option value="">Station wählen</option>
                    <?php foreach ($stationList as $station): ?>
                        <option value="<?php echo htmlspecialchars($station); ?>"><?php echo htmlspecialchars($station); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Anlegen</button>
            </form>
        </section>
        <?php elseif ($page === 'patienten'): ?>
        <!-- Patienten verwalten -->
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
                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                        <td><?php echo htmlspecialchars($p['benutzername']); ?></td>
                        <td><?php echo htmlspecialchars($p['station']); ?></td>
                        <td><?php echo htmlspecialchars($p['betreuer']); ?></td>
                        <td>
                            <button>Bearbeiten</button>
                            <button>Löschen</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h3>Neuen Patienten anlegen</h3>
            <form class="admin-form">
                <input type="text" placeholder="Name" required>
                <input type="text" placeholder="Benutzername" required>
                <input type="password" placeholder="Passwort" required>
                <input type="text" placeholder="Adresse" required>
                <input type="date" placeholder="Geburtsdatum" required>
                <input type="email" placeholder="E-Mail" required>
                <input type="text" placeholder="Telefonnummer" required>
                <select required>
                    <option value="">Station wählen</option>
                    <?php foreach ($stationList as $station): ?>
                        <option value="<?php echo htmlspecialchars($station); ?>"><?php echo htmlspecialchars($station); ?></option>
                    <?php endforeach; ?>
                </select>
                <select required>
                    <option value="">Betreuer wählen</option>
                    <?php foreach ($betreuerList as $b): ?>
                        <option value="<?php echo htmlspecialchars($b['name']); ?>"><?php echo htmlspecialchars($b['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Anlegen</button>
            </form>
        </section>
        <?php elseif ($page === 'stationen'): ?>
        <!-- Stationen verwalten -->
        <section class="admin-section">
            <h2>Stationen verwalten</h2>
            <ul>
                <?php foreach ($stationList as $station): ?>
                    <li>
                        <?php echo htmlspecialchars($station); ?>
                        <button>Bearbeiten</button>
                        <button>Löschen</button>
                    </li>
                <?php endforeach; ?>
            </ul>
            <h3>Neue Station anlegen</h3>
            <form class="admin-form">
                <input type="text" placeholder="Stationsname" required>
                <button type="submit">Anlegen</button>
            </form>
        </section>
        <?php endif; ?>
    </div>
</body>
</html>