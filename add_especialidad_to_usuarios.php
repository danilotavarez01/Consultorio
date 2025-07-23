<?php
require_once "config.php";

try {
    // Agregar campo de especialidad a la tabla usuarios
    $sql = "ALTER TABLE usuarios 
            ADD COLUMN especialidad_id INT NULL,
            ADD FOREIGN KEY (especialidad_id) REFERENCES especialidades(id)";
    
    $conn->exec($sql);
    
    echo "Campo de especialidad agregado exitosamente a la tabla usuarios.\n";
} catch(PDOException $e) {
    // Si el error es porque la columna ya existe, lo ignoramos
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "El campo de especialidad ya existe en la tabla usuarios.\n";
    } else {
        echo "Error al agregar el campo de especialidad: " . $e->getMessage() . "\n";
    }
}
?>
