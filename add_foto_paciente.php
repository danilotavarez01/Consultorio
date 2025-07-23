<?php
require_once "config.php";

try {
    // Verificar si la columna ya existe
    $stmt = $conn->query("SHOW COLUMNS FROM pacientes LIKE 'foto'");
    $columnaExiste = $stmt->rowCount() > 0;
    
    if ($columnaExiste) {
        echo "La columna 'foto' ya existe en la tabla pacientes.\n";
    } else {
        // Añadir la columna foto a la tabla pacientes
        $sql = "ALTER TABLE pacientes ADD COLUMN foto VARCHAR(255) NULL COMMENT 'Ruta a la imagen del paciente'";
        $conn->exec($sql);
        echo "La columna 'foto' ha sido añadida exitosamente a la tabla pacientes.\n";
        
        // Verificar que la columna se haya añadido correctamente
        $stmt = $conn->query("SHOW COLUMNS FROM pacientes LIKE 'foto'");
        $columnaExisteAhora = $stmt->rowCount() > 0;
        if ($columnaExisteAhora) {
            echo "Verificación exitosa: la columna 'foto' está presente en la tabla.\n";
        } else {
            echo "ERROR: La columna 'foto' no se añadió correctamente a pesar de no haber errores.\n";
        }
        
        // Crear directorio para las fotos si no existe
        $directorioFotos = __DIR__ . '/uploads/pacientes';
        if (!file_exists($directorioFotos)) {
            if (mkdir($directorioFotos, 0755, true)) {
                echo "Se ha creado el directorio para almacenar las fotos: $directorioFotos\n";
            } else {
                echo "No se pudo crear el directorio para fotos. Por favor, cree manualmente: $directorioFotos\n";
            }
        } else {
            echo "El directorio para fotos ya existe: $directorioFotos\n";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
