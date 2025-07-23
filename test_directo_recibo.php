<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Test Directo de Recibo</title></head><body>";
echo "<h2>🔧 Test Directo de Recibo</h2>";

// Verificar login
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "<p style='color: red;'>❌ No logueado. <a href='index.php'>Login</a></p>";
    exit();
}

echo "<p style='color: green;'>✅ Usuario logueado: " . htmlspecialchars($_SESSION['username']) . "</p>";

// Test 1: Crear datos en sesión
if (isset($_GET['crear_sesion'])) {
    $_SESSION['ultimo_pago'] = [
        'pago_id' => 123,
        'factura_id' => 1,
        'numero_factura' => 'FAC-DIRECT-TEST',
        'monto' => 75.50,
        'metodo_pago' => 'efectivo',
        'paciente_nombre' => 'Paciente Test Directo',
        'paciente_cedula' => '11111111',
        'medico_nombre' => 'Dr. Test Directo'
    ];
    echo "<p style='color: green;'>✅ Datos creados en sesión</p>";
}

// Test 2: Mostrar estado actual
echo "<h3>Estado Actual de Sesión:</h3>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "ultimo_pago existe: " . (isset($_SESSION['ultimo_pago']) ? 'SÍ' : 'NO') . "\n";
if (isset($_SESSION['ultimo_pago'])) {
    echo "Contenido:\n";
    print_r($_SESSION['ultimo_pago']);
}
echo "</pre>";

// Test 3: Simular el código de imprimir_recibo.php
echo "<h3>Simulación de imprimir_recibo.php:</h3>";

if (isset($_SESSION['ultimo_pago'])) {
    $pago = $_SESSION['ultimo_pago'];
    echo "<p style='color: green;'>✅ Datos de pago obtenidos de sesión</p>";
    
    // Simular procesamiento
    $pago_detalle = [
        'numero_factura' => $pago['numero_factura'] ?? 'N/A',
        'monto' => $pago['monto'] ?? 0,
        'metodo_pago' => $pago['metodo_pago'] ?? 'efectivo',
        'fecha_pago_formato' => date('d/m/Y H:i'),
        'paciente_nombre' => $pago['paciente_nombre'] ?? 'Paciente',
        'paciente_cedula' => $pago['paciente_cedula'] ?? '',
        'medico_nombre' => $pago['medico_nombre'] ?? 'Médico',
        'factura_observaciones' => 'Test de recibo directo'
    ];
    
    echo "<p style='color: green;'>✅ Datos procesados correctamente</p>";
    echo "<h4>Datos finales para el recibo:</h4>";
    echo "<pre>";
    print_r($pago_detalle);
    echo "</pre>";
    
    echo "<p><a href='imprimir_recibo.php' target='_blank' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🖨️ Abrir Recibo Real</a></p>";
    
} else {
    echo "<p style='color: red;'>❌ No hay datos de último pago en sesión</p>";
}

// Enlaces de control
echo "<hr>";
echo "<p>";
echo "<a href='?crear_sesion=1' style='background: #28a745; color: white; padding: 8px; text-decoration: none; border-radius: 3px;'>Crear Datos en Sesión</a> ";
echo "<a href='?' style='background: #17a2b8; color: white; padding: 8px; text-decoration: none; border-radius: 3px;'>Recargar</a> ";
echo "<a href='facturacion.php' style='background: #6c757d; color: white; padding: 8px; text-decoration: none; border-radius: 3px;'>Volver a Facturación</a>";
echo "</p>";

echo "</body></html>";
?>
