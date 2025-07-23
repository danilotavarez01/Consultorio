<?php
session_start();

echo "<h2>🔍 Depuración: Datos de Sesión para Modal</h2>";

echo "<div style='background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; font-family: monospace;'>";

if (isset($_SESSION['show_print_modal'])) {
    echo "<p><strong>show_print_modal:</strong> " . ($_SESSION['show_print_modal'] ? 'true' : 'false') . "</p>";
} else {
    echo "<p><strong>show_print_modal:</strong> NO EXISTE</p>";
}

if (isset($_SESSION['ultimo_pago'])) {
    echo "<p><strong>ultimo_pago:</strong></p>";
    echo "<pre style='background: white; padding: 10px; border: 1px solid #ddd;'>";
    print_r($_SESSION['ultimo_pago']);
    echo "</pre>";
} else {
    echo "<p><strong>ultimo_pago:</strong> NO EXISTE</p>";
}

echo "</div>";

$modal_should_show = isset($_SESSION['show_print_modal']) && $_SESSION['show_print_modal'] === true && isset($_SESSION['ultimo_pago']);

echo "<div style='background: " . ($modal_should_show ? '#d4edda' : '#f8d7da') . "; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
echo "<h3>Resultado:</h3>";
echo "<p><strong>¿Modal debe aparecer?</strong> " . ($modal_should_show ? '✅ SÍ' : '❌ NO') . "</p>";
echo "</div>";

echo "<div style='margin: 20px 0;'>";
echo "<p><a href='facturacion.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📋 Ir a Facturación</a></p>";
echo "<p><a href='simular_pago_directo.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>💰 Simular Pago</a></p>";
echo "<p><a href='imprimir_recibo.php' target='_blank' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🖨️ Probar Recibo Directo</a></p>";
echo "</div>";

// Si el modal debe aparecer, mostrar los datos que se usarían
if ($modal_should_show) {
    $pago = $_SESSION['ultimo_pago'];
    echo "<div style='background: #e2e3e5; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h3>Datos que aparecerían en el modal:</h3>";
    echo "<table style='width: 100%; border-collapse: collapse;'>";
    echo "<tr><td style='padding: 5px; border: 1px solid #ddd;'><strong>Factura:</strong></td><td style='padding: 5px; border: 1px solid #ddd;'>" . htmlspecialchars($pago['numero_factura'] ?? 'N/A') . "</td></tr>";
    echo "<tr><td style='padding: 5px; border: 1px solid #ddd;'><strong>Paciente:</strong></td><td style='padding: 5px; border: 1px solid #ddd;'>" . htmlspecialchars($pago['paciente_nombre'] ?? 'Paciente') . "</td></tr>";
    echo "<tr><td style='padding: 5px; border: 1px solid #ddd;'><strong>Monto:</strong></td><td style='padding: 5px; border: 1px solid #ddd;'>$" . number_format(floatval($pago['monto'] ?? 0), 2) . "</td></tr>";
    echo "<tr><td style='padding: 5px; border: 1px solid #ddd;'><strong>Método:</strong></td><td style='padding: 5px; border: 1px solid #ddd;'>" . ucfirst(str_replace('_', ' ', $pago['metodo_pago'] ?? 'efectivo')) . "</td></tr>";
    echo "</table>";
    echo "</div>";
}
?>
