<?php
require_once 'config.php';

echo "<h1>🔍 Diagnóstico: Guardado de Dientes Seleccionados</h1>";

// 1. Verificar estructura de la tabla historial_medico
echo "<h2>1. Estructura de la tabla historial_medico</h2>";
try {
    $stmt = $conn->prepare("DESCRIBE historial_medico");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $tiene_dientes_col = false;
    $tiene_campos_col = false;
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Columna</th><th>Tipo</th><th>Null</th><th>Clave</th><th>Default</th><th>Extra</th></tr>";
    
    foreach ($columns as $col) {
        if ($col['Field'] === 'dientes_seleccionados') $tiene_dientes_col = true;
        if ($col['Field'] === 'campos_adicionales') $tiene_campos_col = true;
        
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($col['Field']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($col['Extra'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<div style='margin: 15px 0; padding: 10px; border: 2px solid " . ($tiene_dientes_col ? 'green' : 'red') . ";'>";
    echo "<strong>Columna 'dientes_seleccionados':</strong> " . ($tiene_dientes_col ? '✅ EXISTE' : '❌ NO EXISTE');
    echo "</div>";
    
    echo "<div style='margin: 15px 0; padding: 10px; border: 2px solid " . ($tiene_campos_col ? 'green' : 'red') . ";'>";
    echo "<strong>Columna 'campos_adicionales':</strong> " . ($tiene_campos_col ? '✅ EXISTE' : '❌ NO EXISTE');
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>Error al verificar estructura: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// 2. Verificar datos recientes
echo "<h2>2. Últimas 10 consultas en historial_medico</h2>";
try {
    $stmt = $conn->prepare("
        SELECT 
            h.id,
            h.fecha,
            h.dientes_seleccionados,
            h.campos_adicionales,
            p.nombre as paciente_nombre
        FROM historial_medico h
        LEFT JOIN pacientes p ON h.paciente_id = p.id
        ORDER BY h.id DESC
        LIMIT 10
    ");
    $stmt->execute();
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($consultas)) {
        echo "<p style='color: orange;'>⚠️ No hay consultas en la tabla historial_medico</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Fecha</th><th>Paciente</th><th>Dientes (Columna)</th><th>Campos Adicionales</th><th>Dientes en JSON</th></tr>";
        
        foreach ($consultas as $consulta) {
            $campos_json = json_decode($consulta['campos_adicionales'] ?? '{}', true);
            $dientes_en_json = isset($campos_json['dientes_seleccionados']) ? $campos_json['dientes_seleccionados'] : 'No';
            
            echo "<tr>";
            echo "<td>" . $consulta['id'] . "</td>";
            echo "<td>" . htmlspecialchars($consulta['fecha']) . "</td>";
            echo "<td>" . htmlspecialchars($consulta['paciente_nombre'] ?? 'N/A') . "</td>";
            echo "<td style='background: " . (empty($consulta['dientes_seleccionados']) ? 'lightcoral' : 'lightgreen') . ";'>";
            echo htmlspecialchars($consulta['dientes_seleccionados'] ?: 'VACÍO');
            echo "</td>";
            echo "<td>" . htmlspecialchars($consulta['campos_adicionales'] ?: 'NULL') . "</td>";
            echo "<td style='background: " . ($dientes_en_json === 'No' ? 'lightcoral' : 'lightgreen') . ";'>";
            echo htmlspecialchars($dientes_en_json);
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>Error al consultar datos: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// 3. Simular proceso de guardado
echo "<h2>3. Simulación del Proceso de Guardado</h2>";

// Simular datos POST como los enviaría el formulario
$_POST_simulado = [
    'action' => 'crear_consulta',
    'paciente_id' => '1',
    'doctor_id' => '1',
    'fecha' => date('Y-m-d'),
    'motivo_consulta' => 'Test de dientes',
    'diagnostico' => 'Caries',
    'tratamiento' => 'Empaste',
    'observaciones' => 'Test',
    'dientes_seleccionados' => '11,12,21,22',
    'campo_presion' => '120/80'
];

echo "<h3>Datos POST simulados:</h3>";
echo "<pre>" . print_r($_POST_simulado, true) . "</pre>";

// Simular la lógica de nueva_consulta.php
echo "<h3>Procesamiento paso a paso:</h3>";

// Paso 1: Obtener especialidad
try {
    $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    $especialidad_id = $config['especialidad_id'] ?? null;
    
    echo "<p><strong>Paso 1 - Especialidad ID:</strong> " . ($especialidad_id ?? 'NO CONFIGURADA') . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Paso 1 ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    $especialidad_id = 1; // Valor por defecto
}

// Paso 2: Preparar campos adicionales
$campos_adicionales = [];
foreach ($_POST_simulado as $key => $value) {
    if (strpos($key, 'campo_') === 0) {
        $campo_nombre = substr($key, 6);
        $campos_adicionales[$campo_nombre] = $value;
    }
}

// Agregar dientes al JSON
if (isset($_POST_simulado['dientes_seleccionados']) && !empty($_POST_simulado['dientes_seleccionados'])) {
    $campos_adicionales['dientes_seleccionados'] = $_POST_simulado['dientes_seleccionados'];
}

$campos_adicionales_json = !empty($campos_adicionales) ? json_encode($campos_adicionales) : null;

echo "<p><strong>Paso 2 - Campos adicionales array:</strong></p>";
echo "<pre>" . print_r($campos_adicionales, true) . "</pre>";
echo "<p><strong>Paso 2 - JSON generado:</strong> " . htmlspecialchars($campos_adicionales_json) . "</p>";

// Paso 3: Preparar SQL
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

$parametros = [
    $_POST_simulado['paciente_id'],
    $_POST_simulado['doctor_id'],
    $_POST_simulado['fecha'],
    $_POST_simulado['motivo_consulta'],
    $_POST_simulado['diagnostico'],
    $_POST_simulado['tratamiento'],
    $_POST_simulado['observaciones'],
    $campos_adicionales_json,
    $especialidad_id,
    $_POST_simulado['dientes_seleccionados']
];

echo "<p><strong>Paso 3 - SQL:</strong></p>";
echo "<pre>" . htmlspecialchars($sql) . "</pre>";
echo "<p><strong>Paso 3 - Parámetros:</strong></p>";
echo "<pre>" . print_r($parametros, true) . "</pre>";

// 4. Botón para ejecutar la prueba
echo "<h2>4. Ejecutar Prueba Real</h2>";
if (isset($_POST['ejecutar_prueba'])) {
    try {
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute($parametros);
        
        if ($success) {
            $consulta_id = $conn->lastInsertId();
            echo "<div style='background: lightgreen; padding: 15px; border: 2px solid green;'>";
            echo "<h3>✅ ¡Prueba EXITOSA!</h3>";
            echo "<p><strong>ID de consulta creada:</strong> " . $consulta_id . "</p>";
            echo "</div>";
            
            // Verificar los datos guardados
            $stmt = $conn->prepare("SELECT dientes_seleccionados, campos_adicionales FROM historial_medico WHERE id = ?");
            $stmt->execute([$consulta_id]);
            $datos_guardados = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<h3>Verificación de datos guardados:</h3>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Campo</th><th>Valor Guardado</th><th>Estado</th></tr>";
            
            $dientes_columna = $datos_guardados['dientes_seleccionados'];
            $campos_json = json_decode($datos_guardados['campos_adicionales'], true);
            $dientes_json = $campos_json['dientes_seleccionados'] ?? null;
            
            echo "<tr>";
            echo "<td>dientes_seleccionados (columna)</td>";
            echo "<td>" . htmlspecialchars($dientes_columna ?? 'NULL') . "</td>";
            echo "<td style='color: " . (!empty($dientes_columna) ? 'green' : 'red') . ";'>";
            echo (!empty($dientes_columna) ? '✅ GUARDADO' : '❌ VACÍO');
            echo "</td>";
            echo "</tr>";
            
            echo "<tr>";
            echo "<td>dientes_seleccionados (JSON)</td>";
            echo "<td>" . htmlspecialchars($dientes_json ?? 'NULL') . "</td>";
            echo "<td style='color: " . (!empty($dientes_json) ? 'green' : 'red') . ";'>";
            echo (!empty($dientes_json) ? '✅ GUARDADO' : '❌ VACÍO');
            echo "</td>";
            echo "</tr>";
            echo "</table>";
            
            echo "<p><a href='/ver_consulta.php?id=" . $consulta_id . "' target='_blank'>🔍 Ver esta consulta</a></p>";
            
        } else {
            echo "<div style='background: lightcoral; padding: 15px; border: 2px solid red;'>";
            echo "<h3>❌ Error en la prueba</h3>";
            echo "<p>No se pudo ejecutar el INSERT</p>";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div style='background: lightcoral; padding: 15px; border: 2px solid red;'>";
        echo "<h3>❌ EXCEPCIÓN en la prueba:</h3>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
} else {
    echo "<form method='post'>";
    echo "<input type='hidden' name='ejecutar_prueba' value='1'>";
    echo "<button type='submit' style='background: blue; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px;'>";
    echo "🧪 EJECUTAR PRUEBA DE GUARDADO";
    echo "</button>";
    echo "</form>";
    echo "<p><em>Esto creará una consulta de prueba para verificar que el guardado funciona</em></p>";
}

// 5. Verificar si la columna existe y crearla si es necesario
echo "<h2>5. Reparación Automática</h2>";
if (!$tiene_dientes_col) {
    echo "<div style='background: yellow; padding: 10px; border: 2px solid orange;'>";
    echo "<h3>⚠️ PROBLEMA DETECTADO: Falta la columna 'dientes_seleccionados'</h3>";
    
    if (isset($_POST['crear_columna'])) {
        try {
            $sql_add_column = "ALTER TABLE historial_medico ADD COLUMN dientes_seleccionados TEXT";
            $conn->exec($sql_add_column);
            
            echo "<div style='background: lightgreen; padding: 10px; margin: 10px 0;'>";
            echo "✅ Columna 'dientes_seleccionados' creada exitosamente";
            echo "</div>";
            echo "<p><a href='' style='background: green; color: white; padding: 10px; text-decoration: none;'>🔄 Recargar página</a></p>";
            
        } catch (Exception $e) {
            echo "<div style='background: lightcoral; padding: 10px; margin: 10px 0;'>";
            echo "❌ Error al crear columna: " . htmlspecialchars($e->getMessage());
            echo "</div>";
        }
    } else {
        echo "<form method='post' style='margin: 10px 0;'>";
        echo "<input type='hidden' name='crear_columna' value='1'>";
        echo "<button type='submit' style='background: orange; color: white; padding: 10px; border: none;'>";
        echo "🔧 CREAR COLUMNA 'dientes_seleccionados'";
        echo "</button>";
        echo "</form>";
    }
    echo "</div>";
}

echo "<h2>6. Enlaces Útiles</h2>";
echo "<ul>";
echo "<li><a href='/nueva_consulta.php' target='_blank'>Formulario de nueva consulta</a></li>";
echo "<li><a href='/test_completo_dientes.php' target='_blank'>Test completo del sistema</a></li>";
echo "<li><a href='/test_crear_consulta_rapido.php' target='_blank'>Crear consulta de prueba rápida</a></li>";
echo "</ul>";
?>

<style>
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
h1 { color: #2c3e50; }
h2 { color: #34495e; margin-top: 30px; }
h3 { color: #7f8c8d; }
pre { background: #f8f9fa; padding: 10px; border: 1px solid #ddd; overflow-x: auto; }
</style>
