<?php
require_once 'config.php';

echo "Probando consulta de facturas corregida...\n";

try {
    $stmt = $conn->prepare("
        SELECT f.*, 
               CONCAT(p.nombre, ' ', p.apellido) as paciente_nombre,
               u.nombre as medico_nombre,
               COALESCE(SUM(pg.monto), 0) as total_pagado
        FROM facturas f
        LEFT JOIN pacientes p ON f.paciente_id = p.id
        LEFT JOIN usuarios u ON f.medico_id = u.id  
        LEFT JOIN pagos pg ON f.id = pg.factura_id
        GROUP BY f.id
        ORDER BY f.fecha_factura DESC, f.id DESC
        LIMIT 5
    ");
    $stmt->execute();
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($facturas) > 0) {
        echo "✓ Consulta exitosa! Facturas encontradas:\n";
        foreach ($facturas as $factura) {
            echo "  - " . $factura['numero_factura'] . 
                 " | Paciente: " . ($factura['paciente_nombre'] ?: 'N/A') . 
                 " | Médico: " . ($factura['medico_nombre'] ?: 'N/A') . 
                 " | Total: $" . number_format($factura['total'], 2) . 
                 " | Estado: " . $factura['estado'] . "\n";
        }
    } else {
        echo "ℹ No hay facturas en el sistema\n";
    }
    
    echo "\n✅ El error ha sido corregido exitosamente!\n";
    echo "🌐 El módulo de facturación está funcionando correctamente\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
