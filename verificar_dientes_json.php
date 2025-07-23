<?php
// Test completo: verificar guardado de dientes en JSON

session_start();
require_once "config.php";

echo "<h2>🔍 Test: Verificación de guardado de dientes en JSON</h2>";

// Verificar las últimas consultas guardadas
echo "<div style='background: #e1f5fe; padding: 15px; margin: 10px; border: 2px solid #0288d1;'>";
echo "<h3>📋 Últimas 5 consultas en la base de datos:</h3>";

$stmt = $conn->prepare("
    SELECT id, paciente_id, fecha, dientes_seleccionados, campos_adicionales 
    FROM historial_medico 
    ORDER BY id DESC 
    LIMIT 5
");
$stmt->execute();
$consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($consultas as $consulta) {
    echo "<div style='background: white; padding: 10px; margin: 5px 0; border: 1px solid #ddd;'>";
    echo "<strong>ID:</strong> " . $consulta['id'] . " | ";
    echo "<strong>Paciente:</strong> " . $consulta['paciente_id'] . " | ";
    echo "<strong>Fecha:</strong> " . $consulta['fecha'] . "<br>";
    echo "<strong>Dientes (columna):</strong> '" . htmlspecialchars($consulta['dientes_seleccionados'] ?? 'NULL') . "'<br>";
    echo "<strong>Campos adicionales (JSON):</strong><br>";
    
    if ($consulta['campos_adicionales']) {
        $campos = json_decode($consulta['campos_adicionales'], true);
        echo "<pre style='background: #f8f9fa; padding: 5px; margin: 5px 0;'>";
        echo htmlspecialchars(json_encode($campos, JSON_PRETTY_PRINT));
        echo "</pre>";
        
        // Verificar específicamente los dientes en el JSON
        if (isset($campos['dientes_seleccionados'])) {
            echo "<strong style='color: green;'>✅ Dientes en JSON:</strong> '" . htmlspecialchars($campos['dientes_seleccionados']) . "'<br>";
        } else {
            echo "<strong style='color: red;'>❌ NO hay dientes en el JSON</strong><br>";
        }
    } else {
        echo "<em style='color: #666;'>No hay campos adicionales</em><br>";
    }
    echo "</div>";
}
echo "</div>";

// Hacer una prueba de guardado ahora mismo
echo "<div style='background: #f3e5f5; padding: 15px; margin: 10px; border: 2px solid #9c27b0;'>";
echo "<h3>🧪 Test de guardado AHORA:</h3>";

try {
    // Simular datos como los que envía el formulario
    $datos_test = [
        'observa' => 'test desde verificacion',
        'dientes_seleccionados' => '18,21,22'
    ];
    
    echo "<strong>Datos de prueba a guardar:</strong><br>";
    echo "<pre>" . print_r($datos_test, true) . "</pre>";
    
    // Procesar igual que en nueva_consulta.php
    $campos_adicionales = [];
    
    // Campos del sistema que NO deben ir al JSON
    $campos_sistema = [
        'action', 'paciente_id', 'doctor_id', 'fecha', 'motivo_consulta', 
        'diagnostico', 'tratamiento', 'observaciones', 'dientes_seleccionados'
    ];
    
    foreach ($datos_test as $key => $value) {
        // Si el campo comienza con 'campo_' es un campo dinámico
        if (strpos($key, 'campo_') === 0) {
            $campo_nombre = substr($key, 6);
            $campos_adicionales[$campo_nombre] = $value;
        }
        // También capturar otros campos que no sean del sistema (como 'observa')
        elseif (!in_array($key, $campos_sistema) && !empty(trim($value))) {
            $campos_adicionales[$key] = $value;
        }
    }
    
    // Agregar los dientes seleccionados al array de campos adicionales si existen
    if (isset($datos_test['dientes_seleccionados']) && !empty($datos_test['dientes_seleccionados'])) {
        $campos_adicionales['dientes_seleccionados'] = $datos_test['dientes_seleccionados'];
    }
    
    // Convertir el array a JSON
    $campos_adicionales_json = !empty($campos_adicionales) ? json_encode($campos_adicionales) : null;
    
    echo "<strong>Array procesado:</strong><br>";
    echo "<pre>" . print_r($campos_adicionales, true) . "</pre>";
    
    echo "<strong>JSON generado:</strong><br>";
    echo "<pre style='background: white; padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($campos_adicionales_json) . "</pre>";
    
    // Guardar en la base de datos
    $stmt = $conn->prepare("
        INSERT INTO historial_medico (
            paciente_id, doctor_id, fecha, motivo_consulta, 
            campos_adicionales, especialidad_id, dientes_seleccionados
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        1, // paciente_id
        1, // doctor_id
        date('Y-m-d'), // fecha
        'Test de verificación de guardado JSON',
        $campos_adicionales_json,
        1, // especialidad_id
        $datos_test['dientes_seleccionados']
    ]);
    
    $test_id = $conn->lastInsertId();
    
    echo "<strong style='color: green;'>✅ Guardado exitoso - ID: " . $test_id . "</strong><br>";
    
    // Verificar lo que se guardó
    $stmt_verify = $conn->prepare("SELECT dientes_seleccionados, campos_adicionales FROM historial_medico WHERE id = ?");
    $stmt_verify->execute([$test_id]);
    $verificacion = $stmt_verify->fetch(PDO::FETCH_ASSOC);
    
    if ($verificacion) {
        echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border: 1px solid #c3e6cb;'>";
        echo "<h4>🔍 Verificación de lo guardado:</h4>";
        echo "<strong>Columna dientes_seleccionados:</strong> '" . htmlspecialchars($verificacion['dientes_seleccionados']) . "'<br>";
        echo "<strong>JSON campos_adicionales:</strong><br>";
        echo "<pre>" . htmlspecialchars($verificacion['campos_adicionales']) . "</pre>";
        
        // Decodificar JSON para verificar
        $json_decodificado = json_decode($verificacion['campos_adicionales'], true);
        if ($json_decodificado) {
            echo "<strong>JSON decodificado:</strong><br>";
            echo "<pre>" . print_r($json_decodificado, true) . "</pre>";
            
            if (isset($json_decodificado['dientes_seleccionados'])) {
                echo "<strong style='color: green;'>✅ ÉXITO: Los dientes SÍ están en el JSON: '" . $json_decodificado['dientes_seleccionados'] . "'</strong><br>";
            } else {
                echo "<strong style='color: red;'>❌ ERROR: Los dientes NO están en el JSON</strong><br>";
            }
        }
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>";
    echo "<strong>❌ ERROR:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "</div>";

echo "<div style='margin: 20px 0; text-align: center;'>";
echo "<a href='nueva_consulta.php?paciente_id=1' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔙 Nueva Consulta</a>";
echo "<a href='ver_consulta.php?id=" . ($test_id ?? 'last') . "' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>👁️ Ver Consulta Test</a>";
echo "</div>";
?>
