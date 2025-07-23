<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';

echo "<h2>🧪 Prueba: Impresión sin Deslogueo</h2>";

// Verificar estado de login
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 15px 0; border-radius: 5px; color: #721c24;'>";
    echo "❌ <strong>Usuario no logueado</strong><br>";
    echo "<a href='index.php'>Ir al Login</a>";
    echo "</div>";
    exit();
}

echo "<div style='background: #d4edda; padding: 15px; margin: 15px 0; border-radius: 5px; color: #155724;'>";
echo "✅ <strong>Usuario logueado correctamente</strong><br>";
echo "Usuario: " . htmlspecialchars($_SESSION['username'] ?? 'N/A') . "<br>";
echo "ID: " . htmlspecialchars($_SESSION['id'] ?? 'N/A') . "<br>";
echo "Session ID: " . session_id() . "<br>";
echo "</div>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_print'])) {
    // Configurar datos de prueba para impresión
    $_SESSION['show_print_modal'] = true;
    $_SESSION['ultimo_pago'] = [
        'pago_id' => 999,
        'factura_id' => 1,
        'numero_factura' => 'FAC-PRINT-TEST-' . date('Ymd-His'),
        'paciente_nombre' => 'Paciente Test Impresión',
        'paciente_cedula' => '87654321',
        'medico_nombre' => 'Dr. Test',
        'monto' => 125.50,
        'total_factura' => 125.50,
        'metodo_pago' => 'tarjeta_credito',
        'fecha_pago' => date('Y-m-d H:i:s')
    ];
    
    echo "<div style='background: #cce5ff; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
    echo "✅ <strong>Datos de prueba configurados</strong><br>";
    echo "Factura: FAC-PRINT-TEST<br>";
    echo "Paciente: Paciente Test Impresión<br>";
    echo "Monto: $125.50<br>";
    echo "Método: Tarjeta de Crédito<br>";
    echo "</div>";
    
    echo "<div style='margin: 20px 0;'>";
    echo "<p><strong>Paso 1:</strong> <a href='facturacion.php' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📋 Abrir Facturación (nueva ventana)</a></p>";
    echo "<p><em>El modal debe aparecer automáticamente en la nueva ventana</em></p>";
    
    echo "<p><strong>Paso 2:</strong> <a href='imprimir_recibo.php' target='_blank' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🖨️ Probar Recibo Directo</a></p>";
    echo "<p><em>Debe abrir el recibo sin problemas</em></p>";
    echo "</div>";
    
} else {
    echo "<div style='background: #fff3cd; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
    echo "<h3>🔧 Test de Impresión</h3>";
    echo "<p>Esta prueba verifica que:</p>";
    echo "<ul>";
    echo "<li>El modal aparece correctamente después del pago</li>";
    echo "<li>El botón 'Imprimir Recibo' abre la ventana sin desloguear</li>";
    echo "<li>La sesión del usuario se mantiene activa</li>";
    echo "<li>El recibo se muestra con todos los datos</li>";
    echo "</ul>";
    
    echo "<form method='POST'>";
    echo "<button type='submit' name='test_print' style='background: #28a745; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;'>🚀 Iniciar Test de Impresión</button>";
    echo "</form>";
    echo "</div>";
}

echo "<div style='background: #e2e3e5; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
echo "<h3>📊 Estado Actual de la Sesión:</h3>";
echo "<table style='width: 100%; border-collapse: collapse; font-family: monospace;'>";
echo "<tr><td style='padding: 5px; border: 1px solid #ddd; font-weight: bold;'>loggedin:</td><td style='padding: 5px; border: 1px solid #ddd;'>" . ($_SESSION['loggedin'] ?? 'false') . "</td></tr>";
echo "<tr><td style='padding: 5px; border: 1px solid #ddd; font-weight: bold;'>show_print_modal:</td><td style='padding: 5px; border: 1px solid #ddd;'>" . (isset($_SESSION['show_print_modal']) ? ($_SESSION['show_print_modal'] ? 'true' : 'false') : 'no existe') . "</td></tr>";
echo "<tr><td style='padding: 5px; border: 1px solid #ddd; font-weight: bold;'>ultimo_pago:</td><td style='padding: 5px; border: 1px solid #ddd;'>" . (isset($_SESSION['ultimo_pago']) ? 'existe' : 'no existe') . "</td></tr>";
echo "</table>";
echo "</div>";

echo "<div style='margin: 20px 0;'>";
echo "<p><a href='facturacion.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔙 Volver a Facturación</a></p>";
echo "<p><a href='clear_ultimo_pago.php' onclick='location.reload(); return false;' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧹 Limpiar Variables de Modal</a></p>";
echo "</div>";
?>
