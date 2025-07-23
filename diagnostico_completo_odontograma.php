<?php
// Diagnóstico completo del problema del odontograma en ver_consulta.php
require_once "config.php";

echo "<h1>🔍 Diagnóstico Completo - Odontograma en ver_consulta.php</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;} .info{background:#e8f4f8;padding:10px;border-radius:5px;margin:10px 0;}</style>";

// Paso 1: Verificar que hay consultas con dientes
echo "<h2>📋 Paso 1: Verificar consultas con dientes</h2>";
$sql = "SELECT id, fecha, dientes_seleccionados FROM historial_medico WHERE dientes_seleccionados IS NOT NULL AND dientes_seleccionados != '' ORDER BY id DESC LIMIT 3";
$stmt = $conn->prepare($sql);
$stmt->execute();
$consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($consultas) {
    echo "<div class='info'>";
    echo "<p class='ok'>✅ Encontradas " . count($consultas) . " consultas con dientes:</p>";
    foreach ($consultas as $c) {
        echo "<p>• ID: {$c['id']} | Fecha: {$c['fecha']} | Dientes: <strong>{$c['dientes_seleccionados']}</strong></p>";
    }
    echo "</div>";
    $test_id = $consultas[0]['id'];
} else {
    echo "<p class='error'>❌ No hay consultas con dientes seleccionados</p>";
    exit;
}

