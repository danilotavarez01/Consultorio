<?php
require_once 'session_config.php';
session_start();
require_once "config.php";
require_once "permissions.php";

// Función para verificar la conexión y reconectar si es necesario
function verificarConexion($conn) {
    try {
        $conn->query("SELECT 1");
        return $conn;
    } catch (PDOException $e) {
        // Si hay error, intentar reconectar
        try {
            $conn = new PDO(
                "mysql:host=" . DB_SERVER . 
                ";port=" . DB_PORT . 
                ";dbname=" . DB_NAME . 
                ";charset=utf8", 
                DB_USER, 
                DB_PASS, 
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
            return $conn;
        } catch (PDOException $e) {
            throw new PDOException("Error de conexión: " . $e->getMessage());
        }
    }
}

// Función para obtener valor de configuración con default
function getConfigValue($config, $key, $default = '') {
    return isset($config[$key]) && $config[$key] !== null ? $config[$key] : $default;
}

// Verificar si el usuario está logueado
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Verificar si es usuario admin
if($_SESSION["username"] !== "admin"){
    header("location: unauthorized.php");
    exit;
}

$mensaje = '';
$logo_actual = '';
$directorio_logo = 'uploads/config/';
$config = null;

// Obtener configuración actual
try {
    // Verificar si la tabla existe
    $stmt = $conn->query("SHOW TABLES LIKE 'configuracion'");
    if ($stmt->rowCount() == 0) {
        throw new Exception("La tabla 'configuracion' no existe");
    }
    
    // Verificar si existe el registro con id = 1
    $stmt = $conn->query("SELECT COUNT(*) as count FROM configuracion WHERE id = 1");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($count['count'] == 0) {
        // Insertar registro por defecto
        $sql_insert = "INSERT INTO configuracion (
            id, nombre_consultorio, medico_nombre, duracion_cita, 
            hora_inicio, hora_fin, dias_laborables, intervalo_citas,
            moneda, zona_horaria, formato_fecha, idioma, tema_color,
            mostrar_alertas_stock, multi_medico, whatsapp_server
        ) VALUES (
            1, 'Consultorio Médico', 'Dr. Médico', 30, 
            '09:00:00', '18:00:00', '1,2,3,4,5', 30,
            'RD$', 'America/Santo_Domingo', 'Y-m-d', 'es', 'light',
            1, 0, 'https://api.whatsapp.com'
        )";
        $conn->exec($sql_insert);
    }
    
    // Ahora obtener la configuración
    $stmt = $conn->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$config) {
        throw new Exception("No se pudo cargar la configuración");
    }
    
} catch(PDOException $e) {
    $mensaje = '<div class="alert alert-danger">Error de base de datos: ' . $e->getMessage() . '</div>';
    $config = []; // Array vacío para evitar errores
} catch(Exception $e) {
    $mensaje = '<div class="alert alert-danger">Error al cargar la configuración: ' . $e->getMessage() . '</div>';
    $config = []; // Array vacío para evitar errores
}

// Verificar si existe un logo
if (file_exists($directorio_logo . 'logo.png')) {
    $logo_actual = $directorio_logo . 'logo.png';
}

