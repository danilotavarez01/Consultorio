<?php
require_once 'config.php';

echo "=== AGREGANDO PERMISOS ESPECÍFICOS PARA TURNOS Y CITAS ===\n\n";

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

    // Agregar permisos específicos para turnos y citas
    $nuevosPermisos = [
        // Turnos
        ['manage_turnos', 'Gestionar Turnos', 'Turnos'],
        ['view_turnos', 'Ver Turnos', 'Turnos'],
        ['create_turnos', 'Crear Turnos', 'Turnos'],
        ['edit_turnos', 'Editar Turnos', 'Turnos'],
        ['delete_turnos', 'Eliminar Turnos', 'Turnos'],
        
        // Citas
        ['manage_citas', 'Gestionar Citas', 'Citas'],
        ['view_citas', 'Ver Citas', 'Citas'],
        ['create_citas', 'Crear Citas', 'Citas'],
        ['edit_citas', 'Editar Citas', 'Citas'],
        ['delete_citas', 'Eliminar Citas', 'Citas']
    ];

    $stmt = $conn->prepare("
        INSERT IGNORE INTO permissions (name, description, category) 
        VALUES (?, ?, ?)
    ");

    foreach ($nuevosPermisos as $permiso) {
        $stmt->execute($permiso);
        echo "Permiso agregado: {$permiso[0]} - {$permiso[1]} ({$permiso[2]})\n";
    }

    // Asignar todos los nuevos permisos al admin (ID 1)
    $adminId = 1;
    
    $stmtAssign = $conn->prepare("
        INSERT IGNORE INTO receptionist_permissions (receptionist_id, permission, assigned_by) 
        VALUES (?, ?, ?)
    ");

    foreach ($nuevosPermisos as $permiso) {
        $stmtAssign->execute([$adminId, $permiso[0], $adminId]);
        echo "Permiso {$permiso[0]} asignado al admin\n";
    }

    echo "\n✅ Permisos específicos de turnos y citas agregados correctamente.\n";

    // Verificar permisos existentes por categoría
    echo "\nPermisos de Turnos en la base de datos:\n";
    $result = $conn->query("SELECT name, description FROM permissions WHERE category = 'Turnos'");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['name']}: {$row['description']}\n";
    }

    echo "\nPermisos de Citas en la base de datos:\n";
    $result = $conn->query("SELECT name, description FROM permissions WHERE category = 'Citas'");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['name']}: {$row['description']}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
