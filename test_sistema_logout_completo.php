<?php
// Ejemplo de uso del nuevo sistema de plantillas y gestión de sesión
require_once 'page_template.php';

// Verificar permisos específicos si es necesario
// verificarPermiso('manage_patients');

// Mostrar cabecera
mostrarCabecera('Test Sistema de Logout', true);

// Log de actividad
logActividad('Acceso a página de test de logout');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1><i class="fas fa-test-tube"></i> Test del Sistema de Logout Mejorado</h1>
            
            <?php mostrarAlerta('Sistema de logout mejorado activo. Todas las verificaciones de sesión están funcionando.', 'success'); ?>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h3><i class="fas fa-info-circle"></i> Información de la Sesión Actual</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Usuario:</strong> <?= htmlspecialchars($_SESSION['username'] ?? 'N/A') ?></p>
                            <p><strong>Nombre:</strong> <?= htmlspecialchars($_SESSION['nombre'] ?? 'N/A') ?></p>
                            <p><strong>Rol:</strong> <?= htmlspecialchars($_SESSION['rol'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>ID de Sesión:</strong> <code><?= session_id() ?></code></p>
                            <p><strong>Última Actividad:</strong> <?= isset($_SESSION['last_activity']) ? date('Y-m-d H:i:s', $_SESSION['last_activity']) : 'N/A' ?></p>
                            <p><strong>Estado:</strong> <span class="badge badge-success">Activa</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h3><i class="fas fa-sign-out-alt"></i> Opciones de Logout</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <h5>Logout Normal</h5>
                            <button onclick="confirmarLogout()" class="btn btn-primary btn-block">
                                <i class="fas fa-sign-out-alt"></i> Logout con Confirmación
                            </button>
                            <small class="text-muted">Usa la función del sidebar</small>
                        </div>
                        
                        <div class="col-md-3">
                            <h5>Logout Directo</h5>
                            <a href="logout.php" class="btn btn-danger btn-block">
                                <i class="fas fa-power-off"></i> Logout Inmediato
                            </a>
                            <small class="text-muted">Sin confirmación</small>
                        </div>
                        
                        <div class="col-md-3">
                            <h5>Logout via JavaScript</h5>
                            <button onclick="logoutUser('manual')" class="btn btn-warning btn-block">
                                <i class="fas fa-code"></i> Logout JS
                            </button>
                            <small class="text-muted">Usa SessionManager</small>
                        </div>
                        
                        <div class="col-md-3">
                            <h5>Simular Expiración</h5>
                            <button onclick="simularExpiracion()" class="btn btn-secondary btn-block">
                                <i class="fas fa-clock"></i> Forzar Expiración
                            </button>
                            <small class="text-muted">Para testing</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h3><i class="fas fa-shield-alt"></i> Verificaciones de Seguridad</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-check-circle"></i> Funcionalidades Implementadas:</h5>
                        <ul class="mb-0">
                            <li>✅ Verificación automática de sesión cada 5 minutos</li>
                            <li>✅ Advertencia antes de expiración (5 minutos)</li>
                            <li>✅ Logout automático por inactividad (2 horas)</li>
                            <li>✅ Confirmación antes de cerrar sesión manual</li>
                            <li>✅ Mensajes informativos en login después de logout</li>
                            <li>✅ Limpieza completa de cookies y datos de sesión</li>
                            <li>✅ Redirección segura a login.php</li>
                            <li>✅ Logging de actividad para debugging</li>
                        </ul>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <button onclick="verificarSesion()" class="btn btn-outline-info">
                                <i class="fas fa-search"></i> Verificar Estado Sesión
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button onclick="extenderSesion()" class="btn btn-outline-success">
                                <i class="fas fa-clock"></i> Extender Sesión
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-link"></i> Navegación de Prueba</h3>
                </div>
                <div class="card-body">
                    <p>Prueba navegar a diferentes páginas para verificar que la sesión se mantiene:</p>
                    <div class="btn-group" role="group">
                        <a href="index.php" class="btn btn-outline-primary">
                            <i class="fas fa-home"></i> Inicio
                        </a>
                        <a href="pacientes.php" class="btn btn-outline-primary">
                            <i class="fas fa-users"></i> Pacientes
                        </a>
                        <a href="configuracion.php" class="btn btn-outline-primary">
                            <i class="fas fa-cogs"></i> Configuración
                        </a>
                        <button onclick="location.reload()" class="btn btn-outline-info">
                            <i class="fas fa-sync"></i> Recargar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Funciones adicionales para testing
function simularExpiracion() {
    if (confirm('¿Simular expiración de sesión? Esto te desconectará inmediatamente.')) {
        if (window.sessionManager) {
            window.sessionManager.handleSessionExpiry();
        } else {
            window.location.href = 'logout.php?reason=expired';
        }
    }
}

function verificarSesion() {
    fetch('verificar_sesion.php')
        .then(response => response.json())
        .then(data => {
            alert('Estado de sesión: ' + JSON.stringify(data, null, 2));
        })
        .catch(error => {
            alert('Error verificando sesión: ' + error.message);
        });
}

function extenderSesion() {
    if (window.sessionManager) {
        window.sessionManager.extendSession();
    } else {
        fetch('verificar_sesion.php')
            .then(response => response.json())
            .then(data => {
                alert('Sesión extendida: ' + data.message);
            })
            .catch(error => {
                alert('Error extendiendo sesión: ' + error.message);
            });
    }
}

console.log('Página de test de logout cargada correctamente');
</script>

<?php
// Mostrar pie de página
mostrarPie(true);
?>
