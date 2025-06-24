<?php
require_once 'php/db.php';

echo "<h2>Admin Debug</h2>";
echo "<h3>POST Data:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h3>GET Data:</h3>";
echo "<pre>";
print_r($_GET);
echo "</pre>";

echo "<h3>Test Form:</h3>";
?>
<form method="post" action="php/admin.php">
    <input name="name" type="text" placeholder="Name" required><br>
    <input name="username" type="text" placeholder="Benutzername" required><br>
    <input name="password" type="password" placeholder="Passwort" required><br>
    <input name="email" type="email" placeholder="E-Mail" required><br>
    <input name="telefon" type="text" placeholder="Telefonnummer" required><br>
    <select name="station_id" required>
        <option value="">Station wählen</option>
        <option value="1">Station A</option>
    </select><br>
    <input name="max_patienten" type="number" value="24"><br>
    <button type="submit">Test Betreuer anlegen</button>
</form>
