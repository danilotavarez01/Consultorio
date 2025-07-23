<?php
require_once 'config.php';

// Función para agregar permisos base a un recepcionista
function addDefaultPermissionsToReceptionist($userId) {
    global $conn;
    
    // Permisos base para recepcionistas
    $defaultPermissions = [
        'view_appointments',  // Ver turnos y citas
        'manage_appointments' // Gestionar turnos y citas
    ];
    
    try {
        $stmt = $conn->prepare("INSERT INTO receptionist_permissions (receptionist_id, permission) VALUES (?, ?)");
        foreach ($defaultPermissions as $permission) {
            $stmt->execute([$userId, $permission]);
        }
        return true;
    } catch (PDOException $e) {
        error_log("Error al agregar permisos por defecto: " . $e->getMessage());
        return false;
    }
}
?>
