<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';
require_once 'permissions.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || !isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}

// Verificar permisos
if (!hasPermission('seguros_medicos')) {
    header("Location: index.php?error=Sin permisos para acceder a seguros médicos");
    exit();
}

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'crear':
                try {
                    $stmt = $conn->prepare("INSERT INTO seguro_medico (descripcion) VALUES (?)");
                    $stmt->execute([trim($_POST['descripcion'])]);
                    header("Location: seguro_medico.php?mensaje=Seguro médico creado exitosamente");
                    exit();
                } catch(PDOException $e) {
                    $error = "Error al crear seguro médico: " . $e->getMessage();
                }
                break;
                
            case 'editar':
                try {
                    $stmt = $conn->prepare("UPDATE seguro_medico SET descripcion = ? WHERE id = ?");
                    $stmt->execute([trim($_POST['descripcion']), $_POST['id']]);
                    header("Location: seguro_medico.php?mensaje=Seguro médico actualizado exitosamente");
                    exit();
                } catch(PDOException $e) {
                    $error = "Error al actualizar seguro médico: " . $e->getMessage();
                }
                break;
                
            case 'cambiar_estado':
                try {
                    $stmt = $conn->prepare("UPDATE seguro_medico SET activo = ? WHERE id = ?");
                    $activo = $_POST['activo'] == '1' ? 0 : 1; // Toggle estado
                    $stmt->execute([$activo, $_POST['id']]);
                    
                    $estado_texto = $activo ? 'activado' : 'desactivado';
                    header("Location: seguro_medico.php?mensaje=Seguro médico $estado_texto exitosamente");
                    exit();
                } catch(PDOException $e) {
                    $error = "Error al cambiar estado: " . $e->getMessage();
                }
                break;
                
            case 'eliminar':
                try {
                    // Verificar si tiene pacientes asociados
                    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pacientes WHERE seguro_medico_id = ?");
                    $stmt->execute([$_POST['id']]);
                    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($resultado['total'] > 0) {
                        $error = "No se puede eliminar el seguro médico porque tiene {$resultado['total']} paciente(s) asociado(s)";
                    } else {
                        $stmt = $conn->prepare("DELETE FROM seguro_medico WHERE id = ?");
                        $stmt->execute([$_POST['id']]);
                        header("Location: seguro_medico.php?mensaje=Seguro médico eliminado exitosamente");
                        exit();
                    }
                } catch(PDOException $e) {
                    $error = "Error al eliminar seguro médico: " . $e->getMessage();
                }
                break;
        }
    }
}

// Obtener lista de seguros médicos
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

$where_conditions = [];
$params = [];

if ($filtro_estado !== '') {
    $where_conditions[] = "activo = ?";
    $params[] = $filtro_estado;
}

