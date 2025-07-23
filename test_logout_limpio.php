<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test Logout Limpio</title>
    <link rel="stylesheet" href="/assets/libs/bootstrap.min.css">
    <style>
        body { 
            padding: 20px; 
            background: #f8f9fa; 
        }
        .test-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">🔒 Test de Logout Limpio</h1>
        
        <div class="test-card">
            <h3>✅ Logout Simplificado</h3>
            <p>Este logout NO mostrará mensajes de debug y redirigirá directamente al login.</p>
            
            <div class="alert alert-info">
                <strong>¿Qué hace el nuevo logout?</strong>
                <ul class="mb-0 mt-2">
                    <li>Desactiva completamente los errores de PHP</li>
                    <li>Limpia cualquier buffer de salida</li>
                    <li>Destruye la sesión de forma simple</li>
                    <li>Redirige inmediatamente a login.php</li>
                    <li>NO muestra mensajes de debug</li>
                </ul>
            </div>
            
            <div class="text-center mt-4">
                <a href="logout.php" class="btn btn-danger btn-lg">
                    <i class="fas fa-sign-out-alt"></i> Probar Logout Limpio
                </a>
            </div>
            
            <hr class="my-4">
            
            <h4>🔍 Información Actual</h4>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Sesión activa:</strong> 
                        <?php 
                        session_start();
                        echo isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? 'Sí' : 'No'; 
                        ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Usuario:</strong> 
                        <?php echo $_SESSION['username'] ?? 'Ninguno'; ?>
                    </p>
                </div>
            </div>
            
            <div class="alert alert-success mt-3">
                <strong>📋 Instrucciones:</strong><br>
                1. Haz clic en el botón "Probar Logout Limpio"<br>
                2. Deberías ser redirigido inmediatamente al login<br>
                3. NO deberías ver ningún mensaje de debug<br>
                4. Solo deberías ver el mensaje verde de "Sesión cerrada exitosamente"
            </div>
        </div>
        
        <div class="test-card">
            <h3>🔄 Otras Opciones de Test</h3>
            <div class="btn-group d-block text-center">
                <a href="login.php" class="btn btn-outline-primary mr-2">
                    <i class="fas fa-sign-in-alt"></i> Ir a Login
                </a>
                <a href="index.php" class="btn btn-outline-secondary mr-2">
                    <i class="fas fa-home"></i> Ir a Inicio
                </a>
                <button onclick="location.reload()" class="btn btn-outline-info">
                    <i class="fas fa-sync"></i> Recargar
                </button>
            </div>
        </div>
    </div>
    
    <script src="/assets/libs/jquery-3.6.0.min.js"></script>
    <script src="/assets/libs/bootstrap.bundle.min.js"></script>
</body>
</html>
