<?php
/**
 * Script de instalación del modo oscuro en todo el sistema
 * Aplica automáticamente el modo oscuro a las páginas principales
 */

require_once 'session_config.php';
session_start();

// Verificar permisos de administrador
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die('Este script solo puede ser ejecutado por administradores.');
}

$pages_to_update = [
    'pacientes.php',
    'citas.php',
    'turnos.php',
    'facturacion.php',
    'reportes_facturacion.php',
    'configuracion.php',
    'usuarios.php',
    'recetas.php',
    'enfermedades.php',
    'procedimientos.php',
    'nueva_consulta.php',
    'ver_consulta.php',
    'ver_paciente.php',
    'receptionist_permissions.php',
    'user_permissions.php',
    'configuracion_impresora_80mm.php',
    'test_navegacion.php'
];

$updates_made = [];
$errors = [];

foreach ($pages_to_update as $page) {
    if (!file_exists($page)) {
        $errors[] = "Archivo no encontrado: $page";
        continue;
    }
    
    $content = file_get_contents($page);
    $updated = false;
    
    // 1. Añadir CSS del modo oscuro si no existe
    if (strpos($content, 'css/dark-mode.css') === false) {
        $content = str_replace(
            '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">',
            '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">',
            $content
        );
        $updated = true;
    }
    
    // 2. Añadir header si no existe
    if (strpos($content, 'includes/header.php') === false && strpos($content, '<body>') !== false) {
        $content = str_replace(
            '<body>',
            '<body>
    <!-- Header con modo oscuro -->
    <?php include \'includes/header.php\'; ?>',
            $content
        );
        $updated = true;
    }
    
    // 3. Añadir JavaScript del tema si no existe
    if (strpos($content, 'js/theme-manager.js') === false) {
        // Buscar el último script tag antes de </body>
        $pattern = '/(<script[^>]*src="[^"]*bootstrap[^"]*"[^>]*><\/script>)/';
        if (preg_match($pattern, $content)) {
            $content = preg_replace(
                $pattern,
                '$1
    <script src="js/theme-manager.js"></script>',
                $content
            );
            $updated = true;
        }
    }
    
    // 4. Guardar si hubo cambios
    if ($updated) {
        if (file_put_contents($page, $content)) {
            $updates_made[] = $page;
        } else {
            $errors[] = "No se pudo escribir: $page";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Instalación Modo Oscuro - Consultorio</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-moon"></i> Instalación del Modo Oscuro</h3>
                </div>
                <div class="card-body">
                    
                    <?php if (count($updates_made) > 0): ?>
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> Instalación Completada</h5>
                        <p>Se han actualizado <strong><?= count($updates_made) ?></strong> archivos con el sistema de modo oscuro:</p>
                        <ul>
                            <?php foreach ($updates_made as $file): ?>
                            <li><code><?= htmlspecialchars($file) ?></code></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Sin Cambios Necesarios</h5>
                        <p>Todos los archivos ya tienen el modo oscuro instalado.</p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (count($errors) > 0): ?>
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Advertencias</h5>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <h5>Funciones del Modo Oscuro:</h5>
                        <ul class="list-group">
                            <li class="list-group-item">✅ Switch de modo oscuro/claro en el header</li>
                            <li class="list-group-item">✅ Persistencia de preferencia en localStorage</li>
                            <li class="list-group-item">✅ Detección automática de preferencia del sistema</li>
                            <li class="list-group-item">✅ Transiciones suaves entre temas</li>
                            <li class="list-group-item">✅ Compatibilidad con todos los componentes Bootstrap</li>
                            <li class="list-group-item">✅ Estilos específicos para el sistema médico</li>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <h5>Cómo Usar:</h5>
                        <ol>
                            <li>Busca el switch de modo oscuro en la esquina superior derecha</li>
                            <li>Haz clic para cambiar entre modo claro y oscuro</li>
                            <li>Tu preferencia se guardará automáticamente</li>
                            <li>El tema se aplicará en todas las páginas del sistema</li>
                        </ol>
                    </div>
                    
                    <div class="mt-4">
                        <a href="index_temporal.php" class="btn btn-primary">
                            <i class="fas fa-home"></i> Ir al Panel Principal
                        </a>
                        <a href="test_navegacion.php" class="btn btn-secondary">
                            <i class="fas fa-vial"></i> Probar Navegación
                        </a>
                        <button onclick="location.reload()" class="btn btn-info">
                            <i class="fas fa-redo"></i> Ejecutar Nuevamente
                        </button>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/theme-manager.js"></script>

</body>
</html>
