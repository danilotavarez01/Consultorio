<?php
session_start();
require_once 'config.php';

echo "=== SIMULANDO PROCESO COMPLETO DE PAGO ===\n\n";

try {
    // Limpiar cualquier sesión anterior
    unset($_SESSION['ultimo_pago']);
    
    // Buscar una factura pendiente o crear datos de prueba
    $stmt = $conn->query("SELECT * FROM facturas WHERE estado = 'pendiente' LIMIT 1");
    $factura = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$factura) {
        echo "No hay facturas pendientes, usando última factura...\n";
        $stmt = $conn->query("SELECT * FROM facturas ORDER BY id DESC LIMIT 1");
        $factura = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    if (!$factura) {
        echo "❌ No hay facturas en el sistema\n";
        exit;
    }
    
    echo "✓ Usando factura: {$factura['numero_factura']} (Total: \${$factura['total']})\n";
    
    // Simular POST de pago como lo haría el formulario
    $_POST = [
        'action' => 'add_pago',
        'factura_id' => $factura['id'],
        'monto' => '25.00',
        'metodo_pago' => 'efectivo',
        'numero_referencia' => 'REF-' . time(),
        'observaciones_pago' => 'Pago de prueba para modal'
    ];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    echo "✓ Datos de pago simulados:\n";
    echo "  - Factura ID: {$_POST['factura_id']}\n";
    echo "  - Monto: \${$_POST['monto']}\n";
    echo "  - Método: {$_POST['metodo_pago']}\n";
    
    // Ejecutar la lógica de pago (extraída de facturacion.php)
    $factura_id = intval($_POST['factura_id']);
    $monto = floatval($_POST['monto']);
    $metodo_pago = $_POST['metodo_pago'];
    $numero_referencia = trim($_POST['numero_referencia'] ?? '');
    $observaciones_pago = trim($_POST['observaciones_pago'] ?? '');
    
    $conn->beginTransaction();
    
    // Insertar pago
    $stmt = $conn->prepare("
        INSERT INTO pagos (factura_id, fecha_pago, monto, metodo_pago, numero_referencia, observaciones) 
        VALUES (?, CURDATE(), ?, ?, ?, ?)
    ");
    $stmt->execute([$factura_id, $monto, $metodo_pago, $numero_referencia, $observaciones_pago]);
    
    $pago_id = $conn->lastInsertId();
    echo "✓ Pago insertado con ID: $pago_id\n";
    
    // Verificar estado de factura (lógica simplificada)
    $stmt = $conn->prepare("
        SELECT f.total, COALESCE(SUM(p.monto), 0) as total_pagado 
        FROM facturas f 
        LEFT JOIN pagos p ON f.id = p.factura_id 
        WHERE f.id = ? 
        GROUP BY f.id
    ");
    $stmt->execute([$factura_id]);
    $factura_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($factura_info && $factura_info['total_pagado'] >= $factura_info['total']) {
        $stmt = $conn->prepare("UPDATE facturas SET estado = 'pagada', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$factura_id]);
        echo "✓ Factura marcada como pagada\n";
    }
    
    $conn->commit();
    echo "✓ Transacción completada\n";
    
    // Establecer datos en sesión para el modal
    $_SESSION['ultimo_pago'] = [
        'pago_id' => $pago_id,
        'factura_id' => $factura_id,
        'numero_factura' => '',
        'monto' => $monto,
        'metodo_pago' => $metodo_pago
    ];
    
    // Obtener información completa de la factura
    $stmt = $conn->prepare("
        SELECT f.numero_factura, f.fecha_factura, f.total,
               CONCAT(p.nombre, ' ', p.apellido) as paciente_nombre,
               p.dni as paciente_cedula,
               u.nombre as medico_nombre
        FROM facturas f
        LEFT JOIN pacientes p ON f.paciente_id = p.id
        LEFT JOIN usuarios u ON f.medico_id = u.id
        WHERE f.id = ?
    ");
    $stmt->execute([$factura_id]);
    $factura_completa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($factura_completa) {
        $_SESSION['ultimo_pago']['numero_factura'] = $factura_completa['numero_factura'] ?? 'N/A';
        $_SESSION['ultimo_pago']['paciente_nombre'] = $factura_completa['paciente_nombre'] ?? 'Paciente';
        $_SESSION['ultimo_pago']['paciente_cedula'] = $factura_completa['paciente_cedula'] ?? '';
        $_SESSION['ultimo_pago']['medico_nombre'] = $factura_completa['medico_nombre'] ?? 'Médico';
        $_SESSION['ultimo_pago']['fecha_factura'] = $factura_completa['fecha_factura'] ?? date('Y-m-d');
        $_SESSION['ultimo_pago']['total_factura'] = $factura_completa['total'] ?? 0;
        echo "✓ Datos del recibo establecidos en sesión\n";
    } else {
        echo "⚠️  No se pudieron obtener datos completos de la factura\n";
    }
    
    echo "\n📋 Datos en sesión para el modal:\n";
    foreach ($_SESSION['ultimo_pago'] as $key => $value) {
        echo "  - $key: $value\n";
    }
    
    echo "\n🚀 PROCESO COMPLETADO\n";
    echo "🔗 Abra: http://localhost/Consultorio2/facturacion.php\n";
    echo "📱 El modal de impresión DEBE aparecer automáticamente\n";
    echo "🖨️  O pruebe directamente: http://localhost/Consultorio2/imprimir_recibo.php\n";

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
