<?php
require_once 'config.php';

try {
    // Agregar el permiso seguros_medicos si no existe
    $stmt = $conn->prepare("SELECT id FROM permissions WHERE permission_name = ?");
    $stmt->execute(['seguros_medicos']);
    
    if ($stmt->rowCount() == 0) {
        $stmt = $conn->prepare("INSERT INTO permissions (permission_name, permission_description) VALUES (?, ?)");
        $stmt->execute(['seguros_medicos', 'Gestionar seguros médicos y ARS']);
        echo "✅ Permiso 'seguros_medicos' agregado exitosamente.<br>";
    } else {
        echo "ℹ️ Permiso 'seguros_medicos' ya existe.<br>";
    }
    
    // Asignar el permiso al usuario admin si existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        $permission_stmt = $conn->prepare("SELECT id FROM permissions WHERE permission_name = 'seguros_medicos'");
        $permission_stmt->execute();
        $permission = $permission_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($permission) {
            // Verificar si ya tiene el permiso
            $check_stmt = $conn->prepare("SELECT id FROM user_permissions WHERE user_id = ? AND permission_id = ?");
            $check_stmt->execute([$admin['id'], $permission['id']]);
            
            if ($check_stmt->rowCount() == 0) {
                $assign_stmt = $conn->prepare("INSERT INTO user_permissions (user_id, permission_id) VALUES (?, ?)");
                $assign_stmt->execute([$admin['id'], $permission['id']]);
                echo "✅ Permiso 'seguros_medicos' asignado al usuario admin.<br>";
            } else {
                echo "ℹ️ Usuario admin ya tiene el permiso 'seguros_medicos'.<br>";
            }
        }
    }
    
    echo "<br><strong>🎉 Configuración de permisos completada!</strong><br>";
    echo "<a href='seguro_medico.php'>👉 Ir al módulo de Seguros Médicos</a>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
