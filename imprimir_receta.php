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

// Obtener configuración del consultorio
$config = null;
try {
    $stmt = $conn->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Silenciosamente fallar si no se puede obtener la configuración
}

$logo_path = '';
$logo_error = '';
if ($config && !empty($config['logo'])) {
    // Forzar siempre PNG para evitar problemas de renderizado
    $logo_path = 'data:image/png;base64,' . base64_encode($config['logo']);
    if (strlen($config['logo']) < 100) {
        $logo_error = 'El logo configurado es demasiado pequeño o no es una imagen válida.';
    }
} else {
    $logo_path = '';
    $logo_error = 'No hay logo configurado en la base de datos.';
}

$consulta = null;
$paciente = null;
$error = null;

// Verificar si se proporcionó un ID de consulta
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    
    // Obtener datos de la consulta
    $sql = "SELECT h.*, p.nombre, p.apellido, p.dni, p.fecha_nacimiento 
            FROM historial_medico h 
            JOIN pacientes p ON h.paciente_id = p.id 
            WHERE h.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$consulta) {
        $error = "Consulta no encontrada";
    } else {
        // Calcular edad del paciente
        $birthDate = new DateTime($consulta['fecha_nacimiento']);
        $today = new DateTime('today');
        $edad = $birthDate->diff($today)->y;
    }
} else {
    $error = "ID de consulta no proporcionado";
}

// Buscar el médico del turno para la fecha de la receta
$medico_turno_nombre = null;
if ($consulta) {
    $sqlTurno = "SELECT medico_nombre FROM turnos WHERE paciente_id = ? AND fecha_turno = ? LIMIT 1";
    $stmtTurno = $conn->prepare($sqlTurno);
    $stmtTurno->execute([$consulta['paciente_id'], date('Y-m-d', strtotime($consulta['fecha']))]);
    $turno = $stmtTurno->fetch(PDO::FETCH_ASSOC);
    if ($turno && !empty($turno['medico_nombre'])) {
        $medico_turno_nombre = $turno['medico_nombre'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        .header-logo {
            max-width: 200px;
            height: auto;
            display: block;
            margin-bottom: 20px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
    <title>Receta Médica - Consultorio Médico</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <style>
        body { font-family: Arial, sans-serif; }
        .prescription {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .prescription-header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .prescription-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .prescription-body {
            margin-bottom: 30px;
        }
        .prescription-footer {
            margin-top: 50px;
            text-align: center;
        }
        .patient-info {
            margin-bottom: 20px;
        }
        .prescription-content {
            margin-bottom: 30px;
        }
        .doctor-signature {
            margin-top: 50px;
            text-align: center;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
                margin: 0;
            }
            .prescription {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row no-print mb-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Receta Médica</h2>
                    <div>
                        <button onclick="window.print()" class="btn btn-primary mr-2"><i class="fas fa-print"></i> Imprimir</button>
                        <a href="ver_paciente.php?id=<?php echo isset($consulta['paciente_id']) ? $consulta['paciente_id'] : ''; ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($error)): ?>
        <div class="alert alert-danger no-print"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($consulta): ?>
        <div class="prescription">
            <div class="prescription-header">
                <?php if (!empty($logo_path) && empty($logo_error)): ?>
                    <img src="<?php echo $logo_path; ?>?v=<?php echo time(); ?>" alt="Logo" class="header-logo">
                <?php else: ?>
                    <div class="header-logo" style="height:80px;display:flex;align-items:center;justify-content:center;background:#f8f9fa;color:#888;">
                        <span><?php echo htmlspecialchars($logo_error); ?></span>
                    </div>
                    <?php if (!empty($config['logo'])): ?>
                    <div style="word-break:break-all; font-size:10px; color:#c00; background:#fffbe6; padding:8px; margin-top:8px;">
                        <strong>Depuración: Base64 del logo</strong><br>
                        <?php echo base64_encode($config['logo']); ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
                <h1><?php echo htmlspecialchars($config['nombre_consultorio'] ?? 'Consultorio Médico'); ?></h1>
                <?php if (!empty($config['direccion'])): ?>
                    <p><?php echo nl2br(htmlspecialchars($config['direccion'])); ?></p>
                <?php endif; ?>
                <?php if (!empty($config['telefono'])): ?>
                    <p>Tel: <?php echo htmlspecialchars($config['telefono']); ?></p>
                <?php endif; ?>
                <?php if (!empty($config['email_contacto'])): ?>
                    <p>Email: <?php echo htmlspecialchars($config['email_contacto']); ?></p>
                <?php endif; ?>
            </div>

            <div class="prescription-body">
                <div class="row patient-info">
                    <div class="col-md-6">
                        <p><strong>Paciente:</strong> <?php echo htmlspecialchars($consulta['nombre'] . ' ' . $consulta['apellido']); ?></p>
                        <p><strong>DNI:</strong> <?php echo htmlspecialchars($consulta['dni']); ?></p>
                        <p><strong>Edad:</strong> <?php echo $edad; ?> años</p>
                    </div>
                    <div class="col-md-6 text-right">
                        <p><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($consulta['fecha'])); ?></p>
                    </div>
                </div>

                <div class="prescription-content">
                    <h4>Diagnóstico:</h4>
                    <p><?php echo nl2br(htmlspecialchars($consulta['diagnostico'])); ?></p>
                    
                    <h4>Tratamiento:</h4>
                    <p><?php echo nl2br(htmlspecialchars($consulta['tratamiento'])); ?></p>
                </div>                <div class="doctor-signature">
                    <p>____________________________</p>
                    <p>Firma del Médico</p>
                    <p><?php echo htmlspecialchars($medico_turno_nombre ?? ($config['medico_nombre'] ?? 'Médico Tratante')); ?></p>
                </div>
            </div>

            <div class="prescription-footer">
                <p><small>Esta receta tiene validez por 30 días a partir de la fecha de emisión.</small></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>