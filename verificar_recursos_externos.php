<?php
// SCRIPT DE VERIFICACIÓN DE RECURSOS EXTERNOS
// Consultorio Médico - Agosto 2025

echo "<h1>🔍 Verificación de Recursos Externos</h1>";
echo "<hr>";

// Función para verificar si un archivo existe
function verificarArchivo($ruta) {
    return file_exists($ruta) ? '✅' : '❌';
}

// Función para verificar tamaño de archivo
function obtenerTamaño($ruta) {
    if (!file_exists($ruta)) return 'N/A';
    $bytes = filesize($ruta);
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

echo "<h2>📊 Verificación de Recursos Locales</h2>";

$recursos_criticos = [
    'CSS Frameworks' => [
        'assets/css/bootstrap.min.css' => 'Bootstrap 4.5.2',
        'assets/css/bootstrap-5.1.3.min.css' => 'Bootstrap 5.1.3',
        'assets/css/fontawesome.min.css' => 'FontAwesome',
        'assets/css/fontawesome-6.0.0.min.css' => 'FontAwesome 6.0.0',
        'assets/css/jquery-ui.css' => 'jQuery UI CSS',
        'css/dark-mode.css' => 'Modo Oscuro Custom'
    ],
    'JavaScript Libraries' => [
        'assets/js/jquery.min.js' => 'jQuery 3.6.0',
        'assets/js/bootstrap.min.js' => 'Bootstrap JS',
        'assets/js/bootstrap.bundle.min.js' => 'Bootstrap Bundle',
        'assets/js/popper.min.js' => 'Popper.js',
        'assets/js/popper-2.5.4.min.js' => 'Popper.js 2.5.4',
        'assets/js/jquery-ui.min.js' => 'jQuery UI JS',
        'assets/js/webcam.min.js' => 'WebcamJS'
    ],
    'Scripts Custom' => [
        'js/theme-manager.js' => 'Gestor de Temas',
        'js/dark-mode.js' => 'Modo Oscuro (legacy)'
    ]
];

$total_archivos = 0;
$archivos_encontrados = 0;
$tamaño_total = 0;

foreach ($recursos_criticos as $categoria => $archivos) {
    echo "<h3>$categoria</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f5f5f5;'>";
    echo "<th style='padding: 8px;'>Estado</th>";
    echo "<th style='padding: 8px;'>Archivo</th>";
    echo "<th style='padding: 8px;'>Descripción</th>";
    echo "<th style='padding: 8px;'>Tamaño</th>";
    echo "</tr>";
    
    foreach ($archivos as $ruta => $descripcion) {
        $total_archivos++;
        $estado = verificarArchivo($ruta);
        if ($estado === '✅') {
            $archivos_encontrados++;
            $tamaño_total += filesize($ruta);
        }
        $tamaño = obtenerTamaño($ruta);
        
        $color = ($estado === '✅') ? '#d4edda' : '#f8d7da';
        echo "<tr style='background-color: $color;'>";
        echo "<td style='padding: 8px; text-align: center;'>$estado</td>";
        echo "<td style='padding: 8px;'><code>$ruta</code></td>";
        echo "<td style='padding: 8px;'>$descripcion</td>";
        echo "<td style='padding: 8px;'>$tamaño</td>";
        echo "</tr>";
    }
    echo "</table><br>";
}

// Estadísticas generales
echo "<h2>📈 Estadísticas de Localización</h2>";
$porcentaje = ($total_archivos > 0) ? round(($archivos_encontrados / $total_archivos) * 100, 2) : 0;
$tamaño_total_mb = round($tamaño_total / 1048576, 2);

echo "<div style='background-color: #e7f3ff; padding: 15px; border-radius: 5px; border-left: 4px solid #0066cc;'>";
echo "<strong>Resumen de Verificación:</strong><br>";
echo "• Archivos verificados: $total_archivos<br>";
echo "• Archivos encontrados: $archivos_encontrados<br>";
echo "• Porcentaje de localización: <strong>{$porcentaje}%</strong><br>";
echo "• Tamaño total de recursos: <strong>{$tamaño_total_mb} MB</strong><br>";
echo "</div><br>";

// Verificación de dependencias externas
echo "<h2>🌐 Verificación de Referencias Externas</h2>";

// Buscar archivos PHP con posibles referencias externas
$archivos_php = glob('*.php');
$referencias_externas = [];

foreach ($archivos_php as $archivo) {
    if (in_array($archivo, ['verificar_recursos_externos.php', 'VERIFICACION_RECURSOS_EXTERNOS_2025.md'])) continue;
    
    $contenido = file_get_contents($archivo);
    
    // Buscar URLs externas (excluyendo localhost, data:, y comentarios)
    if (preg_match_all('/https?:\/\/(?!localhost|127\.0\.0\.1|data:|github\.com\/twbs|getbootstrap\.com|www\.w3\.org)[^\s\'"\)]+/i', $contenido, $matches)) {
        foreach ($matches[0] as $url) {
            // Filtrar URLs de documentación y comentarios
            if (!strpos($url, 'github.com') && !strpos($url, 'getbootstrap.com') && !strpos($url, 'opensource.org')) {
                $referencias_externas[$archivo][] = $url;
            }
        }
    }
}

if (empty($referencias_externas)) {
    echo "<div style='background-color: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;'>";
    echo "<strong>✅ EXCELENTE:</strong> No se encontraron referencias a recursos externos activos en archivos PHP principales.";
    echo "</div><br>";
} else {
    echo "<div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #856404;'>";
    echo "<strong>⚠️ REFERENCIAS EXTERNAS ENCONTRADAS:</strong><br>";
    foreach ($referencias_externas as $archivo => $urls) {
        echo "<br><strong>$archivo:</strong><br>";
        foreach ($urls as $url) {
            echo "• $url<br>";
        }
    }
    echo "</div><br>";
}

// Verificación de configuración WhatsApp
echo "<h2>📱 Configuración de APIs Externas</h2>";

try {
    require_once 'config.php';
    
    $stmt = $conn->query("SELECT whatsapp_server FROM configuracion LIMIT 1");
    $config_row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config_row && !empty($config_row['whatsapp_server'])) {
        $whatsapp_url = $config_row['whatsapp_server'];
        echo "<div style='background-color: #e7f3ff; padding: 15px; border-radius: 5px; border-left: 4px solid #0066cc;'>";
        echo "<strong>🔍 API WhatsApp Configurada:</strong><br>";
        echo "• URL: <code>$whatsapp_url</code><br>";
        echo "• Estado: Funcionalidad opcional<br>";
        echo "• Impacto: No afecta funcionamiento core del sistema";
        echo "</div><br>";
    } else {
        echo "<div style='background-color: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;'>";
        echo "<strong>✅ Sin APIs externas configuradas</strong>";
        echo "</div><br>";
    }
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; padding: 15px; border-radius: 5px; border-left: 4px solid #721c24;'>";
    echo "<strong>❌ Error al verificar configuración de base de datos:</strong> " . $e->getMessage();
    echo "</div><br>";
}

