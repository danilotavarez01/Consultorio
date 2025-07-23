<?php
session_start();
require_once 'config.php';
require_once 'permissions.php';

echo "=== Test de Acceso a Procedimientos ===\n\n";

// Simular usuario admin logueado
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['rol'] = 'admin';

echo "Usuario simulado:\n";
echo "- ID: " . $_SESSION['user_id'] . "\n";
echo "- Username: " . $_SESSION['username'] . "\n";
echo "- Rol: " . $_SESSION['rol'] . "\n\n";

echo "Verificando permisos:\n";
echo "- gestionar_catalogos: " . (hasPermission('gestionar_catalogos') ? 'SÍ' : 'NO') . "\n";
echo "- manage_users: " . (hasPermission('manage_users') ? 'SÍ' : 'NO') . "\n";
echo "- Es admin: " . ((isset($_SESSION["username"]) && $_SESSION["username"] === "admin") ? 'SÍ' : 'NO') . "\n";

// Condición del sidebar
$puede_ver_procedimientos = hasPermission('gestionar_catalogos') || 
                           hasPermission('manage_users') || 
                           (isset($_SESSION["username"]) && $_SESSION["username"] === "admin");

echo "\nResultado final:\n";
echo "¿Puede ver Procedimientos? " . ($puede_ver_procedimientos ? 'SÍ' : 'NO') . "\n";

if ($puede_ver_procedimientos) {
    echo "\n✅ El enlace de Procedimientos DEBERÍA estar visible\n";
} else {
    echo "\n❌ El enlace de Procedimientos NO estará visible\n";
}

echo "\n=== Fin del test ===\n";
?>
