<?php
// LIBRERÍAS LOCALES OPTIMIZADAS - Generado automáticamente
function incluir_libs_locales() {
    $base_url = "/Consultorio2/assets/libs/";
    echo '
    <!-- CSS Locales -->
    <link href="' . $base_url . 'bootstrap.min.css" rel="stylesheet">
    <link href="' . $base_url . 'fontawesome.min.css" rel="stylesheet">
    
    <!-- JS Locales -->
    <script src="' . $base_url . 'jquery-3.6.0.min.js"></script>
    <script src="' . $base_url . 'bootstrap.bundle.min.js"></script>
    ';
}

function incluir_libs_fallback() {
    echo '
    <!-- CDN Fallback -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    ';
}

// Función para verificar si las librerías locales existen
function verificar_libs_locales() {
    $libs = [
        'assets/libs/jquery-3.6.0.min.js',
        'assets/libs/bootstrap.min.css',
        'assets/libs/bootstrap.bundle.min.js',
        'assets/libs/fontawesome.min.css'
    ];
    
    foreach ($libs as $lib) {
        if (!file_exists($lib)) {
            return false;
        }
    }
    return true;
}

// Función principal para incluir librerías
function incluir_libs_optimizadas() {
    if (verificar_libs_locales()) {
        incluir_libs_locales();
    } else {
        incluir_libs_fallback();
    }
}
?>
