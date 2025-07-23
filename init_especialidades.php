<?php
require_once "config.php";

try {
    $conn->beginTransaction();

    // 1. Crear tabla de especialidades
    require_once "create_especialidades_table.php";

    // 2. Agregar campo de especialidad a usuarios
    require_once "add_especialidad_to_usuarios.php";

    // 3. Agregar campos adicionales a historial_medico
    require_once "add_campos_adicionales.php";

    $conn->commit();
    echo "Inicialización completada exitosamente.\n";
} catch(PDOException $e) {
    $conn->rollBack();
    echo "Error durante la inicialización: " . $e->getMessage() . "\n";
}
?>