// Paso 2: Verificar que el archivo odontograma_svg.php existe y funciona
echo "<h2>🗂️ Paso 2: Verificar archivo odontograma_svg.php</h2>";
$odontograma_path = __DIR__ . '/odontograma_svg.php';
if (file_exists($odontograma_path)) {
    echo "<p class='ok'>✅ Archivo odontograma_svg.php existe</p>";
    
    // Probar carga del odontograma
    ob_start();
    try {
        include $odontograma_path;
        $content = ob_get_contents();
        ob_end_clean();
        
        if (strlen($content) > 1000) {
            echo "<p class='ok'>✅ Odontograma se carga correctamente (" . strlen($content) . " caracteres)</p>";
            if (strpos($content, '<svg') !== false) {
                echo "<p class='ok'>✅ Contiene elemento SVG</p>";
            } else {
                echo "<p class='error'>❌ No contiene elemento SVG</p>";
            }
            if (strpos($content, 'tooth-shape') !== false) {
                echo "<p class='ok'>✅ Contiene clases tooth-shape</p>";
            } else {
                echo "<p class='error'>❌ No contiene clases tooth-shape</p>";
            }
        } else {
            echo "<p class='error'>❌ Contenido muy pequeño: " . strlen($content) . " caracteres</p>";
            echo "<pre>" . htmlspecialchars(substr($content, 0, 500)) . "</pre>";
        }
    } catch (Exception $e) {
        ob_end_clean();
        echo "<p class='error'>❌ Error al cargar: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p class='error'>❌ Archivo odontograma_svg.php NO existe en: $odontograma_path</p>";
}

// Paso 3: Probar AJAX simulado
echo "<h2>🔄 Paso 3: Simulación de llamada AJAX</h2>";
echo "<div id='ajax-test'>";
echo "<button onclick='testAjax()' style='padding:10px; background:#007bff; color:white; border:none; border-radius:5px;'>🧪 Probar AJAX</button>";
echo "<div id='ajax-result' style='margin-top:10px; padding:10px; border:1px solid #ddd; min-height:50px;'>Presiona el botón para probar</div>";
echo "</div>";

// Paso 4: Ver consulta específica con diagnóstico
echo "<h2>👁️ Paso 4: Simulación de ver_consulta.php</h2>";
$sql = "SELECT h.*, p.nombre, p.apellido FROM historial_medico h JOIN pacientes p ON h.paciente_id = p.id WHERE h.id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$test_id]);
$consulta = $stmt->fetch(PDO::FETCH_ASSOC);

if ($consulta) {
    echo "<div class='info'>";
    echo "<h3>Datos de consulta ID: $test_id</h3>";
    echo "<p><strong>Paciente:</strong> {$consulta['nombre']} {$consulta['apellido']}</p>";
    echo "<p><strong>Dientes:</strong> '{$consulta['dientes_seleccionados']}'</p>";
    echo "<p><strong>¿Dientes vacío?:</strong> " . (empty($consulta['dientes_seleccionados']) ? 'SÍ' : 'NO') . "</p>";
    echo "<p><strong>Condición PHP (!empty):</strong> " . (!empty($consulta['dientes_seleccionados']) ? 'VERDADERO' : 'FALSO') . "</p>";
    echo "</div>";
    
    if (!empty($consulta['dientes_seleccionados'])) {
        echo "<div style='background:#d4edda; padding:15px; border-radius:5px; margin:10px 0;'>";
        echo "<h4>✅ La condición PHP se cumple - El odontograma DEBERÍA mostrarse</h4>";
        echo "<p>El problema no está en la lógica PHP, sino posiblemente en:</p>";
        echo "<ul>";
        echo "<li>❌ JavaScript no se ejecuta</li>";
        echo "<li>❌ AJAX falla</li>";
        echo "<li>❌ Problema de permisos/autenticación</li>";
        echo "<li>❌ Error en la consola del navegador</li>";
        echo "</ul>";
        echo "</div>";
        
        // Mostrar el HTML que se generaría
        echo "<h3>📄 HTML que se generaría en ver_consulta.php:</h3>";
        echo "<textarea readonly style='width:100%; height:200px; font-family:monospace; font-size:12px;'>";
        echo "<?php if (!empty(\$consulta['dientes_seleccionados'])): ?>\n";
        echo "<div class=\"row mt-4\">\n";
        echo "    <div class=\"col-12 consultation-detail\">\n";
        echo "        <h3>Odontograma - Dientes Tratados</h3>\n";
        echo "        <div class=\"alert alert-info mb-3\">\n";
        echo "            <strong>Dientes seleccionados:</strong> {$consulta['dientes_seleccionados']}\n";
        echo "        </div>\n";
        echo "        <div id=\"odontograma-consulta-container\" class=\"mb-3\">\n";
        echo "            <!-- Aquí se cargaría el odontograma vía AJAX -->\n";
        echo "        </div>\n";
        echo "    </div>\n";
        echo "</div>\n";
        echo "<?php endif; ?>";
        echo "</textarea>";
    } else {
        echo "<div style='background:#f8d7da; padding:15px; border-radius:5px; margin:10px 0;'>";
        echo "<h4>❌ La condición PHP NO se cumple - El odontograma NO se mostrará</h4>";
        echo "<p>El campo dientes_seleccionados está vacío o es NULL</p>";
        echo "</div>";
    }
}

// Paso 5: Enlaces para probar
echo "<h2>🔗 Paso 5: Enlaces de prueba</h2>";
echo "<div class='info'>";
echo "<p><strong>Para probar directamente:</strong></p>";
echo "<ul>";
echo "<li><a href='/odontograma_svg.php' target='_blank'>📄 Ver odontograma_svg.php directo</a></li>";
echo "<li><a href='/test_ver_consulta_sin_auth.php?id=$test_id' target='_blank'>🧪 Ver consulta SIN autenticación</a></li>";
echo "<li><a href='/test_ajax_odontograma.php' target='_blank'>🔄 Test AJAX paso a paso</a></li>";
echo "<li><a href='/ver_consulta.php?id=$test_id' target='_blank'>👁️ Ver consulta ORIGINAL (requiere login)</a></li>";
echo "</ul>";
echo "</div>";

echo "<h2>💡 Recomendaciones:</h2>";
echo "<div class='info'>";
echo "<ol>";
echo "<li><strong>Primero:</strong> Abre la consola del navegador (F12) y ve a la pestaña 'Console'</li>";
echo "<li><strong>Segundo:</strong> Accede a ver_consulta.php?id=$test_id y busca errores en la consola</li>";
echo "<li><strong>Tercero:</strong> Si ves errores de AJAX o JavaScript, ese es el problema</li>";
echo "<li><strong>Cuarto:</strong> Si no ves la sección del odontograma en el HTML, verifica que estás logueado</li>";
echo "</ol>";
echo "</div>";
?>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
function testAjax() {
    $('#ajax-result').html('🔄 Probando AJAX...');
    
    $.ajax({
        url: 'odontograma_svg.php',
        type: 'GET',
        success: function(data) {
            if (data && data.length > 100) {
                $('#ajax-result').html(
                    '<p style="color:green;">✅ AJAX funciona correctamente</p>' +
                    '<p>Tamaño: ' + data.length + ' caracteres</p>' +
                    '<details><summary>Ver primeros 300 caracteres</summary><pre>' + 
                    data.substring(0, 300) + '...</pre></details>'
                );
            } else {
                $('#ajax-result').html('<p style="color:red;">❌ AJAX retorna contenido vacío</p>');
            }
        },
        error: function(xhr, status, error) {
            $('#ajax-result').html(
                '<p style="color:red;">❌ Error AJAX: ' + status + '</p>' +
                '<p>Error: ' + error + '</p>' +
                '<p>Status Code: ' + xhr.status + '</p>'
            );
        }
    });
}
</script>
