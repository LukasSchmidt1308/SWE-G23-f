<?php
// setup_check.php - Automatically check and create database if needed

function checkAndSetupDatabase(&$pdo) {
    // If PDO connection failed, create database from scratch
    if ($pdo === null) {
        $result = createCompleteDatabase();
        if ($result) {
            $pdo = $result;
            return true;
        }
        return false;
    }
    
    // Check if main tables exist
    $result = $pdo->query("SELECT COUNT(*) FROM information_schema.tables 
                          WHERE table_schema = 'public' 
                          AND table_name IN ('station', 'benutzer', 'betreuer', 'patient')");
    $tableCount = $result->fetchColumn();
    
    // Check if users exist
    $userCount = 0;
    if ($tableCount >= 4) {
        $result = $pdo->query("SELECT COUNT(*) FROM benutzer");
        $userCount = $result->fetchColumn();
    }
    
    // If tables or users missing, run setup
    if ($tableCount < 4 || $userCount == 0) {
        ob_start();
        include __DIR__ . '/datenbank.php';
        ob_end_clean();
    }
    
    return true;
}

function createCompleteDatabase() {
    // Database configuration
    $host = 'localhost';
    $port = 5432;
    $dbname = 'pflegepro';
    $username = 'pflegepro_user';
    $password = 'secure_password123';

    // Connect as postgres superuser
    $connection_attempts = [
        ['user' => 'postgres', 'pass' => 'admin'],
        ['user' => 'postgres', 'pass' => 'postgres'],
        ['user' => 'postgres', 'pass' => 'password'],
        ['user' => 'postgres', 'pass' => ''],
    ];
    
    $pdo_admin = null;
    foreach ($connection_attempts as $attempt) {
        $pdo_admin = @new PDO("pgsql:host=$host;port=$port;dbname=postgres", 
                             $attempt['user'], $attempt['pass']);
        if ($pdo_admin) {
            $pdo_admin->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            break;
        }
    }
    
    if (!$pdo_admin) {
        return false; // Could not connect as superuser
    }
    
    // Create user (ignore if exists)
    @$pdo_admin->exec("CREATE USER $username WITH PASSWORD '$password'");
    
    // Create database (ignore if exists)
    @$pdo_admin->exec("CREATE DATABASE $dbname OWNER $username");
    
    // Grant privileges
    @$pdo_admin->exec("GRANT ALL PRIVILEGES ON DATABASE $dbname TO $username");
    
    // Now connect to our database and create tables
    $pdo_new = @new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    if (!$pdo_new) {
        return false;
    }
    
    $pdo_new->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $pdo_new->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Set global $pdo so datenbank.php can use it
    $GLOBALS['pdo'] = $pdo_new;
    
    // Run the database setup
    ob_start();
    include __DIR__ . '/datenbank.php';
    ob_end_clean(); // Discard output
    
    return $pdo_new; // Return the working PDO connection
}
?>