// Procesar el formulario cuando se envía
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    switch($_POST['action']) {
        case 'update_settings':
            try {
                // Verificar la conexión antes de proceder
                $conn = verificarConexion($conn);                $sql = "UPDATE configuracion SET 
                        nombre_consultorio = :nombre,
                        email_contacto = :email,
                        duracion_cita = :duracion,
                        hora_inicio = :inicio,
                        hora_fin = :fin,
                        especialidad_id = :especialidad_id,
                        dias_laborables = :dias_laborables,
                        intervalo_citas = :intervalo,
                        require_https = :https,
                        modo_mantenimiento = :mantenimiento,
                        telefono = :telefono,
                        direccion = :direccion,
                        moneda = :moneda,
                        zona_horaria = :zona_horaria,
                        formato_fecha = :formato_fecha,
                        idioma = :idioma,
                        tema_color = :tema_color,
                        mostrar_alertas_stock = :alertas_stock,
                        notificaciones_email = :notificaciones_email,
                        medico_nombre = :medico_nombre,
                        multi_medico = :multi_medico,
                        whatsapp_server = :whatsapp_server,
                        updated_by = :usuario,
                        updated_at = CURRENT_TIMESTAMP
                        WHERE id = 1";                  $params = [
                    ':nombre' => $_POST['clinic_name'],
                    ':email' => $_POST['contact_email'],
                    ':duracion' => $_POST['duracion_cita'],
                    ':inicio' => $_POST['hora_inicio'],
                    ':fin' => $_POST['hora_fin'],
                    ':especialidad_id' => $_POST['especialidad_id'],
                    ':dias_laborables' => implode(',', $_POST['dias_laborables'] ?? ['1','2','3','4','5']),
                    ':intervalo' => $_POST['intervalo_citas'],
                    ':https' => isset($_POST['require_https']) ? 1 : 0,
                    ':medico_nombre' => $_POST['medico_nombre'],
                    ':multi_medico' => isset($_POST['multi_medico']) ? 1 : 0,
                    ':mantenimiento' => isset($_POST['maintenance_mode']) ? 1 : 0,
                    ':telefono' => $_POST['telefono'],
                    ':direccion' => $_POST['direccion'],
                    ':moneda' => $_POST['moneda'],
                    ':zona_horaria' => $_POST['zona_horaria'],
                    ':formato_fecha' => $_POST['formato_fecha'],
                    ':idioma' => $_POST['idioma'],
                    ':tema_color' => $_POST['tema_color'],
                    ':alertas_stock' => isset($_POST['mostrar_alertas_stock']) ? 1 : 0,
                    ':notificaciones_email' => isset($_POST['notificaciones_email']) ? 1 : 0,
                    ':whatsapp_server' => $_POST['whatsapp_server'],
                    ':usuario' => $_SESSION['username']
                ];
                
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                
                $mensaje = '<div class="alert alert-success">Configuración actualizada correctamente.</div>';

                // Procesar el logo si se ha subido uno nuevo
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
                    $tipo_archivo = $_FILES['logo']['type'];
                    $tamano_archivo = $_FILES['logo']['size'];
                    $temp_archivo = $_FILES['logo']['tmp_name'];
                    
                    // Validar el tamaño máximo (5MB)
                    if ($tamano_archivo > 5242880) {
                        $mensaje .= '<div class="alert alert-danger">El archivo es demasiado grande. Máximo 5MB.</div>';
                    }
                    // Verificar el tipo de archivo
                    elseif (!in_array($tipo_archivo, ['image/jpeg', 'image/png'])) {
                        $mensaje .= '<div class="alert alert-danger">Solo se permiten archivos JPEG o PNG.</div>';
                    }
                    else {
                        try {
                            // Verificar la conexión
                            $conn = verificarConexion($conn);

                            // Procesar y optimizar la imagen antes de guardarla
                            $imagen_original = null;
                            if ($tipo_archivo === 'image/jpeg') {
                                $imagen_original = imagecreatefromjpeg($temp_archivo);
                            } elseif ($tipo_archivo === 'image/png') {
                                $imagen_original = imagecreatefrompng($temp_archivo);
                            }

                            if ($imagen_original) {
                                // Obtener dimensiones originales
                                $ancho_original = imagesx($imagen_original);
                                $alto_original = imagesy($imagen_original);

                                // Calcular nuevas dimensiones (máximo 800px de ancho o alto)
                                $max_dimension = 800;
                                if ($ancho_original > $max_dimension || $alto_original > $max_dimension) {
                                    if ($ancho_original > $alto_original) {
                                        $ancho_nuevo = $max_dimension;
                                        $alto_nuevo = intval($alto_original * ($max_dimension / $ancho_original));
                                    } else {
                                        $alto_nuevo = $max_dimension;
                                        $ancho_nuevo = intval($ancho_original * ($max_dimension / $alto_original));
                                    }
                                } else {
                                    $ancho_nuevo = $ancho_original;
                                    $alto_nuevo = $alto_original;
                                }

                                // Crear imagen redimensionada
                                $imagen_redimensionada = imagecreatetruecolor($ancho_nuevo, $alto_nuevo);

                                // Mantener transparencia para PNG
                                if ($tipo_archivo === 'image/png') {
                                    imagealphablending($imagen_redimensionada, false);
                                    imagesavealpha($imagen_redimensionada, true);
                                }

                                // Redimensionar
                                imagecopyresampled(
                                    $imagen_redimensionada, 
                                    $imagen_original,
                                    0, 0, 0, 0,
                                    $ancho_nuevo, $alto_nuevo,
                                    $ancho_original, $alto_original
                                );

                                // Guardar la imagen optimizada en memoria
                                ob_start();
                                if ($tipo_archivo === 'image/jpeg') {
                                    imagejpeg($imagen_redimensionada, null, 85);
                                } else {
                                    imagepng($imagen_redimensionada, null, 8);
                                }
                                $logo_contenido = ob_get_clean();

                                // Liberar memoria
                                imagedestroy($imagen_original);
                                imagedestroy($imagen_redimensionada);

                                // Iniciar transacción
                                $conn->beginTransaction();

                                // Actualizar el logo
                                $stmt = $conn->prepare("UPDATE configuracion SET logo = :logo WHERE id = 1");
                                $stmt->bindParam(':logo', $logo_contenido, PDO::PARAM_LOB);
                                $stmt->execute();

                                // Confirmar la transacción
                                $conn->commit();
                                $mensaje .= '<div class="alert alert-success">Logo actualizado correctamente.</div>';
                            } else {
                                throw new Exception("No se pudo procesar la imagen.");
                            }

                        } catch(Exception $e) {
                            if ($conn->inTransaction()) {
                                $conn->rollBack();
                            }
                            $mensaje .= '<div class="alert alert-danger">Error al procesar el logo: ' . $e->getMessage() . '</div>';
                        }
                    }
                }
                
                // Recargar la configuración
                $conn = verificarConexion($conn);
                $stmt = $conn->query("SELECT * FROM configuracion WHERE id = 1");
                $config = $stmt->fetch(PDO::FETCH_ASSOC);

            } catch(PDOException $e) {
                $mensaje = '<div class="alert alert-danger">Error al actualizar la configuración: ' . $e->getMessage() . '</div>';
            }
            break;
            
        case 'delete_logo':
            try {
                $conn = verificarConexion($conn);
                $stmt = $conn->prepare("UPDATE configuracion SET logo = NULL WHERE id = 1");
                $stmt->execute();
                $mensaje = '<div class="alert alert-success">Logo eliminado correctamente.</div>';
            } catch(PDOException $e) {
                $mensaje = '<div class="alert alert-danger">Error al eliminar el logo: ' . $e->getMessage() . '</div>';
            }
            break;
    }
}

