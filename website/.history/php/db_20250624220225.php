<?php
// db.php
// MySQLi-Verbindungsdatei – passe die Werte für Host, User, Passwort und DB-Name an
$dbHost = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "pflegepro";
//$dbPort = 3306;

// Verbindung aufbauen
$mysqli = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

// Prüfen, ob's geklappt hat
if (mysqli_connect_error()) {
    die("Database connection failed: " . mysqli_connect_error());
}

// UTF-8 einstellen
$mysqli->set_charset('utf8mb4');

?>