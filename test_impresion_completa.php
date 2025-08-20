<?php
// test_impresion_completa.php - Prueba completa del sistema de impresión
require_once 'session_config.php';
session_start();
require_once 'config.php';

// Simular datos de pago para prueba
$_SESSION['ultimo_pago'] = [
    'pago_id' => 999,
    'numero_factura' => 'TEST-001',
    'paciente_nombre' => 'Juan Pérez',
    'paciente_cedula' => '12345678',
    'medico_nombre' => 'Dr. García',
    'monto' => 150.00,
    'metodo_pago' => 'efectivo',
    'observaciones' => 'Pago de prueba para verificar impresión',
    'fecha_pago_formato' => date('d/m/Y H:i')
];

$_SESSION['show_print_modal'] = true;
$_SESSION['loggedin'] = true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Completo - Sistema de Impresión</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .test-card { margin-bottom: 20px; }
        .status-badge { font-size: 0.8em; }
        .log-area { height: 200px; overflow-y: auto; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card test-card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-vial mr-2"></i>Test Completo - Sistema de Impresión de Recibos</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5><i class="fas fa-cogs mr-2"></i>Configuración del Test</h5>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between">
                                        Sesión de prueba configurada
                                        <span class="badge badge-success status-badge">✓ OK</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        Datos de pago simulados
                                        <span class="badge badge-success status-badge">✓ OK</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        Modal de impresión habilitado
                                        <span class="badge badge-success status-badge">✓ OK</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="fas fa-info-circle mr-2"></i>Información del Test</h5>
                                <small class="text-muted">
                                    <strong>Datos de prueba:</strong><br>
                                    • Factura: TEST-001<br>
                                    • Paciente: Juan Pérez<br>
                                    • Monto: $150.00<br>
                                    • Método: Efectivo<br>
                                    • ID Pago: 999 (test)
                                </small>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5><i class="fas fa-play mr-2"></i>Ejecutar Pruebas</h5>
                                <div class="btn-group-vertical w-100" role="group">
                                    <button type="button" class="btn btn-primary mb-2" onclick="testImpresionMejorada()">
                                        <i class="fas fa-print mr-2"></i>Test 1: Impresión Mejorada (Nueva Versión)
                                    </button>
                                    <button type="button" class="btn btn-secondary mb-2" onclick="testImpresionOriginal()">
                                        <i class="fas fa-print mr-2"></i>Test 2: Impresión Original (Versión Anterior)
                                    </button>
                                    <button type="button" class="btn btn-info mb-2" onclick="testModalFacturacion()">
                                        <i class="fas fa-window-restore mr-2"></i>Test 3: Modal de Facturación
                                    </button>
                                    <button type="button" class="btn btn-warning mb-2" onclick="testDiagnosticoCompleto()">
                                        <i class="fas fa-stethoscope mr-2"></i>Test 4: Diagnóstico Completo
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5><i class="fas fa-terminal mr-2"></i>Log de Pruebas</h5>
                                <div class="border p-2 bg-dark text-light log-area" id="logArea">
                                    [<?= date('H:i:s') ?>] Sistema de test inicializado...<br>
                                    [<?= date('H:i:s') ?>] Datos de prueba configurados correctamente.<br>
                                    [<?= date('H:i:s') ?>] Listo para ejecutar pruebas.<br>
                                </div>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="clearLog()">
                                        <i class="fas fa-eraser mr-1"></i>Limpiar Log
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="exportLog()">
                                        <i class="fas fa-download mr-1"></i>Exportar Log
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function addLog(mensaje, tipo = 'info') {
            const logArea = document.getElementById('logArea');
            const timestamp = new Date().toLocaleTimeString();
            const iconos = {
                info: '📋',
                success: '✅',
                warning: '⚠️',
                error: '❌',
                debug: '🔍'
            };
            
            logArea.innerHTML += `[${timestamp}] ${iconos[tipo]} ${mensaje}<br>`;
            logArea.scrollTop = logArea.scrollHeight;
        }

        function testImpresionMejorada() {
            addLog('Iniciando test de impresión mejorada...', 'info');
            
            try {
                const url = 'imprimir_recibo_mejorado.php?pago_id=999';
                addLog('Abriendo recibo mejorado: ' + url, 'debug');
                
                const ventana = window.open(url, 'testMejorado_' + Date.now(), 
                    'width=450,height=700,scrollbars=yes,resizable=yes');
                
                if (ventana) {
                    addLog('Ventana de recibo mejorado abierta correctamente', 'success');
                    
                    setTimeout(() => {
                        if (ventana.closed) {
                            addLog('La ventana se cerró inesperadamente', 'warning');
                        } else {
                            addLog('Ventana estable después de 2 segundos', 'success');
                        }
                    }, 2000);
                } else {
                    addLog('Error: No se pudo abrir la ventana (posiblemente bloqueada)', 'error');
                }
            } catch (error) {
                addLog('Error en test mejorado: ' + error.message, 'error');
            }
        }

        function testImpresionOriginal() {
            addLog('Iniciando test de impresión original...', 'info');
            
            try {
                const url = 'imprimir_recibo.php?pago_id=999';
                addLog('Abriendo recibo original: ' + url, 'debug');
                
                const ventana = window.open(url, 'testOriginal_' + Date.now(), 
                    'width=400,height=600,scrollbars=yes,resizable=yes');
                
                if (ventana) {
                    addLog('Ventana de recibo original abierta correctamente', 'success');
                    
                    setTimeout(() => {
                        if (ventana.closed) {
                            addLog('La ventana se cerró inesperadamente', 'warning');
                        } else {
                            addLog('Ventana estable después de 2 segundos', 'success');
                        }
                    }, 2000);
                } else {
                    addLog('Error: No se pudo abrir la ventana (posiblemente bloqueada)', 'error');
                }
            } catch (error) {
                addLog('Error en test original: ' + error.message, 'error');
            }
        }

        function testModalFacturacion() {
            addLog('Redirigiendo a facturación con modal...', 'info');
            
            // Simular el flujo real desde facturación
            window.location.href = 'facturacion.php';
        }

        function testDiagnosticoCompleto() {
            addLog('Abriendo herramientas de diagnóstico...', 'info');
            
            try {
                const ventana = window.open('test_impresion_automatica.php', 'diagnostico', 
                    'width=800,height=600,scrollbars=yes,resizable=yes');
                
                if (ventana) {
                    addLog('Herramientas de diagnóstico abiertas', 'success');
                } else {
                    addLog('Error: No se pudo abrir diagnóstico', 'error');
                }
            } catch (error) {
                addLog('Error en diagnóstico: ' + error.message, 'error');
            }
        }

        function clearLog() {
            document.getElementById('logArea').innerHTML = 
                '[' + new Date().toLocaleTimeString() + '] 🧹 Log limpiado...<br>';
        }

        function exportLog() {
            const logContent = document.getElementById('logArea').textContent;
            const blob = new Blob([logContent], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'test_impresion_log_' + new Date().toISOString().slice(0,10) + '.txt';
            a.click();
            window.URL.revokeObjectURL(url);
            addLog('Log exportado como archivo de texto', 'success');
        }

        // Debug inicial
        addLog('Navegador: ' + navigator.userAgent, 'debug');
        addLog('window.print disponible: ' + (typeof window.print === 'function'), 'debug');
        addLog('Sistema de test listo para usar', 'success');
    </script>
</body>
</html>


