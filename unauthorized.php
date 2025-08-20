<?php
require_once 'session_config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso No Autorizado - Consultorio Médico</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        body { padding: 40px; }
        .error-template { padding: 40px 15px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="error-template">
                    <h1>Oops!</h1>
                    <h2>Acceso No Autorizado</h2>
                    <div class="error-details mb-3">
                        Lo sentimos, no tienes permiso para acceder a esta página.
                    </div>
                    <div class="error-actions">
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-home"></i> Volver al Inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
