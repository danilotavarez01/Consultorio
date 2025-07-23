<?php
require_once "config.php";

try {
    // Agregar campo para almacenar campos adicionales según la especialidad
    $sql = "ALTER TABLE historial_medico 
            ADD COLUMN campos_adicionales JSON NULL,
            ADD COLUMN doctor_id INT NULL,
            ADD FOREIGN KEY (doctor_id) REFERENCES usuarios(id)";
    
    $conn->exec($sql);
    
    echo "Campo campos_adicionales agregado exitosamente a la tabla historial_medico.\n";
} catch(PDOException $e) {
    // Si el error es porque la columna ya existe, lo ignoramos
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "El campo campos_adicionales ya existe en la tabla historial_medico.\n";
    } else {
        echo "Error al agregar el campo: " . $e->getMessage() . "\n";
    }
}
?>
