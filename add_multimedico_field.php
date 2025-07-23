<?php
require_once "config.php";

try {
    // Agregar el campo multi_medico a la tabla configuracion
    $sql = "ALTER TABLE configuracion
            ADD COLUMN multi_medico BOOLEAN DEFAULT FALSE
            AFTER medico_nombre";
    
    $conn->exec($sql);
    
    echo "Campo 'multi_medico' agregado correctamente a la tabla de configuración.<br>";
    
    // Verificar si el campo se agregó correctamente
    $stmt = $conn->query("SHOW COLUMNS FROM configuracion LIKE 'multi_medico'");
    
    if ($stmt->rowCount() > 0) {
        echo "Verificación exitosa: El campo 'multi_medico' existe en la tabla.<br>";
    } else {
        echo "Error: No se pudo verificar la existencia del campo 'multi_medico'.<br>";
    }
    
} catch(PDOException $e) {
    echo "Error al agregar el campo 'multi_medico': " . $e->getMessage() . "<br>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualización de Configuración</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3>Actualización de Configuración</h3>
            </div>
            <div class="card-body">
                <p>Se ha intentado agregar el campo <strong>multi_medico</strong> a la tabla de configuración.</p>
                <p>Este campo permitirá activar la funcionalidad para múltiples médicos en el consultorio.</p>
                
                <div class="alert alert-info">
                    <h5>¿Qué significa Multi-Médico?</h5>
                    <p>Cuando está activado:</p>
                    <ul>
                        <li>Permite asignar consultas y citas a diferentes médicos</li>
                        <li>Habilita la gestión de horarios por médico</li>
                        <li>Activa filtros adicionales por médico en reportes</li>
                    </ul>
                </div>
                
                <div class="mt-4">
                    <a href="configuracion.php" class="btn btn-primary">Ir a Configuración</a>
                    <a href="index.php" class="btn btn-secondary">Volver al Inicio</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
