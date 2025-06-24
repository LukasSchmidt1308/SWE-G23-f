<?php
echo "<h1>Complete Database Diagnostic</h1>";

// Test 1: Basic PHP/PostgreSQL support
echo "<h2>1. PHP Support Check</h2>";
if (extension_loaded('pdo_pgsql')) {
    echo "<p>✓ PDO PostgreSQL extension is loaded</p>";
} else {
    echo "<p>❌ PDO PostgreSQL extension is NOT loaded</p>";
}

// Test 2: Connection test
echo "<h2>2. Database Connection Test</h2>";
$dbHost = "localhost";
$dbUser = "postgres";  
$dbPass = "admin";
$dbName = "pflegepro";
$dbPort = "5432";

try {
    $pdo = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    echo "<p>✓ Database connection successful</p>";
    
    // Test 3: Check if tables exist
    echo "<h2>3. Table Structure Check</h2>";
    $tables = ['benutzer', 'station', 'betreuer', 'patient', 'gesundheitsdaten'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p>✓ Table '$table' exists with $count records</p>";
        } catch (PDOException $e) {
            echo "<p>❌ Table '$table' does NOT exist or has error: " . $e->getMessage() . "</p>";
        }
    }
    
    // Test 4: Check database setup
    echo "<h2>4. Database Setup Check</h2>";
    require_once 'php/setup_check.php';
    $setupResult = checkAndSetupDatabase($pdo);
    if ($setupResult) {
        echo "<p>✓ Database setup completed successfully</p>";
    } else {
        echo "<p>❌ Database setup failed or was not needed</p>";
    }
    
    // Test 5: Admin user test
    echo "<h2>5. Admin User Check</h2>";
    try {
        $stmt = $pdo->prepare("SELECT * FROM Benutzer WHERE Rolle = ?");
        $stmt->execute(['admin']);
        $admin = $stmt->fetch();
        
        if ($admin) {
            echo "<p>✓ Admin user found: " . htmlspecialchars($admin['benutzername']) . "</p>";
        } else {
            echo "<p>⚠️ No admin user found, creating one...</p>";
            $password = password_hash('admin', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO Benutzer (Name, Rolle, Benutzername, Passwort, Kontaktdaten) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(['Administrator', 'admin', 'admin', $password, 'admin@pflegepro.de']);
            echo "<p>✓ Admin user created!</p>";
        }
    } catch (PDOException $e) {
        echo "<p>❌ Error checking/creating admin user: " . $e->getMessage() . "</p>";
    }
    
    // Test 6: Station data check
    echo "<h2>6. Station Data Check</h2>";
    try {
        $stmt = $pdo->query("SELECT * FROM Station");
        $stations = $stmt->fetchAll();
        echo "<p>Found " . count($stations) . " stations:</p>";
        foreach ($stations as $station) {
            echo "<p>- ID: {$station['stationid']}, Name: " . htmlspecialchars($station['name']) . "</p>";
        }
    } catch (PDOException $e) {
        echo "<p>❌ Error loading stations: " . $e->getMessage() . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p>This means either:</p>";
    echo "<ul>";
    echo "<li>PostgreSQL is not running</li>";
    echo "<li>Database 'pflegepro' does not exist</li>";
    echo "<li>Wrong username/password</li>";
    echo "<li>Wrong host/port</li>";
    echo "</ul>";
    
    // Try to connect to postgres database instead
    echo "<h2>Alternative: Try connecting to default 'postgres' database</h2>";
    try {
        $pdo_alt = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=postgres", $dbUser, $dbPass);
        echo "<p>✓ Connected to default 'postgres' database</p>";
        echo "<p>You may need to create the 'pflegepro' database manually:</p>";
        echo "<pre>CREATE DATABASE pflegepro;</pre>";
    } catch (PDOException $e2) {
        echo "<p>❌ Cannot connect to PostgreSQL at all: " . $e2->getMessage() . "</p>";
    }
}

echo "<hr>";
echo "<p><a href='index.php'>Go to Login Page</a></p>";
?>
