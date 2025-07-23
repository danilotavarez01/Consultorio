<?php
// Script para crear las tablas de permisos necesarias
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "=== Creando Tablas de Permisos ===\n\n";

try {
    // Crear tabla permisos
    $sql_permisos = "
    CREATE TABLE IF NOT EXISTS permisos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL UNIQUE,
        descripcion VARCHAR(255) DEFAULT NULL,
        categoria VARCHAR(50) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
    
    $pdo->exec($sql_permisos);
    echo "✅ Tabla 'permisos' creada/verificada\n";
    
    // Crear tabla usuario_permisos
    $sql_usuario_permisos = "
    CREATE TABLE IF NOT EXISTS usuario_permisos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        permiso_id INT NOT NULL,
        granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_usuario_permiso (usuario_id, permiso_id),
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
    
    $pdo->exec($sql_usuario_permisos);
    echo "✅ Tabla 'usuario_permisos' creada/verificada\n";
    
    // Insertar permisos básicos
    echo "\n📝 Insertando permisos básicos...\n";
    
    $permisos_basicos = [
        ['manage_patients', 'Gestionar pacientes (crear, editar, eliminar)', 'Pacientes'],
        ['view_patients', 'Ver información de pacientes', 'Pacientes'],
        ['manage_appointments', 'Gestionar citas médicas', 'Citas'],
        ['view_appointments', 'Ver citas médicas', 'Citas'],
        ['manage_medical_records', 'Gestionar historiales médicos', 'Historiales'],
        ['view_medical_records', 'Ver historiales médicos', 'Historiales'],
        ['manage_users', 'Gestionar usuarios del sistema', 'Administración'],
        ['manage_settings', 'Gestionar configuración del sistema', 'Administración'],
        ['manage_diseases', 'Gestionar catálogo de enfermedades', 'Catálogos'],
        ['manage_specialties', 'Gestionar especialidades médicas', 'Catálogos'],
        ['gestionar_catalogos', 'Gestionar catálogos y procedimientos', 'Catálogos'],
        ['generate_reports', 'Generar reportes', 'Reportes'],
        ['manage_whatsapp', 'Gestionar configuración de WhatsApp', 'Comunicación']
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO permisos (nombre, descripcion, categoria) VALUES (?, ?, ?)");
    
    foreach ($permisos_basicos as $permiso) {
        $stmt->execute($permiso);
        echo "✅ Permiso '{$permiso[0]}' insertado\n";
    }
    
    // Asignar todos los permisos al usuario admin
    echo "\n👤 Asignando permisos al usuario admin...\n";
    
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        $admin_id = $admin['id'];
        echo "✅ Usuario admin encontrado (ID: $admin_id)\n";
        
        // Obtener todos los permisos
        $stmt = $pdo->query("SELECT id FROM permisos");
        $permisos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $stmt = $pdo->prepare("INSERT IGNORE INTO usuario_permisos (usuario_id, permiso_id) VALUES (?, ?)");
        
        foreach ($permisos as $permiso_id) {
            $stmt->execute([$admin_id, $permiso_id]);
        }
        
        echo "✅ Todos los permisos asignados al usuario admin\n";
        
        // Mostrar permisos asignados
        echo "\n📋 Permisos del usuario admin:\n";
        $stmt = $pdo->prepare("
            SELECT p.nombre, p.descripcion 
            FROM usuario_permisos up 
            JOIN permisos p ON up.permiso_id = p.id 
            WHERE up.usuario_id = ?
        ");
        $stmt->execute([$admin_id]);
        $permisos_usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($permisos_usuario as $perm) {
            echo "- {$perm['nombre']}: {$perm['descripcion']}\n";
        }
        
    } else {
        echo "❌ Usuario admin no encontrado\n";
    }
    
    echo "\n🎉 Configuración de permisos completada exitosamente!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Fin ===\n";
?>
