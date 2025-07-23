<?php
require_once 'config.php';

echo "=== ASIGNAR PERMISOS DE FACTURACIÓN A USUARIO DE PRUEBA ===\n\n";

try {
    // Asignar permisos de facturación al usuario "Secretaria" (ID: 3)
    $userId = 3;
    $facturacionPermisos = [
        'ver_facturacion',
        'crear_factura', 
        'ver_reportes_facturacion'
    ];

    // Primero eliminar permisos existentes de facturación
    $stmt = $conn->prepare("DELETE FROM receptionist_permissions WHERE receptionist_id = ? AND permission IN ('ver_facturacion', 'crear_factura', 'editar_factura', 'anular_factura', 'ver_reportes_facturacion')");
    $stmt->execute([$userId]);

    // Asignar los permisos básicos de facturación
    $stmt = $conn->prepare("INSERT INTO receptionist_permissions (receptionist_id, permission, assigned_by) VALUES (?, ?, ?)");
    
    foreach ($facturacionPermisos as $permiso) {
        $stmt->execute([$userId, $permiso, 1]); // Asignado por admin (ID: 1)
        echo "✓ Permiso '$permiso' asignado al usuario ID $userId\n";
    }

    echo "\n=== VERIFICACIÓN ===\n";
    
    // Verificar permisos asignados
    $stmt = $conn->prepare("
        SELECT rp.permission, p.description 
        FROM receptionist_permissions rp 
        LEFT JOIN permissions p ON rp.permission = p.name 
        WHERE rp.receptionist_id = ? AND p.category = 'Facturación'
        ORDER BY rp.permission
    ");
    $stmt->execute([$userId]);
    
    echo "Permisos de facturación asignados al usuario ID $userId:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  • {$row['permission']}: {$row['description']}\n";
    }

    echo "\n✅ Permisos asignados correctamente.\n";
    echo "🔗 Prueba: http://localhost/Consultorio2/user_permissions.php?id=$userId\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
