<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Debug Completo: Guardado de Dientes</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>🔍 Debug Completo: Guardado de Dientes Seleccionados</h1>
    
    <!-- Formulario de prueba -->
    <div style="background: #f8f9fa; padding: 20px; border: 1px solid #ddd; margin: 20px 0;">
        <h2>Formulario de Prueba</h2>
        <form method="POST" id="testForm">
            <input type="hidden" name="action" value="crear_consulta">
            <input type="hidden" name="paciente_id" value="1">
            <input type="hidden" name="doctor_id" value="1">
            
            <!-- Campo de dientes (como en nueva_consulta.php) -->
            <input type="hidden" name="dientes_seleccionados" id="dientes_seleccionados" value="">
            
            <div style="margin: 10px 0;">
                <label><strong>Fecha:</strong></label>
                <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div style="margin: 10px 0;">
                <label><strong>Dientes seleccionados (manual):</strong></label>
                <input type="text" id="dientes_manual" placeholder="Ej: 11,12,21,22" style="width: 200px;">
                <button type="button" id="btn_aplicar_dientes">Aplicar al campo hidden</button>
            </div>
            
            <div style="margin: 10px 0;">
                <label><strong>Motivo consulta:</strong></label>
                <input type="text" name="motivo_consulta" value="Test debug dientes">
            </div>
            
            <div style="margin: 20px 0;">
                <button type="button" id="btn_debug_form">🔍 Debug Formulario</button>
                <button type="submit" id="btn_enviar" style="background: green; color: white; padding: 10px;">📤 Enviar Form</button>
            </div>
        </form>
    </div>
    
    <!-- Área de debug -->
    <div id="debug_output" style="background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; margin: 20px 0;">
        <h3>📊 Output de Debug</h3>
        <div id="debug_content">Haga clic en "Debug Formulario" para ver el estado actual</div>
    </div>
    
    <!-- Simulador de odontograma simple -->
    <div style="background: #e7f3ff; padding: 20px; border: 1px solid #b3d9ff; margin: 20px 0;">
        <h3>🦷 Simulador de Odontograma</h3>
        <p>Haga clic en los números de dientes para simular la selección:</p>
        <div id="dientes_simulados">
            <?php 
            $dientes = [11, 12, 13, 14, 15, 16, 17, 18, 21, 22, 23, 24, 25, 26, 27, 28,
                       31, 32, 33, 34, 35, 36, 37, 38, 41, 42, 43, 44, 45, 46, 47, 48];
            foreach ($dientes as $diente): 
            ?>
                <button type="button" class="btn-diente" data-num="<?php echo $diente; ?>" 
                        style="margin: 2px; padding: 5px 10px; border: 1px solid #ccc; background: #fff;">
                    <?php echo $diente; ?>
                </button>
            <?php endforeach; ?>
        </div>
        <div style="margin-top: 15px;">
            <button type="button" id="btn_limpiar_dientes">🗑️ Limpiar Selección</button>
            <button type="button" id="btn_seleccionar_superiores">⬆️ Superiores</button>
            <button type="button" id="btn_seleccionar_inferiores">⬇️ Inferiores</button>
        </div>
    </div>
    
    <!-- Procesamiento del formulario -->
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'crear_consulta') {
        echo "<div style='background: #d1ecf1; padding: 20px; border: 1px solid #bee5eb; margin: 20px 0;'>";
        echo "<h2>📥 Datos Recibidos por POST</h2>";
        
        echo "<h3>Todos los datos POST:</h3>";
        echo "<pre>" . print_r($_POST, true) . "</pre>";
        
        echo "<h3>Campo dientes_seleccionados específicamente:</h3>";
        $dientes_recibidos = $_POST['dientes_seleccionados'] ?? null;
        echo "<p><strong>Valor:</strong> '" . htmlspecialchars($dientes_recibidos) . "'</p>";
        echo "<p><strong>Vacío:</strong> " . (empty($dientes_recibidos) ? 'SÍ' : 'NO') . "</p>";
        echo "<p><strong>Longitud:</strong> " . strlen($dientes_recibidos ?? '') . " caracteres</p>";
        
        // Intentar el proceso de guardado
        try {
            $conn->beginTransaction();
            
            // Obtener especialidad
            $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
            $stmt->execute();
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
            $especialidad_id = $config['especialidad_id'] ?? 1;
            
            // Preparar campos adicionales
            $campos_adicionales = [];
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'campo_') === 0) {
                    $campo_nombre = substr($key, 6);
                    $campos_adicionales[$campo_nombre] = $value;
                }
            }
            
            // Agregar dientes al JSON
            if (isset($_POST['dientes_seleccionados']) && !empty($_POST['dientes_seleccionados'])) {
                $campos_adicionales['dientes_seleccionados'] = $_POST['dientes_seleccionados'];
            }
            
            $campos_adicionales_json = !empty($campos_adicionales) ? json_encode($campos_adicionales) : null;
            
            echo "<h3>Procesamiento:</h3>";
            echo "<p><strong>Especialidad ID:</strong> " . $especialidad_id . "</p>";
            echo "<p><strong>Campos adicionales JSON:</strong> " . htmlspecialchars($campos_adicionales_json ?? 'NULL') . "</p>";
            
            // Ejecutar INSERT
            $sql = "INSERT INTO historial_medico (
                        paciente_id, doctor_id, fecha, motivo_consulta, 
                        diagnostico, tratamiento, observaciones,
                        campos_adicionales, especialidad_id, dientes_seleccionados
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $success = $stmt->execute([
                $_POST['paciente_id'],
                $_POST['doctor_id'],
                $_POST['fecha'],
                $_POST['motivo_consulta'],
                null, // diagnostico
                null, // tratamiento
                null, // observaciones
                $campos_adicionales_json,
                $especialidad_id,
                $_POST['dientes_seleccionados']
            ]);
            
            if ($success) {
                $consulta_id = $conn->lastInsertId();
                $conn->commit();
                
                echo "<div style='background: lightgreen; padding: 10px; margin: 10px 0;'>";
                echo "<h3>✅ ¡INSERT EXITOSO!</h3>";
                echo "<p><strong>ID de consulta:</strong> " . $consulta_id . "</p>";
                echo "</div>";
                
                // Verificar lo que se guardó
                $stmt = $conn->prepare("SELECT dientes_seleccionados, campos_adicionales FROM historial_medico WHERE id = ?");
                $stmt->execute([$consulta_id]);
                $datos_guardados = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo "<h3>Verificación de datos guardados:</h3>";
                echo "<p><strong>Columna dientes_seleccionados:</strong> '" . htmlspecialchars($datos_guardados['dientes_seleccionados'] ?? 'NULL') . "'</p>";
                echo "<p><strong>JSON campos_adicionales:</strong> " . htmlspecialchars($datos_guardados['campos_adicionales'] ?? 'NULL') . "</p>";
                
                $campos_json = json_decode($datos_guardados['campos_adicionales'], true);
                $dientes_en_json = $campos_json['dientes_seleccionados'] ?? 'No encontrado';
                echo "<p><strong>Dientes extraídos del JSON:</strong> '" . htmlspecialchars($dientes_en_json) . "'</p>";
                
                echo "<p><a href='/ver_consulta.php?id=" . $consulta_id . "' target='_blank' style='background: blue; color: white; padding: 10px; text-decoration: none;'>🔍 Ver Consulta</a></p>";
                
            } else {
                $conn->rollBack();
                echo "<div style='background: lightcoral; padding: 10px; margin: 10px 0;'>";
                echo "<h3>❌ Error en INSERT</h3>";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            $conn->rollBack();
            echo "<div style='background: lightcoral; padding: 10px; margin: 10px 0;'>";
            echo "<h3>❌ EXCEPCIÓN:</h3>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
        
        echo "</div>";
    }
    ?>
    
    <script>
        $(document).ready(function() {
            console.log('Debug script cargado');
            
            // Array para simular dientes seleccionados
            let dientesSeleccionados = [];
            
            // Función para actualizar el campo hidden
            function actualizarCampoHidden() {
                const valorActual = dientesSeleccionados.join(',');
                $('#dientes_seleccionados').val(valorActual);
                console.log('Campo hidden actualizado:', valorActual);
                return valorActual;
            }
            
            // Función de debug del formulario
            $('#btn_debug_form').click(function() {
                console.log('=== DEBUG FORMULARIO ===');
                
                const campoHidden = $('#dientes_seleccionados');
                const valorHidden = campoHidden.val();
                
                let debugInfo = '<h4>Estado del Formulario:</h4>';
                debugInfo += '<ul>';
                debugInfo += '<li><strong>Campo hidden existe:</strong> ' + (campoHidden.length > 0 ? 'SÍ' : 'NO') + '</li>';
                debugInfo += '<li><strong>Valor actual:</strong> "' + valorHidden + '"</li>';
                debugInfo += '<li><strong>Longitud:</strong> ' + valorHidden.length + ' caracteres</li>';
                debugInfo += '<li><strong>Está vacío:</strong> ' + (valorHidden === '' ? 'SÍ' : 'NO') + '</li>';
                debugInfo += '<li><strong>Array JS:</strong> [' + dientesSeleccionados.join(', ') + ']</li>';
                debugInfo += '<li><strong>Coincide con JS:</strong> ' + (valorHidden === dientesSeleccionados.join(',') ? 'SÍ' : 'NO') + '</li>';
                debugInfo += '</ul>';
                
                debugInfo += '<h4>Todos los campos del formulario:</h4>';
                debugInfo += '<ul>';
                $('#testForm').find('input, select, textarea').each(function() {
                    const elemento = $(this);
                    debugInfo += '<li><strong>' + (elemento.attr('name') || elemento.attr('id')) + ':</strong> "' + elemento.val() + '"</li>';
                });
                debugInfo += '</ul>';
                
                $('#debug_content').html(debugInfo);
                console.log('Dientes seleccionados (JS):', dientesSeleccionados);
                console.log('Campo hidden valor:', valorHidden);
            });
            
            // Aplicar dientes manualmente
            $('#btn_aplicar_dientes').click(function() {
                const dientesManual = $('#dientes_manual').val().trim();
                if (dientesManual) {
                    dientesSeleccionados = dientesManual.split(',').map(d => d.trim()).filter(d => d);
                    actualizarCampoHidden();
                    
                    // Actualizar visualización
                    $('.btn-diente').removeClass('selected').css('background', '#fff');
                    dientesSeleccionados.forEach(function(diente) {
                        $('.btn-diente[data-num="' + diente + '"]').addClass('selected').css('background', '#28a745');
                    });
                    
                    alert('Dientes aplicados: ' + dientesSeleccionados.join(','));
                }
            });
            
            // Simulador de clicks en dientes
            $('.btn-diente').click(function() {
                const numDiente = $(this).data('num').toString();
                const index = dientesSeleccionados.indexOf(numDiente);
                
                if (index > -1) {
                    // Deseleccionar
                    dientesSeleccionados.splice(index, 1);
                    $(this).removeClass('selected').css('background', '#fff');
                } else {
                    // Seleccionar
                    dientesSeleccionados.push(numDiente);
                    $(this).addClass('selected').css('background', '#28a745');
                }
                
                actualizarCampoHidden();
                console.log('Diente', numDiente, index > -1 ? 'deseleccionado' : 'seleccionado');
            });
            
            // Botones de selección masiva
            $('#btn_limpiar_dientes').click(function() {
                dientesSeleccionados = [];
                $('.btn-diente').removeClass('selected').css('background', '#fff');
                actualizarCampoHidden();
            });
            
            $('#btn_seleccionar_superiores').click(function() {
                dientesSeleccionados = ['11','12','13','14','15','16','17','18','21','22','23','24','25','26','27','28'];
                $('.btn-diente').removeClass('selected').css('background', '#fff');
                dientesSeleccionados.forEach(function(diente) {
                    $('.btn-diente[data-num="' + diente + '"]').addClass('selected').css('background', '#28a745');
                });
                actualizarCampoHidden();
            });
            
            $('#btn_seleccionar_inferiores').click(function() {
                dientesSeleccionados = ['31','32','33','34','35','36','37','38','41','42','43','44','45','46','47','48'];
                $('.btn-diente').removeClass('selected').css('background', '#fff');
                dientesSeleccionados.forEach(function(diente) {
                    $('.btn-diente[data-num="' + diente + '"]').addClass('selected').css('background', '#28a745');
                });
                actualizarCampoHidden();
            });
            
            // Interceptar envío del formulario
            $('#testForm').on('submit', function(e) {
                console.log('=== ENVÍO DEL FORMULARIO ===');
                const valorFinal = $('#dientes_seleccionados').val();
                console.log('Valor final que se enviará:', valorFinal);
                
                if (!valorFinal || valorFinal.trim() === '') {
                    const confirmar = confirm('⚠️ El campo de dientes está vacío. ¿Continuar de todas formas?');
                    if (!confirmar) {
                        e.preventDefault();
                        return false;
                    }
                }
                
                return true;
            });
        });
    </script>
    
    <style>
        .btn-diente { cursor: pointer; }
        .btn-diente:hover { background: #e9ecef !important; }
        .btn-diente.selected { background: #28a745 !important; color: white; }
    </style>
</body>
</html>