if (!empty($busqueda)) {
    $where_conditions[] = "descripcion LIKE ?";
    $params[] = "%$busqueda%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

$sql = "SELECT s.*, 
        (SELECT COUNT(*) FROM pacientes p WHERE p.seguro_medico_id = s.id) as total_pacientes
        FROM seguro_medico s 
        $where_clause 
        ORDER BY s.descripcion ASC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $seguros = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error al obtener seguros médicos: " . $e->getMessage();
    $seguros = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguros Médicos - Consultorio Médico</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; }
        .sidebar a:hover { background-color: #454d55; text-decoration: none; }
        .content { padding: 20px; }
        
        .badge-activo { background-color: #28a745 !important; }
        .badge-inactivo { background-color: #dc3545 !important; }
        .card-stats {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            border: none;
        }
        .btn-grupo {
            gap: 5px;
        }
        
        /* Efectos hover para las filas */
        .table-hover tbody tr:hover {
            transform: translateY(-1px);
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Content -->
            <div class="col-md-10 content">
                <h2><i class="fas fa-shield-alt mr-2"></i>Seguros Médicos</h2>
                <hr>

                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="text-muted mb-0">Gestión de seguros médicos y ARS</p>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCrear">
                        <i class="fas fa-plus mr-2"></i>Nuevo Seguro
                    </button>
                </div>

                <!-- Alertas -->
                <?php if(isset($_GET['mensaje'])): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>
                    <div><?php echo htmlspecialchars($_GET['mensaje']); ?></div>
                    <button type="button" class="close ml-auto" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>

                <?php if(isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <div><?php echo htmlspecialchars($error); ?></div>
                    <button type="button" class="close ml-auto" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card card-stats">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?php echo count($seguros); ?></h3>
                                <p class="mb-0">Total Seguros</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?php echo count(array_filter($seguros, function($s) { return $s['activo'] == 1; })); ?></h3>
                                <p class="mb-0">Activos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?php echo count(array_filter($seguros, function($s) { return $s['activo'] == 0; })); ?></h3>
                                <p class="mb-0">Inactivos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?php echo array_sum(array_column($seguros, 'total_pacientes')); ?></h3>
                                <p class="mb-0">Pacientes Asignados</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row align-items-end">
                            <div class="col-md-4">
                                <label>Buscar por descripción</label>
                                <input type="text" name="busqueda" class="form-control" 
                                       value="<?php echo htmlspecialchars($busqueda); ?>" 
                                       placeholder="Nombre del seguro...">
                            </div>
                            <div class="col-md-3">
                                <label>Estado</label>
                                <select name="estado" class="form-control">
                                    <option value="">Todos</option>
                                    <option value="1" <?php echo $filtro_estado === '1' ? 'selected' : ''; ?>>Activos</option>
                                    <option value="0" <?php echo $filtro_estado === '0' ? 'selected' : ''; ?>>Inactivos</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fas fa-search mr-1"></i>Buscar
                                </button>
                                <a href="seguro_medico.php" class="btn btn-secondary">
                                    <i class="fas fa-times mr-1"></i>Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla -->
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Pacientes</th>
                                        <th>Fecha Creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($seguros)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            No se encontraron seguros médicos
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($seguros as $seguro): ?>
                                        <tr>
                                            <td><strong>#<?php echo $seguro['id']; ?></strong></td>
                                            <td><?php echo htmlspecialchars($seguro['descripcion']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $seguro['activo'] ? 'badge-activo' : 'badge-inactivo'; ?>">
                                                    <i class="fas fa-<?php echo $seguro['activo'] ? 'check' : 'times'; ?> mr-1"></i>
                                                    <?php echo $seguro['activo'] ? 'Activo' : 'Inactivo'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <i class="fas fa-users mr-1"></i>
                                                    <?php echo $seguro['total_pacientes']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($seguro['fecha_creacion'])); ?></td>
                                            <td>
                                                <div class="btn-group btn-grupo" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editarSeguro(<?php echo $seguro['id']; ?>, '<?php echo htmlspecialchars($seguro['descripcion'], ENT_QUOTES); ?>')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="cambiar_estado">
                                                        <input type="hidden" name="id" value="<?php echo $seguro['id']; ?>">
                                                        <input type="hidden" name="activo" value="<?php echo $seguro['activo']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-<?php echo $seguro['activo'] ? 'warning' : 'success'; ?>"
                                                                onclick="return confirm('¿Estás seguro de <?php echo $seguro['activo'] ? 'desactivar' : 'activar'; ?> este seguro médico?')">
                                                            <i class="fas fa-<?php echo $seguro['activo'] ? 'pause' : 'play'; ?>"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <?php if ($seguro['total_pacientes'] == 0): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="eliminar">
                                                        <input type="hidden" name="id" value="<?php echo $seguro['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                onclick="return confirm('¿Estás seguro de eliminar este seguro médico?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                    <?php else: ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" disabled 
                                                            title="No se puede eliminar porque tiene pacientes asociados">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Crear -->
    <div class="modal fade" id="modalCrear" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus mr-2"></i>Nuevo Seguro Médico</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="crear">
                        <div class="form-group">
                            <label for="descripcion_crear">Descripción *</label>
                            <input type="text" class="form-control" id="descripcion_crear" name="descripcion" 
                                   required maxlength="255" placeholder="Ej: ARS Humano, Seguros Reservas...">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar -->
    <div class="modal fade" id="modalEditar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Editar Seguro Médico</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="editar">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="form-group">
                            <label for="descripcion_editar">Descripción *</label>
                            <input type="text" class="form-control" id="descripcion_editar" name="descripcion" 
                                   required maxlength="255">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/popper-2.5.4.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="js/theme-manager.js"></script>
    
    <script>
        function editarSeguro(id, descripcion) {
            $('#edit_id').val(id);
            $('#descripcion_editar').val(descripcion);
            $('#modalEditar').modal('show');
        }
    </script>
</body>
</html>
