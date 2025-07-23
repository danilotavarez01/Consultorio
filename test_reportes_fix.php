<?php
require_once 'config.php';

echo "Probando consultas de reportes corregidas...\n";

$fecha_desde = date('Y-m-01'); // Primer día del mes actual
$fecha_hasta = date('Y-m-d'); // Fecha actual

echo "Período: $fecha_desde a $fecha_hasta\n\n";

try {
    // 1. Reporte de facturación por período
    echo "1. Probando reporte general...\n";
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_facturas,
            SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as facturas_pendientes,
            SUM(CASE WHEN estado = 'pagada' THEN 1 ELSE 0 END) as facturas_pagadas,
            SUM(total) as total_facturado,
            SUM(CASE WHEN estado = 'pagada' THEN total ELSE 0 END) as total_cobrado
        FROM facturas 
        WHERE fecha_factura BETWEEN ? AND ?
    ");
    $stmt->execute([$fecha_desde, $fecha_hasta]);
    $resumen = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Resumen general obtenido\n";

    // 2. Top procedimientos
    echo "2. Probando top procedimientos...\n";
    $stmt = $conn->prepare("
        SELECT 
            fd.descripcion,
            COUNT(*) as veces_facturado,
            SUM(fd.cantidad) as cantidad_total,
            SUM(fd.subtotal) as ingresos_total
        FROM factura_detalles fd
        JOIN facturas f ON fd.factura_id = f.id
        WHERE f.fecha_factura BETWEEN ? AND ?
        GROUP BY fd.descripcion
        ORDER BY ingresos_total DESC
        LIMIT 5
    ");
    $stmt->execute([$fecha_desde, $fecha_hasta]);
    $top_procedimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✓ Top procedimientos obtenido (" . count($top_procedimientos) . " registros)\n";

    // 3. Métodos de pago (la consulta corregida)
    echo "3. Probando métodos de pago corregidos...\n";
    $stmt = $conn->prepare("
        SELECT 
            p.metodo_pago,
            COUNT(*) as cantidad_pagos,
            SUM(p.monto) as total_monto
        FROM pagos p
        JOIN facturas f ON p.factura_id = f.id
        WHERE f.fecha_factura BETWEEN ? AND ?
        GROUP BY p.metodo_pago
        ORDER BY total_monto DESC
    ");
    $stmt->execute([$fecha_desde, $fecha_hasta]);
    $metodos_pago = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✓ Métodos de pago obtenidos (" . count($metodos_pago) . " registros)\n";

    // 4. Top pacientes
    echo "4. Probando top pacientes...\n";
    $stmt = $conn->prepare("
        SELECT 
            CONCAT(pa.nombre, ' ', pa.apellido) as paciente,
            COUNT(f.id) as total_facturas,
            SUM(f.total) as total_facturado
        FROM facturas f
        JOIN pacientes pa ON f.paciente_id = pa.id
        WHERE f.fecha_factura BETWEEN ? AND ?
        GROUP BY f.paciente_id, pa.nombre, pa.apellido
        ORDER BY total_facturado DESC
        LIMIT 5
    ");
    $stmt->execute([$fecha_desde, $fecha_hasta]);
    $top_pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✓ Top pacientes obtenido (" . count($top_pacientes) . " registros)\n";

    echo "\n📊 Datos de ejemplo:\n";
    echo "   Total facturas: " . $resumen['total_facturas'] . "\n";
    echo "   Total facturado: $" . number_format($resumen['total_facturado'], 2) . "\n";
    
    if (!empty($top_procedimientos)) {
        echo "   Procedimiento top: " . $top_procedimientos[0]['descripcion'] . "\n";
    }
    
    if (!empty($top_pacientes)) {
        echo "   Paciente top: " . $top_pacientes[0]['paciente'] . "\n";
    }

    echo "\n✅ Todas las consultas de reportes funcionan correctamente!\n";
    echo "🌐 El módulo de reportes está listo para usar\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
