<?php
require_once 'session_config.php';
session_start();
require_once "permissions.php";

// Set timezone to Dominican Republic
date_default_timezone_set('America/Santo_Domingo');

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Verificar permisos para gestionar turnos
if (!hasPermission('manage_appointments')) {
    header("location: unauthorized.php");
    exit;
}

require_once "config.php";

// Add this code after the require_once "config.php"; line
// Check if the estado column can accept 'en_consulta'
try {
    $checkColumn = $conn->query("SHOW COLUMNS FROM turnos WHERE Field = 'estado'");
    $columnInfo = $checkColumn->fetch(PDO::FETCH_ASSOC);
    
    // If estado is an ENUM type, we need to modify it
    if (strpos($columnInfo['Type'], 'enum') !== false) {
        // Add 'en_consulta' to the enum if it's not already there
        if (strpos($columnInfo['Type'], 'en_consulta') === false) {
            $conn->exec("ALTER TABLE turnos MODIFY COLUMN estado ENUM('pendiente', 'atendido', 'cancelado', 'en_consulta') DEFAULT 'pendiente'");
        }
    } else {
        // If it's not an enum, make sure it's large enough
        $conn->exec("ALTER TABLE turnos MODIFY COLUMN estado VARCHAR(20) DEFAULT 'pendiente'");
    }
} catch(PDOException $e) {
    // Just continue if there's an error, we'll handle it when actually using the column
}

// Obtener configuración para verificar si multi_medico está habilitado
$config = null;
$multi_medico = false;
$doctores = [];
try {
    $stmt = $conn->query("SELECT multi_medico, medico_nombre FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    $multi_medico = isset($config['multi_medico']) && $config['multi_medico'] == 1;
    
    // Obtener lista de doctores si multi_medico está habilitado
    if ($multi_medico) {
        $stmt = $conn->query("SELECT id, nombre, username FROM usuarios WHERE rol IN ('admin', 'doctor') ORDER BY nombre");
        $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch(PDOException $e) {
    // Si hay error al obtener configuración, continuar con valores por defecto
}

// Verificar y crear columna medico_id en turnos si no existe
try {
    $checkColumn = $conn->query("SHOW COLUMNS FROM turnos LIKE 'medico_id'");
    if ($checkColumn->rowCount() == 0) {
        // Column doesn't exist, create it
        $conn->exec("ALTER TABLE turnos ADD COLUMN medico_id INT NULL");
    }
    
    // También verificar columna medico_nombre por si se necesita
    $checkColumn = $conn->query("SHOW COLUMNS FROM turnos LIKE 'medico_nombre'");
    if ($checkColumn->rowCount() == 0) {
        $conn->exec("ALTER TABLE turnos ADD COLUMN medico_nombre VARCHAR(100) NULL");
    }
} catch(PDOException $e) {
    // Continuar si hay error
}

// Procesar la adición automática de turnos desde Citas.php
if (isset($_GET['agregar_desde_cita']) && isset($_GET['paciente_id']) && isset($_GET['fecha'])) {
    try {
        // Verificar si la cita existe
        $stmt = $conn->prepare("SELECT * FROM citas WHERE id = ?");
        $stmt->execute([$_GET['agregar_desde_cita']]);
        $cita = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cita) {
            // Verificar si ya existe un turno para esta cita
            $stmt = $conn->prepare("SELECT * FROM turnos WHERE paciente_id = ? AND fecha_turno = ? AND hora_turno = ?");
            $stmt->execute([$cita['paciente_id'], $cita['fecha'], $cita['hora']]);
            
            if ($stmt->rowCount() == 0) {
                // No existe un turno para esta cita, entonces lo creamos
                
                // Check if tipo_turno column exists
                $checkColumn = $conn->query("SHOW COLUMNS FROM turnos LIKE 'tipo_turno'");
                if ($checkColumn->rowCount() == 0) {
                    // Column doesn't exist, create it
                    $conn->exec("ALTER TABLE turnos ADD COLUMN tipo_turno VARCHAR(50) DEFAULT 'Consulta'");
                }
                
                // Obtener nombre del médico para las notas
                $stmt = $conn->prepare("SELECT nombre FROM usuarios WHERE id = ?");
                $stmt->execute([$cita['doctor_id']]);
                $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
                $doctor_nombre = $doctor ? $doctor['nombre'] : 'No especificado';
                
                // Crear notas con información de la cita
                $notas = "Turno generado automáticamente desde la cita #" . $cita['id'] . ". " . ($cita['observaciones'] ?? '');
                
                // Insertar el nuevo turno con información del médico
                if ($multi_medico) {
                    // Si multi_medico está habilitado, usar medico_id y medico_nombre
                    $sql = "INSERT INTO turnos (paciente_id, fecha_turno, hora_turno, notas, tipo_turno, estado, medico_id, medico_nombre) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        $cita['paciente_id'],
                        $cita['fecha'],
                        $cita['hora'],
                        $notas,
                        'Consulta',  // Tipo de turno default
                        'pendiente',  // Estado default
                        $cita['doctor_id'],
                        $doctor_nombre
                    ]);
                } else {
                    // Si multi_medico no está habilitado, usar solo medico_nombre del config
                    $sql = "INSERT INTO turnos (paciente_id, fecha_turno, hora_turno, notas, tipo_turno, estado, medico_nombre) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        $cita['paciente_id'],
                        $cita['fecha'],
                        $cita['hora'],
                        $notas,
                        'Consulta',  // Tipo de turno default
                        'pendiente',  // Estado default
                        $config['medico_nombre'] ?? 'Médico Tratante'
                    ]);
                }
                
                // Actualizar el estado de la cita a "Confirmada"
                $stmt = $conn->prepare("UPDATE citas SET estado = 'Confirmada' WHERE id = ?");
                $stmt->execute([$cita['id']]);
                
                // Redirigir a la página de turnos con la fecha de la cita
                header("location: turnos.php?fecha=" . $cita['fecha'] . "&mensaje=Turno agregado correctamente");
                exit();
            } else {
                // Ya existe un turno para esta cita, redirigir con mensaje
                header("location: turnos.php?fecha=" . $cita['fecha'] . "&mensaje=Ya existe un turno para esta cita");
                exit();
            }
        } else {
            // La cita no existe, redirigir con error
            header("location: turnos.php?error=La cita especificada no existe");
            exit();
        }
    } catch(PDOException $e) {
        // Error en la base de datos
        header("location: turnos.php?error=" . urlencode("Error: " . $e->getMessage()));
        exit();
    }
}

