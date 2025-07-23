<?php
// Archivos a verificar
$files = [
    'Citas.php',
    'sidebar.php'
];

echo "Verificando sintaxis de archivos PHP...\n";

foreach ($files as $file) {
    echo "Analizando $file: ";
    
    // Ejecutar php -l para verificar la sintaxis
    $output = [];
    $returnCode = 0;
    exec("php -l $file 2>&1", $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "OK - No se encontraron errores de sintaxis\n";
    } else {
        echo "ERROR - Se encontraron problemas:\n";
        echo implode("\n", $output) . "\n";
    }
}
?>
