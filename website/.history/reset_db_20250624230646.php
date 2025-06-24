<?php
require 'php/db.php';

try {
    // Drop all tables in correct order (foreign keys)
    $tables = ['warnhinweis', 'gesundheitsparameter', 'patient', 'betreuer', 'benutzer', 'station'];
    
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS $table CASCADE");
        echo "Dropped table: $table\n";
    }
    
    // Also drop the trigger function
    $pdo->exec("DROP FUNCTION IF EXISTS check_max_patients() CASCADE");
    echo "Dropped function: check_max_patients\n";
    
    echo "\n✅ All tables and functions deleted successfully!\n";
    echo "Now visit index.php in your browser to test automatic setup.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
