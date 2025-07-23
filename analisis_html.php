<!DOCTYPE html>
<html>
<head>
    <title>Análisis HTML de Facturación</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .resultado { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .problema { background: #f8d7da; color: #721c24; }
        .ok { background: #d4edda; color: #155724; }
        code { background: #f1f1f1; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <h2>🔍 Análisis del HTML de Facturación</h2>
    
    <div class="resultado">
        <h3>Obteniendo contenido de facturación.php...</h3>
        <?php
        // Capturar el HTML de facturación
        ob_start();
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Cookie: " . $_SERVER['HTTP_COOKIE'] . "\r\n"
            ]
        ]);
        
        $html = file_get_contents('http://localhost/Consultorio2/facturacion.php', false, $context);
        
        if ($html === false) {
            echo "<p class='problema'>❌ No se pudo obtener el HTML de facturación.php</p>";
        } else {
            echo "<p class='ok'>✅ HTML obtenido exitosamente (" . strlen($html) . " caracteres)</p>";
            
            // Buscar el modal en el HTML
            if (strpos($html, 'modalImprimirRecibo') !== false) {
                echo "<p>🔍 Modal encontrado en el HTML</p>";
                
                // Verificar si tiene la clase 'show'
                if (strpos($html, 'modalImprimirRecibo') !== false && strpos($html, 'class="modal fade show"') !== false) {
                    echo "<p class='problema'>❌ PROBLEMA: Modal tiene clase 'show' - aparecerá automáticamente</p>";
                } else {
                    echo "<p class='ok'>✅ Modal no tiene clase 'show'</p>";
                }
                
                // Verificar el estilo display
                if (strpos($html, 'style="display: block"') !== false || strpos($html, 'style="display:block"') !== false) {
                    echo "<p class='problema'>❌ PROBLEMA: Modal tiene style='display: block' - aparecerá automáticamente</p>";
                } else {
                    echo "<p class='ok'>✅ Modal no tiene display: block forzado</p>";
                }
                
                // Buscar JavaScript que pueda estar mostrando el modal
                if (strpos($html, 'show_print_modal') !== false) {
                    echo "<p class='problema'>❌ PROBLEMA: Código JavaScript de show_print_modal encontrado - modal se mostrará automáticamente</p>";
                } else {
                    echo "<p class='ok'>✅ No hay código JavaScript que fuerce el modal</p>";
                }
                
            } else {
                echo "<p class='problema'>❌ Modal NO encontrado en el HTML</p>";
            }
        }
        ?>
    </div>
    
    <div class="resultado">
        <h3>🧪 Acciones de Prueba:</h3>
        <p><a href="facturacion.php" target="_blank">🔗 Abrir Facturación (nueva ventana)</a></p>
        <p><a href="diagnostico_sesion.php">🧹 Limpiar Sesión Completa</a></p>
    </div>
    
</body>
</html>
