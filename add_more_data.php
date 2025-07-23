<?php
require_once 'config.php';

echo "Agregando datos adicionales para reportes más completos...\n";

try {
    // Crear una nueva factura
    $stmt = $conn->prepare("
        INSERT INTO facturas (numero_factura, paciente_id, medico_id, fecha_factura, fecha_vencimiento, 
                             subtotal, descuento, total, observaciones, estado) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute(['FAC-002', 2, 1, '2025-07-15', '2025-08-15', 75.00, 0, 75.00, 'Consulta de rutina', 'pendiente']);
    $factura_id = $conn->lastInsertId();
    echo "✓ Factura FAC-002 creada para María García\n";
    
    // Agregar detalles a la nueva factura
    $stmt_detalle = $conn->prepare("
        INSERT INTO factura_detalles (factura_id, procedimiento_id, descripcion, cantidad, precio_unitario, descuento_item, subtotal) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt_detalle->execute([$factura_id, 3, 'Extracción dental', 1, 75.00, 0, 75.00]);
    echo "✓ Detalle agregado: Extracción dental\n";
    
    // Agregar algunos pagos de ejemplo
    $stmt_pago = $conn->prepare("
        INSERT INTO pagos (factura_id, fecha_pago, monto, metodo_pago, numero_referencia, observaciones) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    // Pago para FAC-001
    $stmt_pago->execute([1, '2025-07-16', 100.00, 'transferencia', 'TRF-123456', 'Pago parcial']);
    echo "✓ Pago de $100 agregado para FAC-001 (transferencia)\n";
    
    // Pago para FAC-002
    $stmt_pago->execute([$factura_id, '2025-07-17', 75.00, 'efectivo', '', 'Pago completo']);
    echo "✓ Pago de $75 agregado para FAC-002 (efectivo)\n";
    
    // Actualizar estado de FAC-002 a pagada
    $stmt = $conn->prepare("UPDATE facturas SET estado = 'pagada' WHERE id = ?");
    $stmt->execute([$factura_id]);
    echo "✓ FAC-002 marcada como pagada\n";
    
    echo "\n✅ Datos adicionales agregados exitosamente!\n";
    echo "📊 Ahora los reportes tendrán más variedad de datos\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
