<?php
require_once "config.php";

try {
    // Primero respaldamos el logo actual si existe
    $stmt = $conn->query("SELECT logo FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    $logo_actual = null;
    
    if (!empty($config['logo'])) {
        $ruta_logo = 'uploads/config/' . $config['logo'];
        if (file_exists($ruta_logo)) {
            $logo_actual = file_get_contents($ruta_logo);
        }
    }

    // Modificar el campo logo para almacenar la imagen
    $sql = "ALTER TABLE configuracion MODIFY COLUMN logo MEDIUMBLOB";
    $conn->exec($sql);

    // Si teníamos un logo, lo guardamos en el nuevo formato
    if ($logo_actual !== null) {
        $stmt = $conn->prepare("UPDATE configuracion SET logo = ? WHERE id = 1");
        $stmt->execute([$logo_actual]);
    }

    echo "Campo logo actualizado exitosamente a MEDIUMBLOB.\n";
    
    // Actualizar el código de configuracion.php para manejar el nuevo formato
    $config_content = file_get_contents('configuracion.php');
    
    if ($config_content !== false) {
        // Reemplazar el código que maneja el logo
        $config_content = str_replace(
            'move_uploaded_file($temp_archivo, $directorioUpload . $fotoNombre);',
            '$logoData = file_get_contents($temp_archivo);
            $stmt = $conn->prepare("UPDATE configuracion SET logo = ? WHERE id = 1");
            $stmt->execute([$logoData]);',
            $config_content
        );
        
        file_put_contents('configuracion.php', $config_content);
    }

    echo "Archivos actualizados exitosamente.\n";
} catch(PDOException $e) {
    echo "Error al modificar la tabla: " . $e->getMessage() . "\n";
}
?>
