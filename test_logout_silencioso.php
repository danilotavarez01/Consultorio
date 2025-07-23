<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test Logout Silencioso</title>
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
        <h1 class="text-center mb-4">🔇 Test de Logout Silencioso</h1>
        
        <div class="test-card">
            <h3>✅ Logout Completamente Silencioso</h3>
            <p>Este logout NO mostrará NINGÚN mensaje y redirigirá directamente al login limpio.</p>
            
            <div class="alert alert-info">
                <strong>¿Qué hace el logout ahora?</strong>
                <ul class="mb-0 mt-2">
                    <li>Destruye la sesión silenciosamente</li>
                    <li>Redirige a login.php SIN parámetros</li>
                    <li>NO muestra mensaje de "Sesión cerrada exitosamente"</li>
                    <li>NO muestra ningún tipo de mensaje</li>
                    <li>Login aparece completamente limpio</li>
                </ul>
            </div>
            
            <div class="text-center mt-4">
                <a href="logout.php" class="btn btn-danger btn-lg">
                    <i class="fas fa-sign-out-alt"></i> Probar Logout Silencioso
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
                <strong>📋 Resultado Esperado:</strong><br>
                1. Haz clic en "Probar Logout Silencioso"<br>
                2. Serás redirigido inmediatamente al login<br>
                3. El login aparecerá completamente limpio<br>
                4. <strong>NO verás NINGÚN mensaje</strong><br>
                5. Solo verás el formulario de login normal
            </div>
        </div>
        
        <div class="test-card">
            <h3>🎯 Antes vs Ahora</h3>
            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-danger">❌ ANTES</h5>
                    <ul>
                        <li>Mensaje: "Sesión cerrada exitosamente"</li>
                        <li>Parámetro: ?logout=success</li>
                        <li>Alert verde visible</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5 class="text-success">✅ AHORA</h5>
                    <ul>
                        <li>Sin mensaje alguno</li>
                        <li>Sin parámetros en URL</li>
                        <li>Login completamente limpio</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <script src="/assets/libs/jquery-3.6.0.min.js"></script>
    <script src="/assets/libs/bootstrap.bundle.min.js"></script>
</body>
</html>
