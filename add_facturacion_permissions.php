<?php
require_once 'config.php';

try {
    // Verificar si la tabla permissions existe
    $check = $conn->query("SHOW TABLES LIKE 'permissions'");
    if ($check->rowCount() == 0) {
        echo "Tabla permissions no existe. Creando...\n";
        $conn->exec("
            CREATE TABLE IF NOT EXISTS permissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE,
                description VARCHAR(255),
                category VARCHAR(50) DEFAULT 'General',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    // Agregar permisos de facturación
    $facturacionPermisos = [
        ['ver_facturacion', 'Ver facturación', 'Facturación'],
        ['crear_factura', 'Crear facturas', 'Facturación'],
        ['editar_factura', 'Editar facturas', 'Facturación'],
        ['anular_factura', 'Anular facturas', 'Facturación'],
        ['ver_reportes_facturacion', 'Ver reportes de facturación', 'Facturación']
    ];

    $stmt = $conn->prepare("
        INSERT IGNORE INTO permissions (name, description, category) 
        VALUES (?, ?, ?)
    ");

    foreach ($facturacionPermisos as $permiso) {
        $stmt->execute($permiso);
        echo "Permiso agregado: {$permiso[0]} - {$permiso[1]}\n";
    }

    // Asignar todos los permisos de facturación al admin (ID 1)
    $adminId = 1;
    
    $stmtAssign = $conn->prepare("
        INSERT IGNORE INTO receptionist_permissions (receptionist_id, permission, assigned_by) 
        VALUES (?, ?, ?)
    ");

    foreach ($facturacionPermisos as $permiso) {
        $stmtAssign->execute([$adminId, $permiso[0], $adminId]);
        echo "Permiso {$permiso[0]} asignado al admin\n";
    }

    echo "\n✅ Permisos de facturación agregados correctamente.\n";

    // Verificar permisos existentes
    echo "\nPermisos de facturación en la base de datos:\n";
    $result = $conn->query("SELECT name, description, category FROM permissions WHERE category = 'Facturación'");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['name']}: {$row['description']} ({$row['category']})\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
