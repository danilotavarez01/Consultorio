<?php
// Test del modo oscuro - no requiere login para pruebas
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Modo Oscuro - Consultorio</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
        .test-section {
            margin-bottom: 2rem;
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            background-color: var(--bg-card);
        }
        .color-test {
            padding: 0.5rem;
            margin: 0.25rem 0;
            border-radius: 0.25rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar básico para test -->
            <div class="col-md-2" style="background-color: var(--bg-sidebar); min-height: 100vh; padding-top: 20px;">
                <h5 class="text-white text-center mb-4">Test Sidebar</h5>
                <div class="nav-dark">
                    <a href="#" class="d-block text-white p-2"><i class="fas fa-home"></i> Test 1</a>
                    <a href="#" class="d-block text-white p-2"><i class="fas fa-users"></i> Test 2</a>
                    <a href="#" class="d-block text-white p-2"><i class="fas fa-cog"></i> Test 3</a>
                </div>
            </div>
            
            <!-- Contenido principal -->
            <div class="col-md-10" style="padding: 2rem; background-color: var(--bg-primary);">
                <h1 style="color: var(--text-primary);">Test del Modo Oscuro</h1>
                <p style="color: var(--text-secondary);">Esta página prueba que todos los elementos respondan correctamente al cambio de tema.</p>
                
                <!-- Test de colores -->
                <div class="test-section">
                    <h3 style="color: var(--text-primary);">Test de Colores</h3>
                    <div class="color-test" style="background-color: var(--bg-primary); color: var(--text-primary); border: 1px solid var(--border-color);">
                        Fondo primario con texto primario
                    </div>
                    <div class="color-test" style="background-color: var(--bg-secondary); color: var(--text-secondary);">
                        Fondo secundario con texto secundario
                    </div>
                    <div class="color-test" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
                        Fondo de tarjeta con texto primario
                    </div>
                </div>
                
                <!-- Test de tarjetas Bootstrap -->
                <div class="test-section">
                    <h3 style="color: var(--text-primary);">Test de Tarjetas Bootstrap</h3>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">Tarjeta de Prueba</div>
                                <div class="card-body">
                                    <h5 class="card-title">Título de Tarjeta</h5>
                                    <p class="card-text">Este es un texto de prueba en una tarjeta Bootstrap.</p>
                                    <a href="#" class="btn btn-primary">Botón Primario</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">Otra Tarjeta</div>
                                <div class="card-body">
                                    <h5 class="card-title">Segundo Título</h5>
                                    <p class="card-text">Otro texto de prueba para verificar la consistencia.</p>
                                    <a href="#" class="btn btn-success">Botón Éxito</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Test de formularios -->
                <div class="test-section">
                    <h3 style="color: var(--text-primary);">Test de Formularios</h3>
                    <form>
                        <div class="form-group">
                            <label for="testInput">Campo de texto:</label>
                            <input type="text" class="form-control" id="testInput" placeholder="Escribe algo aquí">
                        </div>
                        <div class="form-group">
                            <label for="testSelect">Select:</label>
                            <select class="form-control" id="testSelect">
                                <option>Opción 1</option>
                                <option>Opción 2</option>
                                <option>Opción 3</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="testTextarea">Textarea:</label>
                            <textarea class="form-control" id="testTextarea" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Enviar</button>
                        <button type="button" class="btn btn-secondary">Cancelar</button>
                    </form>
                </div>
                
                <!-- Test de tabla -->
                <div class="test-section">
                    <h3 style="color: var(--text-primary);">Test de Tabla</h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Juan Pérez</td>
                                <td>juan@email.com</td>
                                <td><span class="badge badge-success">Activo</span></td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>María García</td>
                                <td>maria@email.com</td>
                                <td><span class="badge badge-warning">Pendiente</span></td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Carlos López</td>
                                <td>carlos@email.com</td>
                                <td><span class="badge badge-danger">Inactivo</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Test de alertas -->
                <div class="test-section">
                    <h3 style="color: var(--text-primary);">Test de Alertas</h3>
                    <div class="alert alert-success">¡Éxito! Esta es una alerta de éxito.</div>
                    <div class="alert alert-warning">¡Atención! Esta es una alerta de advertencia.</div>
                    <div class="alert alert-danger">¡Error! Esta es una alerta de error.</div>
                    <div class="alert alert-info">¡Información! Esta es una alerta informativa.</div>
                </div>
                
                <!-- Test de estados del toggle -->
                <div class="test-section">
                    <h3 style="color: var(--text-primary);">Estado Actual del Tema</h3>
                    <div id="theme-status" style="padding: 1rem; background-color: var(--bg-secondary); border-radius: 0.5rem;">
                        <strong>Tema actual:</strong> <span id="current-theme">Detectando...</span><br>
                        <strong>Atributo data-theme:</strong> <span id="data-theme-attr">Detectando...</span><br>
                        <strong>Clase body:</strong> <span id="body-class">Detectando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="js/theme-manager.js"></script>
    
    <script>
        // Actualizar información del estado del tema
        function updateThemeStatus() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const bodyClasses = document.body.className;
            
            document.getElementById('current-theme').textContent = currentTheme;
            document.getElementById('data-theme-attr').textContent = currentTheme;
            document.getElementById('body-class').textContent = bodyClasses || 'Sin clases';
        }
        
        // Actualizar al cargar y cuando cambie el tema
        document.addEventListener('DOMContentLoaded', updateThemeStatus);
        window.addEventListener('themeChanged', updateThemeStatus);
        
        // También actualizar cada 500ms por si hay cambios
        setInterval(updateThemeStatus, 500);
    </script>
</body>
</html>
