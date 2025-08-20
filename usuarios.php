<?php
require_once 'session_config.php';
session_start();
require_once "permissions.php";

// Verificar login y permiso de administración
if(!isset($_SESSION["loggedin"]) || !hasPermission('manage_users')){
    header("location: unauthorized.php");
    exit;
}

require_once "config.php";

// Procesar el formulario de nuevo usuario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'crear') {
            // Validar que solo los administradores puedan crear otros administradores
            if ($_POST['rol'] == ROLE_ADMIN && !isAdmin()) {
                header("location: unauthorized.php");
                exit;
            }

            $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            // Modified to include active status
            $sql = "INSERT INTO usuarios (username, password, nombre, rol, active) VALUES (?, ?, ?, ?, 1)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bindParam(1, $_POST['username'], PDO::PARAM_STR);
                $stmt->bindParam(2, $password_hash, PDO::PARAM_STR);
                $stmt->bindParam(3, $_POST['nombre'], PDO::PARAM_STR);
                $stmt->bindParam(4, $_POST['rol'], PDO::PARAM_STR);
                $stmt->execute();
                $stmt = null;
            }
        } elseif ($_POST['action'] == 'eliminar' && isset($_POST['user_id'])) {
            // Evitar que se eliminen administradores si el usuario actual no es admin
            $sql = "SELECT rol FROM usuarios WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$_POST['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user['rol'] == ROLE_ADMIN && !isAdmin()) {
                header("location: unauthorized.php");
                exit;
            }

            // Evitar eliminar al usuario admin y al usuario actual
            $sql = "DELETE FROM usuarios WHERE id = ? AND username != 'admin' AND id != ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bindParam(1, $_POST['user_id'], PDO::PARAM_INT);
                $stmt->bindParam(2, $_SESSION['id'], PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
            }
        } elseif ($_POST['action'] == 'toggle' && isset($_POST['user_id'])) {
            // Evitar que se active/desactive administradores si el usuario actual no es admin
            $sql = "SELECT rol FROM usuarios WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$_POST['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user['rol'] == ROLE_ADMIN && !isAdmin()) {
                header("location: unauthorized.php");
                exit;
            }

            // Evitar activar/desactivar al usuario admin y al usuario actual
            $sql = "UPDATE usuarios SET active = NOT active WHERE id = ? AND username != 'admin' AND id != ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bindParam(1, $_POST['user_id'], PDO::PARAM_INT);
                $stmt->bindParam(2, $_SESSION['id'], PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
            }
        } elseif ($_POST['action'] == 'editar' && isset($_POST['user_id'])) {
            // Evitar que se editen administradores si el usuario actual no es admin
            $sql = "SELECT rol FROM usuarios WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$_POST['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user['rol'] == ROLE_ADMIN && !isAdmin()) {
                header("location: unauthorized.php");
                exit;
            }
            
            // Evitar editar al usuario admin y al usuario actual si no es admin
            if ($_POST['user_id'] == $_SESSION['id'] && $_POST['rol'] != $_SESSION['rol'] && !isAdmin()) {
                header("location: unauthorized.php");
                exit;
            }
            
            // Si la contraseña está vacía, no la actualizamos
            if (empty($_POST['password'])) {
                $sql = "UPDATE usuarios SET username = ?, nombre = ?, rol = ? WHERE id = ? AND username != 'admin'";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bindParam(1, $_POST['username'], PDO::PARAM_STR);
                    $stmt->bindParam(2, $_POST['nombre'], PDO::PARAM_STR);
                    $stmt->bindParam(3, $_POST['rol'], PDO::PARAM_STR);
                    $stmt->bindParam(4, $_POST['user_id'], PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                }
            } else {
                // Si hay contraseña, la actualizamos
                $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET username = ?, password = ?, nombre = ?, rol = ? WHERE id = ? AND username != 'admin'";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bindParam(1, $_POST['username'], PDO::PARAM_STR);
                    $stmt->bindParam(2, $password_hash, PDO::PARAM_STR);
                    $stmt->bindParam(3, $_POST['nombre'], PDO::PARAM_STR);
                    $stmt->bindParam(4, $_POST['rol'], PDO::PARAM_STR);
                    $stmt->bindParam(5, $_POST['user_id'], PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - Consultorio Médico</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; }
        .sidebar a:hover { background-color: #454d55; text-decoration: none; }
        .content { padding: 20px; }
    </style>
</head>
<body>
    <!-- Header con modo oscuro -->
    <?php include 'includes/header.php'; ?>
    <div class="container-fluid">
        <div class="row">            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Content -->
            <div class="col-md-10 content">
                <h2>Gestión de Usuarios</h2>
                <hr>

                <!-- Botón para nuevo usuario -->
                <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#nuevoUsuarioModal">
                    <i class="fas fa-user-plus"></i> Nuevo Usuario
                </button>

                <!-- Tabla de usuarios -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Consulta optimizada con índices
                            $sql = "SELECT id, username, nombre, rol, active FROM usuarios ORDER BY id";
                            $stmt = $conn->query($sql);
                            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td>".$row['id']."</td>";
                                echo "<td>".$row['username']."</td>";
                                echo "<td>".$row['nombre']."</td>";
                                echo "<td>".ucfirst($row['rol'])."</td>";
                                echo "<td>";
                                echo isset($row['active']) && $row['active'] ? 
                                    '<span class="badge badge-success">Activo</span>' : 
                                    '<span class="badge badge-danger">Inactivo</span>';
                                echo "</td>";
                                // In the user actions section, add an edit button
                                echo "<td>";
                                if($row['username'] != 'admin') { // No mostrar botones para admin
                                    // Edit button
                                    echo "<button type='button' class='btn btn-primary btn-sm mr-1' data-toggle='modal' data-target='#editarUsuarioModal' 
                                        data-id='".$row['id']."' 
                                        data-username='".$row['username']."' 
                                        data-nombre='".$row['nombre']."' 
                                        data-rol='".$row['rol']."'>
                                        <i class='fas fa-edit'></i>
                                    </button>";
                                    
                                    // Toggle button
                                    echo "<form method='POST' style='display:inline; margin-right:5px;' onsubmit='return confirm(\"¿Está seguro de cambiar el estado de este usuario?\");'>";
                                    echo "<input type='hidden' name='action' value='toggle'>";
                                    echo "<input type='hidden' name='user_id' value='".$row['id']."'>";
                                    echo "<button type='submit' class='btn " . (isset($row['active']) && $row['active'] ? "btn-warning" : "btn-success") . " btn-sm'>";
                                    echo "<i class='fas fa-" . (isset($row['active']) && $row['active'] ? "ban" : "check") . "'></i> " . 
                                         (isset($row['active']) && $row['active'] ? "Desactivar" : "Activar") . "</button></form>";
                                    
                                    // Delete button
                                    echo "<form method='POST' style='display:inline;' onsubmit='return confirm(\"¿Está seguro de eliminar este usuario?\");'>";
                                    echo "<input type='hidden' name='action' value='eliminar'>";
                                    echo "<input type='hidden' name='user_id' value='".$row['id']."'>";
                                    echo "<button type='submit' class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></button></form>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Usuario -->
    <div class="modal fade" id="nuevoUsuarioModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Usuario</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="crear">
                        <div class="form-group">
                            <label>Nombre de Usuario</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Contraseña</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Rol</label>
                            <select name="rol" id="rol" class="form-control" required>
                                <option value="doctor">Doctor</option>
                                <option value="recepcionista">Recepcionista</option>
                                <option value="soporte">Soporte</option>
                                <?php if(isAdmin()): ?>
                                <option value="admin">Administrador</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <!-- Selector de especialidad (visible solo para médicos) -->
                        <div class="form-group" id="especialidad_container" style="display: none;">
                            <label>Especialidad</label>
                            <select name="especialidad_id" id="especialidad_id" class="form-control">
                                <option value="">Sin especialidad</option>
                                <?php
                                // Obtener lista de especialidades
                                $especialidades = $conn->query("SELECT id, nombre FROM especialidades ORDER BY nombre")->fetchAll();
                                foreach ($especialidades as $esp): ?>
                                <option value="<?php echo $esp['id']; ?>">
                                    <?php echo htmlspecialchars($esp['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
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

    <!-- Modal Editar Usuario -->
    <div class="modal fade" id="editarUsuarioModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Usuario</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="editar">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        <div class="form-group">
                            <label>Nombre de Usuario</label>
                            <input type="text" name="username" id="edit_username" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Contraseña</label>
                            <input type="password" name="password" class="form-control" placeholder="Dejar en blanco para mantener la actual">
                        </div>
                        <div class="form-group">
                            <label>Nombre Completo</label>
                            <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Rol</label>
                            <select name="rol" id="edit_rol" class="form-control" required>
                                <option value="doctor">Doctor</option>
                                <option value="recepcionista">Recepcionista</option>
                                <option value="soporte">Soporte</option>
                                <?php if(isAdmin()): ?>
                                <option value="admin">Administrador</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <!-- Selector de especialidad (visible solo para médicos) -->
                        <div class="form-group" id="edit_especialidad_container" style="display: none;">
                            <label>Especialidad</label>
                            <select name="especialidad_id" id="especialidad_id" class="form-control">
                                <option value="">Sin especialidad</option>
                                <?php
                                // Obtener lista de especialidades
                                $especialidades = $conn->query("SELECT id, nombre FROM especialidades ORDER BY nombre")->fetchAll();
                                foreach ($especialidades as $esp): ?>
                                <option value="<?php echo $esp['id']; ?>">
                                    <?php echo htmlspecialchars($esp['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
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
    $('#editarUsuarioModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var username = button.data('username');
        var nombre = button.data('nombre');
        var rol = button.data('rol');
        
        var modal = $(this);
        modal.find('#edit_user_id').val(id);
        modal.find('#edit_username').val(username);
        modal.find('#edit_nombre').val(nombre);
        modal.find('#edit_rol').val(rol);
    });

    // Mostrar/ocultar selector de especialidad según el rol
    $('#rol, #edit_rol').change(function() {
        var rol = $(this).val();
        var especialidadContainer = $(this).closest('.modal').find('.form-group').has('[name="especialidad_id"]');
        
        if (rol == 'doctor') {
            especialidadContainer.show();
        } else {
            especialidadContainer.hide();
        }
    });

    $(document).ready(function() {
        // Función para mostrar/ocultar selector de especialidad
        function toggleEspecialidad() {
            if ($('#rol').val() === 'doctor') {
                $('#especialidad_container').show();
            } else {
                $('#especialidad_container').hide();
                $('#especialidad_id').val('');
            }
        }

        // Función para el modal de editar
        function toggleEditEspecialidad() {
            if ($('#edit_rol').val() === 'doctor') {
                $('#edit_especialidad_container').show();
            } else {
                $('#edit_especialidad_container').hide();
            }
        }

        // Manejar cambio de rol para nuevo usuario
        $('#rol').on('change', toggleEspecialidad);
        
        // Manejar cambio de rol para editar usuario
        $('#edit_rol').on('change', toggleEditEspecialidad);

        // Aplicar al cargar la página
        toggleEspecialidad();
        toggleEditEspecialidad();

        // Al editar un usuario
        $('.btn-edit').on('click', function() {
            const userId = $(this).data('id');
            $.ajax({
                url: 'get_user_details.php',
                type: 'POST',
                data: { id: userId },
                dataType: 'json',
                success: function(response) {
                    $('#edit_id').val(response.id);
                    $('#edit_username').val(response.username);
                    $('#edit_nombre').val(response.nombre);
                    $('#edit_rol').val(response.rol);
                    $('#edit_especialidad_id').val(response.especialidad_id || '');
                    if (response.rol === 'doctor') {
                        $('#edit_especialidad_container').show();
                    } else {
                        $('#edit_especialidad_container').hide();
                    }
                }
            });
        });
    });
    </script>
</body>
</html>

$permissions = [
    PERM_MANAGE_TURNOS => 'Gestionar turnos',
    PERM_MANAGE_CITAS => 'Gestionar citas',
    PERM_MANAGE_USUARIOS => 'Gestionar usuarios',
    PERM_MANAGE_ENFERMEDADES => 'Gestionar enfermedades',
    PERM_MANAGE_RECETAS => 'Gestionar recetas',
    PERM_MANAGE_RECEPCIONISTAS => 'Gestionar recepcionistas',
    PERM_MANAGE_DOCTORES => 'Gestionar doctores',
    PERM_MANAGE_ADMIN => 'Gestionar admin',
];

