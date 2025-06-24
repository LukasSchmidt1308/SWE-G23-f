<?php
// setup_check.php - Automatically check and create database if needed

function checkAndSetupDatabase($pdo) {
    try {
        // Check if main tables exist
        $result = $pdo->query("SELECT COUNT(*) FROM information_schema.tables 
                              WHERE table_schema = 'public' 
                              AND table_name IN ('station', 'benutzer', 'betreuer', 'patient')");
        $tableCount = $result->fetchColumn();
        
        // Also check if users exist
        $userCount = 0;
        if ($tableCount >= 4) {
            try {
                $result = $pdo->query("SELECT COUNT(*) FROM benutzer");
                $userCount = $result->fetchColumn();
            } catch (Exception $e) {
                $userCount = 0; // Table exists but might be empty or have issues
            }
        }
        
        if ($tableCount < 4 || $userCount == 0) {
            // Tables don't exist or no users, run setup
            echo "<div style='background: #f0f8ff; padding: 20px; margin: 20px; border: 1px solid #4CAF50; border-radius: 5px;'>";
            echo "<h3>🔧 Erstmalige Datenbank-Einrichtung...</h3>";
            
            // Include and run datenbank.php
            ob_start();
            include 'php/datenbank.php';
            $output = ob_get_clean();
            
            echo $output;
            echo "<p><strong>✅ Setup abgeschlossen!</strong> Sie können sich jetzt anmelden.</p>";
            echo "</div>";
            
            return true; // Setup was run
        }
        
        return false; // No setup needed
        
    } catch (Exception $e) {
        echo "<div style='background: #ffe6e6; padding: 20px; margin: 20px; border: 1px solid #ff4444; border-radius: 5px;'>";
        echo "<h3>❌ Datenbank-Fehler</h3>";
        echo "<p>Fehler beim Überprüfen der Datenbank: " . $e->getMessage() . "</p>";
        echo "</div>";
        return false;
    }
}
?>
