<?php
// Debug session configuration and behavior
session_start();

echo "<h2>PHP Session Configuration</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . session_status() . "<br>";
echo "Session Save Path: " . session_save_path() . "<br>";
echo "Session Name: " . session_name() . "<br>";
echo "Cookie Lifetime: " . ini_get('session.cookie_lifetime') . "<br>";
echo "Cookie Path: " . ini_get('session.cookie_path') . "<br>";
echo "Cookie Domain: " . ini_get('session.cookie_domain') . "<br>";
echo "Cookie Secure: " . ini_get('session.cookie_secure') . "<br>";
echo "Cookie HttpOnly: " . ini_get('session.cookie_httponly') . "<br>";

echo "<h2>Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Cookie Data</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";
?>
