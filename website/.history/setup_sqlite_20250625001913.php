<?php
// Fallback database configuration using SQLite
echo "<h1>Fallback Database Setup (SQLite)</h1>";
echo "<p>This will create a local SQLite database if PostgreSQL is not available.</p>";

try {
    // Create SQLite database
    $dbPath = __DIR__ . '/pflegepro.sqlite';
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    echo "<p>✓ SQLite database created/connected: $dbPath</p>";
    
    // Create tables with SQLite syntax
    $sql = "
    -- Drop existing tables if they exist
    DROP TABLE IF EXISTS Gesundheitsdaten;
    DROP TABLE IF EXISTS Patient;
    DROP TABLE IF EXISTS Betreuer;
    DROP TABLE IF EXISTS Station;
    DROP TABLE IF EXISTS Benutzer;

    -- Create Benutzer table
    CREATE TABLE Benutzer (
        BenutzerID INTEGER PRIMARY KEY AUTOINCREMENT,
        Name VARCHAR(100) NOT NULL,
        Rolle VARCHAR(20) NOT NULL CHECK (Rolle IN ('admin', 'Betreuer', 'Patient')),
        Benutzername VARCHAR(50) UNIQUE NOT NULL,
        Passwort VARCHAR(255) NOT NULL,
        Kontaktdaten TEXT
    );

    -- Create Station table
    CREATE TABLE Station (
        StationID INTEGER PRIMARY KEY AUTOINCREMENT,
        Name VARCHAR(100) NOT NULL,
        Adresse TEXT
    );

    -- Create Betreuer table
    CREATE TABLE Betreuer (
        BetreuerID INTEGER PRIMARY KEY AUTOINCREMENT,
        BenutzerID INTEGER NOT NULL REFERENCES Benutzer(BenutzerID) ON DELETE CASCADE,
        StationID INTEGER REFERENCES Station(StationID) ON DELETE SET NULL,
        MaxPatienten INTEGER DEFAULT 24
    );

    -- Create Patient table
    CREATE TABLE Patient (
        PatientID INTEGER PRIMARY KEY AUTOINCREMENT,
        BenutzerID INTEGER NOT NULL REFERENCES Benutzer(BenutzerID) ON DELETE CASCADE,
        Adresse TEXT,
        Geburtsdatum DATE,
        Pflegeart VARCHAR(50),
        StationID INTEGER REFERENCES Station(StationID) ON DELETE SET NULL,
        BetreuerID INTEGER REFERENCES Betreuer(BetreuerID) ON DELETE SET NULL
    );

    -- Create Gesundheitsdaten table
    CREATE TABLE Gesundheitsdaten (
        DatenID INTEGER PRIMARY KEY AUTOINCREMENT,
        PatientID INTEGER NOT NULL REFERENCES Patient(PatientID) ON DELETE CASCADE,
        Datum DATE NOT NULL DEFAULT (date('now')),
        Zeit TIME NOT NULL DEFAULT (time('now')),
        Blutdruck VARCHAR(20),
        Herzfrequenz INTEGER,
        Temperatur DECIMAL(4,2),
        Gewicht DECIMAL(5,2),
        Blutzucker INTEGER,
        Medikamente TEXT,
        Notizen TEXT,
        Warnung BOOLEAN DEFAULT 0
    );
    ";
    
    $statements = explode(';', $sql);
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    echo "<p>✓ Tables created successfully</p>";
    
    // Insert sample data
    echo "<h2>Inserting Sample Data</h2>";
    
    // Admin user
    $password = password_hash('admin', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO Benutzer (Name, Rolle, Benutzername, Passwort, Kontaktdaten) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['Administrator', 'admin', 'admin', $password, 'admin@pflegepro.de']);
    echo "<p>✓ Admin user created</p>";
    
    // Sample stations
    $stations = [
        'Kardiologie',
        'Orthopädie', 
        'Neurologie',
        'Intensivstation'
    ];
    
    foreach ($stations as $station) {
        $stmt = $pdo->prepare("INSERT INTO Station (Name, Adresse) VALUES (?, ?)");
        $stmt->execute([$station, 'Musterstraße 123, 12345 Musterstadt']);
    }
    echo "<p>✓ Sample stations created</p>";
    
    // Sample betreuer
    $password = password_hash('betreuer123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO Benutzer (Name, Rolle, Benutzername, Passwort, Kontaktdaten) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['Maria Müller', 'Betreuer', 'maria', $password, 'maria@pflegepro.de / 0123-456789']);
    $benutzerID = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("INSERT INTO Betreuer (BenutzerID, StationID, MaxPatienten) VALUES (?, ?, ?)");
    $stmt->execute([$benutzerID, 1, 24]);
    echo "<p>✓ Sample betreuer created</p>";
    
    // Sample patient
    $password = password_hash('patient123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO Benutzer (Name, Rolle, Benutzername, Passwort, Kontaktdaten) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['Hans Schmidt', 'Patient', 'hans', $password, 'hans@example.com / 0987-654321']);
    $benutzerID = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("INSERT INTO Patient (BenutzerID, Adresse, Geburtsdatum, Pflegeart, StationID, BetreuerID) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$benutzerID, 'Patientenstraße 456, 54321 Patientenstadt', '1950-01-15', 'Grundpflege', 1, 1]);
    echo "<p>✓ Sample patient created</p>";
    
    echo "<h2>✅ SQLite Database Setup Complete!</h2>";
    echo "<p>Database file created at: <code>$dbPath</code></p>";
    
    echo "<h3>Now update db.php to use SQLite:</h3>";
    echo "<pre>";
    echo htmlspecialchars('<?php
// SQLite configuration
try {
    $dbPath = __DIR__ . "/../pflegepro.sqlite";
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
$mysqli = $pdo;
?>');
    echo "</pre>";
    
    echo "<p><strong>Login credentials:</strong></p>";
    echo "<ul>";
    echo "<li>Admin: username = admin, password = admin</li>";
    echo "<li>Betreuer: username = maria, password = betreuer123</li>";
    echo "<li>Patient: username = hans, password = patient123</li>";
    echo "</ul>";
    
    echo "<p><a href='index.php'>Go to Login Page</a></p>";
    
} catch (PDOException $e) {
    echo "<p>❌ Error setting up SQLite database: " . $e->getMessage() . "</p>";
}
?>
