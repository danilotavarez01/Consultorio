<?php
session_start();
require_once "permissions.php";
require_once "config.php";

// Verificar si el usuario ha iniciado sesión
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Verificar si el usuario ha venido desde configuración o es admin
if ((!isset($_SESSION['from_config']) || $_SESSION['from_config'] !== true) && $_SESSION["username"] !== "admin") {
    header("location: unauthorized.php");
    exit;
}

// Marcar que el usuario está en esta página desde configuración
$_SESSION['from_config'] = true;

$success_msg = $error_msg = "";

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        
        // Acción: Agregar nueva especialidad
        if ($_POST['action'] == 'add_especialidad') {
            $codigo = trim($_POST['codigo']);
            $nombre = trim($_POST['nombre']);
            $descripcion = trim($_POST['descripcion']);
            $estado = isset($_POST['estado']) ? 'activo' : 'inactivo';
            
            // Validaciones básicas
            if (empty($codigo)) {
                $error_msg = "Debe ingresar un código para la especialidad.";
            } elseif (empty($nombre)) {
                $error_msg = "Debe ingresar un nombre para la especialidad.";
            } else {
                try {
                    // Verificar si ya existe una especialidad con el mismo código
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM especialidades WHERE codigo = ?");
                    $stmt->execute([$codigo]);
                    $count = $stmt->fetchColumn();
                    
                    if ($count > 0) {
                        $error_msg = "El código de especialidad '$codigo' ya está en uso.";
                    } else {
                        // Insertar nueva especialidad
                        $stmt = $conn->prepare("INSERT INTO especialidades (codigo, nombre, descripcion, estado) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$codigo, $nombre, $descripcion, $estado]);
                        
                        $success_msg = "Especialidad agregada correctamente.";
                    }
                } catch (PDOException $e) {
                    $error_msg = "Error al agregar la especialidad: " . $e->getMessage();
                }
            }
        }
        
        // Acción: Actualizar especialidad
        elseif ($_POST['action'] == 'update_especialidad') {
            $id = $_POST['id'];
            $codigo = trim($_POST['codigo']);
            $nombre = trim($_POST['nombre']);
            $descripcion = trim($_POST['descripcion']);
            $estado = isset($_POST['estado']) ? 'activo' : 'inactivo';
            
            // Validaciones básicas
            if (empty($codigo)) {
                $error_msg = "Debe ingresar un código para la especialidad.";
            } elseif (empty($nombre)) {
                $error_msg = "Debe ingresar un nombre para la especialidad.";
            } else {
                try {
                    // Verificar si ya existe otra especialidad con el mismo código
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM especialidades WHERE codigo = ? AND id != ?");
                    $stmt->execute([$codigo, $id]);
                    $count = $stmt->fetchColumn();
                    
                    if ($count > 0) {
                        $error_msg = "El código de especialidad '$codigo' ya está en uso.";
                    } else {
                        // Actualizar especialidad
                        $stmt = $conn->prepare("UPDATE especialidades SET codigo = ?, nombre = ?, descripcion = ?, estado = ? WHERE id = ?");
                        $stmt->execute([$codigo, $nombre, $descripcion, $estado, $id]);
                        
                        $success_msg = "Especialidad actualizada correctamente.";
                    }
                } catch (PDOException $e) {
                    $error_msg = "Error al actualizar la especialidad: " . $e->getMessage();
                }
            }
        }
        
        // Acción: Eliminar especialidad
        elseif ($_POST['action'] == 'delete_especialidad') {
            $id = $_POST['id'];
            
            try {
                // Verificar si la especialidad está en uso
                $stmt = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE especialidad_id = ?");
                $stmt->execute([$id]);
                $enUso = $stmt->fetchColumn();
                
                if ($enUso > 0) {
                    $error_msg = "No se puede eliminar esta especialidad porque está asignada a uno o más médicos.";
                } else {
                    // Eliminar la especialidad
                    $stmt = $conn->prepare("DELETE FROM especialidades WHERE id = ?");
                    $stmt->execute([$id]);
                    
                    $success_msg = "Especialidad eliminada correctamente.";
                }
            } catch (PDOException $e) {
                $error_msg = "Error al eliminar la especialidad: " . $e->getMessage();
            }
        }
    }
}

// Obtener la lista de especialidades
$especialidades = [];
try {
    $sql = "SELECT * FROM especialidades ORDER BY nombre ASC";
    $stmt = $conn->query($sql);
    $especialidades = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_msg = "Error al cargar las especialidades: " . $e->getMessage();
}

