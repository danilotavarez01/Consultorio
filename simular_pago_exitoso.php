<?php
require_once 'config.php';
require_once 'session_config.php';
session_start();

// Simular login para pruebas
$_SESSION['loggedin'] = true;
$_SESSION['username'] = 'admin';

// Simular la creación de un pago exitoso
$_SESSION['ultimo_pago'] = [
    'pago_id' => 'TEST-' . time(),
    'numero_factura' => 'F-TEST-' . date('Ymd-His'),
    'paciente_nombre' => 'Juan Pérez Test',
    'paciente_cedula' => '12345678',
    'monto' => '250.00',
    'metodo_pago' => 'efectivo',
    'fecha_pago_formato' => date('d/m/Y H:i'),
    'medico_nombre' => 'Dr. Test',
    'total_factura' => '250.00'
];

$_SESSION['ultimo_pago_timestamp'] = time();
$_SESSION['show_print_modal'] = true;
$_SESSION['success_message'] = "Pago de prueba registrado exitosamente.";

// Redirigir a facturación para simular el flujo completo
header("Location: facturacion.php");
exit();
?>
