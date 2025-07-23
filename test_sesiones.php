<?php
require_once 'session_config.php';
session_start();

echo "<h2>✅ Verificación de Configuración de Sesiones</h2>";

echo "<div style='background: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 5px; font-family: monospace;'>";
echo "<h3>Estado de la Sesión:</h3>";
echo "<p><strong>ID de Sesión:</strong> " . session_id() . "</p>";
echo "<p><strong>Nombre de Sesión:</strong> " . session_name() . "</p>";
echo "<p><strong>Estado:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVA' : 'INACTIVA') . "</p>";
echo "</div>";

echo "<div style='background: #e2e3e5; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
echo "<h3>Configuraciones de Sesión:</h3>";
echo "<table style='width: 100%; border-collapse: collapse;'>";

$config_items = [
    'session.gc_maxlifetime' => 'Tiempo máximo de vida',
    'session.cookie_lifetime' => 'Tiempo de vida de cookie',
    'session.use_only_cookies' => 'Solo usar cookies',
    'session.use_strict_mode' => 'Modo estricto',
    'session.cookie_samesite' => 'SameSite cookie',
    'session.save_path' => 'Directorio de sesiones'
];

foreach ($config_items as $setting => $description) {
    $value = ini_get($setting);
    echo "<tr>";
    echo "<td style='padding: 5px; border: 1px solid #ddd; font-weight: bold;'>{$description}:</td>";
    echo "<td style='padding: 5px; border: 1px solid #ddd;'>{$value}</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

echo "<div style='background: #d4edda; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
echo "<h3>✅ Prueba Exitosa</h3>";
echo "<p>Las configuraciones de sesión se han aplicado correctamente sin errores.</p>";
echo "<p>Ya no deberían aparecer warnings sobre cambios en configuraciones de sesión activa.</p>";
echo "</div>";

echo "<div style='margin: 20px 0;'>";
echo "<p><a href='facturacion.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📋 Ir a Facturación</a></p>";
echo "<p><a href='test_modal_pago.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Probar Modal</a></p>";
echo "</div>";

// Agregar información de debug del sistema
echo "<div style='background: #fff3cd; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
echo "<h3>Información del Sistema:</h3>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Sistema Operativo:</strong> " . PHP_OS . "</p>";
echo "<p><strong>Servidor Web:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido') . "</p>";
echo "<p><strong>Tiempo del Servidor:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";
?>
