<?php
// Script para forzar la reinicialización del modo oscuro
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reinicializar Modo Oscuro</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
        .debug-info {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 1rem;
            margin: 1rem 0;
            font-family: monospace;
            font-size: 0.9rem;
        }
        .status-ok { color: #28a745; }
        .status-error { color: #dc3545; }
        .status-warning { color: #ffc107; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>🌙 Reinicializar Modo Oscuro</h3>
                    </div>
                    <div class="card-body">
                        <p>Esta herramienta te ayudará a reinicializar y diagnosticar problemas con el modo oscuro.</p>
                        
                        <!-- Diagnóstico automático -->
                        <div class="debug-info">
                            <h5>📊 Diagnóstico Automático</h5>
                            <div id="diagnostic-results">Ejecutando diagnóstico...</div>
                        </div>
                        
                        <!-- Controles manuales -->
                        <div class="row">
                            <div class="col-md-6">
                                <button class="btn btn-primary btn-block" onclick="clearLocalStorage()">
                                    🗑️ Limpiar Preferencias Guardadas
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-secondary btn-block" onclick="forceTheme('light')">
                                    ☀️ Forzar Tema Claro
                                </button>
                            </div>
                        </div>
                        
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <button class="btn btn-dark btn-block" onclick="forceTheme('dark')">
                                    🌙 Forzar Tema Oscuro
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-info btn-block" onclick="reloadThemeManager()">
                                    🔄 Recargar Theme Manager
                                </button>
                            </div>
                        </div>
                        
                        <!-- Estado actual -->
                        <div class="debug-info mt-3">
                            <h5>🔍 Estado Actual</h5>
                            <div id="current-status">
                                <strong>Tema actual:</strong> <span id="current-theme-display">Detectando...</span><br>
                                <strong>Atributo data-theme:</strong> <span id="data-theme-display">Detectando...</span><br>
                                <strong>LocalStorage:</strong> <span id="localstorage-display">Detectando...</span><br>
                                <strong>Preferencia del sistema:</strong> <span id="system-theme-display">Detectando...</span><br>
                                <strong>CSS cargado:</strong> <span id="css-loaded-display">Detectando...</span><br>
                                <strong>JS cargado:</strong> <span id="js-loaded-display">Detectando...</span>
                            </div>
                        </div>
                        
                        <!-- Enlaces rápidos -->
                        <div class="mt-4">
                            <h5>🔗 Enlaces Rápidos</h5>
                            <a href="index.php" class="btn btn-success mr-2">Ir al Inicio</a>
                            <a href="test_modo_oscuro.php" class="btn btn-warning mr-2">Test Completo</a>
                            <a href="javascript:location.reload()" class="btn btn-secondary">Recargar Página</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="js/theme-manager.js"></script>
    
    <script>
        // Funciones de diagnóstico y reparación
        
        function updateStatus() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const storedTheme = localStorage.getItem('consultorio-theme') || 'No guardado';
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            
            document.getElementById('current-theme-display').textContent = currentTheme;
            document.getElementById('data-theme-display').textContent = currentTheme;
            document.getElementById('localstorage-display').textContent = storedTheme;
            document.getElementById('system-theme-display').textContent = systemTheme;
            
            // Verificar si el CSS está cargado
            const cssLoaded = checkIfCSSLoaded();
            document.getElementById('css-loaded-display').innerHTML = cssLoaded ? 
                '<span class="status-ok">✅ Sí</span>' : 
                '<span class="status-error">❌ No</span>';
            
            // Verificar si el JS está cargado
            const jsLoaded = typeof window.themeManager !== 'undefined' || typeof ThemeManager !== 'undefined';
            document.getElementById('js-loaded-display').innerHTML = jsLoaded ? 
                '<span class="status-ok">✅ Sí</span>' : 
                '<span class="status-error">❌ No</span>';
        }
        
        function checkIfCSSLoaded() {
            // Verificar si las variables CSS están definidas
            const testElement = document.createElement('div');
            document.body.appendChild(testElement);
            const computedStyle = getComputedStyle(testElement);
            const bgPrimary = computedStyle.getPropertyValue('--bg-primary');
            document.body.removeChild(testElement);
            return bgPrimary && bgPrimary.trim() !== '';
        }
        
        function runDiagnostic() {
            let results = '<div class="diagnostic-results">';
            
            // 1. Verificar archivos CSS y JS
            const cssLink = document.querySelector('link[href*="dark-mode.css"]');
            const jsScript = document.querySelector('script[src*="theme-manager.js"]');
            
            results += cssLink ? 
                '<div class="status-ok">✅ CSS del modo oscuro encontrado</div>' : 
                '<div class="status-error">❌ CSS del modo oscuro NO encontrado</div>';
                
            results += jsScript ? 
                '<div class="status-ok">✅ JavaScript del theme manager encontrado</div>' : 
                '<div class="status-error">❌ JavaScript del theme manager NO encontrado</div>';
            
            // 2. Verificar variables CSS
            const cssVariablesWork = checkIfCSSLoaded();
            results += cssVariablesWork ? 
                '<div class="status-ok">✅ Variables CSS funcionando</div>' : 
                '<div class="status-error">❌ Variables CSS NO funcionando</div>';
            
            // 3. Verificar localStorage
            const canUseLocalStorage = typeof(Storage) !== "undefined";
            results += canUseLocalStorage ? 
                '<div class="status-ok">✅ LocalStorage disponible</div>' : 
                '<div class="status-error">❌ LocalStorage NO disponible</div>';
            
            // 4. Verificar ThemeManager
            const themeManagerAvailable = typeof ThemeManager !== 'undefined';
            results += themeManagerAvailable ? 
                '<div class="status-ok">✅ ThemeManager disponible</div>' : 
                '<div class="status-error">❌ ThemeManager NO disponible</div>';
            
            results += '</div>';
            
            document.getElementById('diagnostic-results').innerHTML = results;
        }
        
        function clearLocalStorage() {
            try {
                localStorage.removeItem('consultorio-theme');
                alert('✅ Preferencias de tema eliminadas. La página se recargará.');
                location.reload();
            } catch (e) {
                alert('❌ Error al limpiar preferencias: ' + e.message);
            }
        }
        
        function forceTheme(theme) {
            try {
                // Forzar el tema directamente
                document.documentElement.setAttribute('data-theme', theme);
                if (theme === 'dark') {
                    document.body.classList.add('dark-theme');
                } else {
                    document.body.classList.remove('dark-theme');
                }
                localStorage.setItem('consultorio-theme', theme);
                
                // Actualizar toggle si existe
                const checkbox = document.getElementById('theme-checkbox');
                if (checkbox) {
                    checkbox.checked = theme === 'dark';
                }
                
                updateStatus();
                alert(`✅ Tema forzado a: ${theme}`);
            } catch (e) {
                alert('❌ Error al forzar tema: ' + e.message);
            }
        }
        
        function reloadThemeManager() {
            try {
                // Recargar el script del theme manager
                const existingScript = document.querySelector('script[src*="theme-manager.js"]');
                if (existingScript) {
                    existingScript.remove();
                }
                
                const newScript = document.createElement('script');
                newScript.src = 'js/theme-manager.js?t=' + Date.now();
                newScript.onload = () => {
                    alert('✅ Theme Manager recargado');
                    updateStatus();
                };
                newScript.onerror = () => {
                    alert('❌ Error al recargar Theme Manager');
                };
                document.head.appendChild(newScript);
            } catch (e) {
                alert('❌ Error al recargar Theme Manager: ' + e.message);
            }
        }
        
        // Ejecutar diagnóstico al cargar
        document.addEventListener('DOMContentLoaded', () => {
            runDiagnostic();
            updateStatus();
            
            // Actualizar estado cada 2 segundos
            setInterval(updateStatus, 2000);
        });
        
        // Escuchar cambios de tema
        window.addEventListener('themeChanged', () => {
            updateStatus();
        });
    </script>
</body>
</html>

