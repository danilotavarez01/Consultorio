<?php
session_start();
require_once 'config.php';

echo "<h2>🧪 Prueba: Modal Después de Pago</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simular_pago'])) {
    // Simular un pago exitoso con todos los datos necesarios
    $_SESSION['show_print_modal'] = true;
    $_SESSION['ultimo_pago'] = [
        'pago_id' => 999, // ID ficticio para prueba
        'factura_id' => 1,
        'numero_factura' => 'FAC-TEST',
        'paciente_nombre' => 'Paciente Prueba',
        'monto' => '75.00',
        'metodo_pago' => 'efectivo',
        'fecha_pago' => date('Y-m-d H:i:s'),
        'paciente_cedula' => '12345678',
        'medico_nombre' => 'Dr. Prueba'
    ];
    
    echo "<div style='background: #d4edda; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
    echo "✅ <strong>Pago simulado exitosamente</strong><br>";
    echo "Datos configurados: FAC-TEST, Paciente Prueba, $75.00, Efectivo<br>";
    echo "Ahora ve a <a href='facturacion.php'>Facturación</a> - el modal DEBE aparecer automáticamente.";
    echo "</div>";
} else {
    echo "<p>Esta prueba simula el registro de un pago para verificar que el modal aparece correctamente.</p>";
    echo "<form method='POST'>";
    echo "<button type='submit' name='simular_pago' class='btn btn-success'>💰 Simular Pago</button>";
    echo "</form>";
    
    echo "<hr>";
    echo "<p><a href='facturacion.php' class='btn btn-primary'>📋 Ir a Facturación (sin modal)</a></p>";
    echo "<p><a href='clear_ultimo_pago.php' class='btn btn-secondary'>🧹 Limpiar Sesión</a></p>";
}

echo "<div style='background: #fff3cd; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
echo "<h4>⚠️ Comportamiento Esperado:</h4>";
echo "<ul>";
echo "<li><strong>Antes del pago:</strong> Facturación NO debe mostrar modal</li>";
echo "<li><strong>Después del pago:</strong> Facturación DEBE mostrar modal automáticamente</li>";
echo "<li><strong>Cerrar modal:</strong> Sesión se limpia y no vuelve a aparecer</li>";
echo "</ul>";
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
    border: none;
    cursor: pointer;
}
.btn-success { background: #28a745; }
.btn-secondary { background: #6c757d; }
</style>
