<?php
// Script de prueba para verificar que odontograma_svg.php funcione correctamente

echo "<h2>🧪 Prueba Directa del Odontograma SVG</h2>";

echo "<h3>1. Verificación de archivos:</h3>";
$archivos = [
    'odontograma_svg.php' => 'Archivo principal del odontograma',
    'ver_consulta.php' => 'Archivo de visualización de consultas',
    'config.php' => 'Configuración de base de datos'
];

foreach ($archivos as $archivo => $descripcion) {
    if (file_exists($archivo)) {
        echo "✅ <strong>$archivo</strong> - $descripcion (existe)<br>";
    } else {
        echo "❌ <strong>$archivo</strong> - $descripcion (NO existe)<br>";
    }
}

echo "<h3>2. Prueba de carga del odontograma:</h3>";

// Intentar incluir el archivo directamente
try {
    echo "<div style='border: 2px solid #007bff; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h4>Contenido de odontograma_svg.php:</h4>";
    
    // Capturar el output
    ob_start();
    include 'odontograma_svg.php';
    $content = ob_get_clean();
    
    if (strlen($content) > 100) {
        echo "<p style='color: green;'>✅ Archivo cargado exitosamente (" . strlen($content) . " caracteres)</p>";
        echo "<p><strong>Primeros 200 caracteres:</strong></p>";
        echo "<code style='background: #f8f9fa; padding: 5px; display: block;'>" . htmlspecialchars(substr($content, 0, 200)) . "...</code>";
        
        // Verificar que contiene elementos clave
        if (strpos($content, 'odontograma') !== false) {
            echo "<p style='color: green;'>✅ Contiene elemento 'odontograma'</p>";
        }
        if (strpos($content, 'tooth-shape') !== false) {
            echo "<p style='color: green;'>✅ Contiene clase 'tooth-shape'</p>";
        }
        if (strpos($content, 'svg') !== false) {
            echo "<p style='color: green;'>✅ Contiene elementos SVG</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Archivo muy pequeño o vacío (" . strlen($content) . " caracteres)</p>";
        echo "<p><strong>Contenido completo:</strong></p>";
        echo "<code style='background: #f8f9fa; padding: 5px; display: block;'>" . htmlspecialchars($content) . "</code>";
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<p style='color: red;'>❌ Error al cargar odontograma_svg.php:</p>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<h3>3. Prueba AJAX simulada:</h3>";
echo "<div id='ajax-test-container' style='border: 2px solid #28a745; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
echo "<button onclick='testAjaxLoad()' class='btn btn-primary'>🚀 Probar Carga AJAX</button>";
echo "<div id='ajax-result' style='margin-top: 10px;'></div>";
echo "</div>";

echo "<h3>4. Enlace directo:</h3>";
echo "<p><a href='odontograma_svg.php' target='_blank' style='padding: 10px 15px; background: #17a2b8; color: white; text-decoration: none; border-radius: 5px;'>🔗 Abrir odontograma_svg.php directamente</a></p>";

echo "<h3>5. Consultas para probar:</h3>";
require_once "config.php";

try {
    $sql = "SELECT id, dientes_seleccionados FROM historial_medico WHERE dientes_seleccionados IS NOT NULL AND dientes_seleccionados != '' LIMIT 3";
    $stmt = $conn->query($sql);
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($consultas) > 0) {
        foreach ($consultas as $consulta) {
            echo "<p>";
            echo "Consulta ID " . $consulta['id'] . " (Dientes: " . htmlspecialchars($consulta['dientes_seleccionados']) . ") - ";
            echo "<a href='ver_consulta.php?id=" . $consulta['id'] . "' target='_blank' style='padding: 5px 10px; background: #007bff; color: white; text-decoration: none; border-radius: 3px;'>👁️ Ver</a>";
            echo "</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ No hay consultas con dientes para probar</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error al obtener consultas: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<script src="assets/js/jquery.min.js"></script>
<script>
function testAjaxLoad() {
    $('#ajax-result').html('<div style="color: blue;">🔄 Probando carga AJAX...</div>');
    
    $.ajax({
        url: 'odontograma_svg.php',
        type: 'GET',
        cache: false,
        timeout: 10000,
        success: function(data, textStatus, xhr) {
            console.log('AJAX Success:', textStatus, data.length);
            $('#ajax-result').html(
                '<div style="color: green;">✅ AJAX exitoso!</div>' +
                '<p><strong>Status:</strong> ' + textStatus + '</p>' +
                '<p><strong>Tamaño:</strong> ' + data.length + ' caracteres</p>' +
                '<p><strong>Contiene SVG:</strong> ' + (data.includes('svg') ? 'Sí' : 'No') + '</p>' +
                '<p><strong>Contiene tooth-shape:</strong> ' + (data.includes('tooth-shape') ? 'Sí' : 'No') + '</p>' +
                '<details style="margin-top: 10px;"><summary>Ver primeros 300 caracteres</summary>' +
                '<code style="background: #f8f9fa; padding: 5px; display: block; white-space: pre-wrap;">' + 
                data.substring(0, 300) + '...</code></details>'
            );
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error, xhr);
            $('#ajax-result').html(
                '<div style="color: red;">❌ Error AJAX!</div>' +
                '<p><strong>Status:</strong> ' + status + '</p>' +
                '<p><strong>Error:</strong> ' + error + '</p>' +
                '<p><strong>Status Code:</strong> ' + xhr.status + '</p>' +
                '<p><strong>Response Text (primeros 200 chars):</strong></p>' +
                '<code style="background: #f8f9fa; padding: 5px; display: block;">' + 
                xhr.responseText.substring(0, 200) + '</code>'
            );
        }
    });
}
</script>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #0056b3; }
h3 { color: #333; }
.btn { padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
.btn:hover { background: #0056b3; }
</style>

