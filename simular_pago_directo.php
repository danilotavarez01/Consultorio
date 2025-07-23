<?php
session_start();

// Configurar datos de pago completos para la prueba
$_SESSION['show_print_modal'] = true;
$_SESSION['ultimo_pago'] = [
    'pago_id' => 999,
    'factura_id' => 1,
    'numero_factura' => 'FAC-0001',
    'paciente_nombre' => 'Juan Pérez',
    'paciente_cedula' => '12345678',
    'medico_nombre' => 'Dr. Smith',
    'monto' => 150.00,
    'total_factura' => 150.00,
    'metodo_pago' => 'efectivo',
    'fecha_pago' => date('Y-m-d H:i:s')
];

// Redirigir automáticamente a facturación
header('Location: facturacion.php');
exit();
?>
