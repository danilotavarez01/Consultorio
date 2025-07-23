<?php
require_once "config.php";

try {
    // Verificar si la columna especialidad_id existe
    $result = $conn->query("SHOW COLUMNS FROM configuracion LIKE 'especialidad_id'");
    if ($result->rowCount() == 0) {
        // Agregar la columna especialidad_id
        $sql = "ALTER TABLE configuracion 
                ADD COLUMN especialidad_id INT,
                ADD FOREIGN KEY (especialidad_id) REFERENCES especialidades(id)";
        $conn->exec($sql);
        echo "Columna especialidad_id agregada a la tabla configuracion.\n";

        // Establecer Medicina General como especialidad por defecto
        $sql = "UPDATE configuracion SET especialidad_id = (SELECT id FROM especialidades WHERE codigo = 'MG')";
        $conn->exec($sql);
        echo "Especialidad por defecto establecida como Medicina General.\n";
    } else {
        echo "La columna especialidad_id ya existe en la tabla configuracion.\n";
    }

    echo "Actualización completada exitosamente.\n";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
