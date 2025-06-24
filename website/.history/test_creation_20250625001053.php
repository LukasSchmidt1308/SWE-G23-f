<?php
require_once 'php/db.php';

echo "<h1>Database Status Check</h1>";

// Check if tables exist
$tables = ['benutzer', 'station', 'betreuer', 'patient'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "<p>Table '$table': $count records</p>";
    } catch (PDOException $e) {
        echo "<p>Table '$table': ERROR - " . $e->getMessage() . "</p>";
    }
}

echo "<hr>";
echo "<h2>Recent POST Test</h2>";

// Test creating a new betreuer
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>Processing POST request...</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    $name = $_POST['name'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefon = $_POST['telefon'] ?? '';
    $stationID = $_POST['station_id'] ?? '';
    $maxPatienten = (int)($_POST['max_patienten'] ?? 24);
    
    echo "<p>Processing data:</p>";
    echo "<ul>";
    echo "<li>Name: " . htmlspecialchars($name) . "</li>";
    echo "<li>Username: " . htmlspecialchars($username) . "</li>";
    echo "<li>Password: " . (empty($password) ? 'EMPTY' : 'PROVIDED') . "</li>";
    echo "<li>Email: " . htmlspecialchars($email) . "</li>";
    echo "<li>Telefon: " . htmlspecialchars($telefon) . "</li>";
    echo "<li>Station ID: " . htmlspecialchars($stationID) . "</li>";
    echo "<li>Max Patienten: " . $maxPatienten . "</li>";
    echo "</ul>";
    
    // Validate required fields
    if (empty($name) || empty($username) || empty($password) || empty($email) || empty($telefon) || empty($stationID)) {
        echo "<p style='color: red;'>ERROR: Missing required fields!</p>";
        $missing = [];
        if (empty($name)) $missing[] = 'name';
        if (empty($username)) $missing[] = 'username';
        if (empty($password)) $missing[] = 'password';
        if (empty($email)) $missing[] = 'email';
        if (empty($telefon)) $missing[] = 'telefon';
        if (empty($stationID)) $missing[] = 'station_id';
        echo "<p>Missing: " . implode(', ', $missing) . "</p>";
    } else {
        try {
            $kontaktdaten = $email . ' / ' . $telefon;
            $stationID = (int)$stationID;
            $password = password_hash($password, PASSWORD_DEFAULT);
            
            echo "<p>Creating user...</p>";
            // Benutzer anlegen
            $stmt = $pdo->prepare("INSERT INTO Benutzer (Name, Rolle, Benutzername, Passwort, Kontaktdaten) VALUES (?, 'Betreuer', ?, ?, ?)");
            $result = $stmt->execute([$name, $username, $password, $kontaktdaten]);
            echo "<p>User creation result: " . ($result ? 'SUCCESS' : 'FAILED') . "</p>";
            
            $benutzerID = $pdo->lastInsertId();
            echo "<p>New user ID: $benutzerID</p>";
            
            echo "<p>Creating betreuer...</p>";
            // Betreuer anlegen
            $stmt = $pdo->prepare("INSERT INTO Betreuer (BenutzerID, StationID, MaxPatienten) VALUES (?, ?, ?)");
            $result = $stmt->execute([$benutzerID, $stationID, $maxPatienten]);
            echo "<p>Betreuer creation result: " . ($result ? 'SUCCESS' : 'FAILED') . "</p>";
            
            echo "<p style='color: green;'>✓ Betreuer created successfully!</p>";
            
        } catch (PDOException $e) {
            echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
            if (strpos($e->getMessage(), 'benutzer_benutzername_key') !== false) {
                echo "<p>This is a duplicate username error.</p>";
            }
        }
    }
}

// Show available stations
echo "<hr>";
echo "<h2>Available Stations</h2>";
try {
    $stmt = $pdo->query("SELECT StationID, Name FROM Station ORDER BY Name");
    $stations = $stmt->fetchAll();
    if (empty($stations)) {
        echo "<p>No stations found!</p>";
    } else {
        foreach ($stations as $station) {
            echo "<p>ID: {$station['stationid']}, Name: {$station['name']}</p>";
        }
    }
} catch (PDOException $e) {
    echo "<p>Error loading stations: " . $e->getMessage() . "</p>";
}

// Test form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
?>
<hr>
<h2>Test Form</h2>
<form method="post">
    <p>Name: <input type="text" name="name" value="Test Betreuer" required></p>
    <p>Username: <input type="text" name="username" value="testbetreuer<?php echo time(); ?>" required></p>
    <p>Password: <input type="password" name="password" value="test123" required></p>
    <p>Email: <input type="email" name="email" value="test@example.com" required></p>
    <p>Telefon: <input type="text" name="telefon" value="123456789" required></p>
    <p>Station ID: 
        <select name="station_id" required>
            <option value="">Select station...</option>
            <?php
            try {
                $stmt = $pdo->query("SELECT StationID, Name FROM Station ORDER BY Name");
                while ($station = $stmt->fetch()) {
                    echo "<option value=\"{$station['stationid']}\">{$station['name']}</option>";
                }
            } catch (Exception $e) {
                echo "<option disabled>Error loading stations</option>";
            }
            ?>
        </select>
    </p>
    <p>Max Patienten: <input type="number" name="max_patienten" value="24" min="1" required></p>
    <p><button type="submit">Create Test Betreuer</button></p>
</form>
<?php
}
?>
