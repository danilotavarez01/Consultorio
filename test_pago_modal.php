<?php
session_start();
require_once 'config.php';

echo "=== PROBANDO SISTEMA DE PAGO Y MODAL ===\n\n";

try {
    // Simular datos de pago en sesión
    $_SESSION['ultimo_pago'] = [
        'pago_id' => 999,
        'factura_id' => 1,
        'numero_factura' => 'FAC-TEST',
        'monto' => 100.00,
        'metodo_pago' => 'efectivo',
        'paciente_nombre' => 'Paciente de Prueba',
        'paciente_cedula' => '12345678',
        'medico_nombre' => 'Dr. Prueba',
        'fecha_factura' => date('Y-m-d'),
        'total_factura' => 100.00
    ];
    
    $_SESSION['show_print_modal'] = true;
    $_SESSION['success_message'] = 'Pago de prueba registrado exitosamente.';
    
    echo "✓ Datos de prueba configurados en sesión\n";
    echo "✓ Flag para mostrar modal activado\n";
    echo "✓ Mensaje de éxito configurado\n\n";
    
    echo "Ahora vaya a: http://localhost/Consultorio2/facturacion.php\n";
    echo "Debería ver:\n";
    echo "1. Un mensaje de éxito verde\n";
    echo "2. El modal de impresión debería aparecer automáticamente\n";
    echo "3. Al hacer clic en 'Imprimir Recibo' debería abrir una nueva ventana con el recibo\n\n";
    
    echo "Si el modal no aparece, revise la consola del navegador (F12)\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
