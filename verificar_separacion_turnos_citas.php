<?php
require_once 'config.php';

echo "=== VERIFICACIÓN DE SEPARACIÓN DE TURNOS Y CITAS ===\n\n";

try {
    // 1. Verificar permisos de Turnos
    echo "1. Permisos de Turnos en la base de datos:\n";
    $result = $conn->query("SELECT name, description FROM permissions WHERE category = 'Turnos' ORDER BY name");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "   ✓ {$row['name']}: {$row['description']}\n";
    }

    // 2. Verificar permisos de Citas
    echo "\n2. Permisos de Citas en la base de datos:\n";
    $result = $conn->query("SELECT name, description FROM permissions WHERE category = 'Citas' ORDER BY name");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "   ✓ {$row['name']}: {$row['description']}\n";
    }

    // 3. Verificar user_permissions.php
    echo "\n3. Verificando categorías en user_permissions.php...\n";
    $file_content = file_get_contents('user_permissions.php');
    
    if (strpos($file_content, "'Gestión de Turnos' =>") !== false) {
        echo "   ✓ Categoría 'Gestión de Turnos' definida\n";
    } else {
        echo "   ✗ Categoría 'Gestión de Turnos' NO definida\n";
    }

    if (strpos($file_content, "'Gestión de Citas' =>") !== false) {
        echo "   ✓ Categoría 'Gestión de Citas' definida\n";
    } else {
        echo "   ✗ Categoría 'Gestión de Citas' NO definida\n";
    }

    // 4. Verificar sidebar.php
    echo "\n4. Verificando sidebar.php...\n";
    $sidebar_content = file_get_contents('sidebar.php');
    
    if (strpos($sidebar_content, "hasPermission('manage_turnos')") !== false) {
        echo "   ✓ Sidebar usa permisos específicos de turnos\n";
    } else {
        echo "   ✗ Sidebar NO usa permisos específicos de turnos\n";
    }

    if (strpos($sidebar_content, "hasPermission('manage_citas')") !== false) {
        echo "   ✓ Sidebar usa permisos específicos de citas\n";
    } else {
        echo "   ✗ Sidebar NO usa permisos específicos de citas\n";
    }

    // 5. Mostrar estructura de categorías
    echo "\n5. Estructura actual de categorías:\n";
    $result = $conn->query("SELECT DISTINCT category, COUNT(*) as total FROM permissions GROUP BY category ORDER BY category");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "   • {$row['category']}: {$row['total']} permisos\n";
    }

    echo "\n=== VERIFICACIÓN COMPLETADA ===\n";
    echo "\n🔗 Prueba: http://localhost/Consultorio2/user_permissions.php\n";
    echo "📋 Ahora verás categorías separadas: 'Gestión de Turnos' y 'Gestión de Citas'\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
