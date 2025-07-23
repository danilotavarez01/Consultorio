<?php
// Diagnóstico del odontograma y comprobación de archivos
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Diagnóstico del Odontograma</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .resultado {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .resultado-ok {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .resultado-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .archivo-contenido {
            background-color: #f8f9fa;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            max-height: 300px;
            overflow: auto;
            white-space: pre-wrap;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Diagnóstico de Integración del Odontograma</h1>
        <hr>
        
        <h3>Verificación de archivos</h3>
        <div id="archivos">
            <?php
            $archivos_verificar = [
                'Dientes2.html',
                'odontograma_inline.php',
                'nueva_consulta.php'
            ];
            
            foreach ($archivos_verificar as $archivo) {
                $ruta_completa = __DIR__ . '/' . $archivo;
                if (file_exists($ruta_completa)) {
                    $tamaño = filesize($ruta_completa);
                    $modificado = date("Y-m-d H:i:s", filemtime($ruta_completa));
                    echo "<div class='resultado resultado-ok'>";
                    echo "<strong>✅ $archivo:</strong> Existe ($tamaño bytes, modificado: $modificado)";
                    echo "</div>";
                } else {
                    echo "<div class='resultado resultado-error'>";
                    echo "<strong>❌ $archivo:</strong> No existe";
                    echo "</div>";
                }
            }
            ?>
        </div>
        
        <h3>Diagnóstico de nueva_consulta.php</h3>
        <div id="analisis-consulta">
            <?php
            $consulta_file = __DIR__ . '/nueva_consulta.php';
            $consulta_content = file_get_contents($consulta_file);
            
            // Verificar si el archivo contiene la función para mostrar el odontograma
            if (strpos($consulta_content, 'mostrarCamposDinamicosYOdontograma') !== false) {
                echo "<div class='resultado resultado-ok'>";
                echo "<strong>✅ Función:</strong> Se encontró mostrarCamposDinamicosYOdontograma()";
                echo "</div>";
            } else {
                echo "<div class='resultado resultado-error'>";
                echo "<strong>❌ Función:</strong> No se encontró la función para mostrar el odontograma";
                echo "</div>";
            }
            
            // Verificar lógica de detección de especialidad odontología
            if (strpos($consulta_content, "especialidad.includes('odonto')") !== false) {
                echo "<div class='resultado resultado-ok'>";
                echo "<strong>✅ Detección:</strong> Se encontró lógica para detectar especialidad odontológica";
                echo "</div>";
            } else {
                echo "<div class='resultado resultado-error'>";
                echo "<strong>❌ Detección:</strong> No se encontró lógica para detectar especialidad odontológica";
                echo "</div>";
            }
            
            // Verificar si contiene el contenedor de campos dinámicos
            if (strpos($consulta_content, 'id="campos_dinamicos"') !== false) {
                echo "<div class='resultado resultado-ok'>";
                echo "<strong>✅ Contenedor:</strong> Se encontró div#campos_dinamicos";
                echo "</div>";
            } else {
                echo "<div class='resultado resultado-error'>";
                echo "<strong>❌ Contenedor:</strong> No se encontró el contenedor para campos dinámicos";
                echo "</div>";
            }
        ?>
        </div>
        
        <h3>Prueba de acceso a archivos vía HTTP</h3>
        <div id="prueba-http">
            <?php
            function probar_url($url) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                return $httpcode;
            }
            
            $base_url = "http://" . $_SERVER['HTTP_HOST'];
            $urls_probar = [
                '/Dientes2.html',
                '/odontograma_inline.php',
                '/dientes2.html'
            ];
            
            foreach ($urls_probar as $url_path) {
                $url_completa = $base_url . $url_path;
                $codigo = probar_url($url_completa);
                
                if ($codigo >= 200 && $codigo < 300) {
                    echo "<div class='resultado resultado-ok'>";
                    echo "<strong>✅ $url_path:</strong> Accesible (código HTTP $codigo)";
                    echo "</div>";
                } else {
                    echo "<div class='resultado resultado-error'>";
                    echo "<strong>❌ $url_path:</strong> No accesible (código HTTP $codigo)";
                    echo "</div>";
                }
            }
            ?>
        </div>
    </div>
    
    <div class="container mt-4">
        <h3>Solución directa</h3>
        <button id="btn-test-odontograma" class="btn btn-primary">Probar Odontograma Inline</button>
        <div id="odontograma-test-container" class="mt-3" style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;"></div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#btn-test-odontograma').click(function() {
            var html = `
                <div id="odontograma-dinamico" class="mb-4">
                    <h5 class="mt-4 mb-2 text-primary">Odontograma</h5>
                    <div id="odontograma-container" style="max-width: 900px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;">
                        <h2 style="color: #0056b3; text-align: center; margin-bottom: 20px;">Odontograma - Selección de Dientes</h2>
                        <div class="odontograma-simple" style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">
                            <div style="width: 100%; margin-bottom: 10px; text-align: center; font-weight: bold;">Arcada Superior</div>
                        `;
                        
            // Agregar dientes superiores
            [18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28].forEach(function(diente) {
                html += '<button type="button" class="btn-diente" data-diente="' + diente + '" '
                    + 'style="width: 40px; height: 40px; background: white; border: 1px solid #ccc; margin: 3px; cursor: pointer; border-radius: 4px;">'
                    + diente + '</button>';
            });
            
            html += '<div style="width: 100%; margin: 15px 0; text-align: center; font-weight: bold;">Arcada Inferior</div>';
            
            // Agregar dientes inferiores
            [48, 47, 46, 45, 44, 43, 42, 41, 31, 32, 33, 34, 35, 36, 37, 38].forEach(function(diente) {
                html += '<button type="button" class="btn-diente" data-diente="' + diente + '" '
                    + 'style="width: 40px; height: 40px; background: white; border: 1px solid #ccc; margin: 3px; cursor: pointer; border-radius: 4px;">'
                    + diente + '</button>';
            });
            
            html += `
                        </div>
                        <div class="mt-3" style="padding: 15px; background-color: white; border-radius: 8px; box-shadow: 0 1px 3px #0001;">
                            <h4 style="color: #444; margin-bottom: 10px; font-size: 16px;">Dientes seleccionados:</h4>
                            <div id="dientes-seleccionados-lista" style="min-height: 30px;"></div>
                            <input type="hidden" id="dientes_seleccionados" name="dientes_seleccionados" value="">
                        </div>
                    </div>
                </div>
            `;
            
            $('#odontograma-test-container').html(html);
            
            // Agregar funcionalidad de selección de dientes
            $('.btn-diente').click(function() {
                $(this).toggleClass('seleccionado');
                if ($(this).hasClass('seleccionado')) {
                    $(this).css({
                        'background-color': '#ffebeb', 
                        'border-color': '#ff6347',
                        'color': '#ff0000',
                        'font-weight': 'bold'
                    });
                } else {
                    $(this).css({
                        'background-color': 'white', 
                        'border-color': '#ccc',
                        'color': '#000',
                        'font-weight': 'normal'
                    });
                }
                actualizarListaDientesSeleccionados();
            });
            
            function actualizarListaDientesSeleccionados() {
                var dientesSeleccionados = [];
                $('.btn-diente.seleccionado').each(function() {
                    dientesSeleccionados.push($(this).data('diente'));
                });
                
                // Ordenar numéricamente
                dientesSeleccionados.sort(function(a, b) { return a - b; });
                
                // Actualizar campo oculto
                $('#dientes_seleccionados').val(dientesSeleccionados.join(','));
                
                // Actualizar lista visual
                var listaHtml = '';
                if (dientesSeleccionados.length === 0) {
                    listaHtml = '<span style="color: #777;">Ninguno seleccionado</span>';
                } else {
                    dientesSeleccionados.forEach(function(diente) {
                        listaHtml += '<span class="badge badge-primary mr-2 mb-2" style="background: #007bff; color: white; padding: 5px 8px; border-radius: 4px; display: inline-block;">Diente ' + diente + '</span>';
                    });
                }
                $('#dientes-seleccionados-lista').html(listaHtml);
            }
            
            // Inicializar
            actualizarListaDientesSeleccionados();
        });
    });
    </script>
</body>
</html>
