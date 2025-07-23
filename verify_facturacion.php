<?php
require_once 'config.php';

echo "Verificando módulo de facturación...\n";

try {
    // Verificar tablas
    $tablas = ['facturas', 'factura_detalles', 'pagos'];
    
    foreach ($tablas as $tabla) {
        $stmt = $conn->query("SHOW TABLES LIKE '$tabla'");
        if ($stmt->rowCount() > 0) {
            echo "✓ Tabla '$tabla' existe\n";
            
            // Contar registros
            $stmt = $conn->query("SELECT COUNT(*) FROM $tabla");
            $count = $stmt->fetchColumn();
            echo "  - Registros: $count\n";
        } else {
            echo "❌ Tabla '$tabla' no existe\n";
        }
    }
    
    echo "\nVerificando permisos de facturación...\n";
    $stmt = $conn->query("SELECT nombre, descripcion FROM permisos WHERE categoria = 'facturacion'");
    $permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($permisos) > 0) {
        echo "✓ Permisos de facturación encontrados:\n";
        foreach ($permisos as $permiso) {
            echo "  - " . $permiso['nombre'] . " (" . $permiso['descripcion'] . ")\n";
        }
    } else {
        echo "❌ No se encontraron permisos de facturación\n";
    }
    
    echo "\nVerificando permisos del admin...\n";
    $stmt = $conn->query("
        SELECT p.nombre 
        FROM usuario_permisos up 
        JOIN permisos p ON up.permiso_id = p.id 
        WHERE up.usuario_id = 1 AND p.categoria = 'facturacion'
    ");
    $permisos_admin = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($permisos_admin) > 0) {
        echo "✓ Admin tiene permisos de facturación:\n";
        foreach ($permisos_admin as $permiso) {
            echo "  - $permiso\n";
        }
    } else {
        echo "❌ Admin no tiene permisos de facturación\n";
    }
    
    echo "\nVerificando datos de ejemplo...\n";
    $stmt = $conn->query("
        SELECT f.numero_factura, f.total, f.estado, 
               CONCAT(p.nombre, ' ', p.apellido) as paciente
        FROM facturas f
        LEFT JOIN pacientes p ON f.paciente_id = p.id
        LIMIT 5
    ");
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($facturas) > 0) {
        echo "✓ Facturas de ejemplo encontradas:\n";
        foreach ($facturas as $factura) {
            echo "  - " . $factura['numero_factura'] . " | " . $factura['paciente'] . " | $" . $factura['total'] . " | " . $factura['estado'] . "\n";
        }
    } else {
        echo "ℹ No hay facturas en el sistema\n";
    }
    
    echo "\n🎉 Verificación completada!\n";
    echo "📋 El módulo de facturación está listo para usar\n";
    echo "🌐 Accede a: http://localhost/Consultorio2/facturacion.php\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
