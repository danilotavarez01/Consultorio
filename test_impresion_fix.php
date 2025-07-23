<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';

echo "<h2>🧪 Prueba: Flujo de Impresión Corregido</h2>";

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 15px 0; border-radius: 5px; color: #721c24;'>";
    echo "❌ <strong>Usuario no logueado</strong><br>";
    echo "<a href='login.php'>Ir al Login</a>";
    echo "</div>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_test'])) {
    // Configurar datos completos para la prueba
    $_SESSION['show_print_modal'] = true;
    $_SESSION['ultimo_pago'] = [
        'pago_id' => 999,
        'factura_id' => 1,
        'numero_factura' => 'FAC-PRINT-FIX',
        'paciente_nombre' => 'Juan Pérez Test',
        'paciente_cedula' => '12345678',
        'medico_nombre' => 'Dr. García',
        'monto' => 175.75,
        'total_factura' => 175.75,
        'metodo_pago' => 'efectivo',
        'fecha_pago' => date('Y-m-d H:i:s')
    ];
    
    echo "<div style='background: #d4edda; padding: 15px; margin: 15px 0; border-radius: 5px; color: #155724;'>";
    echo "✅ <strong>Datos de prueba configurados</strong><br>";
    echo "Factura: FAC-PRINT-FIX<br>";
    echo "Paciente: Juan Pérez Test<br>";
    echo "Monto: $175.75<br>";
    echo "Método: Efectivo<br>";
    echo "</div>";
    
    echo "<div style='background: #cce5ff; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
    echo "<h3>🔄 Flujo de Prueba Corregido:</h3>";
    echo "<ol>";
    echo "<li><strong>Ir a Facturación:</strong> El modal debe aparecer automáticamente</li>";
    echo "<li><strong>Hacer clic en 'Sí, Imprimir Recibo':</strong> Se abre ventana de recibo</li>";
    echo "<li><strong>El recibo se carga:</strong> Con todos los datos visibles</li>";
    echo "<li><strong>Variables se limpian:</strong> Automáticamente después de cargar</li>";
    echo "<li><strong>Modal no vuelve a aparecer:</strong> Cuando se regrese a facturación</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div style='margin: 20px 0;'>";
    echo "<p><a href='facturacion.php' target='_blank' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: bold;'>📋 Ir a Facturación (nueva ventana)</a></p>";
    echo "<p><em>El modal debe aparecer automáticamente en la nueva ventana</em></p>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
    echo "<h3>⚠️ Verificaciones importantes:</h3>";
    echo "<ul>";
    echo "<li>✅ El modal aparece con los datos correctos</li>";
    echo "<li>✅ El recibo se abre sin errores de 'No hay información'</li>";
    echo "<li>✅ Los datos del pago se muestran en el recibo</li>";
    echo "<li>✅ No hay deslogueo del usuario</li>";
    echo "<li>✅ Las variables se limpian automáticamente</li>";
    echo "</ul>";
    echo "</div>";
    
} else {
    echo "<div style='background: #e2e3e5; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
    echo "<h3>🔧 Problema Corregido</h3>";
    echo "<p><strong>Error anterior:</strong> 'No hay información de pago para imprimir'</p>";
    echo "<p><strong>Causa:</strong> Las variables se limpiaban antes de que el recibo pudiera cargar</p>";
    echo "<p><strong>Solución:</strong> Ahora los datos se mantienen hasta que el recibo termine de cargar</p>";
    echo "</div>";
    
    echo "<div style='background: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
    echo "<h3>📊 Estado actual de la sesión:</h3>";
    echo "<table style='width: 100%; border-collapse: collapse; font-family: monospace;'>";
    echo "<tr><td style='padding: 5px; border: 1px solid #ddd; font-weight: bold;'>Usuario logueado:</td><td style='padding: 5px; border: 1px solid #ddd;'>" . ($_SESSION['loggedin'] ? '✅ Sí' : '❌ No') . "</td></tr>";
    echo "<tr><td style='padding: 5px; border: 1px solid #ddd; font-weight: bold;'>show_print_modal:</td><td style='padding: 5px; border: 1px solid #ddd;'>" . (isset($_SESSION['show_print_modal']) ? '✅ Sí' : '❌ No') . "</td></tr>";
    echo "<tr><td style='padding: 5px; border: 1px solid #ddd; font-weight: bold;'>ultimo_pago:</td><td style='padding: 5px; border: 1px solid #ddd;'>" . (isset($_SESSION['ultimo_pago']) ? '✅ Sí' : '❌ No') . "</td></tr>";
    echo "</table>";
    echo "</div>";
    
    echo "<form method='POST'>";
    echo "<button type='submit' name='setup_test' style='background: #28a745; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; font-weight: bold;'>🚀 Configurar Test de Impresión</button>";
    echo "</form>";
}

echo "<div style='margin: 20px 0;'>";
echo "<p><a href='facturacion.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔙 Volver a Facturación</a></p>";
echo "</div>";
?>
