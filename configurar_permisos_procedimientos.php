<?php
// Script para agregar permisos de procedimientos al usuario admin
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "=== Configurando Permisos para Procedimientos ===\n\n";

try {
    // Verificar si existe el permiso 'gestionar_catalogos'
    $stmt = $pdo->prepare("SELECT id FROM permisos WHERE nombre = ?");
    $stmt->execute(['gestionar_catalogos']);
    $permiso = $stmt->fetch();
    
    if (!$permiso) {
        // Crear el permiso si no existe
        $stmt = $pdo->prepare("INSERT INTO permisos (nombre, descripcion, categoria) VALUES (?, ?, ?)");
        $stmt->execute(['gestionar_catalogos', 'Gestionar catálogos y procedimientos', 'Catálogos']);
        $permiso_id = $pdo->lastInsertId();
        echo "✅ Permiso 'gestionar_catalogos' creado con ID: $permiso_id\n";
    } else {
        $permiso_id = $permiso['id'];
        echo "✅ Permiso 'gestionar_catalogos' ya existe con ID: $permiso_id\n";
    }
    
    // Buscar el usuario admin
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = ?");
    $stmt->execute(['admin']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        $admin_id = $admin['id'];
        echo "✅ Usuario admin encontrado con ID: $admin_id\n";
        
        // Verificar si ya tiene el permiso
        $stmt = $pdo->prepare("SELECT id FROM usuario_permisos WHERE usuario_id = ? AND permiso_id = ?");
        $stmt->execute([$admin_id, $permiso_id]);
        $tiene_permiso = $stmt->fetch();
        
        if (!$tiene_permiso) {
            // Asignar el permiso
            $stmt = $pdo->prepare("INSERT INTO usuario_permisos (usuario_id, permiso_id) VALUES (?, ?)");
            $stmt->execute([$admin_id, $permiso_id]);
            echo "✅ Permiso 'gestionar_catalogos' asignado al usuario admin\n";
        } else {
            echo "ℹ️ El usuario admin ya tiene el permiso 'gestionar_catalogos'\n";
        }
        
    } else {
        echo "❌ Usuario admin no encontrado\n";
    }
    
    // Mostrar todos los permisos del admin
    echo "\n📋 Permisos actuales del usuario admin:\n";
    $stmt = $pdo->prepare("
        SELECT p.nombre, p.descripcion 
        FROM usuario_permisos up 
        JOIN permisos p ON up.permiso_id = p.id 
        JOIN usuarios u ON up.usuario_id = u.id 
        WHERE u.username = 'admin'
    ");
    $stmt->execute();
    $permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($permisos)) {
        echo "- No tiene permisos asignados\n";
    } else {
        foreach ($permisos as $perm) {
            echo "- {$perm['nombre']}: {$perm['descripcion']}\n";
        }
    }
    
    echo "\n🎉 Configuración completada. El enlace de Procedimientos debería estar visible ahora.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Fin ===\n";
?>
