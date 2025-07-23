<?php
require_once 'config.php';

echo "<h1>Test Completo: Guardado y Visualización de Dientes Seleccionados</h1>";
echo "<p>Este script verifica que los dientes seleccionados se guarden correctamente en ambos lugares y se muestren correctamente en ver_consulta.php</p>";

try {
    // 1. Verificar que la tabla tiene las columnas necesarias
    echo "<h2>1. Verificación de Estructura de Base de Datos</h2>";
    
    $stmt = $conn->prepare("DESCRIBE historial_medico");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $tiene_dientes_col = false;
    $tiene_campos_col = false;
    
    echo "<table border='1'><tr><th>Columna</th><th>Tipo</th><th>Null</th><th>Clave</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        if ($col['Field'] === 'dientes_seleccionados') $tiene_dientes_col = true;
        if ($col['Field'] === 'campos_adicionales') $tiene_campos_col = true;
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><strong>✓ Columna 'dientes_seleccionados':</strong> " . ($tiene_dientes_col ? 'Existe' : 'NO EXISTE') . "</p>";
    echo "<p><strong>✓ Columna 'campos_adicionales':</strong> " . ($tiene_campos_col ? 'Existe' : 'NO EXISTE') . "</p>";
    
    // 2. Verificar configuración de especialidad
    echo "<h2>2. Verificación de Configuración de Especialidad</h2>";
    
    $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        $especialidad_id = $config['especialidad_id'];
        echo "<p><strong>Especialidad ID configurada:</strong> " . $especialidad_id . "</p>";
        
        $stmt = $conn->prepare("SELECT * FROM especialidades WHERE id = ?");
        $stmt->execute([$especialidad_id]);
        $especialidad = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($especialidad) {
            echo "<p><strong>Nombre:</strong> " . htmlspecialchars($especialidad['nombre']) . "</p>";
            echo "<p><strong>Código:</strong> " . htmlspecialchars($especialidad['codigo'] ?? 'N/A') . "</p>";
            $es_odontologia = stripos($especialidad['nombre'], 'odonto') !== false || 
                             stripos($especialidad['codigo'], 'odonto') !== false;
            echo "<p><strong>¿Es Odontología?:</strong> " . ($es_odontologia ? 'SÍ' : 'NO') . "</p>";
        } else {
            echo "<p style='color: red;'>⚠️ No se encontró la especialidad con ID " . $especialidad_id . "</p>";
        }
    } else {
        echo "<p style='color: red;'>⚠️ No hay configuración de especialidad</p>";
    }
    
    // 3. Probar la lógica de guardado
    echo "<h2>3. Test de Lógica de Guardado</h2>";
    
    $test_data = [
        'dientes_seleccionados' => '11,12,21,22',
        'campo_presion' => '120/80',
        'campo_temperatura' => '36.5'
    ];
    
    // Simular la lógica de nueva_consulta.php
    $campos_adicionales = [];
    foreach ($test_data as $key => $value) {
        if (strpos($key, 'campo_') === 0) {
            $campo_nombre = substr($key, 6);
            $campos_adicionales[$campo_nombre] = $value;
        }
    }
    
    // Agregar los dientes seleccionados al array de campos adicionales
    if (isset($test_data['dientes_seleccionados']) && !empty($test_data['dientes_seleccionados'])) {
        $campos_adicionales['dientes_seleccionados'] = $test_data['dientes_seleccionados'];
    }
    
    $campos_adicionales_json = !empty($campos_adicionales) ? json_encode($campos_adicionales) : null;
    
    echo "<p><strong>Datos de prueba:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Dientes (columna):</strong> " . htmlspecialchars($test_data['dientes_seleccionados']) . "</li>";
    echo "<li><strong>JSON generado:</strong> " . htmlspecialchars($campos_adicionales_json) . "</li>";
    echo "</ul>";
    
    // 4. Probar la lógica de lectura (como en ver_consulta.php)
    echo "<h2>4. Test de Lógica de Lectura</h2>";
    
    // Simular datos de consulta con diferentes escenarios
    $escenarios = [
        'Dientes en columna y JSON' => [
            'dientes_seleccionados' => '11,12,21,22',
            'campos_adicionales' => '{"presion":"120/80","dientes_seleccionados":"11,12,21,22"}'
        ],
        'Solo dientes en columna' => [
            'dientes_seleccionados' => '31,32,33,34',
            'campos_adicionales' => '{"presion":"110/70"}'
        ],
        'Solo dientes en JSON' => [
            'dientes_seleccionados' => '',
            'campos_adicionales' => '{"temperatura":"36.8","dientes_seleccionados":"41,42,43,44"}'
        ],
        'Sin dientes' => [
            'dientes_seleccionados' => '',
            'campos_adicionales' => '{"presion":"130/85"}'
        ]
    ];
    
    echo "<table border='1' style='width:100%; border-collapse: collapse;'>";
    echo "<tr><th>Escenario</th><th>Columna</th><th>JSON</th><th>Resultado Final</th><th>¿Tiene Dientes?</th></tr>";
    
    foreach ($escenarios as $nombre => $datos) {
        // Simular la lógica de ver_consulta.php
        $dientes_seleccionados = $datos['dientes_seleccionados'];
        
        if (empty($dientes_seleccionados) || trim($dientes_seleccionados) === '') {
            $campos_adicionales = json_decode($datos['campos_adicionales'] ?? '{}', true);
            if (isset($campos_adicionales['dientes_seleccionados'])) {
                $dientes_seleccionados = $campos_adicionales['dientes_seleccionados'];
            }
        }
        
        $tiene_dientes = !empty($dientes_seleccionados) && trim($dientes_seleccionados) !== '';
        
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($nombre) . "</strong></td>";
        echo "<td>" . htmlspecialchars($datos['dientes_seleccionados']) . "</td>";
        echo "<td>" . htmlspecialchars($datos['campos_adicionales']) . "</td>";
        echo "<td>" . htmlspecialchars($dientes_seleccionados) . "</td>";
        echo "<td style='color:" . ($tiene_dientes ? 'green' : 'red') . ";'>" . ($tiene_dientes ? 'SÍ' : 'NO') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 5. Verificar consultas reales recientes
    echo "<h2>5. Consultas Reales Recientes</h2>";
    
    $stmt = $conn->prepare("
        SELECT 
            h.id,
            h.fecha,
            h.dientes_seleccionados,
            h.campos_adicionales,
            p.nombre as paciente_nombre,
            e.nombre as especialidad_nombre
        FROM historial_medico h
        LEFT JOIN pacientes p ON h.paciente_id = p.id
        LEFT JOIN especialidades e ON h.especialidad_id = e.id
        ORDER BY h.fecha DESC, h.id DESC
        LIMIT 10
    ");
    $stmt->execute();
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($consultas)) {
        echo "<p>No hay consultas en la base de datos.</p>";
    } else {
        echo "<table border='1' style='width:100%; border-collapse: collapse;'>";
        echo "<tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Paciente</th>
                <th>Especialidad</th>
                <th>Dientes (Columna)</th>
                <th>Dientes (JSON)</th>
                <th>¿Mostraría Odontograma?</th>
                <th>Acción</th>
              </tr>";
        
        foreach ($consultas as $consulta) {
            // Aplicar la misma lógica que ver_consulta.php
            $dientes_seleccionados = $consulta['dientes_seleccionados'];
            $campos_json = json_decode($consulta['campos_adicionales'] ?? '{}', true);
            $dientes_en_json = $campos_json['dientes_seleccionados'] ?? '';
            
            if (empty($dientes_seleccionados) || trim($dientes_seleccionados) === '') {
                $dientes_seleccionados = $dientes_en_json;
            }
            
            $tiene_dientes = !empty($dientes_seleccionados) && trim($dientes_seleccionados) !== '';
            $es_odontologia = stripos($consulta['especialidad_nombre'], 'odonto') !== false;
            $mostraria_odontograma = $tiene_dientes && $es_odontologia;
            
            echo "<tr>";
            echo "<td>" . $consulta['id'] . "</td>";
            echo "<td>" . htmlspecialchars($consulta['fecha']) . "</td>";
            echo "<td>" . htmlspecialchars($consulta['paciente_nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($consulta['especialidad_nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($consulta['dientes_seleccionados'] ?: 'Vacío') . "</td>";
            echo "<td>" . htmlspecialchars($dientes_en_json ?: 'Vacío') . "</td>";
            echo "<td style='color:" . ($mostraria_odontograma ? 'green' : 'red') . ";'>" . ($mostraria_odontograma ? 'SÍ' : 'NO') . "</td>";
            echo "<td><a href='ver_consulta.php?id=" . $consulta['id'] . "' target='_blank'>Ver</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 6. Enlaces para pruebas manuales
    echo "<h2>6. Pruebas Manuales</h2>";
    echo "<ul>";
    echo "<li><a href='nueva_consulta.php' target='_blank'>Crear nueva consulta</a> - Crear una consulta con dientes seleccionados</li>";
    echo "<li><a href='test_simulacion_consulta.php' target='_blank'>Simulación de consulta</a> - Insertar datos de prueba</li>";
    echo "<li><a href='test_dientes_guardado.php' target='_blank'>Verificar guardado</a> - Ver datos guardados</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #ffebee; padding: 10px; border: 1px solid red;'>";
    echo "<strong>Error:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}
?>

<style>
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
h2 { color: #333; margin-top: 30px; }
h1 { color: #2c3e50; }
</style>
