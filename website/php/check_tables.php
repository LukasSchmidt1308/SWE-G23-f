<?php
require_once 'db.php';
$stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
foreach($stmt->fetchAll() as $table) {
    echo $table['table_name'] . PHP_EOL;
}
?>
