<?php
require_once 'config.php';

echo "<h2>Simulación de Guardado de Consulta con Dientes</h2>";

// Simular datos POST para una nueva consulta
$test_data = [
    'paciente_id' => 1, // Asumiendo que existe un paciente con ID 1
    'doctor_id' => 1,   // Asumiendo que existe un doctor con ID 1
    'fecha' => date('Y-m-d'),
    'motivo_consulta' => 'Test de odontograma',
    'diagnostico' => 'Caries en molares',
    'tratamiento' => 'Empaste',
    'observaciones' => 'Test de funcionalidad de dientes seleccionados',
    'dientes_seleccionados' => '16,17,26,27', // Dientes seleccionados de prueba
    'campo_presion_arterial' => '120/80',
    'campo_temperatura' => '36.5'
];

try {
    // Obtener especialidad configurada
    $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    $especialidad_id = $config['especialidad_id'];
    
    echo "<p><strong>Especialidad ID:</strong> " . $especialidad_id . "</p>";
    
    // Preparar el array de campos personalizados (simulando la lógica de nueva_consulta.php)
    $campos_adicionales = [];
    foreach ($test_data as $key => $value) {
        // Si el campo comienza con 'campo_' es un campo dinámico
        if (strpos($key, 'campo_') === 0) {
            $campo_nombre = substr($key, 6); // Remover el prefijo 'campo_'
            $campos_adicionales[$campo_nombre] = $value;
        }
    }
    
    // Agregar los dientes seleccionados al array de campos adicionales si existen
    if (isset($test_data['dientes_seleccionados']) && !empty($test_data['dientes_seleccionados'])) {
        $campos_adicionales['dientes_seleccionados'] = $test_data['dientes_seleccionados'];
    }
    
    $campos_adicionales_json = !empty($campos_adicionales) ? json_encode($campos_adicionales) : null;
    
    echo "<p><strong>Datos que se van a guardar:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Dientes seleccionados (columna):</strong> " . htmlspecialchars($test_data['dientes_seleccionados']) . "</li>";
    echo "<li><strong>Campos adicionales (JSON):</strong> " . htmlspecialchars($campos_adicionales_json) . "</li>";
    echo "</ul>";
    
    // Verificar si podemos hacer el INSERT
    echo "<h3>¿Proceder con el INSERT?</h3>";
    echo "<form method='post'>";
    echo "<input type='hidden' name='confirmar' value='1'>";
    foreach ($test_data as $key => $value) {
        echo "<input type='hidden' name='" . htmlspecialchars($key) . "' value='" . htmlspecialchars($value) . "'>";
    }
    echo "<button type='submit' style='background: green; color: white; padding: 10px;'>SÍ, INSERTAR CONSULTA DE PRUEBA</button>";
    echo "</form>";
    
    if (isset($_POST['confirmar'])) {
        echo "<h3>Ejecutando INSERT...</h3>";
        
        // Insertar consulta con campos adicionales y dientes seleccionados
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
            $_POST['paciente_id'],
            $_POST['doctor_id'],
            $_POST['fecha'],
            $_POST['motivo_consulta'],
            $_POST['diagnostico'],
            $_POST['tratamiento'],
            $_POST['observaciones'],
            $campos_adicionales_json,
            $especialidad_id,
            $_POST['dientes_seleccionados']
        ]);
        
        if ($success) {
            $consulta_id = $conn->lastInsertId();
            echo "<div style='background: lightgreen; padding: 10px; border: 1px solid green;'>";
            echo "<strong>¡Éxito!</strong> Consulta creada con ID: " . $consulta_id;
            echo "</div>";
            
            // Verificar los datos guardados
            $stmt = $conn->prepare("SELECT * FROM historial_medico WHERE id = ?");
            $stmt->execute([$consulta_id]);
            $consulta_guardada = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<h4>Datos guardados en la base de datos:</h4>";
            echo "<ul>";
            echo "<li><strong>ID:</strong> " . $consulta_guardada['id'] . "</li>";
            echo "<li><strong>Dientes seleccionados (columna):</strong> " . htmlspecialchars($consulta_guardada['dientes_seleccionados']) . "</li>";
            echo "<li><strong>Campos adicionales (JSON):</strong> " . htmlspecialchars($consulta_guardada['campos_adicionales']) . "</li>";
            echo "</ul>";
            
            // Verificar que los dientes están en el JSON
            $campos_json = json_decode($consulta_guardada['campos_adicionales'], true);
            $dientes_en_json = isset($campos_json['dientes_seleccionados']) ? $campos_json['dientes_seleccionados'] : 'No encontrados';
            
            echo "<h4>Verificación:</h4>";
            echo "<ul>";
            echo "<li><strong>Dientes en columna:</strong> " . ($consulta_guardada['dientes_seleccionados'] ? 'SÍ' : 'NO') . "</li>";
            echo "<li><strong>Dientes en JSON:</strong> " . ($dientes_en_json !== 'No encontrados' ? 'SÍ' : 'NO') . "</li>";
            echo "<li><strong>Valores coinciden:</strong> " . ($consulta_guardada['dientes_seleccionados'] === $dientes_en_json ? 'SÍ' : 'NO') . "</li>";
            echo "</ul>";
            
            echo "<p><a href='ver_consulta.php?id=" . $consulta_id . "' target='_blank'>Ver esta consulta</a></p>";
            
        } else {
            echo "<div style='background: lightcoral; padding: 10px; border: 1px solid red;'>";
            echo "<strong>Error:</strong> No se pudo crear la consulta.";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<p><a href="test_dientes_guardado.php">← Volver al test de verificación</a></p>
