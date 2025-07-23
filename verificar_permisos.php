<?php
require_once 'config.php';

echo "=== Verificación de Permisos de Procedimientos ===\n\n";

// Verificar si existen las tablas
$tables = ['permisos', 'usuario_permisos'];

foreach ($tables as $table) {
    try {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabla '$table' existe\n";
            
            // Mostrar contenido si es la tabla permisos
            if ($table === 'permisos') {
                $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
                $count = $stmt->fetch()['count'];
                echo "   - Registros: $count\n";
                
                if ($count > 0) {
                    echo "   - Permisos existentes:\n";
                    $stmt = $conn->query("SELECT nombre, categoria FROM $table ORDER BY categoria, nombre");
                    while ($row = $stmt->fetch()) {
                        echo "     • {$row['nombre']} ({$row['categoria']})\n";
                    }
                }
            }
        } else {
            echo "❌ Tabla '$table' NO existe\n";
        }
    } catch (PDOException $e) {
        echo "❌ Error verificando tabla '$table': " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Verificar permisos específicos de procedimientos
echo "=== Verificando Permisos de Procedimientos ===\n";
$permisos_requeridos = ['manage_procedures', 'view_procedures', 'gestionar_catalogos'];

foreach ($permisos_requeridos as $permiso) {
    try {
        $stmt = $conn->prepare("SELECT * FROM permisos WHERE nombre = ?");
        $stmt->execute([$permiso]);
        if ($stmt->rowCount() > 0) {
            echo "✅ Permiso '$permiso' existe\n";
        } else {
            echo "❌ Permiso '$permiso' NO existe\n";
        }
    } catch (PDOException $e) {
        echo "❌ Error verificando permiso '$permiso': " . $e->getMessage() . "\n";
    }
}

echo "\n=== Estado del Sistema ===\n";
echo "Procedimientos.php: " . (file_exists('procedimientos.php') ? "✅ Existe" : "❌ No existe") . "\n";
echo "Permissions.php: " . (file_exists('permissions.php') ? "✅ Existe" : "❌ No existe") . "\n";
?>
