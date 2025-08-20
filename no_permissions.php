<?php
require_once 'session_config.php';
session_start();

// Verificar si el usuario está logueado
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sin Permisos - Consultorio Médico</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <style>
        body { 
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .no-permission-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .icon-warning {
            font-size: 64px;
            color: #ffc107;
            margin-bottom: 20px;
        }
        .btn-logout {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-permission-container">
            <i class="fas fa-exclamation-triangle icon-warning"></i>
            <h2 class="mb-4">Sin Permisos</h2>
            <div class="alert alert-warning">
                <p>No tienes permisos asignados para utilizar el sistema.</p>
                <p>Por favor, contacta al administrador para que te asigne los permisos necesarios.</p>
                <p>Usuario actual: <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong></p>
            </div>
            <a href="logout.php" class="btn btn-primary btn-logout">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>

