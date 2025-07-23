<?php
require_once 'config.php';
require_once 'add_default_permissions.php';

// Obtener todos los usuarios recepcionistas
$sql = "SELECT id FROM usuarios WHERE rol = 'recepcionista'";
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $receptionists = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($receptionists as $receptionistId) {
        // Verificar si ya tiene los permisos básicos
        $checkSql = "SELECT COUNT(*) FROM receptionist_permissions 
                    WHERE receptionist_id = ? AND permission IN ('view_appointments', 'manage_appointments')";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute([$receptionistId]);
        $hasPermissions = $checkStmt->fetchColumn() > 0;
        
        if (!$hasPermissions) {
            addDefaultPermissionsToReceptionist($receptionistId);
            echo "Permisos agregados para el recepcionista ID: " . $receptionistId . "<br>";
        }
    }
    
    echo "Proceso completado exitosamente.";
    
} catch (PDOException $e) {
    echo "Error al actualizar permisos: " . $e->getMessage();
}
?>
