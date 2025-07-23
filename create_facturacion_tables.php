<?php
require_once 'config.php';

echo "Creando tablas para el módulo de facturación...\n";

try {
    // Tabla de facturas principales
    $sql_facturas = "
    CREATE TABLE IF NOT EXISTS facturas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        numero_factura VARCHAR(20) NOT NULL UNIQUE,
        paciente_id INT NOT NULL,
        medico_id INT,
        fecha_factura DATE NOT NULL,
        fecha_vencimiento DATE,
        subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
        descuento DECIMAL(10,2) DEFAULT 0,
        impuestos DECIMAL(10,2) DEFAULT 0,
        total DECIMAL(10,2) NOT NULL DEFAULT 0,
        estado ENUM('pendiente', 'pagada', 'vencida', 'cancelada') DEFAULT 'pendiente',
        metodo_pago VARCHAR(50),
        observaciones TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT NULL,
        INDEX idx_paciente (paciente_id),
        INDEX idx_medico (medico_id),
        INDEX idx_fecha (fecha_factura),
        INDEX idx_estado (estado)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
    
    $conn->exec($sql_facturas);
    echo "✓ Tabla 'facturas' creada exitosamente\n";

    // Tabla de detalles de factura (items/procedimientos)
    $sql_factura_detalles = "
    CREATE TABLE IF NOT EXISTS factura_detalles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        factura_id INT NOT NULL,
        procedimiento_id INT,
        descripcion VARCHAR(255) NOT NULL,
        cantidad INT NOT NULL DEFAULT 1,
        precio_unitario DECIMAL(10,2) NOT NULL,
        descuento_item DECIMAL(10,2) DEFAULT 0,
        subtotal DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (factura_id) REFERENCES facturas(id) ON DELETE CASCADE,
        INDEX idx_factura (factura_id),
        INDEX idx_procedimiento (procedimiento_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
    
    $conn->exec($sql_factura_detalles);
    echo "✓ Tabla 'factura_detalles' creada exitosamente\n";

    // Tabla de pagos
    $sql_pagos = "
    CREATE TABLE IF NOT EXISTS pagos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        factura_id INT NOT NULL,
        fecha_pago DATE NOT NULL,
        monto DECIMAL(10,2) NOT NULL,
        metodo_pago VARCHAR(50) NOT NULL,
        numero_referencia VARCHAR(100),
        observaciones TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (factura_id) REFERENCES facturas(id) ON DELETE CASCADE,
        INDEX idx_factura (factura_id),
        INDEX idx_fecha (fecha_pago)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
    
    $conn->exec($sql_pagos);
    echo "✓ Tabla 'pagos' creada exitosamente\n";

    // Insertar algunos datos de ejemplo
    echo "\nInsertando datos de ejemplo...\n";
    
    // Verificar si ya existen facturas
    $stmt = $conn->query("SELECT COUNT(*) FROM facturas");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insertar factura de ejemplo
        $stmt = $conn->prepare("
            INSERT INTO facturas (numero_factura, paciente_id, medico_id, fecha_factura, fecha_vencimiento, subtotal, total, estado) 
            VALUES (?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), ?, ?, ?)
        ");
        
        $stmt->execute(['FAC-001', 1, 1, 150.00, 150.00, 'pendiente']);
        $factura_id = $conn->lastInsertId();
        
        // Insertar detalles de ejemplo
        $stmt_detalle = $conn->prepare("
            INSERT INTO factura_detalles (factura_id, procedimiento_id, descripcion, cantidad, precio_unitario, subtotal) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt_detalle->execute([$factura_id, 1, 'Limpieza dental', 1, 50.00, 50.00]);
        $stmt_detalle->execute([$factura_id, 2, 'Consulta general', 1, 100.00, 100.00]);
        
        echo "✓ Datos de ejemplo insertados\n";
    }

    // Agregar permisos de facturación
    echo "\nAgregando permisos de facturación...\n";
    
    $permisos_facturacion = [
        ['manage_billing', 'Gestionar Facturación', 'facturacion'],
        ['create_invoices', 'Crear Facturas', 'facturacion'],
        ['view_invoices', 'Ver Facturas', 'facturacion'],
        ['edit_invoices', 'Editar Facturas', 'facturacion'],
        ['delete_invoices', 'Eliminar Facturas', 'facturacion'],
        ['manage_payments', 'Gestionar Pagos', 'facturacion'],
        ['view_reports', 'Ver Reportes', 'facturacion']
    ];
    
    $stmt_perm = $conn->prepare("INSERT IGNORE INTO permisos (nombre, descripcion, categoria) VALUES (?, ?, ?)");
    
    foreach ($permisos_facturacion as $permiso) {
        $stmt_perm->execute($permiso);
    }
    
    // Asignar permisos al admin
    $stmt_admin = $conn->prepare("
        INSERT IGNORE INTO usuario_permisos (usuario_id, permiso_id) 
        SELECT 1, id FROM permisos WHERE categoria = 'facturacion'
    ");
    $stmt_admin->execute();
    
    echo "✓ Permisos de facturación agregados y asignados al admin\n";

    echo "\n🎉 Módulo de facturación configurado exitosamente!\n";
    echo "📋 Tablas creadas: facturas, factura_detalles, pagos\n";
    echo "🔑 Permisos agregados y asignados\n";
    echo "📊 Datos de ejemplo insertados\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
