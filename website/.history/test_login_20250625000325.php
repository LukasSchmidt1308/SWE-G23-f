<?php
// Test login as patient
echo "<h2>Testing Login as Patient</h2>";

// Make a POST request to login.php
$postdata = http_build_query([
    'username' => 'patient',
    'password' => 'patient'
]);

$opts = [
    'http' => [
        'method'  => 'POST',
        'header'  => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $postdata
    ]
];

$context = stream_context_create($opts);
$result = file_get_contents('http://localhost:8080/php/login.php', false, $context);

echo "Login result:<br>";
echo "<pre>" . htmlspecialchars($result) . "</pre>";

// Check if we got redirected by looking at response headers
echo "<h3>Response Headers:</h3>";
echo "<pre>";
print_r($http_response_header);
echo "</pre>";
?>
