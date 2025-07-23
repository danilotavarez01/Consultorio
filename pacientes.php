<?php
require_once 'session_config.php';
session_start();

// Forzar HTTPS excepto en localhost y red interna - DESHABILITADO PARA DESARROLLO

if ($_SERVER['HTTP_HOST'] !== 'localhost' && 
    $_SERVER['HTTP_HOST'] !== '127.0.0.1' && 
    $_SERVER['HTTP_HOST'] !== '192.168.6.168' && 
    !preg_match('/^192\.168\./', $_SERVER['HTTP_HOST']) &&
    !preg_match('/^10\./', $_SERVER['HTTP_HOST']) &&
    !preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $_SERVER['HTTP_HOST']) &&
    (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on')) {
    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $redirect);
    exit();
}


// Configurar encabezados de seguridad
header("Content-Security-Policy: default-src * 'unsafe-inline' 'unsafe-eval'; media-src * blob: data:; connect-src * 'unsafe-inline'; img-src * data: blob:; upgrade-insecure-requests");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: strict-origin-when-cross-origin");
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

// Procesar el formulario de nuevo paciente
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'crear') {
            try {
                $conn->beginTransaction();
                
                // Insertar paciente
                $sql = "INSERT INTO pacientes (nombre, apellido, dni, sexo, fecha_nacimiento, telefono, email, direccion, 
                        seguro_medico, numero_poliza, contacto_emergencia, telefono_emergencia, foto) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bindParam(1, $_POST['nombre'], PDO::PARAM_STR);
                    $stmt->bindParam(2, $_POST['apellido'], PDO::PARAM_STR);
                    $stmt->bindParam(3, $_POST['dni'], PDO::PARAM_STR);
                    $stmt->bindParam(4, $_POST['sexo'], PDO::PARAM_STR);
                    $stmt->bindParam(5, $_POST['fecha_nacimiento'], PDO::PARAM_STR);
                    $stmt->bindParam(6, $_POST['telefono'], PDO::PARAM_STR);
                    $stmt->bindParam(7, $_POST['email'], PDO::PARAM_STR);
                    $stmt->bindParam(8, $_POST['direccion'], PDO::PARAM_STR);
                    $stmt->bindParam(9, $_POST['seguro_medico'], PDO::PARAM_STR);
                    $stmt->bindParam(10, $_POST['numero_poliza'], PDO::PARAM_STR);
                    $stmt->bindParam(11, $_POST['contacto_emergencia'], PDO::PARAM_STR);
                    $stmt->bindParam(12, $_POST['telefono_emergencia'], PDO::PARAM_STR);                    // Manejo de la foto
                    $fotoNombre = null;
                    $directorioUpload = 'uploads/pacientes/';
                    
                    // Verificar que exista el directorio de uploads, si no, crearlo
                    if (!file_exists($directorioUpload)) {
                        mkdir($directorioUpload, 0755, true);
                    }
                    
                    if (isset($_POST['fotoSource']) && $_POST['fotoSource'] === 'camera' && !empty($_POST['fotoBase64'])) {
                        // Procesar foto capturada con la cámara
                        $fotoBase64 = $_POST['fotoBase64'];
                        // Eliminar el prefijo de los datos base64
                        $fotoData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $fotoBase64));
                        $fotoNombre = 'foto_' . uniqid() . '.png';
                        $rutaCompleta = $directorioUpload . $fotoNombre;
                        
                        // Guardar la imagen
                        file_put_contents($rutaCompleta, $fotoData);
                        $stmt->bindParam(13, $fotoNombre, PDO::PARAM_STR);
                    } 
                    elseif (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
                        // Procesar foto subida
                        $fotoTmpPath = $_FILES['foto']['tmp_name'];
                        $fotoNombre = uniqid('foto_') . '.' . pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                        move_uploaded_file($fotoTmpPath, $directorioUpload . $fotoNombre);
                        $stmt->bindParam(13, $fotoNombre, PDO::PARAM_STR);
                    } 
                    else {
                        $stmt->bindValue(13, null, PDO::PARAM_NULL);
                    }
                    $stmt->execute();
                    $paciente_id = $conn->lastInsertId();
                    
                    // Insertar enfermedades si se han seleccionado y el usuario tiene permisos
                    if (!empty($_POST['enfermedades']) && hasPermission('manage_diseases')) {
                        $sql_enfermedad = "INSERT INTO paciente_enfermedades (paciente_id, enfermedad_id, estado) VALUES (?, ?, 'activa')";
                        $stmt_enfermedad = $conn->prepare($sql_enfermedad);
                        
                        foreach ($_POST['enfermedades'] as $enfermedad_id) {
                            $stmt_enfermedad->execute([$paciente_id, $enfermedad_id]);
                        }
                    }
                    
                    $conn->commit();
                }
            } catch (Exception $e) {
                $conn->rollBack();
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}

