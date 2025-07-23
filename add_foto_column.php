<?php
require_once "config.php";

try {
    // Verificamos si ya existe la columna foto
    $stmt = $conn->query("SHOW COLUMNS FROM pacientes LIKE 'foto'");
    $column_exists = ($stmt->rowCount() > 0);
    
    if (!$column_exists) {
        // Agregar el campo foto a la tabla pacientes si no existe
        $sql = "ALTER TABLE pacientes ADD COLUMN foto VARCHAR(255)";
        $conn->exec($sql);
        echo "Campo 'foto' agregado exitosamente a la tabla 'pacientes'.\n";
    } else {
        echo "El campo 'foto' ya existe en la tabla 'pacientes'.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
