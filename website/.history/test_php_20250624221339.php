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
$dbUser = "root";
$dbPass = "";
$dbName = "pflegepro";

$mysqli = @mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

if (mysqli_connect_error()) {
    echo "✗ Database connection failed: " . mysqli_connect_error() . "<br>";
} else {
    echo "✓ Database connection successful<br>";
    mysqli_close($mysqli);
}
?>
