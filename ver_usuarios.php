<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "Debe estar logueado para ver esta información.";
    exit;
}

try {
    // Consultar todos los usuarios
    $sql = "SELECT id, username, nombre, rol, active, especialidad_id FROM usuarios ORDER BY id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // También consultar especialidades para mostrar nombres
    $sql_esp = "SELECT id, nombre FROM especialidades";
    $stmt_esp = $conn->prepare($sql_esp);
    $stmt_esp->execute();
    $especialidades = $stmt_esp->fetchAll(PDO::FETCH_ASSOC);
    
    // Crear array asociativo de especialidades
    $esp_nombres = [];
    foreach ($especialidades as $esp) {
        $esp_nombres[$esp['id']] = $esp['nombre'];
    }
    
} catch (Exception $e) {
    echo "Error al consultar usuarios: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Usuarios - Consultorio</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-users"></i> Lista de Usuarios del Sistema</h3>
                    <small class="text-muted">Total de usuarios: <?= count($usuarios) ?></small>
                </div>
                <div class="card-body">
                    
                    <?php if (count($usuarios) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Nombre</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Especialidad</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($usuario['id']) ?></strong>
                                    </td>
                                    <td>
                                        <code><?= htmlspecialchars($usuario['username']) ?></code>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($usuario['nombre']) ?>
                                    </td>
                                    <td>
                                        <?php
                                        $rol_color = '';
                                        switch($usuario['rol']) {
                                            case 'admin':
                                                $rol_color = 'badge-danger';
                                                break;
                                            case 'doctor':
                                                $rol_color = 'badge-primary';
                                                break;
                                            case 'recepcionista':
                                                $rol_color = 'badge-success';
                                                break;
                                            default:
                                                $rol_color = 'badge-secondary';
                                        }
                                        ?>
                                        <span class="badge <?= $rol_color ?>">
                                            <?= ucfirst(htmlspecialchars($usuario['rol'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($usuario['active']): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">
                                                <i class="fas fa-times"></i> Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($usuario['especialidad_id']): ?>
                                            <span class="badge badge-info">
                                                <?= htmlspecialchars($esp_nombres[$usuario['especialidad_id']] ?? 'ID: ' . $usuario['especialidad_id']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Sin especialidad</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <?php if ($_SESSION['rol'] === 'admin'): ?>
                                            <button type="button" class="btn btn-outline-primary" 
                                                    onclick="verDetalles(<?= $usuario['id'] ?>)"
                                                    title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" 
                                                    onclick="editarUsuario(<?= $usuario['id'] ?>)"
                                                    title="Editar usuario">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Resumen por roles -->
                    <div class="mt-4">
                        <h5>Resumen por Roles:</h5>
                        <div class="row">
                            <?php
                            $roles_count = [];
                            foreach ($usuarios as $usuario) {
                                $rol = $usuario['rol'];
                                if (!isset($roles_count[$rol])) {
                                    $roles_count[$rol] = 0;
                                }
                                $roles_count[$rol]++;
                            }
                            ?>
                            
                            <?php foreach ($roles_count as $rol => $count): ?>
                            <div class="col-md-3 mb-2">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= $count ?></h5>
                                        <p class="card-text"><?= ucfirst($rol) ?><?= $count > 1 ? 's' : '' ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        No se encontraron usuarios en la base de datos.
                    </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <a href="index_temporal.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Panel
                        </a>
                        
                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                        <a href="usuarios.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Gestionar Usuarios
                        </a>
                        <?php endif; ?>
                        
                        <button onclick="location.reload()" class="btn btn-info">
                            <i class="fas fa-sync"></i> Actualizar
                        </button>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalles del usuario -->
<div class="modal fade" id="modalDetalles" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles del Usuario</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detallesContent">
                <!-- Contenido cargado dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="js/theme-manager.js"></script>

<script>
function verDetalles(userId) {
    // Obtener datos del usuario desde la tabla
    const filaUsuario = $(`tr:has(td:first-child strong:contains('${userId}'))`);
    const username = filaUsuario.find('code').text();
    const nombre = filaUsuario.find('td:nth-child(3)').text();
    const rol = filaUsuario.find('.badge').text();
    const estado = filaUsuario.find('td:nth-child(5) .badge').text();
    const especialidad = filaUsuario.find('td:nth-child(6)').text();
    
    const detallesHTML = `
        <div class="row">
            <div class="col-md-6">
                <p><strong>ID:</strong> ${userId}</p>
                <p><strong>Username:</strong> <code>${username}</code></p>
                <p><strong>Nombre:</strong> ${nombre}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Rol:</strong> ${rol}</p>
                <p><strong>Estado:</strong> ${estado}</p>
                <p><strong>Especialidad:</strong> ${especialidad}</p>
            </div>
        </div>
    `;
    
    $('#detallesContent').html(detallesHTML);
    $('#modalDetalles').modal('show');
}

function editarUsuario(userId) {
    if (confirm('¿Desea editar este usuario?')) {
        window.location.href = `usuarios.php?edit=${userId}`;
    }
}

// Inicializar cuando cargue la página
$(document).ready(function() {
    console.log('Lista de usuarios cargada. Total: <?= count($usuarios) ?>');
});
</script>

</body>
</html>

