<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Intentar incluir y ejecutar una parte básica de Citas.php
ob_start();
try {
    include 'Citas.php';
    echo "Citas.php cargado exitosamente!\n";
} catch (Exception $e) {
    echo "Error al cargar Citas.php: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
} catch (Error $e) {
    echo "Error PHP en Citas.php: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
}
$content = ob_get_clean();
echo $content;
?>
