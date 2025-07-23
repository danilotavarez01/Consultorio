<?php
/**
 * Template base para páginas del sistema con modo oscuro
 * Incluir este archivo en lugar de hacer los includes manualmente
 */

// Asegurar que la sesión esté configurada correctamente
if(!isset($_SESSION)) {
    require_once 'session_config.php';
    session_start();
}

// Función para renderizar el head de HTML con modo oscuro
function renderHTMLHead($title = "Consultorio Médico", $extraCSS = "", $extraJS = "") {
    echo "<!DOCTYPE html>
<html lang=\"es\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>{$title}</title>
    <link rel=\"stylesheet\" href=\"https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css\">
    <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css\">
    <link rel=\"stylesheet\" href=\"css/dark-mode.css\">
    {$extraCSS}
</head>
<body>";
}

// Función para renderizar el footer con JavaScript necesario
function renderHTMLFooter($extraJS = "") {
    echo "
    <script src=\"https://code.jquery.com/jquery-3.6.0.min.js\"></script>
    <script src=\"https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js\"></script>
    <script src=\"https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js\"></script>
    <script src=\"js/theme-manager.js\"></script>
    {$extraJS}
</body>
</html>";
}

// Función para renderizar el header completo
function renderHeader() {
    include 'includes/header.php';
}

// Función para renderizar el sidebar
function renderSidebar() {
    include 'sidebar.php';
}

// Función para iniciar el contenedor principal con sidebar
function startMainContent($useSidebar = true) {
    if ($useSidebar) {
        echo '<div class="container-fluid">
                <div class="row">';
        renderSidebar();
        echo '<div class="col-md-10 content">';
    } else {
        echo '<div class="container-fluid">
                <div class="content p-4">';
    }
}

// Función para cerrar el contenedor principal
function endMainContent($useSidebar = true) {
    if ($useSidebar) {
        echo '    </div>
                </div>
              </div>';
    } else {
        echo '</div>
            </div>';
    }
}

// Función completa para páginas estándar del sistema
function renderPageStart($title = "Consultorio Médico", $useSidebar = true, $extraCSS = "", $extraJS = "") {
    renderHTMLHead($title, $extraCSS, $extraJS);
    renderHeader();
    startMainContent($useSidebar);
}

function renderPageEnd($useSidebar = true, $extraJS = "") {
    endMainContent($useSidebar);
    renderHTMLFooter($extraJS);
}

// CSS adicional para páginas que usen este template
$templateCSS = "
<style>
.content {
    padding: 20px;
    min-height: calc(100vh - 80px);
}

.page-title {
    color: var(--text-primary);
    border-bottom: 2px solid var(--border-color);
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.action-buttons {
    margin-bottom: 20px;
}

.card-custom {
    background-color: var(--bg-card);
    border-color: var(--border-color);
    box-shadow: var(--shadow);
    margin-bottom: 20px;
}

.form-section-custom {
    background-color: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.btn-back {
    background-color: var(--btn-secondary-bg);
    border-color: var(--btn-secondary-bg);
    color: white;
}

.btn-back:hover {
    opacity: 0.9;
    color: white;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .content {
        padding: 15px;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
}
</style>";

// Hacer el CSS del template disponible globalmente
$GLOBALS['template_css'] = $templateCSS;
?>
