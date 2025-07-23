<?php
require_once 'config.php';

echo "<h1>Diagnóstico: Problema con guardado de dientes seleccionados</h1>";

try {
    // 1. Verificar estructura de la tabla
    echo "<h2>1. Estructura de la tabla historial_medico</h2>";
    $stmt = $conn->prepare("DESCRIBE historial_medico");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $tiene_dientes_col = false;
    $columna_dientes = null;
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Clave</th><th>Default</th></tr>";
    
    foreach ($columns as $col) {
        if ($col['Field'] === 'dientes_seleccionados') {
            $tiene_dientes_col = true;
            $columna_dientes = $col;
        }
        
        $color = ($col['Field'] === 'dientes_seleccionados') ? 'background-color: yellow;' : '';
        echo "<tr style='$color'>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($tiene_dientes_col) {
        echo "<p style='color: green;'><strong>✅ La columna 'dientes_seleccionados' SÍ existe en la tabla</strong></p>";
        echo "<p>Tipo: " . $columna_dientes['Type'] . ", Permite NULL: " . $columna_dientes['Null'] . "</p>";
    } else {
        echo "<p style='color: red;'><strong>❌ La columna 'dientes_seleccionados' NO existe en la tabla</strong></p>";
        echo "<p>Esto explica por qué no se guardan los dientes. Necesitamos agregar la columna.</p>";
    }
    
    // 2. Verificar las últimas consultas
    echo "<h2>2. Últimas 5 consultas en la base de datos</h2>";
    $stmt = $conn->prepare("
        SELECT 
            id, 
            fecha, 
            paciente_id,
            " . ($tiene_dientes_col ? "dientes_seleccionados," : "") . "
            campos_adicionales 
        FROM historial_medico 
        ORDER BY id DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($consultas)) {
        echo "<p>No hay consultas en la base de datos.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>Fecha</th>";
        echo "<th>Paciente ID</th>";
        if ($tiene_dientes_col) {
            echo "<th>Dientes Seleccionados</th>";
        }
        echo "<th>Campos Adicionales</th>";
        echo "<th>Dientes en JSON</th>";
        echo "</tr>";
        
        foreach ($consultas as $consulta) {
            $campos_json = json_decode($consulta['campos_adicionales'] ?? '{}', true);
            $dientes_en_json = isset($campos_json['dientes_seleccionados']) ? $campos_json['dientes_seleccionados'] : 'No';
            
            echo "<tr>";
            echo "<td>" . $consulta['id'] . "</td>";
            echo "<td>" . htmlspecialchars($consulta['fecha']) . "</td>";
            echo "<td>" . $consulta['paciente_id'] . "</td>";
            if ($tiene_dientes_col) {
                $dientes_columna = $consulta['dientes_seleccionados'] ?? 'NULL';
                echo "<td style='color: " . (empty($dientes_columna) || $dientes_columna === 'NULL' ? 'red' : 'green') . ";'>" . htmlspecialchars($dientes_columna) . "</td>";
            }
            echo "<td>" . htmlspecialchars(substr($consulta['campos_adicionales'] ?? 'NULL', 0, 50)) . "...</td>";
            echo "<td style='color: " . ($dientes_en_json === 'No' ? 'red' : 'green') . ";'>" . htmlspecialchars($dientes_en_json) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 3. Verificar el INSERT que se está ejecutando
    echo "<h2>3. Análisis del INSERT en nueva_consulta.php</h2>";
    
    // Simular los datos que se enviarían desde el formulario
    $test_post = [
        'action' => 'crear_consulta',
        'paciente_id' => '1',
        'doctor_id' => '1',
        'fecha' => date('Y-m-d'),
        'motivo_consulta' => 'Test diagnóstico',
        'diagnostico' => 'Test',
        'tratamiento' => 'Test',
        'observaciones' => 'Test',
        'dientes_seleccionados' => '11,12,21,22',
        'campo_presion' => '120/80'
    ];
    
    echo "<p><strong>Datos de prueba simulados:</strong></p>";
    echo "<ul>";
    foreach ($test_post as $key => $value) {
        echo "<li><strong>$key:</strong> " . htmlspecialchars($value) . "</li>";
    }
    echo "</ul>";
    
    // Simular la lógica de nueva_consulta.php
    $campos_adicionales = [];
    foreach ($test_post as $key => $value) {
        if (strpos($key, 'campo_') === 0) {
            $campo_nombre = substr($key, 6);
            $campos_adicionales[$campo_nombre] = $value;
        }
    }
    
    if (isset($test_post['dientes_seleccionados']) && !empty($test_post['dientes_seleccionados'])) {
        $campos_adicionales['dientes_seleccionados'] = $test_post['dientes_seleccionados'];
    }
    
    $campos_adicionales_json = !empty($campos_adicionales) ? json_encode($campos_adicionales) : null;
    
    echo "<p><strong>JSON que se generaría:</strong></p>";
    echo "<pre>" . htmlspecialchars($campos_adicionales_json) . "</pre>";
    
    // 4. Probar el INSERT si la columna existe
    if ($tiene_dientes_col) {
        echo "<h2>4. Test de INSERT</h2>";
        echo "<form method='post'>";
        echo "<input type='hidden' name='test_insert' value='1'>";
        echo "<button type='submit' style='background: orange; color: white; padding: 10px;'>Ejecutar INSERT de prueba</button>";
        echo "</form>";
        
        if (isset($_POST['test_insert'])) {
            try {
                $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
                $stmt->execute();
                $config = $stmt->fetch(PDO::FETCH_ASSOC);
                $especialidad_id = $config['especialidad_id'];
                
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
                $result = $stmt->execute([
                    $test_post['paciente_id'],
                    $test_post['doctor_id'],
                    $test_post['fecha'],
                    $test_post['motivo_consulta'],
                    $test_post['diagnostico'],
                    $test_post['tratamiento'],
                    $test_post['observaciones'],
                    $campos_adicionales_json,
                    $especialidad_id,
                    $test_post['dientes_seleccionados']
                ]);
                
                if ($result) {
                    $new_id = $conn->lastInsertId();
                    echo "<div style='background: lightgreen; padding: 10px; border: 1px solid green;'>";
                    echo "<strong>✅ INSERT exitoso!</strong> Nueva consulta ID: $new_id";
                    echo "</div>";
                    
                    // Verificar que se guardó correctamente
                    $stmt = $conn->prepare("SELECT dientes_seleccionados, campos_adicionales FROM historial_medico WHERE id = ?");
                    $stmt->execute([$new_id]);
                    $verificacion = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    echo "<p><strong>Verificación:</strong></p>";
                    echo "<ul>";
                    echo "<li><strong>Dientes en columna:</strong> " . htmlspecialchars($verificacion['dientes_seleccionados'] ?? 'NULL') . "</li>";
                    echo "<li><strong>Campos adicionales:</strong> " . htmlspecialchars($verificacion['campos_adicionales'] ?? 'NULL') . "</li>";
                    echo "</ul>";
                }
                
            } catch (Exception $e) {
                echo "<div style='background: lightcoral; padding: 10px; border: 1px solid red;'>";
                echo "<strong>❌ Error en INSERT:</strong> " . htmlspecialchars($e->getMessage());
                echo "</div>";
            }
        }
    } else {
        echo "<h2>4. Solución: Agregar columna faltante</h2>";
        echo "<p>La columna 'dientes_seleccionados' no existe. Vamos a crearla:</p>";
        echo "<form method='post'>";
        echo "<input type='hidden' name='crear_columna' value='1'>";
        echo "<button type='submit' style='background: red; color: white; padding: 10px;'>Crear columna dientes_seleccionados</button>";
        echo "</form>";
        
        if (isset($_POST['crear_columna'])) {
            try {
                $sql = "ALTER TABLE historial_medico ADD COLUMN dientes_seleccionados TEXT NULL";
                $conn->exec($sql);
                echo "<div style='background: lightgreen; padding: 10px; border: 1px solid green;'>";
                echo "<strong>✅ Columna creada exitosamente!</strong> Recarga la página para verificar.";
                echo "</div>";
            } catch (Exception $e) {
                echo "<div style='background: lightcoral; padding: 10px; border: 1px solid red;'>";
                echo "<strong>❌ Error creando columna:</strong> " . htmlspecialchars($e->getMessage());
                echo "</div>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>Error general: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<style>
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
</style>
