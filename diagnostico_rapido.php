<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "❌ Usuario no autenticado. <a href='index.php'>Login</a>";
    exit();
}

echo "<!DOCTYPE html>";
echo "<html><head><title>Diagnóstico Rápido</title></head><body>";
echo "<h2>🩺 Diagnóstico Rápido de Impresión</h2>";

// 1. Estado de sesión
echo "<h3>📋 Estado de Sesión</h3>";
echo "<p><strong>Usuario logueado:</strong> " . ($_SESSION['loggedin'] ? '✅ SÍ' : '❌ NO') . "</p>";
echo "<p><strong>Datos de último pago:</strong> " . (isset($_SESSION['ultimo_pago']) ? '✅ SÍ' : '❌ NO') . "</p>";

if (isset($_SESSION['ultimo_pago'])) {
    echo "<pre>Datos: " . print_r($_SESSION['ultimo_pago'], true) . "</pre>";
}

// 2. Verificar BD
echo "<h3>🗄️ Base de Datos</h3>";
try {
    $stmt = $conn->query("SELECT COUNT(*) as total FROM pagos");
    $total = $stmt->fetchColumn();
    echo "<p><strong>Total pagos:</strong> $total</p>";
    
    if ($total > 0) {
        $stmt = $conn->query("SELECT id, monto, fecha_pago FROM pagos ORDER BY id DESC LIMIT 1");
        $ultimo = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p><strong>Último pago:</strong> ID {$ultimo['id']}, \${$ultimo['monto']}, {$ultimo['fecha_pago']}</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

// 3. Acciones rápidas
echo "<h3>⚡ Acciones Rápidas</h3>";
echo "<p>";
echo "<a href='restablecer_sesion_pago.php' style='color: blue;'>🔄 Restaurar último pago</a> | ";
echo "<a href='crear_pago_prueba.php' style='color: green;'>💰 Crear pago de prueba</a> | ";
echo "<a href='test_impresion_integral.php' style='color: orange;'>🧪 Test integral</a> | ";
echo "<a href='facturacion.php' style='color: purple;'>🧾 Facturación</a>";
echo "</p>";

echo "</body></html>";
?>
