<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Completo de Impresión</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-step { margin: 15px 0; padding: 15px; border-radius: 5px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2>🧪 Test Completo de Impresión de Recibos</h2>
        
        <?php
        require_once 'session_config.php';
        session_start();
        require_once 'config.php';

        // Verificar login
        if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
            echo '<div class="test-step error"><h4>❌ Error de Autenticación</h4><p>Usuario no logueado. <a href="index.php">Ir al Login</a></p></div>';
            exit();
        }

        echo '<div class="test-step success"><h4>✅ Test 1: Autenticación</h4><p>Usuario logueado correctamente como: ' . htmlspecialchars($_SESSION['username']) . '</p></div>';

        // Test 2: Conexión a base de datos
        try {
            $stmt = $conn->query('SELECT COUNT(*) as total FROM pagos');
            $total_pagos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            echo '<div class="test-step success"><h4>✅ Test 2: Base de Datos</h4><p>Conexión exitosa. Total de pagos: ' . $total_pagos . '</p></div>';
        } catch (Exception $e) {
            echo '<div class="test-step error"><h4>❌ Test 2: Base de Datos</h4><p>Error: ' . htmlspecialchars($e->getMessage()) . '</p></div>';
            exit();
        }

        // Test 3: Obtener último pago
        try {
            $stmt = $conn->query('SELECT p.id, p.monto, f.numero_factura FROM pagos p LEFT JOIN facturas f ON p.factura_id = f.id ORDER BY p.id DESC LIMIT 1');
            $ultimo_pago = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($ultimo_pago) {
                echo '<div class="test-step success"><h4>✅ Test 3: Último Pago</h4>';
                echo '<p>ID: ' . $ultimo_pago['id'] . ', Factura: ' . htmlspecialchars($ultimo_pago['numero_factura']) . ', Monto: $' . $ultimo_pago['monto'] . '</p>';
                echo '</div>';
                $test_pago_id = $ultimo_pago['id'];
            } else {
                echo '<div class="test-step warning"><h4>⚠️ Test 3: Sin Pagos</h4><p>No hay pagos en la base de datos</p></div>';
                $test_pago_id = null;
            }
        } catch (Exception $e) {
            echo '<div class="test-step error"><h4>❌ Test 3: Error al obtener pago</h4><p>' . htmlspecialchars($e->getMessage()) . '</p></div>';
            $test_pago_id = null;
        }

        // Test 4: Simular pago en sesión
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_session'])) {
            $_SESSION['ultimo_pago'] = [
                'pago_id' => $test_pago_id ?? 999,
                'factura_id' => 1,
                'numero_factura' => 'FAC-TEST-SESSION',
                'monto' => 123.45,
                'metodo_pago' => 'efectivo',
                'paciente_nombre' => 'Paciente Test Sesión',
                'paciente_cedula' => '87654321',
                'medico_nombre' => 'Dr. Test Sesión'
            ];
            $_SESSION['show_print_modal'] = true;
            
            echo '<div class="test-step success"><h4>✅ Test 4: Pago en Sesión</h4><p>Datos guardados en sesión exitosamente</p></div>';
        } else {
            echo '<div class="test-step info"><h4>ℹ️ Test 4: Pago en Sesión</h4>';
            echo '<form method="POST" style="display: inline;"><button type="submit" name="test_session" class="btn btn-primary btn-sm">Simular Pago en Sesión</button></form>';
            echo '</div>';
        }

        // Test 5: Estado actual de sesión
        echo '<div class="test-step info"><h4>ℹ️ Test 5: Estado de Sesión</h4>';
        echo '<p><strong>ultimo_pago:</strong> ' . (isset($_SESSION['ultimo_pago']) ? '✅ Existe' : '❌ No existe') . '</p>';
        if (isset($_SESSION['ultimo_pago'])) {
            echo '<pre style="font-size: 11px; max-height: 150px; overflow-y: auto;">' . print_r($_SESSION['ultimo_pago'], true) . '</pre>';
        }
        echo '</div>';

        // Test 6: Links de prueba
        echo '<div class="test-step info"><h4>🔗 Test 6: Enlaces de Prueba</h4>';
        
        if ($test_pago_id) {
            echo '<p><a href="imprimir_recibo.php?pago_id=' . $test_pago_id . '" target="_blank" class="btn btn-success btn-sm">📄 Abrir Recibo por ID (BD)</a></p>';
        }
        
        if (isset($_SESSION['ultimo_pago'])) {
            echo '<p><a href="imprimir_recibo.php" target="_blank" class="btn btn-primary btn-sm">📄 Abrir Recibo por Sesión</a></p>';
        }
        
        echo '<p><a href="debug_session.php" target="_blank" class="btn btn-info btn-sm">🔍 Debug de Sesión</a></p>';
        echo '</div>';

        // Test 7: JavaScript
        echo '<div class="test-step info"><h4>🔧 Test 7: Funciones JavaScript</h4>';
        if ($test_pago_id) {
            echo '<button onclick="abrirReciboPorId(' . $test_pago_id . ')" class="btn btn-success btn-sm">🖨️ Recibo por ID (JS)</button> ';
        }
        if (isset($_SESSION['ultimo_pago'])) {
            echo '<button onclick="abrirReciboPorSesion()" class="btn btn-primary btn-sm">🖨️ Recibo por Sesión (JS)</button> ';
        }
        echo '</div>';

        // Limpiar sesión
        if (isset($_POST['clear_session'])) {
            unset($_SESSION['ultimo_pago']);
            unset($_SESSION['show_print_modal']);
            echo '<div class="test-step warning"><h4>🧹 Sesión Limpiada</h4><p>Variables de impresión eliminadas</p></div>';
            echo '<script>setTimeout(function(){ location.reload(); }, 1000);</script>';
        }
        ?>
        
        <div class="test-step warning">
            <h4>🧹 Limpiar Test</h4>
            <form method="POST" style="display: inline;">
                <button type="submit" name="clear_session" class="btn btn-warning btn-sm">Limpiar Variables de Sesión</button>
            </form>
        </div>

        <div class="mt-4">
            <a href="facturacion.php" class="btn btn-secondary">← Volver a Facturación</a>
            <a href="simular_pago_test.php" class="btn btn-info">🧪 Simulador de Pagos</a>
        </div>
    </div>

    <script>
        function abrirReciboPorId(pagoId) {
            console.log('Abriendo recibo por ID:', pagoId);
            const url = 'imprimir_recibo.php?pago_id=' + pagoId;
            const ventana = window.open(url, 'recibo_id', 'width=800,height=600,scrollbars=yes,resizable=yes');
            if (!ventana) {
                alert('Ventana bloqueada por el navegador');
            }
        }

        function abrirReciboPorSesion() {
            console.log('Abriendo recibo por sesión');
            const ventana = window.open('imprimir_recibo.php', 'recibo_sesion', 'width=800,height=600,scrollbars=yes,resizable=yes');
            if (!ventana) {
                alert('Ventana bloqueada por el navegador');
            }
        }
    </script>
</body>
</html>

