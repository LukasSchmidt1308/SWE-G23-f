<?php
echo "PHP Version: " . phpversion() . "<br>";

if (extension_loaded('mysqli')) {
    echo "✓ MySQLi extension is loaded<br>";
} else {
    echo "✗ MySQLi extension is NOT loaded<br>";
}

if (extension_loaded('pdo_mysql')) {
    echo "✓ PDO MySQL extension is loaded<br>";
} else {
    echo "✗ PDO MySQL extension is NOT loaded<br>";
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