// Procesar formulario de nuevo turno
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'registrar_llegada') {
            try {
                // Verificar si el turno existe
                $stmt = $conn->prepare("SELECT * FROM turnos WHERE id = ?");
                $stmt->execute([$_POST['turno_id']]);
                $turno = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($turno) {
                    // Obtener el número máximo de orden_llegada para la fecha actual
                    $stmt = $conn->prepare("SELECT MAX(orden_llegada) as max_orden FROM turnos WHERE fecha_turno = ?");
                    $stmt->execute([$turno['fecha_turno']]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $nuevo_orden = ($result['max_orden'] ?? 0) + 1;
                    
                    // Actualizar el orden de llegada
                    $stmt = $conn->prepare("UPDATE turnos SET orden_llegada = ? WHERE id = ?");
                    $stmt->execute([$nuevo_orden, $_POST['turno_id']]);
                    
                    // Redirigir a la misma página
                    header("location: turnos.php?fecha=" . $turno['fecha_turno'] . "&mensaje=Llegada registrada correctamente");
                    exit();
                } else {
                    header("location: turnos.php?error=El turno especificado no existe");
                    exit();
                }
            } catch(PDOException $e) {
                header("location: turnos.php?error=" . urlencode("Error: " . $e->getMessage()));
                exit();
            }        } elseif ($_POST['action'] == 'crear') {
            // Check if tipo_turno column exists
            try {
                $checkColumn = $conn->query("SHOW COLUMNS FROM turnos LIKE 'tipo_turno'");
                if ($checkColumn->rowCount() == 0) {
                    // Column doesn't exist, create it
                    $conn->exec("ALTER TABLE turnos ADD COLUMN tipo_turno VARCHAR(50) DEFAULT 'Consulta'");
                }
                
                // Si no se proporciona la hora, usar la hora actual con segundos
                $hora_turno = isset($_POST['hora_turno']) && !empty($_POST['hora_turno']) ? 
                              $_POST['hora_turno'] : date('H:i:s');
                
                // Determinar médico según configuración
                $medico_id = null;
                $medico_nombre = null;
                
                if ($multi_medico && isset($_POST['medico_id']) && !empty($_POST['medico_id'])) {
                    // Si multi_medico está habilitado y se seleccionó un médico
                    $medico_id = $_POST['medico_id'];
                    // Obtener el nombre del médico seleccionado
                    $stmt_medico = $conn->prepare("SELECT nombre FROM usuarios WHERE id = ?");
                    $stmt_medico->execute([$medico_id]);
                    $medico_data = $stmt_medico->fetch(PDO::FETCH_ASSOC);
                    $medico_nombre = $medico_data ? $medico_data['nombre'] : null;
                } else {
                    // Si no está habilitado multi_medico, usar el médico por defecto de configuración
                    $medico_nombre = $config['medico_nombre'] ?? 'Médico Tratante';
                }
                
                $sql = "INSERT INTO turnos (paciente_id, fecha_turno, hora_turno, notas, tipo_turno, medico_id, medico_nombre) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$_POST['paciente_id'], $_POST['fecha_turno'], $hora_turno, $_POST['notas'], $_POST['tipo_turno'], $medico_id, $medico_nombre]);
                header("location: turnos.php?fecha=" . $_POST['fecha_turno']);
                exit();
            } catch(PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        } elseif ($_POST['action'] == 'actualizar_estado') {
            // Solo doctores y admin pueden cambiar el estado a 'atendido'
            if ($_POST['estado'] == 'atendido' && !hasPermission('edit_medical_history')) {
                header("location: unauthorized.php");
                exit;
            }
              $sql = "UPDATE turnos SET estado = ? WHERE id = ?";
            try {
                $stmt = $conn->prepare($sql);
                $stmt->execute([$_POST['estado'], $_POST['turno_id']]);
                
                // Obtener la fecha del turno
                $sql = "SELECT fecha_turno FROM turnos WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$_POST['turno_id']]);
                $turno = $stmt->fetch(PDO::FETCH_ASSOC);
                
                header("location: turnos.php?fecha=" . $turno['fecha_turno']);
                exit();
            } catch(PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Turnos - Consultorio Médico</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; }
        .sidebar a:hover { background-color: #454d55; text-decoration: none; }
        .content { padding: 20px; }
        .estado-pendiente { background-color: #fff3cd; }
        .estado-en_consulta { background-color: #cfe2ff; }
        .estado-atendido { background-color: #d4edda; }
        .estado-cancelado { background-color: #f8d7da; }
    </style>
</head>
<body>
    <!-- Header con modo oscuro -->
    <?php include 'includes/header.php'; ?>
    <div class="container-fluid">
        <div class="row">            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Content -->            <div class="col-md-10 content">                <h2>Gestión de Turnos</h2>
                <hr>
                  <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <strong>Sistema de orden por llegada:</strong> Los turnos ahora se organizan automáticamente por orden de llegada.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <?php if(isset($_GET['mensaje'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['mensaje']); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>

                <!-- Botón para nuevo turno -->
                <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#nuevoTurnoModal">
                    <i class="fas fa-calendar-plus"></i> Nuevo Turno
                </button>

                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="date" id="filtroFecha" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-4">
                        <select id="filtroEstado" class="form-control">
                            <option value="">Todos los estados</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="en_consulta">En Consulta</option>
                            <option value="atendido">Atendido</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                </div>

                <!-- Tabla de turnos -->
                <div class="table-responsive">
                    <table class="table table-hover">                        <thead>                            <tr>
                                <th>#</th>
                                <th>Hora</th>
                                <th>Paciente</th>
                                <th>DNI</th>
                                <th>Tipo</th>
                                <?php if ($multi_medico): ?>
                                <th>Médico</th>
                                <?php endif; ?>
                                <th>Estado</th>
                                <th>Notas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Obtener la fecha del filtro o usar la fecha actual
                            $fecha_mostrar = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');                            // Primero verificar si existe la columna orden_llegada
                            $checkColumn = $conn->query("SHOW COLUMNS FROM turnos LIKE 'orden_llegada'");
                            
                            if ($checkColumn->rowCount() > 0) {
                                // Si la columna existe, ordenar por ella
                                $sql = "SELECT t.*, p.nombre, p.apellido, p.dni 
                                        FROM turnos t 
                                        JOIN pacientes p ON t.paciente_id = p.id 
                                        WHERE fecha_turno = ? 
                                        ORDER BY t.orden_llegada IS NULL, t.orden_llegada, t.hora_turno";
                            } else {
                                // Si la columna no existe, usar el orden normal
                                $sql = "SELECT t.*, p.nombre, p.apellido, p.dni 
                                        FROM turnos t 
                                        JOIN pacientes p ON t.paciente_id = p.id 
                                        WHERE fecha_turno = ? 
                                        ORDER BY t.hora_turno";
                            }
                            try {
                                $stmt = $conn->prepare($sql);
                                $stmt->execute([$fecha_mostrar]);
                                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {                                    $clase_estado = "estado-" . $row['estado'];
                                    echo "<tr class='".$clase_estado."'>";
                                      // Verificar si la columna orden_llegada existe en el conjunto de resultados
                                    if (array_key_exists('orden_llegada', $row)) {
                                        echo "<td>".($row['orden_llegada'] ? $row['orden_llegada'] : '-')."</td>";
                                    } else {
                                        echo "<td>-</td>";                                    }
                                    echo "<td>".date('H:i:s', strtotime($row['hora_turno']))."</td>";
                                    echo "<td>".$row['nombre']." ".$row['apellido']."</td>";
                                    echo "<td>".$row['dni']."</td>";
                                    echo "<td>".htmlspecialchars($row['tipo_turno'] ?? 'Consulta')."</td>";
                                    if ($multi_medico) {
                                        echo "<td>".htmlspecialchars($row['medico_nombre'] ?? 'No asignado')."</td>";
                                    }
                                    echo "<td>".$row['estado']."</td>";
                                    echo "<td>".$row['notas']."</td>";
                                    echo "<td>
                                        <div class='btn-group mr-2 mb-1'>
                                            <button type='button' class='btn btn-sm btn-info dropdown-toggle' data-toggle='dropdown'>
                                                Estado
                                            </button>
                                            <div class='dropdown-menu'>
                                                <form method='POST' class='estado-form'>
                                                    <input type='hidden' name='action' value='actualizar_estado'>
                                                    <input type='hidden' name='turno_id' value='".$row['id']."'>
                                                    <button type='submit' name='estado' value='pendiente' class='dropdown-item'>Pendiente</button>
                                                    <button type='submit' name='estado' value='en_consulta' class='dropdown-item'>En Consulta</button>
                                                    <button type='submit' name='estado' value='atendido' class='dropdown-item'>Atendido</button>
                                                    <button type='submit' name='estado' value='cancelado' class='dropdown-item'>Cancelado</button>
                                                </form>
                                            </div>                                        </div>
                                        <a href='ver_paciente.php?id=".$row['paciente_id']."' class='btn btn-success btn-sm'><i class='fas fa-user'></i></a>
                                        ";
                                          // El botón de llegada ha sido eliminado
                                        
                                        echo "</td>";
                                    echo "</tr>";
                                }
                            } catch(PDOException $e) {
                                echo "Error: " . $e->getMessage();
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Turno -->
    <div class="modal fade" id="nuevoTurnoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Turno</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="crear">
                        <div class="form-group">
                            <label>Paciente</label>
                            <select name="paciente_id" class="form-control" required>
                                <?php
                                $sql = "SELECT id, nombre, apellido, dni FROM pacientes ORDER BY apellido, nombre";
                                try {
                                    $stmt = $conn->query($sql);
                                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='".$row['id']."'>".$row['apellido'].", ".$row['nombre']." - DNI: ".$row['dni']."</option>";
                                    }
                                } catch(PDOException $e) {
                                    echo "Error: " . $e->getMessage();
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fecha</label>
                            <input type="date" name="fecha_turno" class="form-control" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>">
                        </div>                        <div class="form-group">
                            <label>Hora</label>
                            <input type="hidden" name="hora_turno" value="<?php echo date('H:i:s', time()); ?>">
                            <input type="text" class="form-control" disabled value="<?php echo date('H:i:s', time()); ?>">
                            <small class="form-text text-muted">Hora actual: <?php echo date('h:i:s A', time()); ?> (se aplica automáticamente)</small>
                        </div>
                        <div class="form-group">
                            <label>Tipo de Turno</label>
                            <select name="tipo_turno" class="form-control" required>
                                <option value="Consulta">Consulta</option>
                                <option value="Resultados">Resultados</option>
                                <option value="Control">Control</option>
                                <option value="Procedimiento">Procedimiento</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <?php if ($multi_medico): ?>
                        <div class="form-group">
                            <label>Médico</label>
                            <select name="medico_id" class="form-control" required>
                                <option value="">Seleccione un médico</option>
                                <?php foreach ($doctores as $doctor): ?>
                                <option value="<?php echo $doctor['id']; ?>">
                                    <?php echo htmlspecialchars($doctor['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php else: ?>
                        <div class="form-group">
                            <label>Médico</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($config['medico_nombre'] ?? 'Médico Tratante'); ?>" readonly>
                            <small class="form-text text-muted">Médico asignado por defecto en configuración</small>
                        </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label>Notas</label>
                            <textarea name="notas" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="js/theme-manager.js"></script>    <script>
        $(document).ready(function() {
            // Actualizar la hora en tiempo real cuando se abre el modal de nuevo turno
            $('#nuevoTurnoModal').on('shown.bs.modal', function () {
                updateHoraActual();
                // Actualizar la hora cada segundo
                window.horaInterval = setInterval(updateHoraActual, 1000);
            });
            
            // Detener la actualización cuando se cierra el modal
            $('#nuevoTurnoModal').on('hidden.bs.modal', function () {
                clearInterval(window.horaInterval);
            });
            
            // Función para actualizar la hora actual
            function updateHoraActual() {
                var now = new Date();
                var hours = now.getHours().toString().padStart(2, '0');
                var minutes = now.getMinutes().toString().padStart(2, '0');
                var seconds = now.getSeconds().toString().padStart(2, '0');
                var ampm = hours >= 12 ? 'PM' : 'AM';
                var horaCompleta = hours + ':' + minutes + ':' + seconds;
                var horaAMPM = (hours > 12 ? hours - 12 : hours) + ':' + minutes + ':' + seconds + ' ' + ampm;
                
                // Actualizar el campo oculto y el campo visible
                $('input[name="hora_turno"]').val(horaCompleta);
                $('input[name="hora_turno"]').next('input').val(horaCompleta);
                $('.form-text.text-muted').text('Hora actual: ' + horaAMPM + ' (se aplica automáticamente)');
            }
            
            // Manejar cambio de fecha
            $('#filtroFecha').change(function() {
                window.location.href = 'turnos.php?fecha=' + $(this).val();
            });

            // Manejar el envío del formulario de estado
            $('.estado-form').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                var fecha = $('#filtroFecha').val();
                
                $.post('turnos.php', form.serialize() + '&fecha_turno=' + fecha, function(response) {
                    window.location.href = 'turnos.php?fecha=' + fecha;
                });
            });
            
            // Manejar el registro de llegada
            $('.registro-llegada-form').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                var fecha = $('#filtroFecha').val();
                var row = $(this).closest('tr');
                
                $.post('turnos.php', form.serialize(), function(response) {
                    // Recargar la página para mostrar los cambios
                    window.location.href = 'turnos.php?fecha=' + fecha + '&mensaje=Llegada registrada correctamente';
                }).fail(function() {
                    alert('Error al registrar la llegada');
                });
            });

            // Establecer la fecha del filtro si viene en la URL
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('fecha')) {
                $('#filtroFecha').val(urlParams.get('fecha'));
            }
        });
    </script>
</body>
</html>