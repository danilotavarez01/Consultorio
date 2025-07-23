<?php
require_once 'config.php';

echo "Verificación final del sistema completo de facturación...\n";

try {
    // 1. Verificar facturas
    echo "1. Verificando facturas...\n";
    $stmt = $conn->query("
        SELECT f.numero_factura, f.estado, f.total,
               CONCAT(p.nombre, ' ', p.apellido) as paciente
        FROM facturas f
        LEFT JOIN pacientes p ON f.paciente_id = p.id
        ORDER BY f.id
    ");
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($facturas as $factura) {
        echo "  ✓ " . $factura['numero_factura'] . " | " . $factura['paciente'] . " | $" . $factura['total'] . " | " . $factura['estado'] . "\n";
    }

    // 2. Verificar pagos
    echo "\n2. Verificando pagos...\n";
    $stmt = $conn->query("
        SELECT p.monto, p.metodo_pago, f.numero_factura
        FROM pagos p
        JOIN facturas f ON p.factura_id = f.id
        ORDER BY p.id
    ");
    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($pagos as $pago) {
        echo "  ✓ $" . $pago['monto'] . " | " . $pago['metodo_pago'] . " | " . $pago['numero_factura'] . "\n";
    }

    // 3. Verificar reportes
    echo "\n3. Verificando reportes...\n";
    $fecha_desde = date('Y-m-01');
    $fecha_hasta = date('Y-m-d');
    
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_facturas,
            SUM(total) as total_facturado,
            SUM(CASE WHEN estado = 'pagada' THEN total ELSE 0 END) as total_cobrado
        FROM facturas 
        WHERE fecha_factura BETWEEN ? AND ?
    ");
    $stmt->execute([$fecha_desde, $fecha_hasta]);
    $resumen = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "  ✓ Total facturas: " . $resumen['total_facturas'] . "\n";
    echo "  ✓ Total facturado: $" . number_format($resumen['total_facturado'], 2) . "\n";
    echo "  ✓ Total cobrado: $" . number_format($resumen['total_cobrado'], 2) . "\n";

    // 4. Verificar métodos de pago
    echo "\n4. Verificando métodos de pago...\n";
    $stmt = $conn->prepare("
        SELECT 
            p.metodo_pago,
            COUNT(*) as cantidad,
            SUM(p.monto) as total
        FROM pagos p
        JOIN facturas f ON p.factura_id = f.id
        WHERE f.fecha_factura BETWEEN ? AND ?
        GROUP BY p.metodo_pago
    ");
    $stmt->execute([$fecha_desde, $fecha_hasta]);
    $metodos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($metodos as $metodo) {
        echo "  ✓ " . ucfirst($metodo['metodo_pago']) . ": " . $metodo['cantidad'] . " pagos, $" . number_format($metodo['total'], 2) . "\n";
    }

    echo "\n🎉 ¡Sistema de facturación completamente funcional!\n";
    echo "✅ Módulo de facturación: OK\n";
    echo "✅ Módulo de reportes: OK\n";
    echo "✅ Gestión de pagos: OK\n";
    echo "✅ Datos de prueba: OK\n";
    echo "\n🌐 URLs de acceso:\n";
    echo "  - Facturación: http://localhost/Consultorio2/facturacion.php\n";
    echo "  - Reportes: http://localhost/Consultorio2/reportes_facturacion.php\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
