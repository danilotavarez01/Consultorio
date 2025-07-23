<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';

// Verificar login
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header("Location: index.php");
    exit();
}

// Crear datos de prueba para impresión
if (isset($_GET['test_print'])) {
    $_SESSION['ultimo_pago'] = [
        'pago_id' => 999,
        'numero_factura' => 'FAC-PRINT-TEST-' . date('His'),
        'monto' => 150.00,
        'metodo_pago' => 'efectivo',
        'paciente_nombre' => 'PACIENTE TEST IMPRESIÓN',
        'paciente_cedula' => '12345678',
        'medico_nombre' => 'DR. TEST IMPRESIÓN'
    ];
    
    // Redirigir al recibo para auto-impresión
    header("Location: imprimir_recibo.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🖨️ Test de Impresión Automática</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-print mr-2"></i>Test de Impresión Automática</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Propósito:</strong> Esta herramienta permite probar la funcionalidad de impresión automática del sistema.
                        </div>
                        
                        <h5>📋 Instrucciones:</h5>
                        <ol>
                            <li><strong>Verificar impresora:</strong> Asegúrese de que su impresora esté conectada y funcionando</li>
                            <li><strong>Configurar como predeterminada:</strong> La impresora debe estar configurada como predeterminada en Windows</li>
                            <li><strong>Permitir ventanas emergentes:</strong> El navegador debe permitir ventanas emergentes para este sitio</li>
                            <li><strong>Ejecutar test:</strong> Haga clic en el botón de abajo para generar un recibo de prueba</li>
                        </ol>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Importante:</strong> El recibo se abrirá en una nueva ventana y debería imprimir automáticamente.
                            Si no imprime, use el botón "Imprimir Manualmente" en la ventana del recibo.
                        </div>
                        
                        <div class="text-center my-4">
                            <a href="?test_print=1" class="btn btn-success btn-lg">
                                <i class="fas fa-print mr-2"></i>
                                🖨️ PROBAR IMPRESIÓN AUTOMÁTICA
                            </a>
                        </div>
                        
                        <hr>
                        
                        <h5>🔧 Opciones Adicionales:</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <a href="imprimir_recibo.php?pago_id=68" target="_blank" class="btn btn-info btn-block">
                                    📄 Test con Pago Real (ID 68)
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button onclick="testVentanaSimple()" class="btn btn-secondary btn-block">
                                    🪟 Test de Ventana Emergente
                                </button>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h6>📊 Estado del Sistema:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-user text-success"></i> Usuario: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></li>
                                <li><i class="fas fa-server text-success"></i> Servidor: <strong>Conectado</strong></li>
                                <li><i class="fas fa-database text-success"></i> Base de datos: <strong>Funcional</strong></li>
                                <li><i class="fas fa-code text-info"></i> JavaScript: <strong><span id="jsStatus">Verificando...</span></strong></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="text-center">
                            <a href="facturacion.php" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left mr-2"></i>Volver a Facturación
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Verificar JavaScript
        document.getElementById('jsStatus').textContent = 'Funcionando';
        document.getElementById('jsStatus').className = 'text-success';
        
        function testVentanaSimple() {
            console.log('Probando apertura de ventana simple...');
            
            const ventana = window.open('data:text/html,<h1>Test de Ventana</h1><p>Si ve esto, las ventanas emergentes funcionan.</p><button onclick="window.close()">Cerrar</button>', 'test', 'width=400,height=300');
            
            if (ventana) {
                alert('✅ Ventana emergente abierta exitosamente');
            } else {
                alert('❌ Las ventanas emergentes están bloqueadas.\n\nPor favor:\n1. Permita ventanas emergentes para este sitio\n2. Verifique la configuración del navegador\n3. Desactive extensiones que puedan bloquear ventanas');
            }
        }
        
        // Detectar capacidades del navegador
        window.onload = function() {
            console.log('=== INFORMACIÓN DEL NAVEGADOR ===');
            console.log('User Agent:', navigator.userAgent);
            console.log('Cookies habilitadas:', navigator.cookieEnabled);
            console.log('Capacidad de impresión:', typeof window.print === 'function');
            console.log('LocalStorage disponible:', typeof Storage !== 'undefined');
        };
    </script>
</body>
</html>
