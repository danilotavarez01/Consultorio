<?php
session_start();
require_once 'config.php';

echo "<h2>✅ Verificación Final: Modal NO Fijo</h2>";

// Limpiar completamente la sesión
if (isset($_SESSION['show_print_modal'])) {
    unset($_SESSION['show_print_modal']);
    echo "<p>🧹 Limpiado: show_print_modal</p>";
}

if (isset($_SESSION['ultimo_pago'])) {
    unset($_SESSION['ultimo_pago']);
    echo "<p>🧹 Limpiado: ultimo_pago</p>";
}

// Verificar estado actual
echo "<h3>Estado Actual de Sesión:</h3>";
echo "<ul>";
echo "<li>show_print_modal: " . (isset($_SESSION['show_print_modal']) ? '❌ PRESENTE' : '✅ NO PRESENTE') . "</li>";
echo "<li>ultimo_pago: " . (isset($_SESSION['ultimo_pago']) ? '❌ PRESENTE' : '✅ NO PRESENTE') . "</li>";
echo "</ul>";

echo "<h3>🔗 Enlaces de Prueba:</h3>";
echo "<p><a href='facturacion.php' class='btn btn-primary'>📋 Facturación (SIN modal fijo)</a></p>";
echo "<p><a href='test_pago_completo.php' class='btn btn-success'>💰 Simular Pago (CON modal flotante)</a></p>";
echo "<p><a href='test_modal_comportamiento.html' class='btn btn-info'>🔧 Test Técnico del Modal</a></p>";

echo "<div class='alert alert-success mt-4'>";
echo "<h4>✅ Estado Esperado:</h4>";
echo "<ul>";
echo "<li><strong>Facturación normal:</strong> Modal OCULTO, no aparece automáticamente</li>";
echo "<li><strong>Después de pago:</strong> Modal FLOTANTE aparece automáticamente</li>";
echo "<li><strong>Cerrar modal:</strong> Sesión se limpia automáticamente</li>";
echo "</ul>";
echo "</div>";

echo "<div class='alert alert-warning'>";
echo "<h4>⚠️ Si el modal aparece fijo:</h4>";
echo "<ol>";
echo "<li>Refrescar esta página para limpiar sesión</li>";
echo "<li>Ir a facturación.php (debe estar limpio)</li>";
echo "<li>Si persiste, revisar código JavaScript</li>";
echo "</ol>";
echo "</div>";
?>

<style>
.btn { 
    display: inline-block; 
    padding: 10px 20px; 
    margin: 5px; 
    text-decoration: none; 
    background: #007bff; 
    color: white; 
    border-radius: 5px; 
}
.btn-success { background: #28a745; }
.btn-info { background: #17a2b8; }
.alert { 
    padding: 15px; 
    margin: 15px 0; 
    border-radius: 5px; 
}
.alert-success { background: #d4edda; border: 1px solid #c3e6cb; }
.alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; }
</style>
