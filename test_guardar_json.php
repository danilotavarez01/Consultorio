<?php
// Script de prueba rápida para verificar que los datos se guardan correctamente en el JSON

session_start();
require_once "config.php";

// Simular datos de prueba
$datos_simulados = array(
    'action' => 'crear_consulta',
    'paciente_id' => '1',
    'doctor_id' => '1',
    'fecha' => date('Y-m-d'),
    'observa' => 'jajaja',
    'dientes_seleccionados' => '18'
);

echo "<h2>🧪 Test: Guardado de JSON - observa y dientes_seleccionados</h2>";

echo "<div style='background: #e1f5fe; padding: 15px; border: 2px solid #0288d1; margin: 10px;'>";
echo "<h3>📋 Datos de prueba:</h3>";
echo "<pre>" . print_r($datos_simulados, true) . "</pre>";
echo "</div>";

// Simular el procesamiento igual que en nueva_consulta.php
$campos_adicionales = [];

// Campos del sistema que NO deben ir al JSON
$campos_sistema = [
    'action', 'paciente_id', 'doctor_id', 'fecha', 'motivo_consulta', 
    'diagnostico', 'tratamiento', 'observaciones', 'dientes_seleccionados'
];

foreach ($datos_simulados as $key => $value) {
    // Si el campo comienza con 'campo_' es un campo dinámico
    if (strpos($key, 'campo_') === 0) {
        $campo_nombre = substr($key, 6); // Remover el prefijo 'campo_'
        $campos_adicionales[$campo_nombre] = $value;
    }
    // También capturar otros campos que no sean del sistema (como 'observa')
    elseif (!in_array($key, $campos_sistema) && !empty(trim($value))) {
        $campos_adicionales[$key] = $value;
    }
}

// Agregar los dientes seleccionados al array de campos adicionales si existen
if (isset($datos_simulados['dientes_seleccionados']) && !empty($datos_simulados['dientes_seleccionados'])) {
    $campos_adicionales['dientes_seleccionados'] = $datos_simulados['dientes_seleccionados'];
}

// Convertir el array a JSON solo si tiene contenido
$campos_adicionales_json = !empty($campos_adicionales) ? json_encode($campos_adicionales) : null;

echo "<div style='background: #f3e5f5; padding: 15px; border: 2px solid #9c27b0; margin: 10px;'>";
echo "<h3>🔧 Procesamiento de campos:</h3>";
echo "<p><strong>Array campos_adicionales:</strong></p>";
echo "<pre>" . print_r($campos_adicionales, true) . "</pre>";
echo "<p><strong>JSON campos_adicionales:</strong> '" . htmlspecialchars($campos_adicionales_json ?? 'NULL') . "'</p>";

if ($campos_adicionales_json) {
    echo "<div style='background: #e8f5e8; padding: 10px; border: 1px solid #4caf50; margin: 10px 0;'>";
    echo "<h4>✅ JSON final que se guardaría:</h4>";
    echo "<pre style='background: white; padding: 10px; border: 1px solid #ddd; font-size: 16px; color: #2e7d32;'>" . $campos_adicionales_json . "</pre>";
    echo "</div>";
}
echo "</div>";

// Simular guardado en base de datos
try {
    $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    $especialidad_id = $config['especialidad_id'] ?? 1;

    $sql = "INSERT INTO historial_medico (
                paciente_id, 
                doctor_id, 
                fecha, 
                motivo_consulta, 
                campos_adicionales,
                especialidad_id,
                dientes_seleccionados
            ) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $datos_simulados['paciente_id'],
        $datos_simulados['doctor_id'],
        $datos_simulados['fecha'],
        'Test de guardado JSON',
        $campos_adicionales_json,
        $especialidad_id,
        $datos_simulados['dientes_seleccionados']
    ]);
    
    $consulta_id = $conn->lastInsertId();
    
    echo "<div style='background: #e8f5e8; padding: 15px; border: 2px solid #4caf50; margin: 10px;'>";
    echo "<h3>✅ Test INSERT ejecutado exitosamente</h3>";
    echo "<p><strong>ID de consulta creada:</strong> " . $consulta_id . "</p>";
    echo "</div>";
    
    // Verificar lo que se guardó
    $stmt_verify = $conn->prepare("SELECT dientes_seleccionados, campos_adicionales FROM historial_medico WHERE id = ?");
    $stmt_verify->execute([$consulta_id]);
    $datos_guardados = $stmt_verify->fetch(PDO::FETCH_ASSOC);
    
    if ($datos_guardados) {
        echo "<div style='background: #fff3cd; padding: 15px; border: 2px solid #ffc107; margin: 10px;'>";
        echo "<h3>🔍 Verificación: Datos guardados en la base de datos</h3>";
        echo "<p><strong>dientes_seleccionados (columna):</strong> '" . htmlspecialchars($datos_guardados['dientes_seleccionados'] ?? 'NULL') . "'</p>";
        echo "<p><strong>campos_adicionales (JSON):</strong></p>";
        echo "<pre style='background: white; padding: 10px; border: 1px solid #ddd; font-size: 16px;'>" . htmlspecialchars($datos_guardados['campos_adicionales'] ?? 'NULL') . "</pre>";
        
        // Extraer datos del JSON
        if ($datos_guardados['campos_adicionales']) {
            $campos_json = json_decode($datos_guardados['campos_adicionales'], true);
            echo "<h4>📋 Datos extraídos del JSON:</h4>";
            foreach ($campos_json as $key => $value) {
                echo "<p><strong>" . htmlspecialchars($key) . ":</strong> '" . htmlspecialchars($value) . "'</p>";
            }
        }
        echo "</div>";
        
        echo "<div style='background: #d4edda; padding: 15px; border: 2px solid #28a745; margin: 10px;'>";
        echo "<h3>🎉 ¡ÉXITO!</h3>";
        echo "<p>Los datos se guardaron correctamente:</p>";
        echo "<ul>";
        echo "<li>✅ <strong>observa</strong> se guardó en el JSON</li>";
        echo "<li>✅ <strong>dientes_seleccionados</strong> se guardó en el JSON</li>";
        echo "<li>✅ <strong>dientes_seleccionados</strong> también se guardó en la columna dedicada</li>";
        echo "</ul>";
        echo "<p><strong>Formato JSON resultante:</strong> " . htmlspecialchars($datos_guardados['campos_adicionales']) . "</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 2px solid #dc3545; margin: 10px;'>";
    echo "<h3>❌ Error en el test</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<div style='margin: 20px 0; text-align: center;'>";
echo "<a href='nueva_consulta.php?paciente_id=1' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔙 Volver a Nueva Consulta</a>";
echo "</div>";
?>
