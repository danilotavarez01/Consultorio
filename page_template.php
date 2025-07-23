<?php
/**
 * Plantilla base para páginas que requieren autenticación
 * Incluye verificación de sesión y scripts necesarios
 */

// Verificar sesión
require_once __DIR__ . '/verificar_sesion.php';
verificarSesion();

/**
 * Función para incluir los scripts de gestión de sesión
 */
function incluirScriptsSession() {
    echo '
    <!-- Scripts de gestión de sesión -->
    <script src="/assets/libs/jquery-3.6.0.min.js"></script>
    <script src="js/session-manager.js"></script>
    ';
}

/**
 * Función para incluir el CSS base del sistema
 */
function incluirEstilosBase() {
    echo '
    <!-- Estilos base del sistema -->
    <link rel="stylesheet" href="/assets/libs/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/libs/fontawesome.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    ';
}

/**
 * Función para mostrar la cabecera estándar de las páginas
 */
function mostrarCabecera($titulo = 'Sistema Consultorio', $incluir_sidebar = true) {
    // Obtener configuración del consultorio si existe
    $nombre_consultorio = 'Consultorio Médico';
    try {
        require_once 'config.php';
        $stmt = $conn->query("SELECT nombre_consultorio FROM configuracion WHERE id = 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($config && !empty($config['nombre_consultorio'])) {
            $nombre_consultorio = $config['nombre_consultorio'];
        }
    } catch (Exception $e) {
        // Usar nombre por defecto si hay error
    }

    echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($titulo) . ' - ' . htmlspecialchars($nombre_consultorio) . '</title>';
    
    incluirEstilosBase();
    
    echo '
    <style>
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .main-container {
            min-height: 100vh;
        }
        .content-wrapper {
            padding: 20px;
        }
        .sidebar {
            min-height: 100vh;
            background-color: var(--bg-sidebar);
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid main-container">
        <div class="row">';
    
    if ($incluir_sidebar) {
        echo '<div class="col-md-2 p-0">';
        include 'sidebar.php';
        echo '</div>
              <div class="col-md-10 content-wrapper">';
    } else {
        echo '<div class="col-12 content-wrapper">';
    }
}

/**
 * Función para cerrar la estructura HTML de las páginas
 */
function mostrarPie($incluir_scripts_session = true) {
    echo '        </div>
        </div>
    </div>';
    
    if ($incluir_scripts_session) {
        incluirScriptsSession();
    }
    
    echo '
    <script src="/assets/libs/bootstrap.bundle.min.js"></script>
    <script src="js/theme-manager.js"></script>
    
    <!-- Inicialización común -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Configurar tema
            if (window.themeManager) {
                const currentTheme = window.themeManager.getCurrentTheme();
                window.themeManager.setTheme(currentTheme);
            }
            
            // Verificar estado de sesión inicial
            if (window.sessionManager) {
                console.log("SessionManager activo para página:", window.location.pathname);
            }
        });
    </script>
</body>
</html>';
}

/**
 * Función para mostrar alertas/mensajes
 */
function mostrarAlerta($mensaje, $tipo = 'info', $icono = null) {
    $iconos = [
        'success' => 'fas fa-check-circle',
        'danger' => 'fas fa-exclamation-circle',
        'warning' => 'fas fa-exclamation-triangle',
        'info' => 'fas fa-info-circle',
        'primary' => 'fas fa-star'
    ];
    
    $icono_final = $icono ?: ($iconos[$tipo] ?? 'fas fa-info-circle');
    
    echo '<div class="alert alert-' . $tipo . ' alert-dismissible fade show" role="alert">
        <i class="' . $icono_final . '"></i> ' . htmlspecialchars($mensaje) . '
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>';
}

/**
 * Función para verificar permisos específicos
 */
function verificarPermiso($permiso_requerido) {
    require_once 'permissions.php';
    
    if (!hasPermission($permiso_requerido) && $_SESSION["username"] !== "admin") {
        header("Location: login.php?message=no_permissions");
        exit;
    }
}

/**
 * Función para log de actividad del usuario
 */
function logActividad($accion, $detalles = '') {
    $usuario = $_SESSION['username'] ?? 'desconocido';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $page = $_SERVER['REQUEST_URI'] ?? 'unknown';
    
    error_log("ACTIVIDAD_USUARIO: $usuario | $accion | $detalles | IP: $ip | Página: $page");
}
?>
