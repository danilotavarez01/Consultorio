<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Header - Nombre Consultorio</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/fontawesome.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f8f9fa;
            --text-primary: #333333;
            --text-secondary: #666666;
            --border-color: #dee2e6;
            --btn-primary-bg: #007bff;
            --shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        
        [data-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --text-primary: #ffffff;
            --text-secondary: #cccccc;
            --border-color: #404040;
            --btn-primary-bg: #0d6efd;
            --shadow: 0 2px 4px rgba(0,0,0,.3);
        }
        
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }
    </style>
</head>
<body>
    <?php 
    // Simular sesión para el test
    session_start();
    $_SESSION['username'] = 'Usuario Test';
    $_SESSION['rol'] = 'admin';

    // Debug antes de incluir el header
    echo "<!-- DEBUG: Antes de incluir header -->";
    
    // Incluir el header
    include 'includes/header.php'; 
    
    // Debug después de incluir el header
    echo "<!-- DEBUG: Después de incluir header -->";
    echo "<!-- Variable nombreConsultorio: " . htmlspecialchars($nombreConsultorio ?? 'NO DEFINIDA') . " -->";
    ?>
    
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h5>🧪 Test - Header con Nombre del Consultorio</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>📋 Instrucciones:</strong> Revisa el header arriba. Debería mostrar el nombre del consultorio desde la base de datos.
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6>📊 Variables del Header:</h6>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>$nombreConsultorio:</strong>
                                <span class="badge badge-primary"><?= htmlspecialchars($nombreConsultorio ?? 'NO DEFINIDA') ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>$configHeader (existe):</strong>
                                <span class="badge badge-<?= isset($configHeader) ? 'success' : 'danger' ?>">
                                    <?= isset($configHeader) ? 'SÍ' : 'NO' ?>
                                </span>
                            </li>
                            <?php if (isset($configHeader) && is_array($configHeader)): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Registros en config:</strong>
                                <span class="badge badge-info"><?= count($configHeader) ?></span>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>🔧 Funciones Disponibles:</h6>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>getNombreConsultorio():</strong>
                                <span class="badge badge-<?= function_exists('getNombreConsultorio') ? 'success' : 'warning' ?>">
                                    <?= function_exists('getNombreConsultorio') ? 'EXISTE' : 'NO EXISTE' ?>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>obtenerConfiguracionHeader():</strong>
                                <span class="badge badge-<?= function_exists('obtenerConfiguracionHeader') ? 'success' : 'warning' ?>">
                                    <?= function_exists('obtenerConfiguracionHeader') ? 'EXISTE' : 'NO EXISTE' ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <?php if (isset($configHeader) && is_array($configHeader) && !empty($configHeader)): ?>
                <div class="mt-3">
                    <h6>🗄️ Datos de Configuración:</h6>
                    <div class="alert alert-light">
                        <small>
                            <strong>nombre_consultorio:</strong> 
                            <?= isset($configHeader['nombre_consultorio']) ? 
                                "'" . htmlspecialchars($configHeader['nombre_consultorio']) . "'" : 
                                '<span class="text-danger">NO EXISTE</span>' ?>
                        </small>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="mt-3">
                    <a href="debug_header_nombre.php" class="btn btn-info btn-sm">
                        🔍 Ver Debug Detallado
                    </a>
                    <button onclick="location.reload()" class="btn btn-secondary btn-sm">
                        🔄 Recargar Página
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Función básica para modo oscuro (solo para el test)
        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            
            const checkbox = document.getElementById('theme-checkbox');
            if (checkbox) {
                checkbox.checked = newTheme === 'dark';
            }
            
            // Actualizar etiquetas
            updateThemeLabels();
        }
        
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
        
        // Inicializar tema
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            
            const checkbox = document.getElementById('theme-checkbox');
            if (checkbox) {
                checkbox.checked = savedTheme === 'dark';
            }
            
            updateThemeLabels();
        });
    </script>
</body>
</html>


