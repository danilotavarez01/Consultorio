<?php
require_once "config.php";

try {
    $conn->beginTransaction();

    echo "Iniciando actualización de la estructura de la base de datos...\n\n";

    echo "1. Creando estructura de especialidades...\n";
    require_once "create_estructura_especialidades.php";
    echo "\n";

    echo "2. Actualizando tabla de historial médico...\n";
    require_once "update_historial_medico.php";
    echo "\n";

    $conn->commit();
    echo "¡Actualización completada exitosamente!\n";
} catch(PDOException $e) {
    $conn->rollBack();
    echo "Error durante la actualización: " . $e->getMessage() . "\n";
}
?>
