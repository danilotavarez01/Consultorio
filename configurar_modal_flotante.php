<?php
session_start();
require_once 'config.php';

echo "=== CONFIGURANDO MODAL FLOTANTE DE PAGO ===\n\n";

// Configurar datos de prueba para el modal flotante
$_SESSION['ultimo_pago'] = [
    'pago_id' => 999,
    'factura_id' => 1,
    'numero_factura' => 'FAC-0001',
    'monto' => 125.50,
    'metodo_pago' => 'tarjeta_credito',
    'paciente_nombre' => 'María González',
    'paciente_cedula' => '87654321',
    'medico_nombre' => 'Dr. López',
    'fecha_factura' => date('Y-m-d'),
    'total_factura' => 125.50
];

$_SESSION['show_print_modal'] = true;
$_SESSION['success_message'] = 'Pago registrado exitosamente.';

echo "✅ Datos configurados para modal flotante:\n";
echo "   - Factura: FAC-0001\n";
echo "   - Paciente: María González\n";
echo "   - Monto: $125.50\n";
echo "   - Método: Tarjeta de Crédito\n\n";

echo "CARACTERÍSTICAS DEL NUEVO MODAL:\n";
echo "✅ Aparece como ventana flotante centrada\n";
echo "✅ Fondo semi-transparente (backdrop)\n";
echo "✅ No se puede cerrar con ESC o clic fuera\n";
echo "✅ Diseño atractivo con iconos y colores\n";
echo "✅ Botones grandes y fáciles de usar\n";
echo "✅ Información del pago bien organizada\n\n";

echo "PRUEBE AHORA:\n";
echo "1. Vaya a: http://localhost/Consultorio2/facturacion.php\n";
echo "2. El modal flotante debería aparecer automáticamente en 0.5 segundos\n";
echo "3. Observe el nuevo diseño mejorado del modal\n";
echo "4. Pruebe hacer clic en 'Sí, Imprimir Recibo' para abrir la ventana térmica\n\n";

echo "El modal ahora es más prominente y profesional!\n";
?>
