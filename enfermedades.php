<?php
require_once 'session_config.php';
session_start();
require_once "permissions.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Verificar permisos para gestionar enfermedades
if (!hasPermission('manage_diseases')) {
    header("location: unauthorized.php");
    exit;
}

require_once "config.php";

// Procesar el formulario de nueva enfermedad
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'crear') {
            $sql = "INSERT INTO enfermedades (nombre, descripcion) VALUES (?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->execute([$_POST['nombre'], $_POST['descripcion']]);
            }
        } elseif ($_POST['action'] == 'editar') {
            $sql = "UPDATE enfermedades SET nombre = ?, descripcion = ? WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->execute([$_POST['nombre'], $_POST['descripcion'], $_POST['enfermedad_id']]);
            }
        } elseif ($_POST['action'] == 'eliminar') {
            // Primero verificar si la enfermedad está en uso
            $sql = "SELECT COUNT(*) FROM paciente_enfermedades WHERE enfermedad_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$_POST['enfermedad_id']]);
            $count = $stmt->fetchColumn();
            
            if ($count == 0) {
                $sql = "DELETE FROM enfermedades WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$_POST['enfermedad_id']]);
            }
        }
    }
}

// Obtener lista de enfermedades
$enfermedades = $conn->query("SELECT * FROM enfermedades ORDER BY nombre")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Enfermedades - Consultorio Médico</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Content -->
            <div class="col-md-10 content">
                <h2>Gestión de Enfermedades</h2>
                <hr>

                <!-- Botón para nueva enfermedad -->
                <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#nuevaEnfermedadModal">
                    <i class="fas fa-plus"></i> Nueva Enfermedad
                </button>

                <!-- Campo de búsqueda -->
                <div class="form-group mb-3">
                    <input type="text" id="searchEnfermedad" class="form-control" placeholder="Buscar enfermedad...">
                </div>

                <!-- Tabla de enfermedades -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($enfermedades as $e): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($e['id']); ?></td>
                                <td><?php echo htmlspecialchars($e['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($e['descripcion']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editarEnfermedad(<?php echo htmlspecialchars(json_encode($e)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar esta enfermedad?');">
                                        <input type="hidden" name="action" value="eliminar">
                                        <input type="hidden" name="enfermedad_id" value="<?php echo $e['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="crear">
                        <div class="form-group">
                            <label>Nombre de la Enfermedad</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3"></textarea>
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

    <!-- Modal Editar Enfermedad -->
    <div class="modal fade" id="editarEnfermedadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Enfermedad</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="editar">
                        <input type="hidden" name="enfermedad_id" id="edit_enfermedad_id">
                        <div class="form-group">
                            <label>Nombre de la Enfermedad</label>
                            <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea name="descripcion" id="edit_descripcion" class="form-control" rows="3"></textarea>
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

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="js/theme-manager.js"></script>
    <script>
        function editarEnfermedad(enfermedad) {
            $('#edit_enfermedad_id').val(enfermedad.id);
            $('#edit_nombre').val(enfermedad.nombre);
            $('#edit_descripcion').val(enfermedad.descripcion);
            $('#editarEnfermedadModal').modal('show');
        }

        // Función de búsqueda
        $(document).ready(function() {
            $("#searchEnfermedad").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("table tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
</body>
</html>