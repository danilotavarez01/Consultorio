<?php
session_start();
require_once 'config.php';

echo "<h2>Diagnóstico: Modal de Impresión Fijo</h2>";

// Verificar estado de sesión
echo "<h3>Estado de Sesión:</h3>";
echo "<pre>";
echo "show_print_modal: " . (isset($_SESSION['show_print_modal']) ? 'SÍ' : 'NO') . "\n";
echo "ultimo_pago: " . (isset($_SESSION['ultimo_pago']) ? 'SÍ' : 'NO') . "\n";

if (isset($_SESSION['ultimo_pago'])) {
    echo "Datos último pago:\n";
    print_r($_SESSION['ultimo_pago']);
}
echo "</pre>";

// Limpiar sesión si es necesario
if (isset($_SESSION['show_print_modal'])) {
    unset($_SESSION['show_print_modal']);
    echo "<p style='color: red;'>🔴 Limpiando flag de modal...</p>";
}

if (isset($_SESSION['ultimo_pago'])) {
    unset($_SESSION['ultimo_pago']);
    echo "<p style='color: red;'>🔴 Limpiando datos de último pago...</p>";
}

echo "<h3>Verificación:</h3>";
echo "<p>✅ Si el modal aparecía fijo, ahora debería estar limpio.</p>";
echo "<p><a href='facturacion.php'>🔗 Ir a Facturación (sin modal)</a></p>";
echo "<p><a href='test_pago_completo.php'>🔗 Simular Pago (con modal)</a></p>";

// Crear script de prueba de pago si no existe
$test_pago_content = '<?php
session_start();
require_once "config.php";

echo "<h2>Test: Simular Pago para Modal</h2>";

// Simular datos de pago
$_SESSION["show_print_modal"] = true;
$_SESSION["ultimo_pago"] = [
    "numero_factura" => "FAC-0001",
    "paciente_nombre" => "Juan Pérez",
    "monto" => "150.00",
    "metodo_pago" => "efectivo"
];

echo "<p>✅ Datos de pago simulados.</p>";
echo "<p><a href=\"facturacion.php\">🔗 Ir a Facturación (DEBE mostrar modal)</a></p>";
echo "<p><a href=\"test_modal_fijo.php\">🔗 Limpiar modal</a></p>";
?>';

file_put_contents('test_pago_completo.php', $test_pago_content);

echo "<p style='color: green;'>✅ Script de prueba de pago creado.</p>";
?>
