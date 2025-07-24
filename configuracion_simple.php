<?php
/**
 * Versión simplificada de configuracion.php para debug
 */

require_once 'session_config.php';
session_start();
require_once "config.php";

// Verificar usuario admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

if($_SESSION["username"] !== "admin"){
    header("location: unauthorized.php");
    exit;
}

echo "<h2>Configuración - Versión Simplificada</h2>";

// Intentar cargar configuración
$config = null;
$mensaje = '';

try {
    // Verificar tabla
    $stmt = $conn->query("SHOW TABLES LIKE 'configuracion'");
    if ($stmt->rowCount() == 0) {
        throw new Exception("Tabla 'configuracion' no existe. <a href='reparar_configuracion.php'>Ejecutar reparación</a>");
    }
    
    // Cargar configuración
    $stmt = $conn->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$config) {
        throw new Exception("No existe configuración con id=1. <a href='reparar_configuracion.php'>Ejecutar reparación</a>");
    }
    
    $mensaje = '<div class="alert alert-success">✅ Configuración cargada correctamente</div>';
    
} catch (Exception $e) {
    $mensaje = '<div class="alert alert-danger">❌ Error: ' . $e->getMessage() . '</div>';
    // Configuración por defecto
    $config = [
        'nombre_consultorio' => 'Consultorio Médico',
        'medico_nombre' => 'Dr. Médico',
        'email_contacto' => '',
        'telefono' => '',
        'duracion_cita' => 30,
        'hora_inicio' => '09:00',
        'hora_fin' => '18:00'
    ];
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $sql = "UPDATE configuracion SET 
                nombre_consultorio = ?, 
                medico_nombre = ?, 
                email_contacto = ?, 
                telefono = ?, 
                duracion_cita = ?,
                hora_inicio = ?,
                hora_fin = ?
                WHERE id = 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $_POST['nombre_consultorio'],
            $_POST['medico_nombre'],
            $_POST['email_contacto'],
            $_POST['telefono'],
            $_POST['duracion_cita'],
            $_POST['hora_inicio'],
            $_POST['hora_fin']
        ]);
        
        $mensaje = '<div class="alert alert-success">✅ Configuración actualizada correctamente</div>';
        
        // Recargar configuración
        $stmt = $conn->query("SELECT * FROM configuracion WHERE id = 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        $mensaje = '<div class="alert alert-danger">❌ Error al actualizar: ' . $e->getMessage() . '</div>';
    }
}

// Función helper
function getValue($config, $key, $default = '') {
    return isset($config[$key]) && $config[$key] !== null && $config[$key] !== '' ? $config[$key] : $default;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración Simplificada</title>
    <link rel="stylesheet" href="assets/libs/bootstrap.min.css">
    <link rel="stylesheet" href="assets/libs/fontawesome.local.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Configuración del Sistema</h1>
        
        <?php echo $mensaje; ?>
        
        <!-- Debug Info -->
        <div class="alert alert-info">
            <strong>Debug Info:</strong><br>
            Config loaded: <?php echo $config ? 'Yes' : 'No'; ?><br>
            Config type: <?php echo gettype($config); ?><br>
            <?php if ($config): ?>
                Fields count: <?php echo count($config); ?><br>
                nombre_consultorio: "<?php echo htmlspecialchars($config['nombre_consultorio'] ?? 'NULL'); ?>"<br>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label>Nombre del Consultorio</label>
                        <input type="text" 
                               name="nombre_consultorio" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars(getValue($config, 'nombre_consultorio', 'Consultorio Médico')); ?>" 
                               required>
                        <small class="text-muted">Valor actual: "<?php echo htmlspecialchars($config['nombre_consultorio'] ?? 'NULL'); ?>"</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Nombre del Médico</label>
                        <input type="text" 
                               name="medico_nombre" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars(getValue($config, 'medico_nombre', 'Dr. Médico')); ?>" 
                               required>
                        <small class="text-muted">Valor actual: "<?php echo htmlspecialchars($config['medico_nombre'] ?? 'NULL'); ?>"</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Email de Contacto</label>
                        <input type="email" 
                               name="email_contacto" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars(getValue($config, 'email_contacto', '')); ?>">
                        <small class="text-muted">Valor actual: "<?php echo htmlspecialchars($config['email_contacto'] ?? 'NULL'); ?>"</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="tel" 
                               name="telefono" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars(getValue($config, 'telefono', '')); ?>">
                        <small class="text-muted">Valor actual: "<?php echo htmlspecialchars($config['telefono'] ?? 'NULL'); ?>"</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Duración de Cita (min)</label>
                                <input type="number" 
                                       name="duracion_cita" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars(getValue($config, 'duracion_cita', '30')); ?>" 
                                       required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Hora Inicio</label>
                                <input type="time" 
                                       name="hora_inicio" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars(getValue($config, 'hora_inicio', '09:00')); ?>" 
                                       required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Hora Fin</label>
                                <input type="time" 
                                       name="hora_fin" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars(getValue($config, 'hora_fin', '18:00')); ?>" 
                                       required>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    <a href="configuracion.php" class="btn btn-secondary">Ir a Configuración Completa</a>
                </form>
            </div>
        </div>
        
        <div class="mt-4">
            <h4>Enlaces de Debug:</h4>
            <a href="debug_configuracion_detallado.php" class="btn btn-info btn-sm">Debug Detallado</a>
            <a href="reparar_configuracion.php" class="btn btn-warning btn-sm">Reparar Configuración</a>
            <a href="configuracion.php?debug=1" class="btn btn-success btn-sm">Configuración Original</a>
        </div>
    </div>
</body>
</html>