// Obtener detalles de una especialidad específica si se solicita editar
$especialidad_editar = null;
if (isset($_GET['editar']) && !empty($_GET['editar'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM especialidades WHERE id = ?");
        $stmt->execute([$_GET['editar']]);
        $especialidad_editar = $stmt->fetch();
        
        if (!$especialidad_editar) {
            $error_msg = "No se encontró la especialidad solicitada.";
        }
    } catch (PDOException $e) {
        $error_msg = "Error al cargar la especialidad: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Especialidades - Consultorio Médico</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; }
        .sidebar a:hover { background-color: #454d55; text-decoration: none; }
        .content { padding: 20px; }
        .action-buttons { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Content -->            <div class="col-md-10 content">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2>Gestión de Especialidades Médicas</h2>
                    <a href="configuracion.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Configuración
                    </a>
                </div>
                <hr>
                
                <!-- Mensajes de éxito o error -->
                <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- Formulario para agregar/editar especialidades -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <?php echo $especialidad_editar ? 'Editar Especialidad' : 'Agregar Nueva Especialidad'; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="gestionar_especialidades.php">
                                    <input type="hidden" name="action" value="<?php echo $especialidad_editar ? 'update_especialidad' : 'add_especialidad'; ?>">
                                    <?php if ($especialidad_editar): ?>
                                    <input type="hidden" name="id" value="<?php echo $especialidad_editar['id']; ?>">
                                    <?php endif; ?>
                                    
                                    <div class="form-group">
                                        <label for="codigo">Código <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="codigo" name="codigo" maxlength="20" required
                                               value="<?php echo $especialidad_editar ? htmlspecialchars($especialidad_editar['codigo']) : ''; ?>">
                                        <small class="form-text text-muted">Código único para identificar la especialidad (máx. 20 caracteres)</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="nombre">Nombre <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" required
                                               value="<?php echo $especialidad_editar ? htmlspecialchars($especialidad_editar['nombre']) : ''; ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="descripcion">Descripción</label>
                                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo $especialidad_editar ? htmlspecialchars($especialidad_editar['descripcion']) : ''; ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="estado" name="estado"
                                                  <?php echo (!$especialidad_editar || $especialidad_editar['estado'] == 'activo') ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="estado">Activa</label>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> <?php echo $especialidad_editar ? 'Actualizar' : 'Guardar'; ?>
                                    </button>
                                    
                                    <?php if ($especialidad_editar): ?>
                                    <a href="gestionar_especialidades.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                        
                        <?php if ($especialidad_editar): ?>
                        <div class="card mt-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0">Información Adicional</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Creada:</strong> <?php echo date("d/m/Y H:i", strtotime($especialidad_editar['created_at'])); ?></p>
                                <p><strong>Última modificación:</strong> <?php echo date("d/m/Y H:i", strtotime($especialidad_editar['updated_at'])); ?></p>
                                
                                <p class="mb-0">
                                    <a href="configurar_campos_especialidad.php?id=<?php echo $especialidad_editar['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-sliders-h"></i> Configurar Campos
                                    </a>
                                </p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Lista de especialidades existentes -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Especialidades Existentes</h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($especialidades) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Código</th>
                                                <th>Nombre</th>
                                                <th>Descripción</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($especialidades as $esp): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($esp['codigo']); ?></td>
                                                <td><?php echo htmlspecialchars($esp['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars(substr($esp['descripcion'], 0, 50)) . (strlen($esp['descripcion']) > 50 ? '...' : ''); ?></td>
                                                <td>
                                                    <?php if ($esp['estado'] == 'activo'): ?>
                                                    <span class="badge badge-success">Activa</span>
                                                    <?php else: ?>
                                                    <span class="badge badge-secondary">Inactiva</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="action-buttons">
                                                    <a href="gestionar_especialidades.php?editar=<?php echo $esp['id']; ?>" class="btn btn-sm btn-info" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="configurar_campos_especialidad.php?id=<?php echo $esp['id']; ?>" class="btn btn-sm btn-primary" title="Configurar Campos">
                                                        <i class="fas fa-sliders-h"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                           data-toggle="modal" data-target="#deleteModal<?php echo $esp['id']; ?>" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    
                                                    <!-- Modal para confirmar eliminación -->
                                                    <div class="modal fade" id="deleteModal<?php echo $esp['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $esp['id']; ?>" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-danger text-white">
                                                                    <h5 class="modal-title" id="deleteModalLabel<?php echo $esp['id']; ?>">Confirmar Eliminación</h5>
                                                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>¿Está seguro de que desea eliminar la especialidad <strong><?php echo htmlspecialchars($esp['nombre']); ?></strong>?</p>
                                                                    <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Esta acción no se puede deshacer.</p>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                                    <form method="POST" action="gestionar_especialidades.php" style="display: inline;">
                                                                        <input type="hidden" name="action" value="delete_especialidad">
                                                                        <input type="hidden" name="id" value="<?php echo $esp['id']; ?>">
                                                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No hay especialidades registradas.
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
