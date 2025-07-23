<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';
require_once 'permissions.php';

// Verificar que el usuario esté logueado - usar las variables correctas de sesión
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || !isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}

// Endpoint AJAX para obtener el siguiente código
if (isset($_GET['action']) && $_GET['action'] === 'get_next_code') {
    $categoria = $_GET['categoria'] ?? 'procedimiento';
    
    $prefijos = [
        'procedimiento' => 'PROC',
        'utensilio' => 'UTEN',
        'material' => 'MAT',
        'medicamento' => 'MED'
    ];
    
    $prefijo = $prefijos[$categoria] ?? 'PROC';
    
    try {
        // Buscar el último código usado para esta categoría
        $stmt = $conn->prepare("SELECT codigo FROM procedimientos WHERE codigo LIKE ? ORDER BY codigo DESC LIMIT 1");
        $stmt->execute([$prefijo . '%']);
        $ultimo_codigo = $stmt->fetchColumn();
        
        if ($ultimo_codigo) {
            // Extraer el número del código y incrementar
            $numero = intval(substr($ultimo_codigo, strlen($prefijo)));
            $nuevo_numero = $numero + 1;
        } else {
            $nuevo_numero = 1;
        }
        
        // Generar código con padding de 3 dígitos
        $nuevo_codigo = $prefijo . str_pad($nuevo_numero, 3, '0', STR_PAD_LEFT);
        
        // Verificar que el código no existe (por si acaso)
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM procedimientos WHERE codigo = ?");
        $stmt_check->execute([$nuevo_codigo]);
        
        // Si ya existe, incrementar hasta encontrar uno libre
        while ($stmt_check->fetchColumn() > 0) {
            $nuevo_numero++;
            $nuevo_codigo = $prefijo . str_pad($nuevo_numero, 3, '0', STR_PAD_LEFT);
            $stmt_check->execute([$nuevo_codigo]);
        }
        
        echo $nuevo_codigo;
        exit();
        
    } catch (PDOException $e) {
        // En caso de error, devolver código por defecto
        echo $prefijo . '001';
        exit();
    }
}

// Verificar permisos para procedimientos
if (!hasPermission('manage_procedures') && !hasPermission('gestionar_catalogos') && !isAdmin()) {
    echo "<div class='alert alert-danger'>No tiene permisos para acceder a esta sección.</div>";
    echo "<a href='index.php' class='btn btn-secondary'>Volver al Inicio</a>";
    exit();
}