// Obtener configuración actual usando la nueva función de verificación
try {
    $conn = verificarConexion($conn);
    
    // Verificar si existe el registro
    $stmt = $conn->query("SELECT COUNT(*) as count FROM configuracion WHERE id = 1");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($count['count'] == 0) {
        // Insertar registro por defecto si no existe
        $sql_insert = "INSERT INTO configuracion (
            id, nombre_consultorio, medico_nombre, duracion_cita, 
            hora_inicio, hora_fin, dias_laborables, intervalo_citas,
            moneda, zona_horaria, formato_fecha, idioma, tema_color,
            mostrar_alertas_stock, multi_medico, whatsapp_server
        ) VALUES (
            1, 'Consultorio Médico', 'Dr. Médico', 30, 
            '09:00:00', '18:00:00', '1,2,3,4,5', 30,
            'RD$', 'America/Santo_Domingo', 'Y-m-d', 'es', 'light',
            1, 0, 'https://api.whatsapp.com'
        )";
        $conn->exec($sql_insert);
    }
    
    $stmt = $conn->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$config) {
        throw new Exception("No se pudo cargar la configuración después de la actualización");
    }
    
} catch(PDOException $e) {
    $mensaje .= '<div class="alert alert-danger">Error de base de datos al recargar: ' . $e->getMessage() . '</div>';
    // Mantener el $config existente si hay error
} catch(Exception $e) {
    $mensaje .= '<div class="alert alert-danger">Error al recargar la configuración: ' . $e->getMessage() . '</div>';
    // Mantener el $config existente si hay error
}

// Verificar si existe un logo
if (file_exists($directorio_logo . 'logo.png')) {
    $logo_actual = $directorio_logo . 'logo.png';
}