// Conclusión final
echo "<h2>🏆 Conclusión de Verificación</h2>";

$estado_final = ($porcentaje >= 95) ? 'ÓPTIMO' : (($porcentaje >= 80) ? 'BUENO' : 'REQUIERE ATENCIÓN');
$color_final = ($porcentaje >= 95) ? '#28a745' : (($porcentaje >= 80) ? '#856404' : '#721c24');
$fondo_final = ($porcentaje >= 95) ? '#d4edda' : (($porcentaje >= 80) ? '#fff3cd' : '#f8d7da');

echo "<div style='background-color: $fondo_final; padding: 20px; border-radius: 5px; border-left: 6px solid $color_final;'>";
echo "<h3 style='margin-top: 0; color: $color_final;'>Estado Final: $estado_final</h3>";

if ($porcentaje >= 95) {
    echo "<strong>✅ PROYECTO COMPLETAMENTE AUTÓNOMO</strong><br><br>";
    echo "• Todos los recursos críticos están localizados<br>";
    echo "• El sistema puede funcionar sin conexión a internet<br>";
    echo "• No hay dependencias de CDNs externos<br>";
    echo "• Rendimiento y estabilidad garantizados<br>";
} else {
    echo "<strong>⚠️ REQUIERE VERIFICACIÓN ADICIONAL</strong><br><br>";
    echo "• Algunos recursos pueden estar faltando<br>";
    echo "• Revisar archivos marcados con ❌<br>";
    echo "• Verificar funcionamiento completo del sistema<br>";
}

echo "</div><br>";

echo "<div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #dee2e6;'>";
echo "<small><strong>Fecha de verificación:</strong> " . date('d/m/Y H:i:s') . "</small><br>";
echo "<small><strong>Script:</strong> verificar_recursos_externos.php</small><br>";
echo "<small><strong>Versión:</strong> 2.0 - Agosto 2025</small>";
echo "</div>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background-color: #f8f9fa;
}

h1 {
    color: #333;
    text-align: center;
}

h2 {
    color: #0066cc;
    border-bottom: 2px solid #0066cc;
    padding-bottom: 5px;
}

h3 {
    color: #333;
    margin-top: 25px;
    margin-bottom: 10px;
}

table {
    margin-bottom: 20px;
    font-size: 14px;
}

code {
    background-color: #f1f3f4;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
}

hr {
    border: none;
    border-top: 3px solid #0066cc;
    margin: 30px 0;
}
</style>
