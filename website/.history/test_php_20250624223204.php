<?php
echo "PHP Version: " . phpversion() . "<br>";

if (extension_loaded('pdo')) {
    echo "✓ PDO extension is loaded<br>";
} else {
    echo "✗ PDO extension is NOT loaded<br>";
}

if (extension_loaded('pdo_pgsql')) {
    echo "✓ PDO PostgreSQL extension is loaded<br>";
} else {
    echo "✗ PDO PostgreSQL extension is NOT loaded<br>";
}

// Test database connection
$dbHost = "localhost";
$dbUser = "postgres";
$dbPass = "your_password_here";  // PUT YOUR POSTGRESQL PASSWORD HERE
$dbName = "postgres";  // Use default 'postgres' database first
$dbPort = "5432";

try {
    $pdo = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
    echo "✓ PostgreSQL connection successful<br>";
} catch (PDOException $e) {
    echo "✗ PostgreSQL connection failed: " . $e->getMessage() . "<br>";
}
?>
