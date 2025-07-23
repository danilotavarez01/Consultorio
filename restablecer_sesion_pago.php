<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';

// Verificar autenticación
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit();
}

header('Content-Type: application/json');

try {
    // Buscar el último pago disponible
    $stmt = $conn->query("
        SELECT p.id, p.monto, p.fecha_pago, p.paciente_id, p.factura_id, p.metodo_pago,
               CONCAT(pac.nombre, ' ', pac.apellido) as paciente_nombre,
               pac.dni as paciente_cedula,
               f.numero_factura, f.fecha_factura, f.total as total_factura,
               u.nombre as medico_nombre
        FROM pagos p 
        LEFT JOIN pacientes pac ON p.paciente_id = pac.id 
        LEFT JOIN facturas f ON p.factura_id = f.id
        LEFT JOIN usuarios u ON f.medico_id = u.id
        ORDER BY p.id DESC 
        LIMIT 1
    ");
    
    $pago = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($pago) {
        // Restablecer datos completos en sesión
        $_SESSION['ultimo_pago'] = [
            'pago_id' => $pago['id'],
            'factura_id' => $pago['factura_id'],
            'numero_factura' => $pago['numero_factura'] ?? '',
            'monto' => $pago['monto'],
            'metodo_pago' => $pago['metodo_pago'] ?? 'efectivo',
            'paciente_nombre' => $pago['paciente_nombre'] ?? 'Paciente',
            'paciente_cedula' => $pago['paciente_cedula'] ?? '',
            'medico_nombre' => $pago['medico_nombre'] ?? 'Médico',
            'fecha_factura' => $pago['fecha_factura'] ?? $pago['fecha_pago'],
            'total_factura' => $pago['total_factura'] ?? $pago['monto']
        ];
        
        // También establecer el flag del modal
        $_SESSION['show_print_modal'] = true;
        
        echo json_encode([
            'success' => true,
            'message' => 'Datos de pago restablecidos en sesión',
            'pago_id' => $pago['id'],
            'datos' => $_SESSION['ultimo_pago']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No hay pagos en la base de datos',
            'pago_id' => null
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al consultar la base de datos: ' . $e->getMessage(),
        'pago_id' => null
    ]);
}
?>
