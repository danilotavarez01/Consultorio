<?php
require_once 'config.php';

echo "<h1>Debug del Proceso de Guardado de Dientes</h1>";

// Verificar si hay datos POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Datos POST recibidos:</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h2>Procesamiento de datos:</h2>";
    
    // Simular exactamente la lógica de nueva_consulta.php
    if (isset($_POST['action']) && $_POST['action'] == 'crear_consulta') {
        echo "<p>✅ Acción de crear consulta detectada</p>";
        
        // Verificar dientes_seleccionados
        if (isset($_POST['dientes_seleccionados'])) {
            echo "<p>✅ Campo 'dientes_seleccionados' presente en POST</p>";
            echo "<p><strong>Valor:</strong> '" . htmlspecialchars($_POST['dientes_seleccionados']) . "'</p>";
            echo "<p><strong>Longitud:</strong> " . strlen($_POST['dientes_seleccionados']) . " caracteres</p>";
            echo "<p><strong>Vacío:</strong> " . (empty($_POST['dientes_seleccionados']) ? 'SÍ' : 'NO') . "</p>";
        } else {
            echo "<p>❌ Campo 'dientes_seleccionados' NO presente en POST</p>";
        }
        
        // Procesar campos adicionales
        $campos_adicionales = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'campo_') === 0) {
                $campo_nombre = substr($key, 6);
                $campos_adicionales[$campo_nombre] = $value;
                echo "<p>Campo dinámico encontrado: $campo_nombre = " . htmlspecialchars($value) . "</p>";
            }
        }
        
        // Agregar dientes al JSON
        if (isset($_POST['dientes_seleccionados']) && !empty($_POST['dientes_seleccionados'])) {
            $campos_adicionales['dientes_seleccionados'] = $_POST['dientes_seleccionados'];
            echo "<p>✅ Dientes agregados al JSON</p>";
        } else {
            echo "<p>❌ Dientes NO agregados al JSON (campo vacío o inexistente)</p>";
        }
        
        $campos_adicionales_json = !empty($campos_adicionales) ? json_encode($campos_adicionales) : null;
        echo "<p><strong>JSON final:</strong> " . htmlspecialchars($campos_adicionales_json) . "</p>";
        
        // Intentar guardar
        try {
            $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
            $stmt->execute();
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
            $especialidad_id = $config['especialidad_id'];
            
            echo "<p>Especialidad ID: $especialidad_id</p>";
            
            // Verificar si la columna existe
            $stmt = $conn->prepare("SHOW COLUMNS FROM historial_medico LIKE 'dientes_seleccionados'");
            $stmt->execute();
            $columna_existe = $stmt->fetch();
            
            if ($columna_existe) {
                echo "<p>✅ Columna 'dientes_seleccionados' existe</p>";
                
                // Intentar INSERT
                $sql = "INSERT INTO historial_medico (
                            paciente_id, 
                            doctor_id, 
                            fecha, 
                            motivo_consulta, 
                            diagnostico, 
                            tratamiento, 
                            observaciones,
                            campos_adicionales,
                            especialidad_id,
                            dientes_seleccionados
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                $success = $stmt->execute([
                    $_POST['paciente_id'] ?? 1,
                    $_POST['doctor_id'] ?? 1,
                    $_POST['fecha'] ?? date('Y-m-d'),
                    $_POST['motivo_consulta'] ?? 'Test debug',
                    $_POST['diagnostico'] ?? 'Debug',
                    $_POST['tratamiento'] ?? 'Debug',
                    $_POST['observaciones'] ?? 'Debug',
                    $campos_adicionales_json,
                    $especialidad_id,
                    $_POST['dientes_seleccionados'] ?? null
                ]);
                
                if ($success) {
                    $new_id = $conn->lastInsertId();
                    echo "<div style='background: lightgreen; padding: 10px; margin: 10px 0;'>";
                    echo "<strong>✅ Consulta guardada exitosamente!</strong> ID: $new_id";
                    echo "</div>";
                    
                    // Verificar los datos guardados
                    $stmt = $conn->prepare("SELECT dientes_seleccionados, campos_adicionales FROM historial_medico WHERE id = ?");
                    $stmt->execute([$new_id]);
                    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    echo "<h3>Datos guardados:</h3>";
                    echo "<ul>";
                    echo "<li><strong>Columna dientes_seleccionados:</strong> '" . htmlspecialchars($resultado['dientes_seleccionados'] ?? 'NULL') . "'</li>";
                    echo "<li><strong>JSON campos_adicionales:</strong> " . htmlspecialchars($resultado['campos_adicionales'] ?? 'NULL') . "</li>";
                    echo "</ul>";
                    
                    $json_data = json_decode($resultado['campos_adicionales'], true);
                    if (isset($json_data['dientes_seleccionados'])) {
                        echo "<p>✅ Dientes también están en el JSON: '" . htmlspecialchars($json_data['dientes_seleccionados']) . "'</p>";
                    } else {
                        echo "<p>❌ Dientes NO están en el JSON</p>";
                    }
                    
                } else {
                    echo "<div style='background: lightcoral; padding: 10px; margin: 10px 0;'>";
                    echo "<strong>❌ Error al guardar la consulta</strong>";
                    echo "</div>";
                }
                
            } else {
                echo "<p>❌ Columna 'dientes_seleccionados' NO existe</p>";
            }
            
        } catch (Exception $e) {
            echo "<div style='background: lightcoral; padding: 10px; margin: 10px 0;'>";
            echo "<strong>Error:</strong> " . htmlspecialchars($e->getMessage());
            echo "</div>";
        }
    }
} else {
    echo "<h2>Formulario de prueba para debug</h2>";
    echo "<p>Use este formulario para simular el envío desde nueva_consulta.php:</p>";
}
?>

