<?php
require_once 'config.php';

echo "=== VERIFICACIÓN DE PERMISOS DE FACTURACIÓN EN GESTIÓN ===\n\n";

try {
    // 1. Verificar que los permisos estén en la base de datos
    echo "1. Permisos de facturación en la base de datos:\n";
    $result = $conn->query("SELECT name, description, category FROM permissions WHERE category = 'Facturación' ORDER BY name");
    $permisos_bd = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "   ✓ {$row['name']}: {$row['description']}\n";
        $permisos_bd[] = $row['name'];
    }

    // 2. Verificar que los permisos estén en user_permissions.php
    echo "\n2. Verificando user_permissions.php...\n";
    $file_content = file_get_contents('user_permissions.php');
    
    $permisos_esperados = [
        'ver_facturacion' => 'Ver Facturación',
        'crear_factura' => 'Crear Facturas', 
        'editar_factura' => 'Editar Facturas',
        'anular_factura' => 'Anular Facturas',
        'ver_reportes_facturacion' => 'Ver Reportes de Facturación'
    ];

    foreach ($permisos_esperados as $permiso => $descripcion) {
        if (strpos($file_content, "'$permiso'") !== false && strpos($file_content, "'$descripcion'") !== false) {
            echo "   ✓ $permiso => $descripcion está en user_permissions.php\n";
        } else {
            echo "   ✗ $permiso => $descripcion NO está en user_permissions.php\n";
        }
    }

    // 3. Verificar que esté la categoría 'Facturación'
    if (strpos($file_content, "'Facturación' =>") !== false) {
        echo "   ✓ Categoría 'Facturación' definida correctamente\n";
    } else {
        echo "   ✗ Categoría 'Facturación' NO está definida\n";
    }

    // 4. Verificar usuarios disponibles
    echo "\n3. Usuarios disponibles para asignar permisos:\n";
    $result = $conn->query("SELECT id, nombre, rol FROM usuarios WHERE rol IN ('recepcionista', 'doctor') ORDER BY rol, nombre");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "   • ID {$row['id']}: {$row['nombre']} ({$row['rol']})\n";
    }

    echo "\n=== VERIFICACIÓN COMPLETADA ===\n";
    echo "\n🔗 Abra: http://localhost/Consultorio2/user_permissions.php\n";
    echo "📋 Seleccione un usuario y verifique que aparezca la sección 'Facturación'\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