// Cargar configuración final para el formulario (por si no se cargó antes)
if (!$config || empty($config)) {
    try {
        $conn = verificarConexion($conn);
        $stmt = $conn->query("SELECT * FROM configuracion WHERE id = 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$config) {
            // Si aún no hay configuración, crear una por defecto
            $config = [
                'nombre_consultorio' => 'Consultorio Médico',
                'medico_nombre' => 'Dr. Médico',
                'email_contacto' => '',
                'telefono' => '',
                'direccion' => '',
                'duracion_cita' => 30,
                'hora_inicio' => '09:00',
                'hora_fin' => '18:00',
                'dias_laborables' => '1,2,3,4,5',
                'intervalo_citas' => 30,
                'multi_medico' => 0,
                'moneda' => 'RD$',
                'zona_horaria' => 'America/Santo_Domingo',
                'formato_fecha' => 'Y-m-d',
                'idioma' => 'es',
                'tema_color' => 'light',
                'mostrar_alertas_stock' => 1,
                'notificaciones_email' => 0,
                'whatsapp_server' => 'https://api.whatsapp.com',
                'require_https' => 0,
                'modo_mantenimiento' => 0,
                'especialidad_id' => null
            ];
        }
    } catch (Exception $e) {
        // Si hay error, usar configuración por defecto
        $config = [
            'nombre_consultorio' => 'Consultorio Médico',
            'medico_nombre' => 'Dr. Médico',
            'email_contacto' => '',
            'telefono' => '',
            'direccion' => '',
            'duracion_cita' => 30,
            'hora_inicio' => '09:00',
            'hora_fin' => '18:00',
            'dias_laborables' => '1,2,3,4,5',
            'intervalo_citas' => 30,
            'multi_medico' => 0,
            'moneda' => 'RD$',
            'zona_horaria' => 'America/Santo_Domingo',
            'formato_fecha' => 'Y-m-d',
            'idioma' => 'es',
            'tema_color' => 'light',
            'mostrar_alertas_stock' => 1,
            'notificaciones_email' => 0,
            'whatsapp_server' => 'https://api.whatsapp.com',
            'require_https' => 0,
            'modo_mantenimiento' => 0,
            'especialidad_id' => null
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración del Sistema - Consultorio Médico</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; }
        .sidebar a:hover { background-color: #454d55; text-decoration: none; }
        .content { padding: 20px; }
        .config-section { margin-bottom: 30px; }
        .config-section h3 { border-bottom: 1px solid #dee2e6; padding-bottom: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <!-- Header con modo oscuro -->
    <?php include 'includes/header.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Content -->
            <div class="col-md-10 content">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2>Configuración del Sistema</h2>
                </div>
                <hr>

                <?php if(!empty($mensaje)) echo $mensaje; ?>

                <!-- Debug temporal - remover después -->
                <?php if (isset($_GET['debug'])): ?>
                <div class="alert alert-info">
                    <strong>Debug Info:</strong><br>
                    Config loaded: <?php echo $config ? 'Yes' : 'No'; ?><br>
                    <?php if ($config): ?>
                        nombre_consultorio: <?php echo htmlspecialchars($config['nombre_consultorio'] ?? 'NULL'); ?><br>
                        medico_nombre: <?php echo htmlspecialchars($config['medico_nombre'] ?? 'NULL'); ?><br>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_settings">
                            
                            <div class="config-section">
                                <h3>Información General</h3>                                <div class="form-group">
                                    <label>Nombre del Consultorio</label>
                                    <input type="text" name="clinic_name" class="form-control" required
                                           value="<?php echo htmlspecialchars(getConfigValue($config, 'nombre_consultorio', 'Consultorio Médico')); ?>">
                                </div>                                <div class="form-group">
                                    <label>Nombre del Médico</label>
                                    <input type="text" name="medico_nombre" class="form-control" required
                                           value="<?php echo htmlspecialchars(getConfigValue($config, 'medico_nombre', 'Dr. Médico')); ?>">
                                    <small class="form-text text-muted">
                                        Este nombre aparecerá en las recetas y consultas como el médico tratante.
                                    </small>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="multi_medico" name="multi_medico" 
                                               <?php echo (isset($config['multi_medico']) && $config['multi_medico'] == 1) ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="multi_medico">Habilitar múltiples médicos</label>
                                        <small class="form-text text-muted">
                                            Active esta opción para permitir la gestión de varios médicos en el sistema.
                                        </small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email de Contacto</label>
                                            <input type="email" name="contact_email" class="form-control"
                                                   value="<?php echo htmlspecialchars($config['email_contacto'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Teléfono</label>
                                            <input type="tel" name="telefono" class="form-control"
                                                   value="<?php echo htmlspecialchars($config['telefono'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Dirección</label>
                                    <textarea name="direccion" class="form-control" rows="3"><?php echo htmlspecialchars($config['direccion'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Logo del Consultorio</label>
                                    <input type="file" name="logo" class="form-control-file" accept="image/jpeg,image/png">
                                    <?php if(!empty($config['logo'])): ?>
                                        <div class="mt-2">
                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($config['logo']); ?>" 
                                                 alt="Logo actual" style="max-height: 100px;">
                                            <small class="d-block">Logo actual</small>
                                        </div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted">
                                        Formatos permitidos: PNG, JPEG. Tamaño máximo: 5MB.<br>
                                        El logo se utilizará en las recetas y la página de inicio de sesión.
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label>Especialidad del Consultorio</label>                                    <select name="especialidad_id" class="form-control" required>
                                        <?php
                                        // Obtener todas las especialidades
                                        $stmt = $conn->query("SELECT id, codigo, nombre FROM especialidades ORDER BY nombre");
                                        while ($esp = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $selected = ($esp['id'] == ($config['especialidad_id'] ?? '')) ? 'selected' : '';
                                            echo "<option value='{$esp['id']}' {$selected}>";
                                            echo htmlspecialchars("{$esp['nombre']} ({$esp['codigo']})");
                                            echo "</option>";
                                        }
                                        ?>
                                    </select>
                                    <small class="form-text text-muted">
                                        Esta especialidad determinará los campos específicos para las consultas médicas.
                                    </small>
                                    <div class="mt-2">
                                        <a href="gestionar_campos_especialidad.php" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit"></i> Configurar Campos de Especialidad
                                        </a>
                                        <a href="gestionar_especialidades.php" class="btn btn-outline-success btn-sm" onclick="setFromConfig()">
                                            <i class="fas fa-plus"></i> Gestionar Especialidades
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="config-section">
                                <h3>Horarios y Citas</h3>
                                <div class="form-group">
                                    <label>Duración Default de Citas (minutos)</label>
                                    <input type="number" name="duracion_cita" class="form-control" required
                                           value="<?php echo htmlspecialchars($config['duracion_cita'] ?? '30'); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Intervalo entre Citas (minutos)</label>
                                    <input type="number" name="intervalo_citas" class="form-control" required
                                           value="<?php echo htmlspecialchars($config['intervalo_citas'] ?? '30'); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Horario de Atención</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Hora de Inicio</label>
                                            <input type="time" name="hora_inicio" class="form-control" required
                                                   value="<?php echo htmlspecialchars($config['hora_inicio'] ?? '09:00'); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Hora de Fin</label>
                                            <input type="time" name="hora_fin" class="form-control" required
                                                   value="<?php echo htmlspecialchars($config['hora_fin'] ?? '18:00'); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Días Laborables</label>
                                    <div class="custom-control custom-checkbox">
                                        <?php
                                        $dias_laborables = explode(',', $config['dias_laborables'] ?? '1,2,3,4,5');
                                        $dias = [
                                            1 => 'Lunes',
                                            2 => 'Martes',
                                            3 => 'Miércoles',
                                            4 => 'Jueves',
                                            5 => 'Viernes',
                                            6 => 'Sábado',
                                            7 => 'Domingo'
                                        ];
                                        foreach($dias as $num => $nombre): ?>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" 
                                                       name="dias_laborables[]" value="<?php echo $num; ?>" 
                                                       id="dia<?php echo $num; ?>"
                                                       <?php echo in_array($num, $dias_laborables) ? 'checked' : ''; ?>>
                                                <label class="custom-control-label" for="dia<?php echo $num; ?>">
                                                    <?php echo $nombre; ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="config-section">
                                <h3>Configuración Regional</h3>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Moneda</label>
                                            <select name="moneda" class="form-control">
                                                <option value="$" <?php echo ($config['moneda'] ?? '$') == '$' ? 'selected' : ''; ?>>$ (Dólar)</option>
                                                <option value="RD$" <?php echo ($config['moneda'] ?? '$') == 'RD$' ? 'selected' : ''; ?>>RD$ (Peso Dominicano)</option>
                                                <option value="€" <?php echo ($config['moneda'] ?? '$') == '€' ? 'selected' : ''; ?>>€ (Euro)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Zona Horaria</label>
                                            <select name="zona_horaria" class="form-control">
                                                <option value="America/Santo_Domingo" <?php echo ($config['zona_horaria'] ?? 'America/Santo_Domingo') == 'America/Santo_Domingo' ? 'selected' : ''; ?>>Santo Domingo</option>
                                                <option value="America/New_York" <?php echo ($config['zona_horaria'] ?? 'America/Santo_Domingo') == 'America/New_York' ? 'selected' : ''; ?>>Nueva York</option>
                                                <option value="America/Mexico_City" <?php echo ($config['zona_horaria'] ?? 'America/Santo_Domingo') == 'America/Mexico_City' ? 'selected' : ''; ?>>Ciudad de México</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Formato de Fecha</label>
                                            <select name="formato_fecha" class="form-control">
                                                <option value="Y-m-d" <?php echo ($config['formato_fecha'] ?? 'Y-m-d') == 'Y-m-d' ? 'selected' : ''; ?>>AAAA-MM-DD</option>
                                                <option value="d/m/Y" <?php echo ($config['formato_fecha'] ?? 'Y-m-d') == 'd/m/Y' ? 'selected' : ''; ?>>DD/MM/AAAA</option>
                                                <option value="m/d/Y" <?php echo ($config['formato_fecha'] ?? 'Y-m-d') == 'm/d/Y' ? 'selected' : ''; ?>>MM/DD/AAAA</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Idioma</label>
                                    <select name="idioma" class="form-control">
                                        <option value="es" <?php echo ($config['idioma'] ?? 'es') == 'es' ? 'selected' : ''; ?>>Español</option>
                                        <option value="en" <?php echo ($config['idioma'] ?? 'es') == 'en' ? 'selected' : ''; ?>>English</option>
                                        <option value="fr" <?php echo ($config['idioma'] ?? 'es') == 'fr' ? 'selected' : ''; ?>>Français</option>
                                    </select>
                                </div>
                            </div>                            <div class="config-section">
                                <h3>Apariencia y Notificaciones</h3>
                                <div class="form-group">
                                    <label>Servidor de WhatsApp</label>
                                    <input type="url" name="whatsapp_server" class="form-control" 
                                           value="<?php echo htmlspecialchars($config['whatsapp_server'] ?? 'https://api.whatsapp.com'); ?>"
                                           placeholder="URL del servidor de WhatsApp">
                                    <small class="form-text text-muted">
                                        URL del servidor de API de WhatsApp para el envío de mensajes.
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label>Tema de Color</label>
                                    <select name="tema_color" class="form-control" id="tema_color">
                                        <option value="light" <?php echo ($config['tema_color'] ?? 'light') == 'light' ? 'selected' : ''; ?>>Claro</option>
                                        <option value="dark" <?php echo ($config['tema_color'] ?? 'light') == 'dark' ? 'selected' : ''; ?>>Oscuro</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="alertas_stock" 
                                               name="mostrar_alertas_stock" value="1"
                                               <?php echo ($config['mostrar_alertas_stock'] ?? true) ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="alertas_stock">
                                            Mostrar alertas de stock bajo
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="notificaciones_email" 
                                               name="notificaciones_email" value="1"
                                               <?php echo ($config['notificaciones_email'] ?? false) ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="notificaciones_email">
                                            Enviar notificaciones por email
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="config-section">
                                <h3>Seguridad</h3>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="require_https" 
                                               name="require_https" value="1"
                                               <?php echo ($config['require_https'] ?? false) ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="require_https">
                                            Requerir HTTPS
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="maintenance_mode" 
                                               name="maintenance_mode" value="1"
                                               <?php echo ($config['modo_mantenimiento'] ?? false) ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="maintenance_mode">
                                            Modo Mantenimiento
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="js/theme-manager.js"></script>
    <script>
        // Función para establecer la variable de sesión al ir a la página de especialidades
        function setFromConfig() {
            fetch('set_from_config.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'set_from_config=true'
            }).catch(error => console.error('Error:', error));
        }

        // Función para cambiar el tema
        function cambiarTema(tema) {
            if (tema === 'dark') {
                $('body').addClass('bg-dark text-light');
                $('.card').addClass('bg-dark text-light border-secondary');
                $('.form-control').addClass('bg-dark text-light border-secondary');
                $('.custom-control-label').addClass('text-light');
            } else {
                $('body').removeClass('bg-dark text-light');
                $('.card').removeClass('bg-dark text-light border-secondary');
                $('.form-control').removeClass('bg-dark text-light border-secondary');
                $('.custom-control-label').removeClass('text-light');
            }
        }

        // Escuchar cambios en el selector de tema
        $('#tema_color').on('change', function() {
            cambiarTema(this.value);
        });

        // Aplicar tema inicial
        $(document).ready(function() {
            cambiarTema($('#tema_color').val());
        });
    </script>
</body>
</html>
