<?php
session_start();
require_once 'config.php';

echo "=== SIMULACIÓN DE PAGO PARA PROBAR RECIBO ===\n\n";

try {
    // Simular datos de un pago reciente
    $_SESSION['ultimo_pago'] = [
        'pago_id' => 1,
        'factura_id' => 1,
        'numero_factura' => 'FAC-0001',
        'monto' => 150.00,
        'metodo_pago' => 'efectivo',
        'paciente_nombre' => 'Juan Pérez García',
        'paciente_cedula' => '001-1234567-8',
        'medico_nombre' => 'Dr. María González',
        'fecha_factura' => '2025-01-18',
        'total_factura' => 200.00
    ];

    echo "✅ Datos de pago simulados guardados en sesión\n";
    echo "\nPara probar la funcionalidad:\n";
    echo "1. Abra: http://localhost/Consultorio2/facturacion.php\n";
    echo "2. O directamente: http://localhost/Consultorio2/imprimir_recibo.php\n";
    echo "\nDatos del pago simulado:\n";
    echo "- Factura: {$_SESSION['ultimo_pago']['numero_factura']}\n";
    echo "- Paciente: {$_SESSION['ultimo_pago']['paciente_nombre']}\n";
    echo "- Monto: \${$_SESSION['ultimo_pago']['monto']}\n";
    echo "- Método: {$_SESSION['ultimo_pago']['metodo_pago']}\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
