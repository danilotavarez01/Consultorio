<?php
require_once 'session_config.php';
session_start();
require_once "permissions.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Verificar permisos para gestionar permisos de usuarios
if (!hasPermission('manage_receptionist_permissions') && !hasPermission('manage_users')) {
    header("location: unauthorized.php");
    exit;
}

require_once "config.php";

// Procesar cambios en los permisos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'update_permissions') {
            try {
                $conn->beginTransaction();
                
                // Eliminar permisos existentes para este usuario
                $stmt = $conn->prepare("DELETE FROM receptionist_permissions WHERE receptionist_id = ?");
                $stmt->execute([$_POST['user_id']]);
                
                // Insertar nuevos permisos
                if (!empty($_POST['permissions'])) {
                    $stmt = $conn->prepare("INSERT INTO receptionist_permissions (receptionist_id, permission, assigned_by) VALUES (?, ?, ?)");
                    foreach ($_POST['permissions'] as $permission) {
                        $stmt->execute([$_POST['user_id'], $permission, $_SESSION['id']]);
                    }
                }
                
                $conn->commit();
                $success_msg = "Permisos actualizados correctamente";
            } catch (Exception $e) {
                $conn->rollBack();
                $error_msg = "Error al actualizar los permisos: " . $e->getMessage();
            }
        }
    }
}

// Obtener lista de usuarios filtrada por rol si se especifica
if (isset($_GET['role']) && $_GET['role'] !== 'all') {
    $sql = "SELECT id, username, nombre, rol FROM usuarios WHERE rol = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_GET['role']]);
} else {
    // Obtener todos los usuarios (receptionistas y médicos)
    $sql = "SELECT id, username, nombre, rol FROM usuarios WHERE rol IN (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([ROLE_RECEPTIONIST, ROLE_DOCTOR]);
}
$usuarios = $stmt->fetchAll();

// Obtener lista de permisos disponibles organizados por categorías
$available_permissions = [
    'Gestión de Usuarios' => [
        'manage_users' => 'Gestión de Usuarios',
        'manage_doctors' => 'Gestionar Médicos',
        'manage_receptionist_permissions' => 'Gestionar Permisos de Usuarios'
    ],
    'Gestión de Pacientes' => [
        'manage_patients' => 'Gestionar Pacientes'
    ],
    'Gestión de Turnos' => [
        'manage_turnos' => 'Gestionar Turnos',
        'view_turnos' => 'Ver Turnos',
        'create_turnos' => 'Crear Turnos',
        'edit_turnos' => 'Editar Turnos',
        'delete_turnos' => 'Eliminar Turnos',
        'manage_appointments' => 'Gestionar Turnos (Legacy)'
    ],
    'Gestión de Citas' => [
        'manage_citas' => 'Gestionar Citas',
        'view_citas' => 'Ver Citas',
        'create_citas' => 'Crear Citas',
        'edit_citas' => 'Editar Citas',
        'delete_citas' => 'Eliminar Citas',
        'view_appointments' => 'Ver Citas (Legacy)'
    ],
    'Recetas y Prescripciones' => [
        'manage_prescriptions' => 'Gestionar Recetas',
        'view_prescriptions' => 'Ver Recetas'
    ],
    'Historiales Médicos' => [
        'view_medical_history' => 'Ver Historial Médico',
        'edit_medical_history' => 'Editar Historial Médico'
    ],
    'Catálogos y Procedimientos' => [
        'manage_diseases' => 'Gestionar Enfermedades',
        'manage_procedures' => 'Gestionar Procedimientos',
        'view_procedures' => 'Ver Procedimientos',
        'gestionar_catalogos' => 'Gestionar Catálogos',
        'manage_specialties' => 'Gestionar Especialidades'
    ],
    'Configuración y Administración' => [
        'manage_settings' => 'Configuración del Sistema',
        'generate_reports' => 'Generar Reportes',
        'manage_whatsapp' => 'Gestionar WhatsApp'
    ],
    'Facturación' => [
        'ver_facturacion' => 'Ver Facturación',
        'crear_factura' => 'Crear Facturas',
        'editar_factura' => 'Editar Facturas',
        'anular_factura' => 'Anular Facturas',
        'ver_reportes_facturacion' => 'Ver Reportes de Facturación'
    ]
];

