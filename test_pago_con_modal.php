<?php
session_start();
require_once 'config.php';

echo "=== SIMULANDO PAGO PARA PROBAR MODAL ===\n\n";

try {
    // Buscar una factura pendiente
    $stmt = $conn->query("SELECT * FROM facturas WHERE estado = 'pendiente' ORDER BY id DESC LIMIT 1");
    $factura = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$factura) {
        echo "❌ No hay facturas pendientes. Creando una...\n";
        
        // Crear factura de prueba
        $conn->beginTransaction();
        
        $stmt = $conn->query("SELECT id FROM pacientes LIMIT 1");
        $paciente_id = $stmt->fetchColumn();
        
        $stmt = $conn->query("SELECT id FROM usuarios LIMIT 1");
        $medico_id = $stmt->fetchColumn();
        
        if (!$paciente_id || !$medico_id) {
            throw new Exception("No hay pacientes o médicos en el sistema");
        }
        
        // Generar número de factura
        $stmt = $conn->query("SELECT numero_factura FROM facturas ORDER BY id DESC LIMIT 1");
        $ultimo_numero = $stmt->fetchColumn();
        $numero = $ultimo_numero ? intval(substr($ultimo_numero, 4)) + 1 : 1;
        $numero_factura = 'FAC-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
        
        $stmt = $conn->prepare("
            INSERT INTO facturas (numero_factura, paciente_id, medico_id, fecha_factura, fecha_vencimiento, 
                                 subtotal, descuento, total, observaciones, estado) 
            VALUES (?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), ?, ?, ?, ?, 'pendiente')
        ");
        $total = 80.00;
        $stmt->execute([$numero_factura, $paciente_id, $medico_id, $total, 0, $total, 'Consulta de prueba']);
        
        $factura_id = $conn->lastInsertId();
        
        $stmt = $conn->prepare("
            INSERT INTO factura_detalles (factura_id, descripcion, cantidad, precio_unitario, subtotal) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$factura_id, 'Consulta general', 1, $total, $total]);
        
        $conn->commit();
        
        $factura = [
            'id' => $factura_id,
            'numero_factura' => $numero_factura,
            'total' => $total
        ];
        
        echo "✅ Factura creada: $numero_factura\n";
    } else {
        echo "✅ Usando factura existente: {$factura['numero_factura']}\n";
    }
    
    // Simular el registro de pago (como si fuera POST)
    $conn->beginTransaction();
    
    $stmt = $conn->prepare("
        INSERT INTO pagos (factura_id, fecha_pago, monto, metodo_pago, observaciones) 
        VALUES (?, NOW(), ?, ?, ?)
    ");
    $stmt->execute([$factura['id'], $factura['total'], 'efectivo', 'Pago de prueba - Test modal']);
    
    $pago_id = $conn->lastInsertId();
    
    // Actualizar estado de factura
    $stmt = $conn->prepare("UPDATE facturas SET estado = 'pagada' WHERE id = ?");
    $stmt->execute([$factura['id']]);
    
    $conn->commit();
    
    // Configurar sesión como lo haría el sistema real
    $_SESSION['ultimo_pago'] = [
        'pago_id' => $pago_id,
        'factura_id' => $factura['id'],
        'numero_factura' => $factura['numero_factura'],
        'monto' => $factura['total'],
        'metodo_pago' => 'efectivo',
        'paciente_nombre' => 'Paciente de Prueba',
        'paciente_cedula' => '12345678',
        'medico_nombre' => 'Dr. Prueba',
        'fecha_factura' => date('Y-m-d'),
        'total_factura' => $factura['total']
    ];
    
    $_SESSION['show_print_modal'] = true;
    $_SESSION['success_message'] = 'Pago registrado exitosamente.';
    
    echo "✅ Pago simulado registrado (ID: $pago_id)\n";
    echo "✅ Datos de sesión configurados para modal\n\n";
    
    echo "AHORA:\n";
    echo "1. Vaya a: http://localhost/Consultorio2/facturacion.php\n";
    echo "2. Debería ver:\n";
    echo "   - Una alerta verde con el pago exitoso\n";
    echo "   - El botón 'Imprimir Recibo Ahora'\n";
    echo "   - El modal debería aparecer automáticamente después de 0.8 segundos\n\n";
    
    echo "3. En el modal, haga clic en 'Sí, Imprimir Recibo' para abrir la ventana térmica\n\n";
    
    echo "¡El sistema ahora debería funcionar correctamente!\n";

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
