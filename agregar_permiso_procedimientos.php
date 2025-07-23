<?php
// Script para agregar el permiso específico de procedimientos
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "=== Agregando Permiso de Procedimientos ===\n\n";

try {
    // Agregar el permiso de procedimientos si no existe
    $stmt = $pdo->prepare("INSERT IGNORE INTO permisos (nombre, descripcion, categoria) VALUES (?, ?, ?)");
    $stmt->execute(['manage_procedures', 'Gestionar procedimientos odontológicos', 'Catálogos']);
    
    if ($stmt->rowCount() > 0) {
        echo "✅ Permiso 'manage_procedures' agregado\n";
    } else {
        echo "ℹ️ Permiso 'manage_procedures' ya existía\n";
    }
    
    // Obtener el ID del permiso
    $stmt = $pdo->prepare("SELECT id FROM permisos WHERE nombre = ?");
    $stmt->execute(['manage_procedures']);
    $permiso = $stmt->fetch();
    
    if ($permiso) {
        $permiso_id = $permiso['id'];
        
        // Asignar al usuario admin
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = 'admin'");
        $stmt->execute();
        $admin = $stmt->fetch();
        
        if ($admin) {
            $admin_id = $admin['id'];
            
            $stmt = $pdo->prepare("INSERT IGNORE INTO usuario_permisos (usuario_id, permiso_id) VALUES (?, ?)");
            $stmt->execute([$admin_id, $permiso_id]);
            
            if ($stmt->rowCount() > 0) {
                echo "✅ Permiso 'manage_procedures' asignado al admin\n";
            } else {
                echo "ℹ️ Admin ya tenía el permiso 'manage_procedures'\n";
            }
        }
    }
    
    // Mostrar todos los permisos relacionados con procedimientos/catálogos
    echo "\n📋 Permisos de catálogos disponibles:\n";
    $stmt = $pdo->prepare("SELECT nombre, descripcion FROM permisos WHERE categoria = 'Catálogos' OR nombre LIKE '%procedure%' OR nombre LIKE '%catalog%'");
    $stmt->execute();
    $permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($permisos as $perm) {
        echo "- {$perm['nombre']}: {$perm['descripcion']}\n";
    }
    
    echo "\n🎉 Permiso de procedimientos configurado correctamente!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Fin ===\n";
?>