// Función para generar código automático
function generarCodigoAutomatico() {
    global $conn;
    
    $categoria = $_POST['categoria'] ?? 'procedimiento';
    
    // Prefijos por categoría
    $prefijos = [
        'procedimiento' => 'PROC',
        'utensilio' => 'UTEN',
        'material' => 'MAT',
        'medicamento' => 'MED'
    ];
    
    $prefijo = $prefijos[$categoria] ?? 'PROC';
    
    try {
        // Obtener el último número para esta categoría
        $stmt = $conn->prepare("SELECT codigo FROM procedimientos WHERE codigo LIKE ? ORDER BY codigo DESC LIMIT 1");
        $stmt->execute([$prefijo . '%']);
        $ultimo_codigo = $stmt->fetchColumn();
        
        if ($ultimo_codigo) {
            // Extraer el número del código (ej: PROC001 -> 001)
            $numero = intval(substr($ultimo_codigo, strlen($prefijo)));
            $nuevo_numero = $numero + 1;
        } else {
            $nuevo_numero = 1;
        }
        
        // Generar nuevo código con formato de 3 dígitos
        $nuevo_codigo = $prefijo . str_pad($nuevo_numero, 3, '0', STR_PAD_LEFT);
        
        return $nuevo_codigo;
        
    } catch (PDOException $e) {
        return $prefijo . '001'; // Código por defecto en caso de error
    }
}

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
        $codigo = trim($_POST['codigo'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio_costo = floatval($_POST['precio_costo'] ?? 0);
        $precio_venta = floatval($_POST['precio_venta'] ?? 0);
        $activo = isset($_POST['activo']) ? 1 : 0;
        $categoria = $_POST['categoria'] ?? 'procedimiento';
        
        // Generar código automático si está vacío
        if (empty($codigo)) {
            $codigo = generarCodigoAutomatico();
        }
        
        if (empty($nombre)) {
            $error = "El nombre del procedimiento es obligatorio.";
        } elseif ($precio_venta <= 0) {
            $error = "El precio de venta debe ser mayor a 0.";
        } else {
            try {
                if ($action === 'create') {
                    $stmt = $conn->prepare("INSERT INTO procedimientos (codigo, nombre, descripcion, precio_costo, precio_venta, activo, categoria) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$codigo, $nombre, $descripcion, $precio_costo, $precio_venta, $activo, $categoria]);
                    $success = "Procedimiento creado exitosamente.";
                } else {
                    $id = intval($_POST['id']);
                    $stmt = $conn->prepare("UPDATE procedimientos SET codigo = ?, nombre = ?, descripcion = ?, precio_costo = ?, precio_venta = ?, activo = ?, categoria = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$codigo, $nombre, $descripcion, $precio_costo, $precio_venta, $activo, $categoria, $id]);
                    $success = "Procedimiento actualizado exitosamente.";
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = "El código ya existe. Por favor, elija un código diferente.";
                } else {
                    $error = "Error en la base de datos: " . $e->getMessage();
                }
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        try {
            $stmt = $conn->prepare("DELETE FROM procedimientos WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Procedimiento eliminado exitosamente.";
        } catch (PDOException $e) {
            $error = "Error al eliminar: " . $e->getMessage();
        }
    } elseif ($action === 'toggle_status') {
        $id = intval($_POST['id']);
        try {
            $stmt = $conn->prepare("UPDATE procedimientos SET activo = 1 - activo WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Estado del procedimiento actualizado.";
        } catch (PDOException $e) {
            $error = "Error al cambiar estado: " . $e->getMessage();
        }
    }
}

// Obtener filtros
$filtro_categoria = $_GET['categoria'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';
$search = $_GET['search'] ?? '';

// Construir consulta con filtros
$where_conditions = [];
$params = [];

if (!empty($filtro_categoria)) {
    $where_conditions[] = "categoria = ?";
    $params[] = $filtro_categoria;
}

if ($filtro_estado !== '') {
    $where_conditions[] = "activo = ?";
    $params[] = intval($filtro_estado);
}

if (!empty($search)) {
    $where_conditions[] = "(nombre LIKE ? OR descripcion LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

try {
    $stmt = $conn->prepare("SELECT * FROM procedimientos $where_clause ORDER BY categoria, nombre");
    $stmt->execute($params);
    $procedimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al obtener procedimientos: " . $e->getMessage();
    $procedimientos = [];
}

// Obtener procedimiento para edición
$procedimiento_edit = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    try {
        $stmt = $conn->prepare("SELECT * FROM procedimientos WHERE id = ?");
        $stmt->execute([$edit_id]);
        $procedimiento_edit = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error al obtener procedimiento: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Procedimientos - Sistema de Consultorios</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; }
        .sidebar a:hover { background-color: #454d55; text-decoration: none; }
        .content { padding: 20px; }
        .categoria-badge {
            font-size: 0.75em;
        }
        .precio-cell {
            text-align: right;
            font-family: monospace;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-categoria {
            margin: 2px;
        }
        .table-responsive {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
                <div class="d-flex justify-content-between align-items-center my-4">
                    <h1><i class="fas fa-teeth mr-2"></i>Gestión de Procedimientos</h1>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Volver al Inicio
                    </a>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success) ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Formulario de Crear/Editar -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-<?= $procedimiento_edit ? 'edit' : 'plus' ?> mr-2"></i>
                            <?= $procedimiento_edit ? 'Editar Procedimiento' : 'Nuevo Procedimiento' ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?= $procedimiento_edit ? 'update' : 'create' ?>">
                            <?php if ($procedimiento_edit): ?>
                                <input type="hidden" name="id" value="<?= $procedimiento_edit['id'] ?>">
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="codigo" class="form-label">Código</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="codigo" name="codigo" 
                                                   value="<?= htmlspecialchars($procedimiento_edit['codigo'] ?? '') ?>" 
                                                   placeholder="Se generará automáticamente">
                                            <button type="button" class="btn btn-outline-secondary" id="btnRegenerar" 
                                                    title="Generar código automático">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">Código único generado automáticamente según la categoría</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre *</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               value="<?= htmlspecialchars($procedimiento_edit['nombre'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="categoria" class="form-label">Categoría</label>
                                        <select class="form-select" id="categoria" name="categoria">
                                            <option value="procedimiento" <?= ($procedimiento_edit['categoria'] ?? '') === 'procedimiento' ? 'selected' : '' ?>>
                                                Procedimiento
                                            </option>
                                            <option value="utensilio" <?= ($procedimiento_edit['categoria'] ?? '') === 'utensilio' ? 'selected' : '' ?>>
                                                Utensilio
                                            </option>
                                            <option value="material" <?= ($procedimiento_edit['categoria'] ?? '') === 'material' ? 'selected' : '' ?>>
                                                Material
                                            </option>
                                            <option value="medicamento" <?= ($procedimiento_edit['categoria'] ?? '') === 'medicamento' ? 'selected' : '' ?>>
                                                Medicamento
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($procedimiento_edit['descripcion'] ?? '') ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="precio_costo" class="form-label">Precio de Costo</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="precio_costo" name="precio_costo" 
                                                   step="0.01" min="0" value="<?= $procedimiento_edit['precio_costo'] ?? '0' ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="precio_venta" class="form-label">Precio de Venta *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="precio_venta" name="precio_venta" 
                                                   step="0.01" min="0.01" value="<?= $procedimiento_edit['precio_venta'] ?? '' ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="activo" class="form-label">Estado</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                                                   <?= ($procedimiento_edit['activo'] ?? 1) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="activo">Activo</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fas fa-save mr-2"></i>
                                    <?= $procedimiento_edit ? 'Actualizar' : 'Crear' ?>
                                </button>
                                <?php if ($procedimiento_edit): ?>
                                    <a href="procedimientos.php" class="btn btn-secondary">
                                        <i class="fas fa-times mr-2"></i>Cancelar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Filtros y Lista -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list mr-2"></i>Lista de Procedimientos
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <form method="GET" class="mb-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <select name="categoria" class="form-select" onchange="this.form.submit()">
                                        <option value="">Todas las categorías</option>
                                        <option value="procedimiento" <?= $filtro_categoria === 'procedimiento' ? 'selected' : '' ?>>
                                            Procedimientos
                                        </option>
                                        <option value="utensilio" <?= $filtro_categoria === 'utensilio' ? 'selected' : '' ?>>
                                            Utensilios
                                        </option>
                                        <option value="material" <?= $filtro_categoria === 'material' ? 'selected' : '' ?>>
                                            Materiales
                                        </option>
                                        <option value="medicamento" <?= $filtro_categoria === 'medicamento' ? 'selected' : '' ?>>
                                            Medicamentos
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="estado" class="form-select" onchange="this.form.submit()">
                                        <option value="">Todos los estados</option>
                                        <option value="1" <?= $filtro_estado === '1' ? 'selected' : '' ?>>Activos</option>
                                        <option value="0" <?= $filtro_estado === '0' ? 'selected' : '' ?>>Inactivos</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o descripción..." 
                                               value="<?= htmlspecialchars($search) ?>">
                                        <button type="submit" class="btn btn-outline-secondary">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <?php if (!empty($search) || !empty($filtro_categoria) || $filtro_estado !== ''): ?>
                                            <a href="procedimientos.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Tabla -->
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Categoría</th>
                                        <th>Descripción</th>
                                        <th>Precio Costo</th>
                                        <th>Precio Venta</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($procedimientos)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                                <br>No se encontraron procedimientos
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($procedimientos as $proc): ?>
                                            <tr>
                                                <td><?= $proc['id'] ?></td>
                                                <td>
                                                    <?= !empty($proc['codigo']) ? '<code>' . htmlspecialchars($proc['codigo']) . '</code>' : '<em class="text-muted">Sin código</em>' ?>
                                                </td>
                                                <td>
                                                    <strong><?= htmlspecialchars($proc['nombre']) ?></strong>
                                                </td>
                                                <td>
                                                    <?php
                                                    switch($proc['categoria']) {
                                                        case 'procedimiento':
                                                            $categoria_class = 'bg-primary';
                                                            break;
                                                        case 'utensilio':
                                                            $categoria_class = 'bg-success';
                                                            break;
                                                        case 'material':
                                                            $categoria_class = 'bg-warning text-dark';
                                                            break;
                                                        case 'medicamento':
                                                            $categoria_class = 'bg-info';
                                                            break;
                                                        default:
                                                            $categoria_class = 'bg-secondary';
                                                    }
                                                    ?>
                                                    <span class="badge <?= $categoria_class ?> categoria-badge">
                                                        <?= ucfirst($proc['categoria']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?= !empty($proc['descripcion']) ? htmlspecialchars($proc['descripcion']) : '<em class="text-muted">Sin descripción</em>' ?>
                                                </td>
                                                <td class="precio-cell">
                                                    $<?= number_format($proc['precio_costo'], 2) ?>
                                                </td>
                                                <td class="precio-cell">
                                                    <strong>$<?= number_format($proc['precio_venta'], 2) ?></strong>
                                                </td>
                                                <td>
                                                    <?php if ($proc['activo']): ?>
                                                        <span class="badge bg-success">Activo</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Inactivo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="?edit=<?= $proc['id'] ?>" class="btn btn-outline-primary" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('¿Cambiar el estado de este procedimiento?')">
                                                            <input type="hidden" name="action" value="toggle_status">
                                                            <input type="hidden" name="id" value="<?= $proc['id'] ?>">
                                                            <button type="submit" class="btn btn-outline-warning" title="Cambiar Estado">
                                                                <i class="fas fa-toggle-<?= $proc['activo'] ? 'on' : 'off' ?>"></i>
                                                            </button>
                                                        </form>
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('¿Está seguro de eliminar este procedimiento? Esta acción no se puede deshacer.')">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?= $proc['id'] ?>">
                                                            <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Resumen -->
                        <?php if (!empty($procedimientos)): ?>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Total: <?= count($procedimientos) ?> procedimiento(s)
                                        <?php
                                        $activos = array_filter($procedimientos, function($p) { return $p['activo']; });
                                        $inactivos = count($procedimientos) - count($activos);
                                        ?>
                                        | <span class="text-success"><?= count($activos) ?> activos</span>
                                        <?php if ($inactivos > 0): ?>
                                            | <span class="text-danger"><?= $inactivos ?> inactivos</span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/theme-manager.js"></script>
    <script>
        // Función para generar código automático basado en categoría
        function generarCodigoPorCategoria() {
            const categoria = document.getElementById('categoria').value;
            const codigoInput = document.getElementById('codigo');
            const btnRegenerar = document.getElementById('btnRegenerar');
            
            // Mostrar indicador de carga
            btnRegenerar.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btnRegenerar.disabled = true;
            
            const prefijos = {
                'procedimiento': 'PROC',
                'utensilio': 'UTEN', 
                'material': 'MAT',
                'medicamento': 'MED'
            };
            
            const prefijo = prefijos[categoria] || 'PROC';
            
            // Hacer una petición AJAX para obtener el siguiente número
            fetch('?action=get_next_code&categoria=' + categoria)
                .then(response => response.text())
                .then(codigo => {
                    if (codigo && codigo.trim()) {
                        codigoInput.value = codigo.trim();
                        codigoInput.placeholder = 'Código generado automáticamente';
                    } else {
                        // Fallback: generar código genérico
                        codigoInput.value = prefijo + '001';
                    }
                })
                .catch(() => {
                    // Fallback: generar código genérico
                    codigoInput.value = prefijo + '001';
                })
                .finally(() => {
                    // Restaurar botón
                    btnRegenerar.innerHTML = '<i class="fas fa-sync-alt"></i>';
                    btnRegenerar.disabled = false;
                });
        }
        
        // Activar generación automática cuando cambie la categoría
        document.addEventListener('DOMContentLoaded', function() {
            const categoriaSelect = document.getElementById('categoria');
            const codigoInput = document.getElementById('codigo');
            const btnRegenerar = document.getElementById('btnRegenerar');
            
            // Generar código inicial si el campo está vacío
            if (!codigoInput.value) {
                generarCodigoPorCategoria();
            }
            
            // Generar nuevo código cuando cambie la categoría
            categoriaSelect.addEventListener('change', function() {
                // Solo regenerar si el campo está vacío o contiene un código auto-generado
                if (!codigoInput.value || codigoInput.value.match(/^(PROC|UTEN|MAT|MED)\d{3}$/)) {
                    generarCodigoPorCategoria();
                }
            });
            
            // Evento para el botón de regenerar
            btnRegenerar.addEventListener('click', function() {
                codigoInput.value = '';
                generarCodigoPorCategoria();
            });
        });
    </script>
</body>
</html>
