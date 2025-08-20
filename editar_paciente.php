<?php
require_once 'session_config.php';
session_start();
require_once "permissions.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Verificar permisos para gestionar pacientes
if (!hasPermission('manage_patients')) {
    header("location: unauthorized.php");
    exit;
}

require_once "config.php";

$paciente = null;
$error = null;

// Verificar si se proporcionó un ID de paciente
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    
    // Obtener datos del paciente con información del seguro médico
    $sql = "SELECT p.*, sm.descripcion as seguro_medico_nombre 
            FROM pacientes p 
            LEFT JOIN seguro_medico sm ON p.seguro_medico_id = sm.id 
            WHERE p.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$paciente) {
        $error = "Paciente no encontrado";
    } else {
        // Obtener enfermedades del paciente si el usuario tiene permisos
        $enfermedades_paciente = [];
        if (hasPermission('manage_diseases')) {
            $sql = "SELECT enfermedad_id FROM paciente_enfermedades WHERE paciente_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $enfermedades_paciente[] = $row['enfermedad_id'];
            }
        }
    }
} else {
    $error = "ID de paciente no proporcionado";
}

// Procesar el formulario de edición
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'editar') {
    try {
        $conn->beginTransaction();
        
        // Actualizar paciente
        $sql = "UPDATE pacientes SET 
                nombre = ?, 
                apellido = ?, 
                dni = ?, 
                sexo = ?,
                fecha_nacimiento = ?, 
                telefono = ?, 
                email = ?, 
                direccion = ?,
                seguro_medico_id = ?,
                numero_poliza = ?,
                contacto_emergencia = ?,
                telefono_emergencia = ?
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(1, $_POST['nombre'], PDO::PARAM_STR);
        $stmt->bindParam(2, $_POST['apellido'], PDO::PARAM_STR);
        $stmt->bindParam(3, $_POST['dni'], PDO::PARAM_STR);
        $stmt->bindParam(4, $_POST['sexo'], PDO::PARAM_STR);
        $stmt->bindParam(5, $_POST['fecha_nacimiento'], PDO::PARAM_STR);
        $stmt->bindParam(6, $_POST['telefono'], PDO::PARAM_STR);
        $stmt->bindParam(7, $_POST['email'], PDO::PARAM_STR);
        $stmt->bindParam(8, $_POST['direccion'], PDO::PARAM_STR);
        $stmt->bindParam(9, $_POST['seguro_medico_id'] === '' ? null : $_POST['seguro_medico_id'], PDO::PARAM_INT);
        $stmt->bindParam(10, $_POST['numero_poliza'], PDO::PARAM_STR);
        $stmt->bindParam(11, $_POST['contacto_emergencia'], PDO::PARAM_STR);
        $stmt->bindParam(12, $_POST['telefono_emergencia'], PDO::PARAM_STR);
        $stmt->bindParam(13, $_POST['id'], PDO::PARAM_INT);
        $stmt->execute();
        
        // Actualizar enfermedades si el usuario tiene permisos
        if (hasPermission('manage_diseases')) {
            // Eliminar enfermedades actuales
            $sql = "DELETE FROM paciente_enfermedades WHERE paciente_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$_POST['id']]);
            
            // Insertar nuevas enfermedades seleccionadas
            if (!empty($_POST['enfermedades'])) {
                $sql = "INSERT INTO paciente_enfermedades (paciente_id, enfermedad_id, estado) VALUES (?, ?, 'activa')";
                $stmt = $conn->prepare($sql);
                
                foreach ($_POST['enfermedades'] as $enfermedad_id) {
                    $stmt->execute([$_POST['id'], $enfermedad_id]);
                }
            }
        }        // Procesar foto del paciente
        if (isset($_POST['eliminarFoto']) && $_POST['eliminarFoto'] == 1) {
            // Si se seleccionó eliminar foto, eliminar la foto actual
            if (!empty($paciente['foto'])) {
                $uploadDir = 'uploads/pacientes/';
                $rutaFoto = $uploadDir . $paciente['foto'];
                if (file_exists($rutaFoto)) {
                    @unlink($rutaFoto); // Eliminar archivo físico
                }
                
                // Actualizar base de datos para eliminar referencia a la foto
                $sql = "UPDATE pacientes SET foto = NULL WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$_POST['id']]);
            }
        }
        elseif (!isset($_POST['mantenerFoto'])) {
            // Si no se seleccionó mantener la foto actual, procesar la nueva foto
            if (isset($_POST['fotoSource'])) {
                if ($_POST['fotoSource'] == 'upload' && isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
                    // Subir nueva foto
                    $fileTmpPath = $_FILES['foto']['tmp_name'];
                    $fileName = $_FILES['foto']['name'];
                    $fileSize = $_FILES['foto']['size'];
                    $fileType = $_FILES['foto']['type'];
                    
                    $allowedFileTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (in_array($fileType, $allowedFileTypes)) {
                        // Verificar que existe el directorio de uploads
                        $uploadDir = 'uploads/pacientes/';
                        if (!file_exists($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        
                        $newFileName = uniqid('foto_', true) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
                        $dest_path = $uploadDir . $newFileName;
                        
                        if (move_uploaded_file($fileTmpPath, $dest_path)) {
                            // Eliminar foto anterior si existe
                            if (!empty($paciente['foto']) && file_exists($uploadDir . $paciente['foto'])) {
                                @unlink($uploadDir . $paciente['foto']);
                            }
                            
                            // Actualizar nombre de archivo en la base de datos
                            $sql = "UPDATE pacientes SET foto = ? WHERE id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute([$newFileName, $_POST['id']]);
                        } else {
                            $error = "Error al mover el archivo subido";
                        }
                    } else {
                        $error = "Tipo de archivo no permitido. Solo se permiten imágenes JPEG, PNG y GIF.";
                    }
                } elseif ($_POST['fotoSource'] == 'camera' && !empty($_POST['fotoBase64'])) {
                    // Procesar foto desde la cámara (Base64)
                    // Verificar que existe el directorio de uploads
                    $uploadDir = 'uploads/pacientes/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $base64String = $_POST['fotoBase64'];
                    // Eliminar el prefijo de los datos base64
                    $base64String = preg_replace('#^data:image/\w+;base64,#i', '', $base64String);
                    $imageData = base64_decode($base64String);
                    
                    $newFileName = uniqid('foto_', true) . '.jpg';
                    $dest_path = $uploadDir . $newFileName;
                    
                    if (file_put_contents($dest_path, $imageData)) {
                        // Eliminar foto anterior si existe
                        if (!empty($paciente['foto']) && file_exists($uploadDir . $paciente['foto'])) {
                            @unlink($uploadDir . $paciente['foto']);
                        }
                        
                        // Actualizar nombre de archivo en la base de datos
                        $sql = "UPDATE pacientes SET foto = ? WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$newFileName, $_POST['id']]);
                    } else {
                        $error = "Error al guardar la imagen capturada";
                    }
                }
            }
        }
        
        $conn->commit();
        header("location: pacientes.php");
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

// Obtener lista de enfermedades para el formulario si el usuario tiene permisos
$mostrarEnfermedades = hasPermission('manage_diseases');
$enfermedades = [];
if ($mostrarEnfermedades) {
    $enfermedades = $conn->query("SELECT * FROM enfermedades ORDER BY nombre")->fetchAll();
}

// Obtener lista de seguros médicos
$seguros_medicos = [];
try {
    $stmt = $conn->query("SELECT id, descripcion FROM seguro_medico WHERE activo = 1 ORDER BY descripcion");
    $seguros_medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Si hay error con la tabla seguro_medico, continuar sin ella
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Paciente - Consultorio Médico</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; }
        .sidebar a:hover { background-color: #454d55; text-decoration: none; }
        .content { padding: 20px; }
        /* Estilos para la cámara y foto */
        #camera {
            width: 320px;
            height: 240px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
        }
        #fotoPreview {
            max-width: 150px;
            max-height: 150px;
            border: 1px solid #ddd;
            margin-top: 10px;
        }
        .foto-paciente {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
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
                <h2>Editar Paciente</h2>
                <hr>

                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($paciente): ?>
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="editar">
                            <input type="hidden" name="id" value="<?php echo $paciente['id']; ?>">
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Nombre</label>
                                    <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($paciente['nombre']); ?>" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Apellido</label>
                                    <input type="text" name="apellido" class="form-control" value="<?php echo htmlspecialchars($paciente['apellido']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>DNI</label>
                                    <input type="text" name="dni" class="form-control" value="<?php echo htmlspecialchars($paciente['dni']); ?>" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Sexo</label>
                                    <select name="sexo" class="form-control">
                                        <option value="">Seleccionar...</option>
                                        <option value="M" <?php echo isset($paciente['sexo']) && $paciente['sexo'] == 'M' ? 'selected' : ''; ?>>Masculino</option>
                                        <option value="F" <?php echo isset($paciente['sexo']) && $paciente['sexo'] == 'F' ? 'selected' : ''; ?>>Femenino</option>
                                        <option value="O" <?php echo isset($paciente['sexo']) && $paciente['sexo'] == 'O' ? 'selected' : ''; ?>>Otro</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Fecha de Nacimiento</label>
                                    <input type="date" name="fecha_nacimiento" class="form-control" value="<?php echo htmlspecialchars($paciente['fecha_nacimiento']); ?>" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Teléfono</label>
                                    <input type="tel" name="telefono" class="form-control" value="<?php echo htmlspecialchars($paciente['telefono']); ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($paciente['email']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Dirección</label>
                                <textarea name="direccion" class="form-control" rows="2"><?php echo htmlspecialchars($paciente['direccion']); ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Seguro Médico</label>
                                    <select name="seguro_medico_id" class="form-control">
                                        <option value="">Sin seguro médico</option>
                                        <?php foreach($seguros_medicos as $seguro): ?>
                                            <option value="<?php echo $seguro['id']; ?>" 
                                                <?php echo (isset($paciente['seguro_medico_id']) && $paciente['seguro_medico_id'] == $seguro['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($seguro['descripcion']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Número de Póliza</label>
                                    <input type="text" name="numero_poliza" class="form-control" value="<?php echo isset($paciente['numero_poliza']) ? htmlspecialchars($paciente['numero_poliza']) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Contacto de Emergencia</label>
                                    <input type="text" name="contacto_emergencia" class="form-control" placeholder="Nombre del contacto" value="<?php echo isset($paciente['contacto_emergencia']) ? htmlspecialchars($paciente['contacto_emergencia']) : ''; ?>">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Teléfono de Emergencia</label>
                                    <input type="tel" name="telefono_emergencia" class="form-control" value="<?php echo isset($paciente['telefono_emergencia']) ? htmlspecialchars($paciente['telefono_emergencia']) : ''; ?>">
                                </div>
                            </div>
                              <!-- Foto del paciente -->
                            <div class="form-group">
                                <label>Foto del Paciente</label>
                                <div class="row">                            <div class="col-md-3">
                                        <?php if (!empty($paciente['foto'])): ?>
                                            <img src="uploads/pacientes/<?php echo htmlspecialchars($paciente['foto']); ?>" class="foto-paciente mb-2" alt="Foto del paciente">
                                            <div class="form-check mb-2">
                                                <input type="checkbox" class="form-check-input" id="mantenerFoto" name="mantenerFoto" value="1" checked>
                                                <label class="form-check-label" for="mantenerFoto">Mantener foto actual</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="eliminarFoto" name="eliminarFoto" value="1">
                                                <label class="form-check-label" for="eliminarFoto">Eliminar foto</label>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-info">No hay foto</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-row">
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="fotoSource" id="fotoUpload" value="upload" <?php echo !empty($paciente['foto']) ? 'disabled' : 'checked'; ?>>
                                                    <label class="form-check-label" for="fotoUpload">
                                                        Subir nueva foto
                                                    </label>
                                                </div>
                                                <input type="file" name="foto" id="inputFoto" class="form-control mt-2" accept="image/*" <?php echo !empty($paciente['foto']) ? 'disabled' : ''; ?>>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="fotoSource" id="fotoCamera" value="camera" <?php echo !empty($paciente['foto']) ? 'disabled' : ''; ?>>
                                                    <label class="form-check-label" for="fotoCamera">
                                                        Tomar con cámara
                                                    </label>
                                                </div>
                                                <button type="button" id="btnStartCamera" class="btn btn-sm btn-outline-primary mt-2" disabled>Iniciar cámara</button>
                                                <button type="button" id="btnCapturePhoto" class="btn btn-sm btn-outline-success mt-2" disabled>Capturar</button>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <div id="camera" style="display:none;"></div>
                                            </div>
                                            <div class="col-md-6">
                                                <img id="fotoPreview" src="" alt="" style="display:none;">
                                                <input type="hidden" name="fotoBase64" id="fotoBase64">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($mostrarEnfermedades): ?>
                            <div class="form-group">
                                <label>Enfermedades</label>
                                <select name="enfermedades[]" class="form-control" multiple>
                                    <?php foreach($enfermedades as $enfermedad): ?>
                                        <option value="<?php echo $enfermedad['id']; ?>" <?php echo in_array($enfermedad['id'], $enfermedades_paciente) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($enfermedad['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Mantenga presionada la tecla Ctrl para seleccionar múltiples enfermedades</small>
                            </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <a href="pacientes.php" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/webcam.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Cambiar entre foto upload y cámara
            $('input[name="fotoSource"]').change(function() {
                if ($(this).val() === 'camera') {
                    $('#btnStartCamera').prop('disabled', false);
                    $('#inputFoto').prop('disabled', true);
                } else {
                    $('#btnStartCamera').prop('disabled', true);
                    $('#btnCapturePhoto').prop('disabled', true);
                    $('#inputFoto').prop('disabled', false);
                    // Parar cámara si está activa
                    Webcam.reset();
                    $('#camera').hide();
                    $('#fotoPreview').hide();
                }
            });

            // Iniciar cámara
            $('#btnStartCamera').click(function() {
                Webcam.set({
                    width: 320,
                    height: 240,
                    image_format: 'jpeg',
                    jpeg_quality: 90
                });
                Webcam.attach('#camera');
                $('#camera').show();
                $('#btnCapturePhoto').prop('disabled', false);
                $(this).prop('disabled', true).text('Cámara activa');
            });

            // Capturar foto
            $('#btnCapturePhoto').click(function() {
                Webcam.snap(function(data_uri) {
                    $('#fotoPreview').attr('src', data_uri).show();
                    $('#fotoBase64').val(data_uri);
                    
                    // Detener cámara
                    Webcam.reset();
                    $('#camera').hide();
                    $('#btnStartCamera').prop('disabled', false).text('Iniciar cámara');
                    $('#btnCapturePhoto').prop('disabled', true);
                });
            });

            // Vista previa de la imagen subida
            $('#inputFoto').change(function() {
                if (this.files && this.files[0]) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        $('#fotoPreview').attr('src', e.target.result).show();
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });            // Gestionar el checkbox para mantener la foto actual
            $('#mantenerFoto').change(function() {
                if(this.checked) {
                    $('#inputFoto').prop('disabled', true);
                    $('#fotoUpload').prop('disabled', true);
                    $('#fotoCamera').prop('disabled', true);
                    $('#btnStartCamera').prop('disabled', true);
                    $('#eliminarFoto').prop('checked', false);
                    // Parar cámara si está activa
                    Webcam.reset();
                    $('#camera').hide();
                    $('#fotoPreview').hide();
                } else {
                    $('#inputFoto').prop('disabled', false);
                    $('#fotoUpload').prop('disabled', false);
                    $('#fotoCamera').prop('disabled', false);
                    if($('#fotoCamera').is(':checked')) {
                        $('#btnStartCamera').prop('disabled', false);
                    }
                }
            });
            
            // Gestionar el checkbox para eliminar la foto
            $('#eliminarFoto').change(function() {
                if(this.checked) {
                    $('#mantenerFoto').prop('checked', false);
                    $('#inputFoto').prop('disabled', true);
                    $('#fotoUpload').prop('disabled', true);
                    $('#fotoCamera').prop('disabled', true);
                    $('#btnStartCamera').prop('disabled', true);
                    // Parar cámara si está activa
                    Webcam.reset();
                    $('#camera').hide();
                    $('#fotoPreview').hide();
                } else {
                    if (!$('#mantenerFoto').is(':checked')) {
                        $('#inputFoto').prop('disabled', false);
                        $('#fotoUpload').prop('disabled', false);
                        $('#fotoCamera').prop('disabled', false);
                        if($('#fotoCamera').is(':checked')) {
                            $('#btnStartCamera').prop('disabled', false);
                        }
                    }
                }
            });
        });
    </script>
    <script src="js/theme-manager.js"></script>
</body>
</html>
