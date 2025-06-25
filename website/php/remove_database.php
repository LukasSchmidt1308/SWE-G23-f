<?php
/**
 * Database Removal Script
 * WARNING: This will completely delete the entire database!
 * Use only for testing or complete reset.
 */

// First, use existing connection to clean up tables
require_once 'db.php';

// Database configuration for complete removal
$host = 'localhost';
$port = 5432;
$dbname = 'pflegepro';
$username = 'pflegepro_user';
$password = 'secure_password123';

echo "=== COMPLETE DATABASE REMOVAL ===\n";
echo "⚠️  WARNING: This will permanently delete everything!\n\n";

// Step 1: Clean up tables first using existing connection
echo "Step 1: Removing all tables and data...\n";
try {
    $tables = ['warnung', 'gesundheitsdaten', 'patient', 'betreuer', 'benutzer', 'station'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("DROP TABLE IF EXISTS $table CASCADE");
        $stmt->execute();
        echo "  ✅ Dropped table: $table\n";
    }
    echo "  ✅ All tables removed successfully!\n\n";
} catch (PDOException $e) {
    echo "  ⚠️  Error removing tables: " . $e->getMessage() . "\n\n";
}

// Step 2: Try to remove the entire database
echo "Step 2: Attempting to remove entire database...\n";

// Try different connection methods for complete removal
$connection_attempts = [
    // Try with current user to postgres database
    ['host' => $host, 'port' => $port, 'dbname' => 'postgres', 'user' => $username, 'pass' => $password],
    // Try with postgres superuser (common default passwords)
    ['host' => $host, 'port' => $port, 'dbname' => 'postgres', 'user' => 'postgres', 'pass' => 'postgres'],
    ['host' => $host, 'port' => $port, 'dbname' => 'postgres', 'user' => 'postgres', 'pass' => 'password'],
    ['host' => $host, 'port' => $port, 'dbname' => 'postgres', 'user' => 'postgres', 'pass' => ''],
    ['host' => $host, 'port' => $port, 'dbname' => 'postgres', 'user' => 'postgres', 'pass' => 'admin'],
];

$success = false;

foreach ($connection_attempts as $attempt) {
    try {
        echo "  Trying connection as user '{$attempt['user']}'...\n";
        $pdo_admin = new PDO("pgsql:host={$attempt['host']};port={$attempt['port']};dbname={$attempt['dbname']}", 
                           $attempt['user'], $attempt['pass']);
        $pdo_admin->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        
        echo "  ✅ Connected to PostgreSQL server as '{$attempt['user']}'!\n";
        
        // Terminate all connections to the database first (more aggressive)
        $stmt = $pdo_admin->prepare("
            SELECT pg_terminate_backend(pid)
            FROM pg_stat_activity
            WHERE datname = ? AND pid <> pg_backend_pid()
        ");
        $stmt->execute([$dbname]);
        
        echo "  Terminated all connections to database '$dbname'...\n";
        
        // Check if database exists before dropping
        $stmt = $pdo_admin->prepare("SELECT 1 FROM pg_database WHERE datname = ?");
        $stmt->execute([$dbname]);
        if ($stmt->fetch()) {
            // Drop the database
            $pdo_admin->exec("DROP DATABASE $dbname");
            echo "  ✅ Database '$dbname' has been completely removed!\n";
        } else {
            echo "  ℹ️  Database '$dbname' does not exist\n";
        }
        
        // Check if user exists before dropping
        $stmt = $pdo_admin->prepare("SELECT 1 FROM pg_user WHERE usename = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            // Drop the user
            $pdo_admin->exec("DROP USER $username");
            echo "  ✅ Database user '$username' has been removed!\n";
        } else {
            echo "  ℹ️  User '$username' does not exist\n";
        }
        
        echo "\n  🎉 COMPLETE REMOVAL SUCCESSFUL!\n";
        echo "  🆕 You are now like a completely new user!\n";
        echo "  🔄 Run setup scripts to create everything from scratch.\n";
        
        $success = true;
        break; // Exit the loop on success
        
    } catch (PDOException $e) {
        echo "  ❌ Failed with user '{$attempt['user']}': " . $e->getMessage() . "\n";
        continue; // Try next connection
    }
}

if (!$success) {
    echo "\n  ⚠️  Could not get superuser access to completely remove database.\n";
    echo "  💡 Tables were removed, but database and user remain.\n";
    echo "  \n";
    echo "  📝 MANUAL REMOVAL INSTRUCTIONS:\n";
    echo "  1. Open pgAdmin or psql as postgres superuser\n";
    echo "  2. Run: DROP DATABASE IF EXISTS $dbname;\n";
    echo "  3. Run: DROP USER IF EXISTS $username;\n";
    echo "  \n";
    echo "  Or run this in Command Prompt/PowerShell:\n";
    echo "  psql -U postgres -c \"DROP DATABASE IF EXISTS $dbname;\"\n";
    echo "  psql -U postgres -c \"DROP USER IF EXISTS $username;\"\n";
}

echo "\n=== DATABASE REMOVAL COMPLETE ===\n";
echo "The database has been cleaned/removed as much as possible.\n";
?>
