<?php
session_start();

echo "<h2>🔍 Diagnóstico Completo - Estado de Sesión</h2>";

echo "<div style='background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
echo "<h3>Variables de Sesión Actuales:</h3>";

if (empty($_SESSION)) {
    echo "<p style='color: green;'>✅ Sesión está VACÍA - esto es correcto</p>";
} else {
    echo "<p style='color: red;'>❌ Sesión contiene datos:</p>";
    echo "<ul>";
    foreach ($_SESSION as $key => $value) {
        echo "<li><strong>$key:</strong> ";
        if (is_array($value)) {
            echo "<pre>" . print_r($value, true) . "</pre>";
        } else {
            echo htmlspecialchars($value);
        }
        echo "</li>";
    }
    echo "</ul>";
}
echo "</div>";

// Limpiar TODO
session_destroy();
session_start();

echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
echo "<h3>✅ Sesión Destruida y Reiniciada</h3>";
echo "<p>Ahora debería estar completamente limpia.</p>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
echo "<h3>🧪 Pruebas:</h3>";
echo "<p><a href='facturacion.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📋 Ir a Facturación (debe estar LIMPIO)</a></p>";
echo "<p><a href='test_modal_manual.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Configurar Modal de Prueba</a></p>";
echo "</div>";

// Verificar si el código PHP de facturación tiene algún problema
echo "<div style='background: #e2e3e5; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
echo "<h3>🔧 Verificación Técnica:</h3>";
echo "<p><strong>Condición del modal en facturación.php:</strong></p>";
echo "<code>if (isset(\$_SESSION['show_print_modal']) && \$_SESSION['show_print_modal'] === true && isset(\$_SESSION['ultimo_pago']))</code>";
echo "<br><br>";

$show_modal = isset($_SESSION['show_print_modal']);
$modal_true = isset($_SESSION['show_print_modal']) && $_SESSION['show_print_modal'] === true;
$has_pago = isset($_SESSION['ultimo_pago']);

echo "<p>• show_print_modal existe: " . ($show_modal ? "❌ SÍ" : "✅ NO") . "</p>";
echo "<p>• show_print_modal es true: " . ($modal_true ? "❌ SÍ" : "✅ NO") . "</p>";
echo "<p>• ultimo_pago existe: " . ($has_pago ? "❌ SÍ" : "✅ NO") . "</p>";

$should_show = $show_modal && $modal_true && $has_pago;
echo "<p><strong>Resultado: Modal debería aparecer: " . ($should_show ? "❌ SÍ (PROBLEMA)" : "✅ NO (CORRECTO)") . "</strong></p>";
echo "</div>";
?>
