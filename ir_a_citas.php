<?php
// Redirección simple para probar si Citas.php es accesible

// Registrar el intento
file_put_contents('redirect_log.txt', date('Y-m-d H:i:s') . " - Intento de redirección a Citas.php\n", FILE_APPEND);

// Redirigir
header("Location: Citas.php");
exit;
?>
