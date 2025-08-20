<?php
// Test específico para los colores del dashboard
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Dashboard - Modo Oscuro</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
        /* Estilos específicos del index.php para el dashboard */
        body {
            background-color: var(--bg-primary) !important;
            color: var(--text-primary) !important;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        .content { 
            padding: 20px; 
            background-color: var(--bg-primary) !important;
            color: var(--text-primary) !important;
        }
        
        /* Tarjetas del Dashboard - Modo Oscuro Compatible */
        .dashboard-card.bg-primary {
            background: linear-gradient(135deg, #007bff, #0056b3) !important;
        }
        
        .dashboard-card.bg-success {
            background: linear-gradient(135deg, #28a745, #1e7e34) !important;
        }
        
        .dashboard-card.bg-warning {
            background: linear-gradient(135deg, #ffc107, #e0a800) !important;
        }
        
        .dashboard-card.bg-info {
            background: linear-gradient(135deg, #17a2b8, #138496) !important;
        }
        
        /* En modo oscuro, ajustar los colores de las tarjetas */
        [data-theme="dark"] .dashboard-card.bg-primary {
            background: linear-gradient(135deg, #0d6efd, #084298) !important;
        }
        
        [data-theme="dark"] .dashboard-card.bg-success {
            background: linear-gradient(135deg, #198754, #146c43) !important;
        }
        
        [data-theme="dark"] .dashboard-card.bg-warning {
            background: linear-gradient(135deg, #fd7e14, #d63384) !important;
            color: #fff !important;
        }
        
        [data-theme="dark"] .dashboard-card.bg-info {
            background: linear-gradient(135deg, #0dcaf0, #087990) !important;
            color: #fff !important;
        }
        
        /* Asegurar que el texto sea legible en las tarjetas */
        .dashboard-card {
            transition: all 0.3s ease;
            box-shadow: var(--shadow-lg);
        }
        
        .dashboard-card .card-header {
            background: rgba(255, 255, 255, 0.1) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: #fff !important;
        }
        
        .dashboard-card .card-body {
            color: #fff !important;
        }
        
        .dashboard-card .card-title {
            color: #fff !important;
            font-weight: bold;
        }
        
        .dashboard-card .card-text {
            color: rgba(255, 255, 255, 0.9) !important;
        }
        
        .dashboard-card .card-text a {
            color: #fff !important;
            text-decoration: none;
            transition: opacity 0.2s ease;
        }
        
        .dashboard-card .card-text a:hover {
            opacity: 0.8;
            text-decoration: underline;
        }
        
        /* Efectos hover para las tarjetas */
        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        
        .card-compact .card-header {
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
            font-weight: bold;
        }
        .card-compact .card-body {
            padding: 0.6rem;
        }
        .card-compact h5.card-title {
            font-size: 1rem;
            margin-bottom: 0.4rem;
        }
        .card-compact p.card-text {
            font-size: 0.8rem;
            margin-bottom: 0.2rem;
        }
        .dashboard-card {
            margin-bottom: 0.75rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar básico -->
            <div class="col-md-2" style="background-color: var(--bg-sidebar); min-height: 100vh; padding-top: 20px;">
                <h5 class="text-white text-center mb-4">Dashboard Test</h5>
                <div class="nav-dark">
                    <a href="index.php" class="d-block text-white p-2"><i class="fas fa-home"></i> Volver al Inicio</a>
                    <a href="test_modo_oscuro.php" class="d-block text-white p-2"><i class="fas fa-test-tube"></i> Test Completo</a>
                </div>
            </div>
            
            <!-- Contenido principal -->
            <div class="col-md-10 content">
                <h1 style="color: var(--text-primary);">Test de Dashboard - Colores en Modo Oscuro</h1>
                <p style="color: var(--text-secondary);">Verifica que las tarjetas del dashboard se vean correctamente en ambos modos.</p>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <strong>Instrucciones:</strong> 
                    Cambia entre modo claro y oscuro usando el toggle del header para ver los diferentes estilos de las tarjetas.
                </div>
                
                <!-- Dashboard Cards - Exactamente como en index.php -->
                <h3 style="color: var(--text-primary); margin-top: 2rem;">Tarjetas del Dashboard</h3>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary dashboard-card card-compact">
                            <div class="card-header">Turnos de Hoy</div>
                            <div class="card-body">
                                <h5 class="card-title">15 turnos</h5>
                                <p class="card-text">
                                    <a href="#" class="text-white">Ver turnos <i class="fas fa-arrow-right"></i></a>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-white bg-success dashboard-card card-compact">
                            <div class="card-header">Total Pacientes</div>
                            <div class="card-body">
                                <h5 class="card-title">247 pacientes</h5>
                                <p class="card-text">
                                    <a href="#" class="text-white">Ver pacientes <i class="fas fa-arrow-right"></i></a>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-white bg-warning dashboard-card card-compact">
                            <div class="card-header">Citas de Hoy</div>
                            <div class="card-body">
                                <h5 class="card-title">8 citas</h5>
                                <p class="card-text">
                                    <a href="#" class="text-white">Ver citas <i class="fas fa-arrow-right"></i></a>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-white bg-info dashboard-card card-compact">
                            <div class="card-header">Recetas del Mes</div>
                            <div class="card-body">
                                <h5 class="card-title">132 recetas</h5>
                                <p class="card-text">
                                    <a href="#" class="text-white">Ver recetas <i class="fas fa-arrow-right"></i></a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Comparación con tarjetas normales -->
                <h3 style="color: var(--text-primary); margin-top: 2rem;">Comparación con Tarjetas Normales</h3>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-header">Tarjeta Normal</div>
                            <div class="card-body">
                                <h5 class="card-title">Título Normal</h5>
                                <p class="card-text">Esta es una tarjeta normal que responde al modo oscuro.</p>
                                <a href="#" class="btn btn-primary">Acción</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-header">Tarjeta Clara</div>
                            <div class="card-body">
                                <h5 class="card-title">Título Claro</h5>
                                <p class="card-text">Tarjeta con fondo claro.</p>
                                <a href="#" class="btn btn-secondary">Acción</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-dark text-white">
                            <div class="card-header">Tarjeta Oscura</div>
                            <div class="card-body">
                                <h5 class="card-title">Título Oscuro</h5>
                                <p class="card-text">Tarjeta con fondo oscuro fijo.</p>
                                <a href="#" class="btn btn-light">Acción</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Estado actual del tema -->
                <div class="mt-4 p-3" style="background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    <h4 style="color: var(--text-primary);">Estado Actual del Tema</h4>
                    <div id="theme-status">
                        <strong>Tema actual:</strong> <span id="current-theme">Detectando...</span><br>
                        <strong>Atributo data-theme:</strong> <span id="data-theme-attr">Detectando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="js/theme-manager.js"></script>
    
    <script>
        function updateThemeStatus() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            document.getElementById('current-theme').textContent = currentTheme;
            document.getElementById('data-theme-attr').textContent = currentTheme;
        }
        
        document.addEventListener('DOMContentLoaded', updateThemeStatus);
        window.addEventListener('themeChanged', updateThemeStatus);
        setInterval(updateThemeStatus, 1000);
    </script>
</body>
</html>

