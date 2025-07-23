<?php
require_once "config.php";

try {
    // Verificar si la columna ya existe
    $checkColumn = $conn->query("SHOW COLUMNS FROM configuracion LIKE 'whatsapp_server'");
    
    if ($checkColumn->rowCount() == 0) {
        // La columna no existe, así que la creamos
        $sql = "ALTER TABLE configuracion ADD COLUMN whatsapp_server VARCHAR(255) DEFAULT 'https://api.whatsapp.com'";
        $conn->exec($sql);
        
        echo "Campo 'whatsapp_server' agregado correctamente a la tabla de configuración.\n";
    } else {
        echo "El campo 'whatsapp_server' ya existe en la tabla.\n";
    }
} catch(PDOException $e) {
    echo "Error al modificar la tabla: " . $e->getMessage() . "\n";
}
?>
