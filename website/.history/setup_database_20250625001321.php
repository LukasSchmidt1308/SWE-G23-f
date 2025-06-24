<?php
echo "<h1>Database Creation and Setup Script</h1>";

$dbHost = "localhost";
$dbUser = "postgres";
$dbPass = "admin";
$dbName = "pflegepro";
$dbPort = "5432";

// Step 1: Try to connect to PostgreSQL server (not specific database)
echo "<h2>Step 1: Connect to PostgreSQL Server</h2>";
try {
    $pdo_server = new PDO("pgsql:host=$dbHost;port=$dbPort", $dbUser, $dbPass);
    $pdo_server->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✓ Connected to PostgreSQL server</p>";
    
    // Step 2: Create database if it doesn't exist
    echo "<h2>Step 2: Create Database</h2>";
    try {
        $pdo_server->exec("CREATE DATABASE $dbName");
        echo "<p>✓ Database '$dbName' created</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "<p>ℹ️ Database '$dbName' already exists</p>";
        } else {
            echo "<p>❌ Error creating database: " . $e->getMessage() . "</p>";
        }
    }
    
    // Step 3: Connect to the specific database
    echo "<h2>Step 3: Connect to PflegePro Database</h2>";
    try {
        $pdo = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        echo "<p>✓ Connected to '$dbName' database</p>";
        
        // Step 4: Create tables
        echo "<h2>Step 4: Create Tables</h2>";
        $sql = "
        -- Drop existing tables if they exist
        DROP TABLE IF EXISTS Gesundheitsdaten CASCADE;
        DROP TABLE IF EXISTS Patient CASCADE;
        DROP TABLE IF EXISTS Betreuer CASCADE;
        DROP TABLE IF EXISTS Station CASCADE;
        DROP TABLE IF EXISTS Benutzer CASCADE;

        -- Create Benutzer table
        CREATE TABLE Benutzer (
            BenutzerID SERIAL PRIMARY KEY,
            Name VARCHAR(100) NOT NULL,
            Rolle VARCHAR(20) NOT NULL CHECK (Rolle IN ('admin', 'Betreuer', 'Patient')),
            Benutzername VARCHAR(50) UNIQUE NOT NULL,
            Passwort VARCHAR(255) NOT NULL,
            Kontaktdaten TEXT
        );

        -- Create Station table
        CREATE TABLE Station (
            StationID SERIAL PRIMARY KEY,
            Name VARCHAR(100) NOT NULL,
            Adresse TEXT
        );

        -- Create Betreuer table
        CREATE TABLE Betreuer (
            BetreuerID SERIAL PRIMARY KEY,
            BenutzerID INTEGER NOT NULL REFERENCES Benutzer(BenutzerID) ON DELETE CASCADE,
            StationID INTEGER REFERENCES Station(StationID) ON DELETE SET NULL,
            MaxPatienten INTEGER DEFAULT 24
        );

        -- Create Patient table
        CREATE TABLE Patient (
            PatientID SERIAL PRIMARY KEY,
            BenutzerID INTEGER NOT NULL REFERENCES Benutzer(BenutzerID) ON DELETE CASCADE,
            Adresse TEXT,
            Geburtsdatum DATE,
            Pflegeart VARCHAR(50),
            StationID INTEGER REFERENCES Station(StationID) ON DELETE SET NULL,
            BetreuerID INTEGER REFERENCES Betreuer(BetreuerID) ON DELETE SET NULL
        );

        -- Create Gesundheitsdaten table
        CREATE TABLE Gesundheitsdaten (
            DatenID SERIAL PRIMARY KEY,
            PatientID INTEGER NOT NULL REFERENCES Patient(PatientID) ON DELETE CASCADE,
            Datum DATE NOT NULL DEFAULT CURRENT_DATE,
            Zeit TIME NOT NULL DEFAULT CURRENT_TIME,
            Blutdruck VARCHAR(20),
            Herzfrequenz INTEGER,
            Temperatur DECIMAL(4,2),
            Gewicht DECIMAL(5,2),
            Blutzucker INTEGER,
            Medikamente TEXT,
            Notizen TEXT,
            Warnung BOOLEAN DEFAULT FALSE
        );
        ";
        
        $statements = explode(';', $sql);
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    echo "<p>⚠️ Statement error: " . $e->getMessage() . "</p>";
                }
            }
        }
        echo "<p>✓ Tables created successfully</p>";
        
        // Step 5: Insert sample data
        echo "<h2>Step 5: Insert Sample Data</h2>";
        
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
        
        echo "<h2>✅ Database Setup Complete!</h2>";
        echo "<p>You can now:</p>";
        echo "<ul>";
        echo "<li><a href='index.php'>Login with username: admin, password: admin</a></li>";
        echo "<li><a href='php/admin.php'>Go directly to admin panel</a> (if logged in)</li>";
        echo "</ul>";
        
    } catch (PDOException $e) {
        echo "<p>❌ Error connecting to database: " . $e->getMessage() . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Cannot connect to PostgreSQL server: " . $e->getMessage() . "</p>";
    echo "<p>Please make sure:</p>";
    echo "<ul>";
    echo "<li>PostgreSQL is installed and running</li>";
    echo "<li>Username 'postgres' with password 'admin' exists</li>";
    echo "<li>PostgreSQL is listening on localhost:5432</li>";
    echo "</ul>";
    
    echo "<h2>Alternative: Manual Setup Instructions</h2>";
    echo "<p>If PostgreSQL is not available, you can:</p>";
    echo "<ol>";
    echo "<li>Install PostgreSQL</li>";
    echo "<li>Create a database named 'pflegepro'</li>";
    echo "<li>Run the SQL script above</li>";
    echo "</ol>";
}
?>
