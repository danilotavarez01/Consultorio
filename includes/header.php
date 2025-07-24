<?php
// Header universal con modo oscuro para el sistema de consultorio
if(!isset($_SESSION)) {
    session_start();
}

// Incluir configuración de base de datos
require_once __DIR__ . '/../config.php';

// Función para obtener la configuración completa usando la conexión de config.php
function obtenerConfiguracionHeader() {
    global $pdo, $conn;
    
    try {
        // Usar la conexión global de config.php
        $conexion = isset($pdo) ? $pdo : $conn;
        
        if (!$conexion) {
            return array();
        }
        
        // Consultar toda la configuración
        $stmt = $conexion->query("SELECT * FROM configuracion WHERE id = 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $config ? $config : array();
        
    } catch (Exception $e) {
        // En caso de error, devolver array vacío
        return array();
    }
}

// Obtener la configuración
$configHeader = obtenerConfiguracionHeader();

// Obtener el nombre del consultorio
if (function_exists('getNombreConsultorio')) {
    // Si existe la función de configuracion.php, usarla con el parámetro correcto
    $nombreConsultorio = getNombreConsultorio($configHeader);
} else {
    // Si no existe, obtener directamente
    $nombreConsultorio = isset($configHeader['nombre_consultorio']) && 
                        $configHeader['nombre_consultorio'] !== null && 
                        $configHeader['nombre_consultorio'] !== '' 
                        ? $configHeader['nombre_consultorio'] 
                        : 'Consultorio Médico';
}
?>

<!-- Header con modo oscuro -->
<header class="main-header">
    <div class="d-flex justify-content-between align-items-center p-3" style="background-color: var(--bg-secondary); border-bottom: 1px solid var(--border-color);">
        <!-- Logo y título -->
        <div class="header-left d-flex align-items-center">
            <i class="fas fa-stethoscope fa-2x text-primary mr-3"></i>
            <div>
                <h4 class="mb-0" style="color: var(--text-primary);"><?= htmlspecialchars($nombreConsultorio) ?></h4>
             
                <small style="color: var(--text-secondary);">Sistema de Gestión Integral</small>
            </div>
        </div>
        
        <!-- Información del usuario y controles -->
        <div class="header-right d-flex align-items-center">
            <!-- Información del usuario -->
            <?php if (isset($_SESSION['username']) || isset($_SESSION['id'])): ?>
            <div class="user-info mr-4">
                <span style="color: var(--text-secondary);">Bienvenido,</span>
                <strong style="color: var(--text-primary);">
                    <?= htmlspecialchars($_SESSION['username'] ?? $_SESSION['nombre'] ?? 'Usuario') ?>
                </strong>
                <?php if (isset($_SESSION['rol'])): ?>
                <br><small class="text-muted"><?= ucfirst($_SESSION['rol']) ?></small>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Switch de modo oscuro -->
            <div class="theme-switch-wrapper mr-3">
                <label class="theme-switch" for="theme-checkbox" title="Cambiar modo de visualización">
                    <input type="checkbox" id="theme-checkbox" onchange="toggleTheme()">
                    <div class="slider">
                        <i class="fas fa-sun theme-icon-light" style="position: absolute; left: 8px; top: 8px; font-size: 12px; color: #ffc107;"></i>
                        <i class="fas fa-moon theme-icon-dark" style="position: absolute; right: 8px; top: 8px; font-size: 12px; color: #6c757d; display: none;"></i>
                    </div>
                </label>
                <span class="theme-switch-label">
                    <span class="theme-label-light">Claro</span>
                    <span class="theme-label-dark" style="display: none;">Oscuro</span>
                </span>
            </div>
            
            <!-- Botones de acción -->
            <div class="header-actions">
                <button type="button" class="btn btn-outline-secondary btn-sm mr-2" onclick="location.reload()" title="Actualizar página">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>
</header>

<!-- Estilos adicionales para el header -->
<style>
.main-header {
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: var(--shadow);
}

.user-info {
    text-align: right;
    font-size: 14px;
}

.header-actions .btn {
    border-radius: 20px;
}

.theme-switch-wrapper {
    display: flex;
    align-items: center;
}

.theme-switch {
    display: inline-block;
    height: 28px;
    position: relative;
    width: 52px;
}

.theme-switch input {
    display: none;
}

.slider {
    background-color: #ccc;
    bottom: 0;
    cursor: pointer;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
    transition: .4s;
    border-radius: 28px;
}

.slider:before {
    background-color: #fff;
    bottom: 2px;
    content: "";
    height: 24px;
    left: 2px;
    position: absolute;
    transition: .4s;
    width: 24px;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

input:checked + .slider {
    background-color: var(--btn-primary-bg);
}

input:checked + .slider:before {
    transform: translateX(24px);
}

input:checked + .slider .theme-icon-light {
    display: none;
}

input:checked + .slider .theme-icon-dark {
    display: block !important;
    color: #fff !important;
}

.theme-switch-label {
    margin-left: 8px;
    color: var(--text-primary);
    font-size: 13px;
    font-weight: 500;
}

/* Responsive */
@media (max-width: 768px) {
    .header-left h4 {
        font-size: 1.1rem;
    }
    
    .user-info {
        display: none;
    }
    
    .theme-switch-label {
        display: none;
    }
    
    .theme-switch {
        width: 44px;
        height: 24px;
    }
    
    .slider:before {
        height: 20px;
        width: 20px;
    }
    
    input:checked + .slider:before {
        transform: translateX(20px);
    }
}

/* Animación para los iconos del tema */
.theme-icon-light, .theme-icon-dark {
    transition: all 0.3s ease;
}
</style>

<script>
// Actualizar etiquetas del tema
function updateThemeLabels() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const lightLabel = document.querySelector('.theme-label-light');
    const darkLabel = document.querySelector('.theme-label-dark');
    
    if (lightLabel && darkLabel) {
        if (isDark) {
            lightLabel.style.display = 'none';
            darkLabel.style.display = 'inline';
        } else {
            lightLabel.style.display = 'inline';
            darkLabel.style.display = 'none';
        }
    }
}

// Escuchar cambios de tema
window.addEventListener('themeChanged', updateThemeLabels);

// Actualizar al cargar
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(updateThemeLabels, 100);
});
</script>
