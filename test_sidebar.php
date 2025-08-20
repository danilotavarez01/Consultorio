<?php
session_start();

// ARCHIVO DE TEST DESACTIVADO PARA EVITAR AUTO-LOGIN
// Para usar este test, descomente las siguientes líneas manualmente:
/*
$_SESSION["loggedin"] = true;
$_SESSION["rol"] = "admin";
$_SESSION["id"] = 1;
$_SESSION["username"] = "admin";
$_SESSION["nombre"] = "Administrador";
*/

require_once "permissions.php";

// Verificación de sesión habilitada
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px;'>";
    echo "<h3>❌ Error: Usuario no autenticado</h3>";
    echo "<p>Este es un archivo de test que requiere autenticación.</p>";
    echo "<a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;'>Ir al Login</a>";
    echo "</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test del Sidebar</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <style>
        .sidebar { background-color: #343a40; color: white; padding: 20px; }
        .sidebar a { color: white; display: block; padding: 5px 0; }
        .debug-info { background-color: #f0f0f0; padding: 15px; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Test de inclusión del sidebar</h1>
        
        <div class="row">
            <div class="col-md-3">
                <h3>Sidebar Original</h3>
                <div class="container-fluid">
                    <div class="row">
                        <?php include "sidebar.php"; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <h3>Menú de Citas Manual</h3>
                <div class="sidebar">
                    <nav>
                        <?php if(hasPermission('manage_appointments')): ?>
                        <a href="Citas.php"><i class="fas fa-calendar-check"></i> Citas</a>
                        <?php else: ?>
                        <p>No tiene permiso manage_appointments</p>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="debug-info">
                    <h3>Variables de sesión:</h3>
                    <pre><?php print_r($_SESSION); ?></pre>
                    
                    <h3>Permisos:</h3>
                    <p>manage_appointments: <?php echo (hasPermission("manage_appointments") ? "SÍ" : "NO"); ?></p>
                    <p>manage_patients: <?php echo (hasPermission("manage_patients") ? "SÍ" : "NO"); ?></p>
                    <p>manage_users: <?php echo (hasPermission("manage_users") ? "SÍ" : "NO"); ?></p>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="debug-info">
                    <h3>Código de sidebar.php:</h3>
                    <pre><?php echo htmlspecialchars(file_get_contents("sidebar.php")); ?></pre>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
?>

