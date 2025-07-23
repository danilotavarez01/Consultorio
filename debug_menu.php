<?php
session_start();
require_once "permissions.php";

echo "===== Información de sesión =====\n";
echo "Usuario logueado: " . (isset($_SESSION["loggedin"]) ? "Sí" : "No") . "\n";
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"]) {
    echo "Usuario ID: " . $_SESSION["id"] . "\n";
    echo "Nombre: " . $_SESSION["nombre"] . "\n";
    echo "Rol: " . $_SESSION["rol"] . "\n";
    
    echo "\n===== Verificación de permisos =====\n";
    echo "Permiso 'manage_appointments': " . (hasPermission('manage_appointments') ? "SÍ" : "NO") . "\n";
    
    if ($_SESSION["rol"] === ROLE_RECEPTIONIST) {
        require_once "config.php";
        $stmt = $conn->prepare("SELECT permission FROM receptionist_permissions WHERE receptionist_id = ?");
        $stmt->execute([$_SESSION["id"]]);
        $permisos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "\nPermisos asignados en la base de datos para este usuario:\n";
        if (count($permisos) > 0) {
            foreach ($permisos as $perm) {
                echo "- " . $perm . "\n";
            }
        } else {
            echo "No tiene permisos asignados en la base de datos.\n";
        }
    }
}

echo "\n===== Prueba de menú =====\n";
echo "Código del menú para 'manage_appointments':\n";
?>
<?php if(hasPermission('manage_appointments')): ?>
    <p>SÍ debería ver esto - Tiene permiso 'manage_appointments'</p>
    <a href="turnos.php"><i class="fas fa-calendar-alt"></i> Turnos</a>
    <a href="Citas.php"><i class="fas fa-calendar-check"></i> Citas</a>
<?php else: ?>
    <p>NO debería ver esto - No tiene permiso 'manage_appointments'</p>
<?php endif; ?>
