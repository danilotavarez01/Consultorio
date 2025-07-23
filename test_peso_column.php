<?php
require_once 'config.php';
try {
    $conn->query('SELECT peso FROM historial_medico LIMIT 1');
    echo 'Columna peso existe y es accesible';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
