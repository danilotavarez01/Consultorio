<?php
session_start();
require_once 'config.php';

echo "=== DEBUG: Permisos del Usuario ===\n\n";

if (isset($_SESSION['user_id'])) {
    echo "Usuario ID: " . $_SESSION['user_id'] . "\n";
    echo "Username: " . ($_SESSION['username'] ?? 'No definido') . "\n";
    echo "Rol: " . ($_SESSION['rol'] ?? 'No definido') . "\n\n";
    
    // Verificar permisos
    try {
        $stmt = $pdo->prepare("
            SELECT p.nombre, p.descripcion 
            FROM usuario_permisos up 
            JOIN permisos p ON up.permiso_id = p.id 
            WHERE up.usuario_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Permisos del usuario:\n";
        if (empty($permisos)) {
            echo "- No tiene permisos asignados\n";
        } else {
            foreach ($permisos as $permiso) {
                echo "- {$permiso['nombre']}: {$permiso['descripcion']}\n";
            }
        }
        
        echo "\n=== Verificando permisos específicos ===\n";
        
        // Incluir función de permisos si existe
        if (function_exists('hasPermission')) {
            echo "Función hasPermission() existe\n";
            echo "gestionar_catalogos: " . (hasPermission('gestionar_catalogos') ? 'SÍ' : 'NO') . "\n";
            echo "manage_users: " . (hasPermission('manage_users') ? 'SÍ' : 'NO') . "\n";
        } else {
            echo "Función hasPermission() NO existe\n";
        }
        
    } catch (Exception $e) {
        echo "Error al consultar permisos: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "Usuario no está logueado\n";
}

echo "\n=== Fin del debug ===\n";
?>
