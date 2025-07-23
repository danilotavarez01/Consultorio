<?php
require_once "config.php";
$plain = '820416Dts';  // Your test password
$hash = password_hash($plain, PASSWORD_DEFAULT);
echo "Generated hash: $hash<br>";

$test = password_verify($plain, $hash);
echo "Verification: " . ($test ? '✅ Success' : '❌ Failure');
?>