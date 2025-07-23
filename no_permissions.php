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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
