<?php
// db.php
// PDO PostgreSQL-Verbindungsdatei – passe die Werte für Host, User, Passwort und DB-Name an
$dbHost = "localhost";
$dbUser = "postgres";  // default PostgreSQL user (change if different)
$dbPass = "";          // your PostgreSQL password
$dbName = "pflegepro";
$dbPort = "5432";      // default PostgreSQL port

try {
    // Verbindung aufbauen
    $pdo = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
    
    // PDO Konfiguration
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // UTF-8 einstellen
    $pdo->exec("SET NAMES UTF8");
    
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// For backward compatibility with existing code, create a $mysqli variable
$mysqli = $pdo;

?>