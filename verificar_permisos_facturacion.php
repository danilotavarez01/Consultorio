<?php
require_once 'config.php';

echo "=== VERIFICACIÓN DE PERMISOS DE FACTURACIÓN ===\n\n";

try {
    // 1. Verificar permisos en la tabla permissions
    echo "1. Permisos de facturación en la tabla permissions:\n";
    $result = $conn->query("SELECT name, description, category FROM permissions WHERE category = 'Facturación' ORDER BY name");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "   ✓ {$row['name']}: {$row['description']}\n";
    }

    // 2. Verificar asignaciones del admin
    echo "\n2. Permisos de facturación asignados al admin (ID: 1):\n";
    $result = $conn->query("
        SELECT rp.permission, p.description 
        FROM receptionist_permissions rp 
        LEFT JOIN permissions p ON rp.permission = p.name 
        WHERE rp.receptionist_id = 1 AND p.category = 'Facturación'
        ORDER BY rp.permission
    ");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "   ✓ {$row['permission']}: {$row['description']}\n";
    }

    // 3. Verificar que todos los permisos están en permissions.php
    echo "\n3. Verificando permissions.php...\n";
    $permissionsFile = file_get_contents('permissions.php');
    $facturacionPermisos = [
        'ver_facturacion',
        'crear_factura',
        'editar_factura',
        'anular_factura',
        'ver_reportes_facturacion'
    ];
    
    foreach ($facturacionPermisos as $permiso) {
        if (strpos($permissionsFile, $permiso) !== false) {
            echo "   ✓ $permiso está definido en permissions.php\n";
        } else {
            echo "   ✗ $permiso NO está definido en permissions.php\n";
        }
    }

    // 4. Verificar sidebar.php
    echo "\n4. Verificando sidebar.php...\n";
    $sidebarFile = file_get_contents('sidebar.php');
    if (strpos($sidebarFile, "hasPermission('ver_facturacion')") !== false) {
        echo "   ✓ Sidebar usa permisos correctos para facturación\n";
    } else {
        echo "   ✗ Sidebar NO usa permisos correctos para facturación\n";
    }

    if (strpos($sidebarFile, "hasPermission('ver_reportes_facturacion')") !== false) {
        echo "   ✓ Sidebar usa permisos correctos para reportes\n";
    } else {
        echo "   ✗ Sidebar NO usa permisos correctos para reportes\n";
    }

    echo "\n=== VERIFICACIÓN COMPLETADA ===\n";
    echo "\nPara probar:\n";
    echo "1. Ingresar como admin al sistema\n";
    echo "2. Ir a Gestión de Permisos\n";
    echo "3. Verificar que aparezca la categoría 'Facturación'\n";
    echo "4. Verificar que los enlaces de Facturación y Reportes sean visibles\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