<form method="POST" style="background: #f8f9fa; padding: 20px; margin: 20px 0; border: 1px solid #ddd;">
    <h3>Simular envío de nueva_consulta.php</h3>
    
    <input type="hidden" name="action" value="crear_consulta">
    
    <div style="margin: 10px 0;">
        <label><strong>Paciente ID:</strong></label><br>
        <input type="number" name="paciente_id" value="1" required>
    </div>
    
    <div style="margin: 10px 0;">
        <label><strong>Doctor ID:</strong></label><br>
        <input type="number" name="doctor_id" value="1" required>
    </div>
    
    <div style="margin: 10px 0;">
        <label><strong>Fecha:</strong></label><br>
        <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" required>
    </div>
    
    <div style="margin: 10px 0;">
        <label><strong>Motivo consulta:</strong></label><br>
        <input type="text" name="motivo_consulta" value="Debug test de dientes" required>
    </div>
    
    <div style="margin: 10px 0;">
        <label><strong>Diagnóstico:</strong></label><br>
        <textarea name="diagnostico">Debug: Test de guardado de dientes seleccionados</textarea>
    </div>
    
    <div style="margin: 10px 0;">
        <label><strong>Tratamiento:</strong></label><br>
        <textarea name="tratamiento">Debug: Verificar funcionalidad</textarea>
    </div>
    
    <div style="margin: 10px 0;">
        <label><strong>Observaciones:</strong></label><br>
        <textarea name="observaciones">Debug: Test completo del sistema</textarea>
    </div>
    
    <div style="margin: 10px 0;">
        <label><strong>Dientes seleccionados (CRÍTICO):</strong></label><br>
        <input type="text" name="dientes_seleccionados" value="11,12,21,22" placeholder="Ej: 11,12,21,22" style="width: 300px; background: yellow;">
        <small style="display: block; color: #666;">Este es el campo que debe llegar desde JavaScript</small>
    </div>
    
    <div style="margin: 10px 0;">
        <label><strong>Campo adicional - Presión:</strong></label><br>
        <input type="text" name="campo_presion" value="120/80">
    </div>
    
    <div style="margin: 10px 0;">
        <label><strong>Campo adicional - Temperatura:</strong></label><br>
        <input type="text" name="campo_temperatura" value="36.5">
    </div>
    
    <div style="margin: 20px 0;">
        <button type="submit" style="background: #007bff; color: white; padding: 15px 30px; border: none; font-size: 16px; cursor: pointer;">
            🦷 ENVIAR TEST DEBUG
        </button>
    </div>
</form>

<div style="background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; margin: 20px 0;">
    <h3>🔍 Puntos críticos a verificar:</h3>
    <ol>
        <li><strong>Campo dientes_seleccionados:</strong> ¿Llega el valor desde el formulario?</li>
        <li><strong>Columna en base de datos:</strong> ¿Existe la columna 'dientes_seleccionados'?</li>
        <li><strong>JavaScript:</strong> ¿Está actualizando correctamente el campo hidden?</li>
        <li><strong>JSON:</strong> ¿Se incluyen los dientes en campos_adicionales?</li>
        <li><strong>SQL INSERT:</strong> ¿Se ejecuta sin errores?</li>
    </ol>
</div>

<script>
// Simular el comportamiento de nueva_consulta.php
console.log('Debug script cargado');

// Mostrar el valor actual del campo dientes_seleccionados cada segundo
setInterval(function() {
    const dientesField = document.querySelector('input[name="dientes_seleccionados"]');
    if (dientesField) {
        console.log('Valor actual dientes_seleccionados:', dientesField.value);
    }
}, 1000);

// Simular actualización desde JavaScript (como lo haría el odontograma)
function simularSeleccionDientes() {
    const dientesField = document.querySelector('input[name="dientes_seleccionados"]');
    if (dientesField) {
        const nuevosValores = ['11', '12', '21', '22', '31', '32'];
        dientesField.value = nuevosValores.join(',');
        console.log('Dientes actualizados via JavaScript:', dientesField.value);
    }
}

// Botón para simular la selección de dientes
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        const botonSimular = document.createElement('button');
        botonSimular.type = 'button';
        botonSimular.innerHTML = '🦷 Simular selección de dientes desde JS';
        botonSimular.style.cssText = 'background: orange; color: white; padding: 10px; border: none; margin: 10px; cursor: pointer;';
        botonSimular.onclick = simularSeleccionDientes;
        form.appendChild(botonSimular);
    }
});
</script>
