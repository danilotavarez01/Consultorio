<?php
session_start();
require_once "config.php";
require_once "permissions.php";

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
    $stmt = $conn->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $mensaje = '<div class="alert alert-danger">Error al cargar la configuración: ' . $e->getMessage() . '</div>';
}

// Verificar si existe un logo
if (file_exists($directorio_logo . 'logo.png')) {
    $logo_actual = $directorio_logo . 'logo.png';
}

// Procesar el formulario cuando se envía
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    switch($_POST['action']) {
        case 'update_settings':
            // Actualizar la configuración en la base de datos
            try {
                $sql = "UPDATE configuracion SET 
                        nombre_consultorio = :nombre,
                        email_contacto = :email,
                        duracion_cita = :duracion,
                        hora_inicio = :inicio,
                        hora_fin = :fin,
                        require_https = :https,
                        modo_mantenimiento = :mantenimiento,
                        telefono = :telefono,
                        direccion = :direccion,
                        updated_by = :usuario
                        WHERE id = 1";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':nombre' => $_POST['clinic_name'],
                    ':email' => $_POST['contact_email'],
                    ':duracion' => $_POST['appointment_duration'],
                    ':inicio' => $_POST['start_time'],
                    ':fin' => $_POST['end_time'],
                    ':https' => isset($_POST['require_https']) ? 1 : 0,
                    ':mantenimiento' => isset($_POST['maintenance_mode']) ? 1 : 0,
                    ':telefono' => $_POST['telefono'] ?? null,
                    ':direccion' => $_POST['direccion'] ?? null,
                    ':usuario' => $_SESSION['username']
                ]);
                
                $mensaje = '<div class="alert alert-success">Configuración actualizada correctamente.</div>';

                // Procesar el logo si se ha subido uno nuevo
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
                    $tipo_archivo = $_FILES['logo']['type'];
                    $tamano_archivo = $_FILES['logo']['size'];
                    $temp_archivo = $_FILES['logo']['tmp_name'];
                    
                    // Verificar el tipo de archivo
                    if ($tipo_archivo == "image/png" || $tipo_archivo == "image/jpeg" || $tipo_archivo == "image/jpg") {
                        // Verificar el tamaño (5MB máximo)
                        if ($tamano_archivo <= 5242880) {
                            // Crear el directorio si no existe
                            if (!file_exists($directorio_logo)) {
                                mkdir($directorio_logo, 0755, true);
                            }
                            
                            // Eliminar logo anterior si existe
                            if (file_exists($directorio_logo . 'logo.png')) {
                                unlink($directorio_logo . 'logo.png');
                            }
                            
                            // Convertir y guardar como PNG
                            $imagen = null;
                            if ($tipo_archivo == "image/jpeg" || $tipo_archivo == "image/jpg") {
                                $imagen = imagecreatefromjpeg($temp_archivo);
                            } else {
                                $imagen = imagecreatefrompng($temp_archivo);
                            }
                            
                            if ($imagen) {
                                // Redimensionar si es necesario
                                $ancho = imagesx($imagen);
                                $alto = imagesy($imagen);
                                $max_dimension = 400; // Tamaño máximo permitido
                                
                                if ($ancho > $max_dimension || $alto > $max_dimension) {
                                    if ($ancho > $alto) {
                                        $nuevo_ancho = $max_dimension;
                                        $nuevo_alto = intval($alto * ($max_dimension / $ancho));
                                    } else {
                                        $nuevo_alto = $max_dimension;
                                        $nuevo_ancho = intval($ancho * ($max_dimension / $alto));
                                    }
                                    
                                    $imagen_redimensionada = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
                                    imagealphablending($imagen_redimensionada, false);
                                    imagesavealpha($imagen_redimensionada, true);
                                    imagecopyresampled($imagen_redimensionada, $imagen, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho, $alto);
                                    imagepng($imagen_redimensionada, $directorio_logo . 'logo.png');
                                    imagedestroy($imagen_redimensionada);
                                } else {
                                    imagepng($imagen, $directorio_logo . 'logo.png');
                                }
                                imagedestroy($imagen);
                                $logo_actual = $directorio_logo . 'logo.png';
                                $mensaje = '<div class="alert alert-success">Configuración y logo actualizados correctamente.</div>';
                            }
                        } else {
                            $mensaje = '<div class="alert alert-danger">El archivo es demasiado grande. Máximo 5MB.</div>';
                        }
                    } else {
                        $mensaje = '<div class="alert alert-danger">Tipo de archivo no permitido. Solo se permiten imágenes PNG y JPEG.</div>';
                    }
                }
            } catch(PDOException $e) {
                $mensaje = '<div class="alert alert-danger">Error al actualizar la configuración: ' . $e->getMessage() . '</div>';
            }
            break;
            
        case 'delete_logo':
            if (file_exists($directorio_logo . 'logo.png')) {
                if (unlink($directorio_logo . 'logo.png')) {
                    try {
                        $stmt = $conn->prepare("UPDATE configuracion SET logo = NULL WHERE id = 1");
                        $stmt->execute();
                        $mensaje = '<div class="alert alert-success">Logo eliminado correctamente.</div>';
                        $logo_actual = '';
                    } catch(PDOException $e) {
                        $mensaje = '<div class="alert alert-warning">El logo se eliminó del servidor pero hubo un error al actualizar la base de datos: ' . $e->getMessage() . '</div>';
                    }
                } else {
                    $mensaje = '<div class="alert alert-danger">No se pudo eliminar el logo.</div>';
                }
            }
            break;
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

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_settings">
                            
                            <div class="config-section">
                                <h3>Configuración General</h3>
                                <div class="form-group">
                                    <label>Nombre del Consultorio</label>
                                    <input type="text" name="clinic_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($config['nombre_consultorio'] ?? 'Consultorio Médico'); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Email de Contacto</label>
                                    <input type="email" name="contact_email" class="form-control"
                                           value="<?php echo htmlspecialchars($config['email_contacto'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Teléfono</label>
                                    <input type="tel" name="telefono" class="form-control" 
                                           value="<?php echo htmlspecialchars($config['telefono'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Dirección</label>
                                    <textarea name="direccion" class="form-control" rows="3"><?php echo htmlspecialchars($config['direccion'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Logo del Consultorio</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-body text-center">
                                                    <?php if (!empty($logo_actual)): ?>
                                                        <img src="<?php echo htmlspecialchars($logo_actual); ?>?v=<?php echo time(); ?>" 
                                                             alt="Logo actual" 
                                                             class="img-fluid mb-2" 
                                                             style="max-height: 150px;">
                                                        <form method="POST" class="mt-2">
                                                            <input type="hidden" name="action" value="delete_logo">
                                                            <button type="submit" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-trash"></i> Eliminar Logo
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <div class="text-muted">
                                                            <i class="fas fa-image fa-4x mb-2"></i>
                                                            <p>No hay logo configurado</p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="custom-file mb-3">
                                                <input type="file" class="custom-file-input" id="logo" name="logo" accept="image/png,image/jpeg">
                                                <label class="custom-file-label" for="logo">Seleccionar archivo...</label>
                                            </div>
                                            <small class="form-text text-muted">
                                                Formatos permitidos: PNG, JPEG. Tamaño máximo: 5MB.<br>
                                                El logo se utilizará en las recetas y la página de inicio de sesión.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="config-section">
                                <h3>Configuración de Citas</h3>
                                <div class="form-group">
                                    <label>Duración Default de Citas (minutos)</label>
                                    <input type="number" name="appointment_duration" class="form-control" 
                                           value="<?php echo htmlspecialchars($config['duracion_cita'] ?? '30'); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Horario de Atención</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Hora de Inicio</label>
                                            <input type="time" name="start_time" class="form-control" 
                                                   value="<?php echo htmlspecialchars($config['hora_inicio'] ?? '09:00'); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Hora de Fin</label>
                                            <input type="time" name="end_time" class="form-control" 
                                                   value="<?php echo htmlspecialchars($config['hora_fin'] ?? '18:00'); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="config-section">
                                <h3>Seguridad</h3>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="require_https" name="require_https"
                                               <?php echo ($config['require_https'] ?? false) ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="require_https">Forzar HTTPS</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="maintenance_mode" name="maintenance_mode"
                                               <?php echo ($config['modo_mantenimiento'] ?? false) ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="maintenance_mode">Modo Mantenimiento</label>
                                    </div>
                                </div>
                            </div>

                            <div class="config-section">
                                <h3>Backup y Mantenimiento</h3>
                                <div class="form-group">
                                    <button type="button" class="btn btn-info mr-2" disabled>
                                        <i class="fas fa-download"></i> Generar Backup
                                    </button>
                                    <button type="button" class="btn btn-warning" disabled>
                                        <i class="fas fa-broom"></i> Limpiar Archivos Temporales
                                    </button>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Configuración
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Actualizar el nombre del archivo seleccionado en el input
        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
            
            // Validar el tamaño del archivo
            if (this.files[0].size > 5242880) {
                alert('El archivo es demasiado grande. Por favor seleccione un archivo de menos de 5MB.');
                this.value = '';
                $(this).next('.custom-file-label').html('Seleccionar archivo...');
            }
        });
    </script>
</body>
</html>