// Obtener lista de enfermedades para el formulario si el usuario tiene permisos
$mostrarEnfermedades = hasPermission('manage_diseases');
$enfermedades = [];
if ($mostrarEnfermedades) {
    $enfermedades = $conn->query("SELECT * FROM enfermedades ORDER BY nombre")->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Pacientes - Consultorio Médico</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
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
        .foto-paciente-lista {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
        }    </style>    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="js/theme-manager.js"></script>
    <script src="js/camera.js"></script>
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
                <h2>Gestión de Pacientes</h2>
                <hr>

                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Botón para nuevo paciente -->
                <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#nuevoPacienteModal">
                    <i class="fas fa-user-plus"></i> Nuevo Paciente
                </button>

                <!-- Campo de búsqueda -->
                <div class="form-group mb-3">
                    <input type="text" id="searchPaciente" class="form-control" placeholder="Buscar paciente por nombre, apellido o DNI...">
                </div>

                <!-- Tabla de pacientes -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>                            <tr>
                                <th>Foto</th>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>DNI</th>
                                <th>Edad</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <?php if ($mostrarEnfermedades): ?>
                                <th>Enfermedades</th>
                                <?php endif; ?>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT p.*, GROUP_CONCAT(e.nombre SEPARATOR ', ') as enfermedades 
                                   FROM pacientes p 
                                   LEFT JOIN paciente_enfermedades pe ON p.id = pe.paciente_id 
                                   LEFT JOIN enfermedades e ON pe.enfermedad_id = e.id 
                                   GROUP BY p.id 
                                   ORDER BY p.apellido, p.nombre";
                            $stmt = $conn->query($sql);
                            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                // Calculate age from birth date
                                $birthDate = new DateTime($row['fecha_nacimiento']);
                                $today = new DateTime('today');
                                $age = $birthDate->diff($today)->y;
                                  echo "<tr>";
                                // Mostrar foto del paciente o imagen predeterminada
                                echo "<td>";
                                if (!empty($row['foto'])) {
                                    $rutaFoto = 'uploads/pacientes/' . htmlspecialchars($row['foto']);
                                    if (file_exists($rutaFoto)) {
                                        echo "<img src='$rutaFoto' class='foto-paciente-lista' alt='Foto' onerror=\"this.src='https://via.placeholder.com/40/cccccc/666666?text=Error'\">";
                                    } else {
                                        echo "<img src='https://via.placeholder.com/40/ffcc00/000000?text=404' class='foto-paciente-lista' alt='Archivo no encontrado' title='Archivo no encontrado: " . htmlspecialchars($row['foto']) . "'>";
                                    }
                                } else {
                                    echo "<img src='https://via.placeholder.com/40/f0f0f0/999999?text=Sin+Foto' class='foto-paciente-lista' alt='Sin foto'>";
                                }
                                echo "</td>";
                                echo "<td>".$row['id']."</td>";
                                echo "<td>".$row['nombre']."</td>";
                                echo "<td>".$row['apellido']."</td>";
                                echo "<td>".$row['dni']."</td>";
                                echo "<td>".$age." años</td>";
                                echo "<td>".$row['telefono']."</td>";
                                echo "<td>".$row['email']."</td>";
                                if ($mostrarEnfermedades) {
                                    echo "<td>".($row['enfermedades'] ? htmlspecialchars($row['enfermedades']) : 'Ninguna')."</td>";
                                }
                                echo "<td>
                                    <a href='ver_paciente.php?id=".$row['id']."' class='btn btn-info btn-sm'><i class='fas fa-eye'></i></a>
                                    <a href='editar_paciente.php?id=".$row['id']."' class='btn btn-warning btn-sm'><i class='fas fa-edit'></i></a>
                                    <a href='historial_medico.php?id=".$row['id']."' class='btn btn-primary btn-sm'><i class='fas fa-notes-medical'></i></a>
                                    </td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Paciente -->
    <div class="modal fade" id="nuevoPacienteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Paciente</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="crear">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Nombre</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Apellido</label>
                                <input type="text" name="apellido" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>DNI</label>
                                <input type="text" name="dni" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Sexo</label>
                                <select name="sexo" class="form-control" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                    <option value="O">Otro</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Fecha de Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Teléfono</label>
                                <input type="tel" name="telefono" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Dirección</label>
                            <textarea name="direccion" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Seguro Médico</label>
                                <input type="text" name="seguro_medico" class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Número de Póliza</label>
                                <input type="text" name="numero_poliza" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Contacto de Emergencia</label>
                            <input type="text" name="contacto_emergencia" class="form-control" placeholder="Nombre del contacto">
                        </div>                        <div class="form-group">
                            <label>Teléfono de Emergencia</label>
                            <input type="tel" name="telefono_emergencia" class="form-control" placeholder="Teléfono del contacto">
                        </div>
                        <div class="form-group">
                            <label>Foto del Paciente</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="fotoSource" id="fotoUpload" value="upload" checked>
                                        <label class="form-check-label" for="fotoUpload">
                                            Subir foto
                                        </label>
                                    </div>
                                    <input type="file" name="foto" id="inputFoto" class="form-control-file mt-2" accept="image/*">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="fotoSource" id="fotoCamera" value="camera">
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
                        <?php if ($mostrarEnfermedades): ?>
                        <div class="form-group">
                            <label>Enfermedades</label>
                            <div class="input-group">
                                <select name="enfermedades[]" class="form-control" multiple>
                                    <?php foreach($enfermedades as $enfermedad): ?>
                                        <option value="<?php echo $enfermedad['id']; ?>"><?php echo htmlspecialchars($enfermedad['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#nuevaEnfermedadModal" title="Crear nueva enfermedad">
                                        <i class="fas fa-plus-circle"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Mantenga presionada la tecla Ctrl para seleccionar múltiples enfermedades</small>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Nueva Enfermedad -->
    <div class="modal fade" id="nuevaEnfermedadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Enfermedad</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="formNuevaEnfermedad">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nombre de la Enfermedad</label>
                            <input type="text" id="nombreEnfermedad" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea id="descripcionEnfermedad" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>    <script>
        // Función de búsqueda
        $(document).ready(function() {
            $("#searchPaciente").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("table tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Manejar el formulario de nueva enfermedad
            $("#formNuevaEnfermedad").on("submit", function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: "ajax_crear_enfermedad.php",
                    type: "POST",
                    data: {
                        nombre: $("#nombreEnfermedad").val(),
                        descripcion: $("#descripcionEnfermedad").val()
                    },
                    dataType: "json",
                    success: function(response) {
                        if(response.success) {
                            // Agregar la nueva enfermedad al select
                            $("select[name='enfermedades[]']").append(
                                $("<option></option>")
                                    .attr("value", response.id)
                                    .text(response.nombre)
                                    .prop("selected", true)
                            );
                            
                            // Cerrar el modal y limpiar el formulario
                            $("#nuevaEnfermedadModal").modal("hide");
                            $("#formNuevaEnfermedad")[0].reset();
                            
                            // Mostrar mensaje de éxito
                            alert("Enfermedad creada correctamente");
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function() {
                        alert("Error al procesar la solicitud");
                    }
                });
            });
        });
    </script>    <script>
        $(document).ready(function() {
            let cameraStream = null;
            let video = document.createElement('video');
            let canvas = document.createElement('canvas');

            // Verificar si el navegador soporta getUserMedia
            function hasGetUserMedia() {
                return !!(navigator.mediaDevices &&
                    navigator.mediaDevices.getUserMedia);
            }            // Función para iniciar la cámara
            async function startCamera() {
                try {
                    // Verificar soporte antes de intentar
                    if (!hasCameraSupport()) {
                        throw new Error('El navegador no soporta acceso a cámara');
                    }

                    // Configurar video
                    video.setAttribute('autoplay', '');
                    video.setAttribute('playsinline', '');
                    video.setAttribute('muted', ''); // Necesario para autoplay en algunos navegadores
                    
                    // Limpiar y mostrar contenedor
                    document.getElementById('camera').innerHTML = '';
                    document.getElementById('camera').appendChild(video);
                    $('#camera').show();

                    // Usar las constraints modernas definidas globalmente
                    const stream = await navigator.mediaDevices.getUserMedia(window.cameraConstraints);
                    
                    // Configurar el stream
                    cameraStream = stream;
                    video.srcObject = stream;
                    
                    // Reproducir video con manejo de errores
                    await video.play();
                    
                    // Habilitar el botón de captura
                    $('#btnCapturePhoto').prop('disabled', false);
                    
                    console.log('Cámara iniciada correctamente');
                    
                } catch (err) {
                    console.error("Error al acceder a la cámara:", err);
                    
                    // Mensajes de error más específicos
                    let mensaje = "No se pudo acceder a la cámara. ";
                    switch (err.name) {
                        case 'NotAllowedError':
                            mensaje += "Asegúrate de conceder permiso para usar la cámara.";
                            break;
                        case 'NotFoundError':
                            mensaje += "No se encontró ninguna cámara en el dispositivo.";
                            break;
                        case 'NotReadableError':
                            mensaje += "La cámara está en uso por otra aplicación.";
                            break;
                        case 'OverconstrainedError':
                            mensaje += "La configuración de cámara solicitada no es compatible.";
                            break;
                        case 'SecurityError':
                            mensaje += "Acceso denegado por motivos de seguridad.";
                            break;
                        default:
                            mensaje += err.message || "Error desconocido.";
                    }
                    
                    // Mostrar error específico para HTTPS
                    if (err.name === 'NotAllowedError' && window.location.protocol !== 'https:' && !isLocalNetwork(window.location.hostname)) {
                        mensaje += "\n\nNota: La cámara requiere HTTPS en sitios web públicos.";
                    }
                    
                    alert(mensaje);
                    
                    // Revertir a modo de upload
                    $('#fotoCamera').prop('checked', false);
                    $('#fotoUpload').prop('checked', true);
                    $('#camera').hide();
                }
            }

            // Función mejorada para capturar foto
            function capturePhoto() {
                try {
                    if (!cameraStream || !video || video.readyState !== 4) {
                        throw new Error('La cámara no está lista para capturar');
                    }

                    // Crear canvas para capturar la imagen
                    canvas = document.createElement('canvas');
                    
                    // Usar las dimensiones reales del video
                    const videoWidth = video.videoWidth || 640;
                    const videoHeight = video.videoHeight || 480;
                    
                    canvas.width = videoWidth;
                    canvas.height = videoHeight;
                    
                    const ctx = canvas.getContext('2d');
                    
                    // Dibujar el frame actual del video
                    ctx.drawImage(video, 0, 0, videoWidth, videoHeight);
                    
                    // Convertir a base64 con calidad optimizada
                    const imgData = canvas.toDataURL('image/jpeg', 0.8);
                    
                    // Verificar que la imagen se capturó correctamente
                    if (imgData.length < 100) {
                        throw new Error('Error al capturar la imagen');
                    }
                    
                    // Mostrar la vista previa
                    $('#fotoPreview').attr('src', imgData).show();
                    
                    // Almacenar en campo oculto para enviar al servidor
                    $('#fotoBase64').val(imgData);
                    
                    // Detener la cámara
                    stopCamera();
                    
                    // Feedback visual
                    console.log('Foto capturada correctamente');
                    
                    // Efecto flash (opcional)
                    const flash = $('<div>').css({
                        position: 'fixed',
                        top: 0,
                        left: 0,
                        width: '100%',
                        height: '100%',
                        backgroundColor: 'white',
                        zIndex: 9999,
                        opacity: 0.8
                    }).appendTo('body');
                    
                    flash.fadeOut(200, function() {
                        flash.remove();
                    });
                    
                } catch (err) {
                    console.error('Error al capturar foto:', err);
                    alert('Error al capturar la foto: ' + err.message);
                }
            }

            // Función mejorada para detener la cámara
            function stopCamera() {
                try {
                    if (cameraStream) {
                        // Detener todos los tracks de video
                        cameraStream.getTracks().forEach(track => {
                            track.stop();
                            console.log('Track detenido:', track.kind);
                        });
                        cameraStream = null;
                    }
                    
                    // Limpiar el video
                    if (video && video.srcObject) {
                        video.srcObject = null;
                    }
                    
                    // Ocultar la cámara y resetear botones
                    $('#camera').hide();
                    $('#btnCapturePhoto').prop('disabled', true);
                    $('#btnStartCamera').prop('disabled', false);
                    
                    console.log('Cámara detenida correctamente');
                    
                } catch (err) {
                    console.error('Error al detener la cámara:', err);
                }
            }

            // Manejar cambio en la fuente de la foto
            $('input[name="fotoSource"]').change(function() {
                if (this.value === 'camera') {
                    $('#inputFoto').prop('disabled', true);
                    $('#btnStartCamera').prop('disabled', false);
                    
                    // Detener cámara previa si estaba activa
                    stopCamera();
                } else {
                    $('#inputFoto').prop('disabled', false);
                    $('#btnStartCamera').prop('disabled', true);
                    stopCamera();
                    $('#fotoPreview').hide();
                    $('#fotoBase64').val('');
                }
            });

            // Botón para iniciar cámara
            $('#btnStartCamera').click(function() {
                startCamera();
                $(this).prop('disabled', true);
            });

            // Botón para capturar foto
            $('#btnCapturePhoto').click(capturePhoto);

            // Vista previa de la imagen subida
            $('#inputFoto').change(function() {
                if (this.files && this.files[0]) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        $('#fotoPreview').attr('src', e.target.result).show();
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });

            // Al cerrar el modal, detener la cámara si estaba activa
            $('#nuevoPacienteModal').on('hidden.bs.modal', function() {
                stopCamera();
                $('#fotoPreview').hide();
                $('#fotoBase64').val('');
                $('#inputFoto').val('');
            });

            // Configuración mejorada para cámara web
            // Configuración para permitir tanto HTTP como HTTPS en entorno local/interno
            function isLocalNetwork(hostname) {
                return hostname === 'localhost' || 
                       hostname === '127.0.0.1' || 
                       hostname.startsWith('192.168.') || 
                       hostname.startsWith('10.') ||
                       hostname.startsWith('172.16.');
            }

            // Solo verificar HTTPS si no estamos en red local (no forzar redirección)
            if (!isLocalNetwork(window.location.hostname) && window.location.protocol !== 'https:') {
                console.warn('Se recomienda usar HTTPS para funcionalidad completa de cámara');
            }

            // Configuración moderna de cámara
            window.cameraConstraints = {
                video: {
                    width: { ideal: 640, min: 320 },
                    height: { ideal: 480, min: 240 },
                    facingMode: 'user' // Cámara frontal por defecto
                },
                audio: false
            };

            // Función mejorada para detectar soporte de cámara
            function hasCameraSupport() {
                return !!(navigator.mediaDevices && 
                         navigator.mediaDevices.getUserMedia);
            }

            // Mostrar advertencia si no hay soporte
            if (!hasCameraSupport()) {
                console.warn('El navegador no soporta acceso a cámara');
                $('#btnStartCamera').prop('disabled', true)
                    .text('Cámara no soportada');
            }
        });
    </script>
    <script src="js/theme-manager.js"></script>
</body>
</html>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>