// Si se selecciona un usuario, obtener sus permisos actuales
$selected_user = null;
$current_permissions = [];
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ? AND rol IN (?, ?)");
    $stmt->execute([$_GET['id'], ROLE_RECEPTIONIST, ROLE_DOCTOR]);
    $selected_user = $stmt->fetch();
    
    if ($selected_user) {
        // Para mantener compatibilidad, seguimos usando la tabla receptionist_permissions
        $stmt = $conn->prepare("SELECT permission FROM receptionist_permissions WHERE receptionist_id = ?");
        $stmt->execute([$_GET['id']]);
        $current_permissions = array_column($stmt->fetchAll(), 'permission');
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>    <meta charset="UTF-8">
    <title>Gestión de Permisos de Usuarios - Consultorio Médico</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; }
        .sidebar a:hover { background-color: #454d55; text-decoration: none; }
        .content { padding: 20px; }
        .permission-card { margin-bottom: 15px; }
    </style>
</head>
<body>
    <!-- Header con modo oscuro -->
    <?php include 'includes/header.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>            <!-- Content -->
            <div class="col-md-10 content">
                <h2>Gestión de Permisos de Usuarios</h2>
                <hr>

                <?php if (isset($success_msg)): ?>
                    <div class="alert alert-success"><?php echo $success_msg; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_msg)): ?>
                    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                <?php endif; ?>                <!-- Filtros de rol -->
                <div class="mb-3">
                    <div class="btn-group" role="group">
                        <a href="?role=all" class="btn btn-outline-primary <?php echo (!isset($_GET['role']) || $_GET['role'] === 'all') ? 'active' : ''; ?>">Todos</a>
                        <a href="?role=<?php echo ROLE_RECEPTIONIST; ?>" class="btn btn-outline-primary <?php echo (isset($_GET['role']) && $_GET['role'] === ROLE_RECEPTIONIST) ? 'active' : ''; ?>">Recepcionistas</a>
                        <a href="?role=<?php echo ROLE_DOCTOR; ?>" class="btn btn-outline-primary <?php echo (isset($_GET['role']) && $_GET['role'] === ROLE_DOCTOR) ? 'active' : ''; ?>">Médicos</a>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Lista de usuarios -->
                    <div class="col-md-4">
                        <div class="card"><div class="card-header">
                                <h5 class="card-title mb-0">Usuarios</h5>
                            </div>
                            <div class="card-body">                                <div class="list-group">
                                    <?php foreach ($usuarios as $usuario): ?>
                                    <a href="?id=<?php echo $usuario['id']; ?><?php echo isset($_GET['role']) ? '&role=' . htmlspecialchars($_GET['role']) : ''; ?>" 
                                       class="list-group-item list-group-item-action <?php echo (isset($_GET['id']) && $_GET['id'] == $usuario['id']) ? 'active' : ''; ?>">
                                        <?php echo htmlspecialchars($usuario['nombre']); ?>
                                        <small class="d-block text-muted">
                                            <?php echo htmlspecialchars($usuario['username']); ?> 
                                            (<?php echo getRoleName($usuario['rol']); ?>)
                                        </small>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de permisos -->
                    <div class="col-md-8">
                        <?php if ($selected_user): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    Permisos para <?php echo htmlspecialchars($selected_user['nombre']); ?> 
                                    (<?php echo getRoleName($selected_user['rol']); ?>)
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <strong><i class="fas fa-info-circle"></i> Información:</strong>
                                    Todos los permisos están disponibles para cualquier usuario. 
                                    Como administrador, puedes asignar los permisos que consideres apropiados según las responsabilidades del usuario.
                                </div>
                                
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_permissions">
                                    <input type="hidden" name="user_id" value="<?php echo $selected_user['id']; ?>">
                                      <div class="row">
                                        <?php
                                        // Mostrar todos los permisos organizados por categorías
                                        foreach ($available_permissions as $categoria => $permisos): 
                                        ?>
                                        <div class="col-12 mb-3">
                                            <h6 class="text-primary border-bottom pb-2">
                                                <i class="fas fa-folder-open"></i> <?php echo $categoria; ?>
                                            </h6>
                                            <div class="row">
                                                <?php foreach ($permisos as $permission => $label): ?>
                                                <div class="col-md-6 mb-2">
                                                    <div class="card permission-card">
                                                        <div class="card-body py-2">
                                                            <div class="custom-control custom-switch">
                                                                <input type="checkbox" 
                                                                       class="custom-control-input" 
                                                                       id="permission_<?php echo $permission; ?>"
                                                                       name="permissions[]"
                                                                       value="<?php echo $permission; ?>"
                                                                       <?php echo in_array($permission, $current_permissions) ? 'checked' : ''; ?>>
                                                                <label class="custom-control-label" for="permission_<?php echo $permission; ?>">
                                                                    <strong><?php echo $label; ?></strong>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Guardar Cambios
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Seleccione un usuario para gestionar sus permisos
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="js/theme-manager.js"></script>
</body>
</html>

<?php
// Función para obtener el nombre del rol
function getRoleName($rol) {
    switch ($rol) {
        case ROLE_ADMIN:
            return 'Administrador';
        case ROLE_DOCTOR:
            return 'Médico';
        case ROLE_RECEPTIONIST:
            return 'Recepcionista';
        default:
            return 'Usuario';
    }
}
?>