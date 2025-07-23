<?php
// Test mínimo sin configuración de sesión compleja
session_start();
require_once 'config.php';

// Solo verificar lo básico
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "<script>alert('No logueado'); window.close();</script>";
    exit();
}

// Crear datos de prueba simples
$_SESSION['ultimo_pago'] = [
    'pago_id' => 999,
    'numero_factura' => 'FAC-SIMPLE-' . date('His'),
    'monto' => 100.00,
    'metodo_pago' => 'efectivo',
    'paciente_nombre' => 'Paciente Simple Test',
    'paciente_cedula' => '12345678',
    'medico_nombre' => 'Dr. Simple Test'
];

echo "<!DOCTYPE html><html><head><title>Test Simple</title></head><body>";
echo "<h2>Test Simple de Sesión y Recibo</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Usuario: " . htmlspecialchars($_SESSION['username'] ?? 'N/A') . "</p>";
echo "<p>Datos creados: SÍ</p>";
echo "<hr>";
echo "<button onclick=\"window.open('imprimir_recibo_simple.php', '_blank', 'width=600,height=400')\">Abrir Recibo Simple</button>";
echo "<p><a href='test_directo_recibo.php'>Test Directo</a> | <a href='facturacion.php'>Facturación</a></p>";
echo "</body></html>";
?>
