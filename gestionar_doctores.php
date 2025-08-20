<?php
require_once 'session_config.php';
session_start();
require_once "permissions.php";

// Verificar si el usuario está logueado
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Verificar permisos para gestionar médicos
if (!hasPermission('manage_users') && !hasPermission('manage_doctors')) {
    header("location: unauthorized.php");
    exit;
}

require_once "config.php";

$success_msg = $error_msg = '';

// Manejar creación de nuevo doctor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'crear_doctor') {
    try {
        $conn->beginTransaction();        // Validar campos requeridos
        if(empty($_POST['nombre']) || empty($_POST['apellido'])) {
            throw new Exception("Por favor, complete todos los campos obligatorios");
        }
        
        // Generar nombre completo
        $nombre_completo = $_POST['nombre'] . ' ' . $_POST['apellido'];
          // Función para eliminar acentos y caracteres especiales
        function eliminarAcentos($cadena) {
            $originales = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ';
            $modificadas = 'AAAAAAACEEEEIIIIDNOOOOOOUUUUYBsaaaaaaaceeeeiiiidnoooooouuuuyby';
            return strtr($cadena, $originales, $modificadas);
        }
        
        // Generar un nombre de usuario automático basado en el nombre y apellido
        $nombre_sin_acentos = eliminarAcentos($_POST['nombre']);
        $apellido_sin_acentos = eliminarAcentos($_POST['apellido']);
        
        $username_base = strtolower(
            substr($nombre_sin_acentos, 0, 1) . 
            $apellido_sin_acentos
        );
        $username_base = preg_replace('/[^a-z0-9]/', '', $username_base); // Solo permitir letras y números
        
        // Verificar si el username ya existe y agregar un número si es necesario
        $username = $username_base;
        $contador = 1;
        
        do {
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ?");
            $stmt->execute([$username]);
            if($stmt->rowCount() == 0) {
                break; // Username disponible
            }
            $username = $username_base . $contador++;
        } while(true);
          // Generar una contraseña aleatoria (como los doctores no inician sesión)
        $password_random = bin2hex(random_bytes(8)); // 16 caracteres aleatorios
        $password_hash = password_hash($password_random, PASSWORD_DEFAULT);
        
        // Comprobar especialidad_id
        $especialidad_id = null;
        if (!empty($_POST['especialidad_id'])) {
            // Verificar si la especialidad existe
            $stmt = $conn->prepare("SELECT id FROM especialidades WHERE id = ?");
            $stmt->execute([$_POST['especialidad_id']]);
            if ($stmt->rowCount() > 0) {
                $especialidad_id = $_POST['especialidad_id'];
            }
        }
        
        // Insertar el nuevo doctor
        $estado = isset($_POST['estado']) && $_POST['estado'] === 'inactivo' ? 'inactivo' : 'activo';
        $sql = "INSERT INTO usuarios (username, password, nombre, rol, especialidad_id, estado) VALUES (?, ?, ?, 'doctor', ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(1, $username, PDO::PARAM_STR);
        $stmt->bindParam(2, $password_hash, PDO::PARAM_STR);
        $stmt->bindParam(3, $nombre_completo, PDO::PARAM_STR);
        $stmt->bindParam(4, $especialidad_id, PDO::PARAM_INT);
        $stmt->bindParam(5, $estado, PDO::PARAM_STR);
        $stmt->execute();
        
        $conn->commit();
        $success_msg = "Doctor creado exitosamente";
    } catch (Exception $e) {
        $conn->rollBack();
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Manejar actualización de doctor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'actualizar_doctor') {
    try {
        $conn->beginTransaction();
        
        // Validar campos requeridos
        if(empty($_POST['edit_nombre']) || empty($_POST['edit_apellido'])) {
            throw new Exception("Por favor, complete todos los campos obligatorios");
        }        // Generar nombre completo
        $nombre_completo = $_POST['edit_nombre'] . ' ' . $_POST['edit_apellido'];
        
        // Comprobar especialidad_id
        $especialidad_id = null;
        if (!empty($_POST['edit_especialidad_id'])) {
            // Verificar si la especialidad existe
            $stmt = $conn->prepare("SELECT id FROM especialidades WHERE id = ?");
            $stmt->execute([$_POST['edit_especialidad_id']]);
            if ($stmt->rowCount() > 0) {
                $especialidad_id = $_POST['edit_especialidad_id'];
            }
        }
        
        // Actualizar información del doctor (sin modificar la contraseña)
        $estado = isset($_POST['edit_estado']) && $_POST['edit_estado'] === 'inactivo' ? 'inactivo' : 'activo';
        $sql = "UPDATE usuarios SET nombre = ?, especialidad_id = ?, estado = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(1, $nombre_completo, PDO::PARAM_STR);
        $stmt->bindParam(2, $especialidad_id, PDO::PARAM_INT);
        $stmt->bindParam(3, $estado, PDO::PARAM_STR);
        $stmt->bindParam(4, $_POST['doctor_id'], PDO::PARAM_INT);
        $stmt->execute();
        
        $conn->commit();
        $success_msg = "Doctor actualizado exitosamente";
    } catch (Exception $e) {
        $conn->rollBack();
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Manejar eliminación de doctor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'eliminar_doctor') {
    try {
        $conn->beginTransaction();
        
        // Verificar si el doctor tiene consultas asignadas
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM historial_medico WHERE doctor_id = ?");
        $stmt->execute([$_POST['doctor_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            throw new Exception("No se puede eliminar el doctor porque tiene consultas asociadas");
        }
        
        // Verificar si el doctor tiene citas asignadas
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM citas WHERE doctor_id = ?");
        $stmt->execute([$_POST['doctor_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            throw new Exception("No se puede eliminar el doctor porque tiene citas asociadas");
        }
        
        // Eliminar el doctor
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ? AND rol = 'doctor'");
        $stmt->execute([$_POST['doctor_id']]);
        
        $conn->commit();
        $success_msg = "Doctor eliminado exitosamente";
    } catch (Exception $e) {
        $conn->rollBack();
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Obtener lista de especialidades para el formulario
$especialidades = $conn->query("SELECT id, nombre FROM especialidades ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

// Función para separar nombre y apellido
function extraerNombreApellido($nombreCompleto) {
    $partes = explode(' ', $nombreCompleto, 2);
    if (count($partes) > 1) {
        return ['nombre' => $partes[0], 'apellido' => $partes[1]];
    }
    return ['nombre' => $nombreCompleto, 'apellido' => ''];
}

// Obtener lista de doctores
$doctores = [];
$stmt = $conn->query("SELECT u.id, u.username, u.nombre, u.rol, u.especialidad_id, u.estado, e.nombre as especialidad 
                      FROM usuarios u 
                      LEFT JOIN especialidades e ON u.especialidad_id = e.id 
                      WHERE u.rol = 'doctor' 
                      ORDER BY u.nombre");
$doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Doctores - Consultorio Médico</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; }
        .sidebar a:hover { background-color: #454d55; text-decoration: none; }
        .content-wrapper { padding: 20px; background: #f8f9fa; color: #222; }
        .table { background-color: #fff !important; color: #222; border-radius: 12px; overflow: hidden; }
        thead th { background-color: #e9ecef !important; color: #222; }
        .badge-doctor { font-size: 0.8rem; padding: 5px 10px; background-color: #e9ecef; color: #222; }
        .btn-circle {
            width: 40px;
            height: 40px;
            padding: 6px 0;
            border-radius: 20px;
            text-align: center;
            font-size: 18px;
            line-height: 1.42857;
            margin-right: 10px;
            background-color: #e9ecef;
            color: #222;
        }
        .modal-content { background-color: #fff; color: #222; }
        .form-control { background-color: #f8f9fa; color: #222; border: 1px solid #ced4da; }
        .form-control:focus { background-color: #fff; color: #222; border-color: #007bff; }
        .btn-primary { background-color: #007bff; border-color: #007bff; }
        .btn-secondary { background-color: #454d55; border-color: #454d55; color: #fff; }
        .btn-danger { background-color: #c82333; border-color: #c82333; }
        .btn-warning { background-color: #ffc107; border-color: #ffc107; color: #222; }
    </style>
</head>
<body>

<!-- Header con modo oscuro -->
<?php include 'includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Contenido principal -->
        <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4 content-wrapper">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1><i class="fas fa-user-md"></i> Gestión de Doctores</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#agregarDoctorModal">
                        <i class="fas fa-plus"></i> Nuevo Doctor
                    </button>
                </div>
            </div>
            
            <?php if(!empty($success_msg)): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>
            
            <?php if(!empty($error_msg)): ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            <?php endif; ?>
            
            <!-- Vista de doctores -->
            <div class="table-responsive">
                <table class="table table-striped table-hover" style="background:#fff; border-radius:12px; overflow:hidden;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Usuario</th>
                            <th>Nombre</th>
                            <th>Especialidad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($doctores) > 0): ?>
                            <?php foreach($doctores as $i => $doctor): 
                                $datos = extraerNombreApellido($doctor['nombre']);
                                $estadoClass = ($doctor['estado'] === 'activo') ? 'table-success' : 'table-secondary';
                            ?>
                            <tr class="<?php echo $estadoClass; ?>">
                                <td><?php echo $i+1; ?></td>
                                <td><?php echo htmlspecialchars($doctor['username']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['nombre']); ?></td>
                                <td><?php echo !empty($doctor['especialidad']) ? htmlspecialchars($doctor['especialidad']) : '<span class=\'text-muted\'>Sin especialidad</span>'; ?></td>
                                <td><span class="badge badge-<?php echo ($doctor['estado'] === 'activo') ? 'success' : 'secondary'; ?>"> <?php echo ucfirst($doctor['estado']); ?> </span></td>
                                <td>
                                    <button class="btn btn-warning btn-sm edit-doctor-btn" 
                                            data-id="<?php echo $doctor['id']; ?>"
                                            data-username="<?php echo htmlspecialchars($doctor['username']); ?>"
                                            data-nombre="<?php echo htmlspecialchars($datos['nombre']); ?>"
                                            data-apellido="<?php echo htmlspecialchars($datos['apellido']); ?>"
                                            data-especialidad="<?php echo $doctor['especialidad_id']; ?>"
                                            data-toggle="modal" data-target="#editarDoctorModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm delete-doctor-btn" 
                                            data-id="<?php echo $doctor['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($doctor['nombre']); ?>"
                                            data-toggle="modal" data-target="#eliminarDoctorModal">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted"><i class="fas fa-info-circle"></i> No hay doctores registrados en el sistema.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<!-- Modal para agregar doctor -->
<div class="modal fade" id="agregarDoctorModal" tabindex="-1" role="dialog" aria-labelledby="agregarDoctorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="agregarDoctorModalLabel">Nuevo Doctor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="">                <div class="modal-body">
                    <input type="hidden" name="action" value="crear_doctor">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Apellido</label>
                        <input type="text" name="apellido" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Especialidad</label>
                        <select name="especialidad_id" class="form-control">
                            <option value="NULL">Sin especialidad</option>
                            <?php foreach($especialidades as $especialidad): ?>
                                <option value="<?php echo $especialidad['id']; ?>"><?php echo htmlspecialchars($especialidad['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" class="form-control">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar doctor -->
<div class="modal fade" id="editarDoctorModal" tabindex="-1" role="dialog" aria-labelledby="editarDoctorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarDoctorModalLabel">Editar Doctor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="">                <div class="modal-body">
                    <input type="hidden" name="action" value="actualizar_doctor">
                    <input type="hidden" name="doctor_id" id="edit_doctor_id">
                    <input type="hidden" id="edit_username" name="edit_username">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="edit_nombre" id="edit_nombre" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Apellido</label>
                        <input type="text" name="edit_apellido" id="edit_apellido" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Especialidad</label>
                        <select name="edit_especialidad_id" id="edit_especialidad_id" class="form-control">
                            <option value="NULL">Sin especialidad</option>
                            <?php foreach($especialidades as $especialidad): ?>
                                <option value="<?php echo $especialidad['id']; ?>"><?php echo htmlspecialchars($especialidad['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="edit_estado" id="edit_estado" class="form-control">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para eliminar doctor -->
<div class="modal fade" id="eliminarDoctorModal" tabindex="-1" role="dialog" aria-labelledby="eliminarDoctorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eliminarDoctorModalLabel">Eliminar Doctor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="eliminar_doctor">
                    <input type="hidden" name="doctor_id" id="delete_doctor_id">
                    <p>¿Está seguro que desea eliminar al doctor <strong id="delete_doctor_name"></strong>?</p>
                    <p class="text-danger">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/popper.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="js/theme-manager.js"></script>
<script>
$(document).ready(function() {
    // Editar doctor
    $('.edit-doctor-btn').on('click', function() {
        const id = $(this).data('id');
        const username = $(this).data('username');
        const nombre = $(this).data('nombre');
        const apellido = $(this).data('apellido');
        const especialidad = $(this).data('especialidad');
        const estado = $(this).closest('.card-doctor').find('.badge').text().trim().toLowerCase();
        $('#edit_doctor_id').val(id);
        $('#edit_username').val(username);
        $('#edit_nombre').val(nombre);
        $('#edit_apellido').val(apellido);
        $('#edit_especialidad_id').val(especialidad);
        $('#edit_estado').val(estado);
    });
    // Eliminar doctor
    $('.delete-doctor-btn').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        $('#delete_doctor_id').val(id);
        $('#delete_doctor_name').text(name);
    });
});
</script>
</body>
</html>

