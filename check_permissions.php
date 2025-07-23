<?php
session_start();
require_once "permissions.php";

echo "Usuario actual: " . $_SESSION["username"] . " (" . $_SESSION["rol"] . ")\n";
echo "Permisos disponibles:\n";

$permisosDisponibles = [
    'manage_users',
    'manage_patients',
    'manage_appointments',
    'manage_prescriptions',
    'view_prescriptions',
    'manage_diseases',
    'view_medical_history',
    'edit_medical_history',
    'manage_receptionist_permissions'
];

foreach ($permisosDisponibles as $permiso) {
    echo "- $permiso: " . (hasPermission($permiso) ? "SÍ" : "NO") . "\n";
}

// Obtener roles de usuario
echo "\nRoles de usuario disponibles:\n";
require_once "config.php";
try {
    $stmt = $conn->query("SELECT username, rol, nombre FROM usuarios");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- " . $row['username'] . " (" . $row['nombre'] . "): " . $row['rol'] . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
