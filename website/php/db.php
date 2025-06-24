<?php
// db.php
// MySQLi-Verbindungsdatei – passe die Werte für Host, User, Passwort und DB-Name an
$dbHost = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "pflegepro";
$mysqli = "";
//$dbPort = 3306;

// Verbindung aufbauen
$mysqli = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

// Prüfen, ob’s geklappt hat
if ($mysqli) {
    echo"You are connected";
}
  else{
    echo"could not connected";
  }

// UTF-8 einstellen
//$conn->set_charset('utf8mb4');


?>

