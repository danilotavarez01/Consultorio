<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';

// Establecer header para JSON
header('Content-Type: application/json');

try {
    // Buscar el último pago
    $stmt = $conn->query('SELECT id, monto, fecha_pago, paciente_id FROM pagos ORDER BY id DESC LIMIT 1');
    $pago = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($pago) {
        // También buscar información del paciente
        $stmt_paciente = $conn->prepare('SELECT nombre, apellido FROM pacientes WHERE id = ?');
        $stmt_paciente->execute([$pago['paciente_id']]);
        $paciente = $stmt_paciente->fetch(PDO::FETCH_ASSOC);
        
        $response = [
            'success' => true,
            'pago_id' => $pago['id'],
            'monto' => $pago['monto'],
            'fecha_pago' => $pago['fecha_pago'],
            'paciente_nombre' => ($paciente ? $paciente['nombre'] . ' ' . $paciente['apellido'] : 'Desconocido'),
            'message' => 'Último pago encontrado'
        ];
        
        // Actualizar sesión con los datos del último pago
        $_SESSION['ultimo_pago'] = [
            'pago_id' => $pago['id'],
            'monto' => $pago['monto'],
            'fecha_pago' => $pago['fecha_pago'],
            'paciente_nombre' => ($paciente ? $paciente['nombre'] . ' ' . $paciente['apellido'] : 'Desconocido')
        ];
        
        echo json_encode($response);
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
