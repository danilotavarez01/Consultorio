<?php
// Test para verificar que el header cargue el nombre del consultorio correctamente
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - Nombre Consultorio en Header</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f8f9fa;
            --text-primary: #333333;
            --text-secondary: #666666;
            --border-color: #dee2e6;
            --btn-primary-bg: #007bff;
            --shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        
        [data-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --text-primary: #ffffff;
            --text-secondary: #cccccc;
            --border-color: #404040;
            --btn-primary-bg: #0d6efd;
            --shadow: 0 2px 4px rgba(0,0,0,.3);
        }
        
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }
    </style>
</head>
<body>
    <?php 
    // Simular sesión para el test
    session_start();
    $_SESSION['username'] = 'Usuario Test';
    $_SESSION['rol'] = 'admin';
    
    // Incluir el header
    include 'includes/header.php'; 
    ?>
    
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h5>Test - Nombre del Consultorio en Header</h5>
            </div>
            <div class="card-body">
                <p><strong>Resultado:</strong> El header debería mostrar el nombre del consultorio desde la base de datos.</p>
                <p><strong>Nombre obtenido:</strong> <span class="badge badge-primary"><?= htmlspecialchars($nombreConsultorio ?? 'No definido') ?></span></p>
                
                <div class="mt-3">
                    <h6>Información de la función:</h6>
                    <ul>
                        <li>Si existe el campo <code>nombre_consultorio</code> en la tabla <code>configuracion</code>, se mostrará ese valor</li>
                        <li>Si hay algún error o no existe el campo, se mostrará "Consultorio Médico" por defecto</li>
                        <li>La función incluye manejo de errores para evitar problemas si la base de datos no está disponible</li>
                    </ul>
                </div>
                
                <div class="alert alert-info mt-3">
                    <strong>Nota:</strong> Para cambiar el nombre del consultorio, actualiza el campo <code>nombre_consultorio</code> 
                    en la tabla <code>configuracion</code> con <code>id = 1</code>.
                </div>
            </div>
        </div>
    </div>

    <script>
        // Función básica para modo oscuro (solo para el test)
        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            
            const checkbox = document.getElementById('theme-checkbox');
            if (checkbox) {
                checkbox.checked = newTheme === 'dark';
            }
            
            // Actualizar etiquetas
            updateThemeLabels();
        }
        
        function updateThemeLabels() {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            const lightLabel = document.querySelector('.theme-label-light');
            const darkLabel = document.querySelector('.theme-label-dark');
            
            if (lightLabel && darkLabel) {
                if (isDark) {
                    lightLabel.style.display = 'none';
                    darkLabel.style.display = 'inline';
                } else {
                    lightLabel.style.display = 'inline';
                    darkLabel.style.display = 'none';
                }
            }
        }
        
        // Inicializar tema
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            
            const checkbox = document.getElementById('theme-checkbox');
            if (checkbox) {
                checkbox.checked = savedTheme === 'dark';
            }
            
            updateThemeLabels();
        });
    </script>
</body>
</html>
