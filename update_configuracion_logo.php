<?php
require_once "config.php";

try {
    // Modificar la columna logo para almacenar LONGBLOB
    $sql = "ALTER TABLE configuracion MODIFY COLUMN logo LONGBLOB";
    $conn->exec($sql);
    
    // Verificar si hay un logo existente en el directorio
    $directorio_logo = 'uploads/config/';
    if (file_exists($directorio_logo . 'logo.png')) {
        // Leer el logo existente y guardarlo en la base de datos
        $logoData = file_get_contents($directorio_logo . 'logo.png');
        $stmt = $conn->prepare("UPDATE configuracion SET logo = ? WHERE id = 1");
        $stmt->execute([$logoData]);
        
        // Eliminar el archivo físico ya que ahora está en la base de datos
        unlink($directorio_logo . 'logo.png');
    }
    
    echo "Campo logo actualizado exitosamente a LONGBLOB.\n";
    if (isset($logoData)) {
        echo "El logo existente ha sido convertido y almacenado en la base de datos.\n";
    }
} catch(PDOException $e) {
    echo "Error al modificar la tabla: " . $e->getMessage() . "\n";
}
?>
