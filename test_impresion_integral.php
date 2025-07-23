<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';

// Verificar autenticación
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "❌ Usuario no autenticado. <a href='index.php'>Ir al login</a>";
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Integral de Impresión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .warning { background-color: #fff3cd; border-color: #ffeaa7; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Integral de Impresión de Recibos</h1>
        <p class="text-muted">Esta página verifica todo el flujo de impresión paso a paso</p>
        
        <!-- Test 1: Estado de Sesión -->
        <div class="test-section <?php echo isset($_SESSION['ultimo_pago']) ? 'success' : 'warning'; ?>">
            <h3>📋 Test 1: Estado de la Sesión</h3>
            <p><strong>Usuario logueado:</strong> <?php echo $_SESSION['loggedin'] ? '✅ SÍ' : '❌ NO'; ?></p>
            <p><strong>ID de usuario:</strong> <?php echo $_SESSION['id'] ?? 'No definido'; ?></p>
            <p><strong>Último pago en sesión:</strong> <?php echo isset($_SESSION['ultimo_pago']) ? '✅ SÍ' : '⚠️ NO'; ?></p>
            
            <?php if (isset($_SESSION['ultimo_pago'])): ?>
                <div class="mt-3">
                    <h5>Datos del último pago:</h5>
                    <pre><?php print_r($_SESSION['ultimo_pago']); ?></pre>
                </div>
            <?php endif; ?>
        </div>

        <!-- Test 2: Verificar Base de Datos -->
        <div class="test-section">
            <h3>🗄️ Test 2: Verificar Base de Datos</h3>
            <?php
            try {
                $stmt = $conn->query("SELECT COUNT(*) as total FROM pagos");
                $total_pagos = $stmt->fetchColumn();
                
                if ($total_pagos > 0) {
                    echo "<p class='text-success'>✅ Hay $total_pagos pagos en la base de datos</p>";
                    
                    // Obtener el último pago
                    $stmt = $conn->query("
                        SELECT p.id, p.monto, p.fecha_pago, p.paciente_id, 
                               CONCAT(pac.nombre, ' ', pac.apellido) as paciente_nombre
                        FROM pagos p 
                        LEFT JOIN pacientes pac ON p.paciente_id = pac.id 
                        ORDER BY p.id DESC 
                        LIMIT 1
                    ");
                    $ultimo_pago_bd = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($ultimo_pago_bd) {
                        echo "<div class='alert alert-info mt-3'>";
                        echo "<h5>Último pago en BD:</h5>";
                        echo "<p><strong>ID:</strong> {$ultimo_pago_bd['id']}</p>";
                        echo "<p><strong>Monto:</strong> $" . number_format($ultimo_pago_bd['monto'], 2) . "</p>";
                        echo "<p><strong>Fecha:</strong> {$ultimo_pago_bd['fecha_pago']}</p>";
                        echo "<p><strong>Paciente:</strong> {$ultimo_pago_bd['paciente_nombre']}</p>";
                        echo "</div>";
                        
                        // Botón para usar este pago
                        echo "<button onclick='usarUltimoPago({$ultimo_pago_bd['id']})' class='btn btn-primary'>Usar este pago para prueba</button>";
                    }
                } else {
                    echo "<p class='text-warning'>⚠️ No hay pagos en la base de datos</p>";
                    echo "<p>Necesitas crear un pago para probar la impresión.</p>";
                }
            } catch (Exception $e) {
                echo "<p class='text-danger'>❌ Error al consultar base de datos: " . $e->getMessage() . "</p>";
            }
            ?>
        </div>

        <!-- Test 3: Test de API -->
        <div class="test-section">
            <h3>🔗 Test 3: API get_ultimo_pago.php</h3>
            <button onclick="testApiUltimoPago()" class="btn btn-info">Probar API</button>
            <div id="api-result" class="mt-3"></div>
        </div>

        <!-- Test 4: Simulación de Impresión -->
        <div class="test-section">
            <h3>🖨️ Test 4: Simulación de Impresión</h3>
            <p>Estos botones simularán el flujo completo de impresión:</p>
            
            <div class="row">
                <div class="col-md-6">
                    <h5>Opción A: Con datos de sesión</h5>
                    <button onclick="simularImpresionConSesion()" class="btn btn-success w-100">Simular con datos de sesión</button>
                </div>
                <div class="col-md-6">
                    <h5>Opción B: Sin datos de sesión</h5>
                    <button onclick="simularImpresionSinSesion()" class="btn btn-warning w-100">Simular sin datos de sesión</button>
                </div>
            </div>
            
            <div id="simulation-result" class="mt-3"></div>
        </div>

        <!-- Test 5: Acciones Directas -->
        <div class="test-section">
            <h3>⚡ Test 5: Acciones Directas</h3>
            <div class="row">
                <div class="col-md-3">
                    <button onclick="abrirReciboDirecto()" class="btn btn-primary w-100">Abrir Recibo Directo</button>
                </div>
                <div class="col-md-3">
                    <button onclick="abrirReciboMejorado()" class="btn btn-success w-100">Abrir Recibo Mejorado</button>
                </div>
                <div class="col-md-3">
                    <button onclick="limpiarSesion()" class="btn btn-warning w-100">Limpiar Sesión</button>
                </div>
                <div class="col-md-3">
                    <button onclick="crearPagoPrueba()" class="btn btn-info w-100">Crear Pago Prueba</button>
                </div>
            </div>
        </div>

        <!-- Test 6: Logs del Navegador -->
        <div class="test-section">
            <h3>📝 Test 6: Logs del Navegador</h3>
            <p>Abre las herramientas de desarrollador (F12) para ver los logs detallados.</p>
            <button onclick="mostrarLogsCompletos()" class="btn btn-secondary">Mostrar logs en consola</button>
        </div>

        <div class="mt-4">
            <a href="facturacion.php" class="btn btn-outline-primary">🧾 Ir a Facturación</a>
            <a href="verificar_pagos.php" class="btn btn-outline-info">🔍 Verificar Pagos</a>
            <a href="index.php" class="btn btn-outline-secondary">🏠 Volver al Inicio</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        console.log('=== TEST INTEGRAL DE IMPRESIÓN INICIADO ===');
        
        function mostrarLogsCompletos() {
            console.log('🔍 === INFORMACIÓN COMPLETA DEL SISTEMA ===');
            console.log('URL actual:', window.location.href);
            console.log('User Agent:', navigator.userAgent);
            console.log('Timestamp:', new Date().toISOString());
            
            // Verificar datos de sesión en PHP
            <?php if (isset($_SESSION['ultimo_pago'])): ?>
                console.log('✅ Datos de sesión PHP disponibles:', <?php echo json_encode($_SESSION['ultimo_pago']); ?>);
            <?php else: ?>
                console.log('⚠️ No hay datos de sesión PHP');
            <?php endif; ?>
        }

        function testApiUltimoPago() {
            console.log('🔍 Probando API get_ultimo_pago.php...');
            const resultDiv = document.getElementById('api-result');
            resultDiv.innerHTML = '<div class="spinner-border" role="status"></div> Consultando...';
            
            fetch('get_ultimo_pago.php')
                .then(response => {
                    console.log('📡 Respuesta del servidor:', response);
                    return response.json();
                })
                .then(data => {
                    console.log('📋 Datos recibidos:', data);
                    
                    if (data.success) {
                        resultDiv.innerHTML = `
                            <div class="alert alert-success">
                                <h5>✅ API funcionando correctamente</h5>
                                <p><strong>Pago ID:</strong> ${data.pago_id}</p>
                                <p><strong>Monto:</strong> $${data.monto}</p>
                                <p><strong>Paciente:</strong> ${data.paciente_nombre}</p>
                            </div>
                        `;
                    } else {
                        resultDiv.innerHTML = `
                            <div class="alert alert-warning">
                                <h5>⚠️ API respondió sin éxito</h5>
                                <p>${data.message}</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('❌ Error en API:', error);
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <h5>❌ Error en la API</h5>
                            <p>${error.message}</p>
                        </div>
                    `;
                });
        }

        function usarUltimoPago(pagoId) {
            console.log('🔄 Configurando último pago:', pagoId);
            
            // Hacer una petición para establecer este pago en la sesión
            fetch('get_ultimo_pago.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Pago configurado en sesión. Recarga la página para ver los cambios.');
                        window.location.reload();
                    } else {
                        alert('❌ Error al configurar el pago: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('❌ Error: ' + error.message);
                });
        }

        function simularImpresionConSesion() {
            console.log('🖨️ Simulando impresión CON datos de sesión...');
            const resultDiv = document.getElementById('simulation-result');
            
            // Simular el flujo de la función imprimirRecibo() de facturacion.php
            <?php if (isset($_SESSION['ultimo_pago'])): ?>
                console.log('✅ Datos de sesión disponibles');
                const pagoId = '<?php echo $_SESSION['ultimo_pago']['pago_id']; ?>';
                resultDiv.innerHTML = `
                    <div class="alert alert-success">
                        <h5>✅ Simulación exitosa con sesión</h5>
                        <p>Se abriría la ventana de impresión con pago ID: ${pagoId}</p>
                        <button onclick="abrirVentanaImpresion('${pagoId}')" class="btn btn-primary">Abrir ventana real</button>
                    </div>
                `;
            <?php else: ?>
                console.log('❌ No hay datos de sesión');
                resultDiv.innerHTML = `
                    <div class="alert alert-warning">
                        <h5>⚠️ No hay datos de sesión</h5>
                        <p>Se activaría el flujo de recuperación desde la base de datos</p>
                    </div>
                `;
            <?php endif; ?>
        }

        function simularImpresionSinSesion() {
            console.log('🔍 Simulando impresión SIN datos de sesión...');
            const resultDiv = document.getElementById('simulation-result');
            
            // Simular consulta a la API
            fetch('get_ultimo_pago.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultDiv.innerHTML = `
                            <div class="alert alert-success">
                                <h5>✅ Recuperación exitosa desde BD</h5>
                                <p>Se encontró pago ID: ${data.pago_id}</p>
                                <button onclick="abrirVentanaImpresion('${data.pago_id}')" class="btn btn-primary">Abrir ventana real</button>
                            </div>
                        `;
                    } else {
                        resultDiv.innerHTML = `
                            <div class="alert alert-warning">
                                <h5>⚠️ No se encontraron pagos</h5>
                                <p>Se sugeriría crear un pago de prueba</p>
                                <button onclick="crearPagoPrueba()" class="btn btn-info">Crear pago de prueba</button>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <h5>❌ Error en la consulta</h5>
                            <p>${error.message}</p>
                        </div>
                    `;
                });
        }

        function abrirVentanaImpresion(pagoId) {
            console.log('🖨️ Abriendo ventana de impresión para pago:', pagoId);
            
            const url = 'imprimir_recibo_mejorado.php?pago_id=' + encodeURIComponent(pagoId);
            const windowFeatures = 'width=450,height=700,scrollbars=yes,resizable=yes,menubar=no,toolbar=no,location=no,status=yes,titlebar=yes,directories=no,fullscreen=no,channelmode=no,dependent=yes';
            
            const ventana = window.open(url, 'recibo_' + pagoId, windowFeatures);
            
            if (ventana) {
                console.log('✅ Ventana abierta exitosamente');
            } else {
                console.error('❌ No se pudo abrir la ventana (popup bloqueado?)');
                alert('❌ No se pudo abrir la ventana. Verifique si los popups están bloqueados.');
            }
        }

        function abrirReciboDirecto() {
            window.open('imprimir_recibo.php', '_blank');
        }

        function abrirReciboMejorado() {
            window.open('imprimir_recibo_mejorado.php', '_blank');
        }

        function limpiarSesion() {
            if (confirm('¿Está seguro de que quiere limpiar los datos de sesión de impresión?')) {
                fetch('clear_ultimo_pago.php', { method: 'POST' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert('✅ Sesión limpiada exitosamente');
                            window.location.reload();
                        } else {
                            alert('❌ Error al limpiar sesión: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('❌ Error: ' + error.message);
                    });
            }
        }

        function crearPagoPrueba() {
            window.location.href = 'crear_pago_prueba.php';
        }

        // Ejecutar logs completos al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            mostrarLogsCompletos();
        });
    </script>
</body>
</html>
