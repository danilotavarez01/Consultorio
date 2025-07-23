<?php
// Versión simplificada de imprimir_recibo.php para debug
session_start();

echo "<!DOCTYPE html><html><head><title>Recibo Simple</title></head><body>";
echo "<h2>🧪 Recibo Simple - Debug</h2>";

// Debug básico
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Headers sent:</strong> " . (headers_sent() ? 'SÍ' : 'NO') . "</p>";

// Verificar login
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "<p style='color: red;'>❌ Usuario no logueado</p>";
    echo "<p>Datos de sesión disponibles:</p>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
    echo "</body></html>";
    exit();
}

echo "<p style='color: green;'>✅ Usuario logueado: " . htmlspecialchars($_SESSION['username'] ?? 'N/A') . "</p>";

// Verificar datos de pago
if (!isset($_SESSION['ultimo_pago'])) {
    echo "<p style='color: red;'>❌ No hay datos de último pago</p>";
    echo "<p>Variables de sesión disponibles:</p>";
    echo "<pre>";
    foreach ($_SESSION as $key => $value) {
        echo "$key => " . (is_array($value) ? 'Array' : $value) . "\n";
    }
    echo "</pre>";
} else {
    echo "<p style='color: green;'>✅ Datos de pago encontrados</p>";
    
    $pago = $_SESSION['ultimo_pago'];
    
    echo "<div style='border: 1px solid #ccc; padding: 15px; margin: 15px 0; background: #f9f9f9;'>";
    echo "<h3>📄 RECIBO DE PAGO</h3>";
    echo "<p><strong>Factura:</strong> " . htmlspecialchars($pago['numero_factura'] ?? 'N/A') . "</p>";
    echo "<p><strong>Paciente:</strong> " . htmlspecialchars($pago['paciente_nombre'] ?? 'N/A') . "</p>";
    echo "<p><strong>Cédula:</strong> " . htmlspecialchars($pago['paciente_cedula'] ?? 'N/A') . "</p>";
    echo "<p><strong>Médico:</strong> " . htmlspecialchars($pago['medico_nombre'] ?? 'N/A') . "</p>";
    echo "<p><strong>Monto:</strong> $" . number_format(floatval($pago['monto'] ?? 0), 2) . "</p>";
    echo "<p><strong>Método:</strong> " . htmlspecialchars($pago['metodo_pago'] ?? 'N/A') . "</p>";
    echo "<p><strong>Fecha:</strong> " . date('d/m/Y H:i') . "</p>";
    echo "</div>";
    
    echo "<p style='color: green; font-weight: bold;'>✅ RECIBO GENERADO CORRECTAMENTE</p>";
}

echo "<hr>";
echo "<button onclick='window.print()'>🖨️ Imprimir</button> ";
echo "<button onclick='window.close()'>❌ Cerrar</button>";
echo "</body></html>";
?>
