<?php
require_once 'session_config.php';
session_start();

// Simular usuario loggeado para prueba
if (!isset($_SESSION['loggedin'])) {
    $_SESSION['loggedin'] = true;
    $_SESSION['username'] = 'test_user';
    $_SESSION['id'] = 1;
    $_SESSION['nombre'] = 'Usuario de Prueba';
    $_SESSION['rol'] = 'admin';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test Logout Mejorado</title>
    <link rel="stylesheet" href="/assets/libs/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/libs/fontawesome.min.css">
    <style>
        body { padding: 20px; }
        .test-section { 
            background: #f8f9fa; 
            padding: 20px; 
            border-radius: 8px; 
            margin: 20px 0; 
            border-left: 4px solid #007bff;
        }
        .sidebar-demo {
            background: #343a40;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .sidebar-demo a {
            color: white;
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin: 5px 0;
        }
        .sidebar-demo a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            text-decoration: none;
            color: white;
        }
        .session-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-bug"></i> Test de Logout Mejorado</h1>
        
        <div class="session-info">
            <h4><i class="fas fa-user"></i> Información de Sesión Actual</h4>
            <p><strong>Estado:</strong> <?= isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? 'Conectado' : 'Desconectado' ?></p>
            <p><strong>Usuario:</strong> <?= $_SESSION['username'] ?? 'N/A' ?></p>
            <p><strong>ID de Sesión:</strong> <?= session_id() ?></p>
            <p><strong>Nombre:</strong> <?= $_SESSION['nombre'] ?? 'N/A' ?></p>
        </div>

        <div class="test-section">
            <h3><i class="fas fa-clipboard-check"></i> Tests de Logout</h3>
            <div class="row">
                <div class="col-md-6">
                    <h5>1. Logout Directo</h5>
                    <a href="logout.php" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout Directo
                    </a>
                    <small class="text-muted d-block mt-2">Redirige directamente a logout.php</small>
                </div>
                
                <div class="col-md-6">
                    <h5>2. Logout con Confirmación</h5>
                    <a href="logout.php" onclick="return confirm('¿Cerrar sesión?')" class="btn btn-warning">
                        <i class="fas fa-question-circle"></i> Logout con Confirmación
                    </a>
                    <small class="text-muted d-block mt-2">Pide confirmación antes de cerrar</small>
                </div>
            </div>
        </div>

        <div class="test-section">
            <h3><i class="fas fa-bars"></i> Simulación del Sidebar</h3>
            <div class="sidebar-demo">
                <h5 style="color: white; margin-bottom: 20px;">
                    <i class="fas fa-hospital"></i> Menú del Sistema
                </h5>
                <a href="index.php"><i class="fas fa-home"></i> Inicio</a>
                <a href="pacientes.php"><i class="fas fa-users"></i> Pacientes</a>
                <a href="turnos.php"><i class="fas fa-calendar-alt"></i> Turnos</a>
                <a href="configuracion.php"><i class="fas fa-cogs"></i> Configuración</a>
                <hr style="border-color: rgba(255,255,255,0.3);">
                <a href="logout.php" onclick="return confirmarLogout();" class="text-warning">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>

        <div class="test-section">
            <h3><i class="fas fa-code"></i> Tests de JavaScript</h3>
            <div class="row">
                <div class="col-md-4">
                    <button onclick="testLogoutJS()" class="btn btn-info">
                        <i class="fas fa-code"></i> Test JS Logout
                    </button>
                </div>
                <div class="col-md-4">
                    <button onclick="testSessionInfo()" class="btn btn-secondary">
                        <i class="fas fa-info"></i> Info de Sesión
                    </button>
                </div>
                <div class="col-md-4">
                    <button onclick="testRedirect()" class="btn btn-success">
                        <i class="fas fa-external-link-alt"></i> Test Redirect
                    </button>
                </div>
            </div>
        </div>

        <div class="test-section">
            <h3><i class="fas fa-link"></i> Links de Navegación</h3>
            <a href="index.php" class="btn btn-outline-primary mr-2">
                <i class="fas fa-home"></i> Ir a Index
            </a>
            <a href="login.php" class="btn btn-outline-secondary mr-2">
                <i class="fas fa-sign-in-alt"></i> Ir a Login
            </a>
            <button onclick="location.reload()" class="btn btn-outline-info">
                <i class="fas fa-sync"></i> Recargar Página
            </button>
        </div>
    </div>

    <script src="/assets/libs/jquery-3.6.0.min.js"></script>
    <script src="/assets/libs/bootstrap.bundle.min.js"></script>
    
    <script>
        // Función de confirmación para logout (igual que en sidebar.php)
        function confirmarLogout() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                console.log('Cerrando sesión del usuario...');
                window.location.href = 'logout.php';
                return false;
            }
            return false;
        }

        // Test de logout mediante JavaScript
        function testLogoutJS() {
            console.log('Iniciando logout via JavaScript...');
            if (confirm('¿Ejecutar logout via JavaScript?')) {
                window.location.href = 'logout.php';
            }
        }

        // Mostrar información de sesión
        function testSessionInfo() {
            const info = `
Session ID: <?= session_id() ?>
Usuario: <?= $_SESSION['username'] ?? 'N/A' ?>
Estado: <?= isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? 'Conectado' : 'Desconectado' ?>
URL Actual: ${window.location.href}
Referrer: ${document.referrer || 'Ninguno'}
            `;
            alert(info);
        }

        // Test de redirección
        function testRedirect() {
            console.log('Testing redirect functionality...');
            const url = prompt('Ingrese URL para redireccionar:', 'logout.php');
            if (url && url.trim()) {
                console.log('Redirecting to:', url);
                window.location.href = url.trim();
            }
        }

        // Log de eventos de navegación
        window.addEventListener('beforeunload', function(e) {
            console.log('Página siendo descargada...');
        });

        // Log de clics en enlaces
        document.addEventListener('click', function(e) {
            if (e.target.closest('a')) {
                const link = e.target.closest('a');
                console.log('Click en enlace:', link.href || 'sin href');
            }
        });

        console.log('Test de logout inicializado correctamente');
    </script>
</body>
</html>
