<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test Logout Debug</title>
    <link rel="stylesheet" href="/assets/libs/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/libs/fontawesome.min.css">
    <style>
        body { padding: 20px; }
        .debug-info { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 10px 0; 
            font-family: monospace;
        }
    </style>
</head>
<body>
    <h1>Test de Debug para el Menu Logout</h1>
    
    <div class="debug-info">
        <strong>URL Actual:</strong> <span id="current-url"></span><br>
        <strong>Referrer:</strong> <span id="referrer"></span><br>
        <strong>User Agent:</strong> <span id="user-agent"></span>
    </div>

    <!-- Replica del sidebar -->
    <div class="col-md-2 sidebar sidebar-dark" style="background: #343a40; min-height: 300px; padding: 20px;">
        <nav class="nav-dark">
            <a href="index.php" style="color: white; display: block; padding: 10px;"><i class="fas fa-home"></i> Inicio</a>
            <a href="pacientes.php" style="color: white; display: block; padding: 10px;"><i class="fas fa-users"></i> Pacientes</a>
            <a href="#" id="test-logout-1" style="color: white; display: block; padding: 10px;"><i class="fas fa-sign-out-alt"></i> Test Logout 1</a>
            <a href="logout.php" id="test-logout-2" style="color: white; display: block; padding: 10px;"><i class="fas fa-sign-out-alt"></i> Test Logout 2</a>
            <a href="logout.php" onclick="return confirmLogout();" id="test-logout-3" style="color: white; display: block; padding: 10px;"><i class="fas fa-sign-out-alt"></i> Test Logout 3</a>
        </nav>
    </div>

    <div class="debug-info">
        <h4>Log de eventos:</h4>
        <div id="event-log"></div>
    </div>

    <script src="/assets/libs/jquery-3.6.0.min.js"></script>
    <script>
        // Función para log de eventos
        function logEvent(message) {
            const log = document.getElementById('event-log');
            const timestamp = new Date().toLocaleTimeString();
            log.innerHTML += `<div>[${timestamp}] ${message}</div>`;
        }

        // Información de debug inicial
        document.getElementById('current-url').textContent = window.location.href;
        document.getElementById('referrer').textContent = document.referrer || 'No referrer';
        document.getElementById('user-agent').textContent = navigator.userAgent;

        // Interceptar todos los clics en el documento
        document.addEventListener('click', function(e) {
            logEvent(`Click detectado en: ${e.target.tagName} - ID: ${e.target.id || 'sin ID'} - Href: ${e.target.href || 'sin href'}`);
            
            if (e.target.closest('a')) {
                const link = e.target.closest('a');
                logEvent(`Link clickeado: ${link.href || 'sin href'} - ID: ${link.id || 'sin ID'}`);
            }
        });

        // Test específico para logout
        function confirmLogout() {
            logEvent('confirmLogout() ejecutada');
            if (confirm('¿Estás seguro de cerrar sesión?')) {
                logEvent('Confirmación aceptada - procediendo con logout');
                return true;
            } else {
                logEvent('Confirmación cancelada');
                return false;
            }
        }

        // Test logout 1
        document.getElementById('test-logout-1').addEventListener('click', function(e) {
            e.preventDefault();
            logEvent('Test Logout 1 clickeado - redirigiendo a logout.php');
            window.location.href = 'logout.php';
        });

        // Verificar si jQuery está interfiriendo
        $(document).on('click', 'a[href="logout.php"]', function(e) {
            logEvent('jQuery detectó click en logout.php');
        });

        logEvent('Debug script inicializado');
    </script>
</body>
</html>
