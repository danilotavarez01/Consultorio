<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';

// Verificar login
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "<h3>❌ No logueado. <a href='index.php'>Ir al Login</a></h3>";
    exit();
}

echo "<!DOCTYPE html>";
echo "<html><head><title>🔧 Diagnóstico de Impresión</title>";
echo "<style>body { font-family: Arial; padding: 20px; } .test { margin: 15px 0; padding: 15px; border: 1px solid #ccc; background: #f9f9f9; } .success { background: #d4edda; } .error { background: #f8d7da; }</style>";
echo "</head><body>";

echo "<h2>🔧 Diagnóstico de Impresión</h2>";

// Test 1: Estado de sesión
echo "<div class='test'>";
echo "<h4>Test 1: Estado de Sesión</h4>";
echo "<p><strong>Usuario:</strong> " . htmlspecialchars($_SESSION['username'] ?? 'N/A') . "</p>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>ultimo_pago existe:</strong> " . (isset($_SESSION['ultimo_pago']) ? '✅ SÍ' : '❌ NO') . "</p>";
if (isset($_SESSION['ultimo_pago'])) {
    echo "<pre>" . print_r($_SESSION['ultimo_pago'], true) . "</pre>";
}
echo "</div>";

// Test 2: Simular datos de pago
if (isset($_GET['simular'])) {
    $_SESSION['ultimo_pago'] = [
        'pago_id' => 999,
        'numero_factura' => 'FAC-DIAG-' . date('His'),
        'monto' => 123.45,
        'metodo_pago' => 'efectivo',
        'paciente_nombre' => 'Paciente Diagnóstico',
        'paciente_cedula' => '12345678',
        'medico_nombre' => 'Dr. Diagnóstico'
    ];
    echo "<div class='test success'><h4>✅ Datos simulados creados</h4></div>";
}

// Test 3: Verificar último pago en BD
try {
    $stmt = $conn->query("SELECT p.id, p.monto, f.numero_factura FROM pagos p LEFT JOIN facturas f ON p.factura_id = f.id ORDER BY p.id DESC LIMIT 1");
    $ultimo_pago_bd = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<div class='test'>";
    echo "<h4>Test 3: Último Pago en BD</h4>";
    if ($ultimo_pago_bd) {
        echo "<p>✅ <strong>ID:</strong> " . $ultimo_pago_bd['id'] . "</p>";
        echo "<p><strong>Factura:</strong> " . htmlspecialchars($ultimo_pago_bd['numero_factura']) . "</p>";
        echo "<p><strong>Monto:</strong> $" . $ultimo_pago_bd['monto'] . "</p>";
        $test_pago_id = $ultimo_pago_bd['id'];
    } else {
        echo "<p>❌ No hay pagos en la base de datos</p>";
        $test_pago_id = null;
    }
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='test error'><h4>❌ Error BD:</h4><p>" . $e->getMessage() . "</p></div>";
    $test_pago_id = null;
}

// Test 4: Enlaces de prueba
echo "<div class='test'>";
echo "<h4>Test 4: Enlaces de Prueba</h4>";

if (!isset($_SESSION['ultimo_pago'])) {
    echo "<p><a href='?simular=1' style='background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>🔧 Simular Datos de Sesión</a></p>";
} else {
    echo "<p><a href='imprimir_recibo.php' target='_blank' style='background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>📄 Abrir Recibo (Sesión)</a></p>";
}

if ($test_pago_id) {
    echo "<p><a href='imprimir_recibo.php?pago_id=" . $test_pago_id . "' target='_blank' style='background: #17a2b8; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>📄 Abrir Recibo (BD ID: " . $test_pago_id . ")</a></p>";
}

echo "</div>";

// Test 5: Prueba directa con JavaScript
echo "<div class='test'>";
echo "<h4>Test 5: Prueba con JavaScript</h4>";
echo "<button onclick='probarImpresion()' style='background: #ffc107; color: black; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;'>🖨️ Probar Impresión con JS</button>";
echo "<div id='resultado' style='margin-top: 10px; padding: 10px; background: #e9ecef; display: none;'></div>";
echo "</div>";

// Test 6: Verificar navegador
echo "<div class='test'>";
echo "<h4>Test 6: Información del Navegador</h4>";
echo "<p><strong>User Agent:</strong></p>";
echo "<pre style='font-size: 11px; overflow-wrap: break-word;'>" . htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') . "</pre>";
echo "</div>";

echo "<hr>";
echo "<p><a href='facturacion.php'>← Volver a Facturación</a></p>";

echo "<script>";
echo "function probarImpresion() {";
echo "    const resultado = document.getElementById('resultado');";
echo "    resultado.style.display = 'block';";
echo "    resultado.innerHTML = '🔄 Abriendo ventana de recibo...';";
echo "    ";
echo "    console.log('Iniciando prueba de impresión...');";
echo "    ";
if (isset($_SESSION['ultimo_pago'])) {
    echo "    const url = 'imprimir_recibo.php';";
} else if ($test_pago_id) {
    echo "    const url = 'imprimir_recibo.php?pago_id=" . $test_pago_id . "';";
} else {
    echo "    resultado.innerHTML = '❌ No hay datos de pago disponibles';";
    echo "    return;";
}
echo "    ";
echo "    console.log('URL del recibo:', url);";
echo "    ";
echo "    const ventana = window.open(url, 'recibo_test', 'width=400,height=600,scrollbars=yes,resizable=yes,menubar=no,toolbar=no,location=no,status=no');";
echo "    ";
echo "    if (ventana) {";
echo "        resultado.innerHTML = '✅ Ventana abierta. Verificando carga...';";
echo "        ";
echo "        ventana.onload = function() {";
echo "            console.log('Ventana cargada');";
echo "            resultado.innerHTML += '<br>✅ Ventana cargada correctamente';";
echo "        };";
echo "        ";
echo "        setTimeout(function() {";
echo "            if (ventana.closed) {";
echo "                resultado.innerHTML += '<br>❌ La ventana se cerró inesperadamente';";
echo "            } else {";
echo "                resultado.innerHTML += '<br>✅ Ventana sigue abierta después de 2 segundos';";
echo "            }";
echo "        }, 2000);";
echo "        ";
echo "    } else {";
echo "        resultado.innerHTML = '❌ No se pudo abrir la ventana. Posibles causas:<br>' +";
echo "                            '• Bloqueador de ventanas emergentes activo<br>' +";
echo "                            '• Navegador no soporta window.open<br>' +";
echo "                            '• Error de JavaScript';";
echo "        console.error('Error: No se pudo abrir ventana emergente');";
echo "    }";
echo "}";
echo "</script>";

echo "</body></html>";
?>
