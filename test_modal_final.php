<?php
session_start();
require_once 'config.php';

echo "=== CONFIGURANDO PAGO PARA MODAL FLOTANTE ===\n\n";

// Limpiar cualquier sesión previa
unset($_SESSION['ultimo_pago']);
unset($_SESSION['show_print_modal']);
unset($_SESSION['success_message']);

// Configurar datos del pago como si acabara de registrarse
$_SESSION['ultimo_pago'] = [
    'pago_id' => 999,
    'factura_id' => 1,
    'numero_factura' => 'FAC-0010',
    'monto' => 50.00,
    'metodo_pago' => 'efectivo',
    'paciente_nombre' => 'Ana Martínez',
    'paciente_cedula' => '12345678',
    'medico_nombre' => 'Dr. García',
    'fecha_factura' => date('Y-m-d'),
    'total_factura' => 50.00
];

$_SESSION['success_message'] = "Pago registrado exitosamente.";
$_SESSION['show_print_modal'] = true;

echo "✅ Pago configurado:\n";
echo "   - Factura: FAC-0010\n";
echo "   - Paciente: Ana Martínez\n";
echo "   - Monto: $50.00\n";
echo "   - Método: Efectivo\n\n";

echo "CARACTERÍSTICAS DEL MODAL CORREGIDO:\n";
echo "✅ Oculto por defecto con CSS y JavaScript\n";
echo "✅ Solo aparece cuando hay flag 'show_print_modal'\n";
echo "✅ Se muestra como ventana flotante centrada\n";
echo "✅ Datos llenados dinámicamente\n";
echo "✅ No interfiere con la página normal\n\n";

echo "PRUEBE AHORA:\n";
echo "1. Vaya a: http://localhost/Consultorio2/facturacion.php\n";
echo "2. La página debe cargar normalmente SIN mostrar el modal fijo\n";
echo "3. Después de 1 segundo, el modal debe aparecer FLOTANTE\n";
echo "4. El modal debe mostrar los datos del pago\n";
echo "5. Al hacer clic en 'Sí, Imprimir Recibo' debe abrir la ventana térmica\n\n";

echo "Si ve el modal fijo en la página, hay un problema de CSS.\n";
echo "Si no aparece el modal flotante, revise la consola (F12).\n\n";

echo "¡El modal ahora solo debe aparecer cuando registre un pago!\n";
?>
