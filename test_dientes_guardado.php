<?php
require_once 'config.php';

echo "<h2>Test de Guardado de Dientes Seleccionados</h2>";

try {
    // Consultar las últimas 5 consultas para verificar el guardado
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
        ORDER BY h.fecha DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Últimas 5 consultas:</h3>";
    echo "<table border='1' style='width:100%; border-collapse: collapse;'>";
    echo "<tr>
            <th>ID</th>
            <th>Fecha</th>
            <th>Paciente</th>
            <th>Especialidad</th>
            <th>Dientes Seleccionados (columna)</th>
            <th>Campos Adicionales (JSON)</th>
            <th>Dientes en JSON</th>
          </tr>";

    foreach ($consultas as $consulta) {
        $campos_adicionales = json_decode($consulta['campos_adicionales'], true);
        $dientes_en_json = isset($campos_adicionales['dientes_seleccionados']) ? $campos_adicionales['dientes_seleccionados'] : 'No disponible';
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($consulta['id']) . "</td>";
        echo "<td>" . htmlspecialchars($consulta['fecha']) . "</td>";
        echo "<td>" . htmlspecialchars($consulta['paciente_nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($consulta['especialidad_nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($consulta['dientes_seleccionados'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($consulta['campos_adicionales'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($dientes_en_json) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Verificar si hay especialidad de Odontología configurada
    echo "<h3>Verificación de Especialidad:</h3>";
    $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        $especialidad_id = $config['especialidad_id'];
        echo "<p>Especialidad ID configurada: " . $especialidad_id . "</p>";
        
        $stmt = $conn->prepare("SELECT nombre FROM especialidades WHERE id = ?");
        $stmt->execute([$especialidad_id]);
        $especialidad = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($especialidad) {
            echo "<p>Nombre de especialidad: " . htmlspecialchars($especialidad['nombre']) . "</p>";
            echo "<p>¿Es Odontología?: " . (stripos($especialidad['nombre'], 'odonto') !== false ? 'SÍ' : 'NO') . "</p>";
        }
    }

    // Estadísticas
    echo "<h3>Estadísticas:</h3>";
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_consultas,
            COUNT(dientes_seleccionados) as consultas_con_dientes_columna,
            COUNT(CASE WHEN campos_adicionales LIKE '%dientes_seleccionados%' THEN 1 END) as consultas_con_dientes_json
        FROM historial_medico 
        WHERE fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<ul>";
    echo "<li>Total consultas (últimos 30 días): " . $stats['total_consultas'] . "</li>";
    echo "<li>Consultas con dientes en columna: " . $stats['consultas_con_dientes_columna'] . "</li>";
    echo "<li>Consultas con dientes en JSON: " . $stats['consultas_con_dientes_json'] . "</li>";
    echo "</ul>";

} catch (Exception $e) {
    echo "<div style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<h3>Instrucciones para prueba:</h3>
<ol>
    <li>Vaya a <a href="nueva_consulta.php" target="_blank">nueva_consulta.php</a></li>
    <li>Cree una nueva consulta seleccionando algunos dientes en el odontograma</li>
    <li>Regrese a esta página para verificar que los dientes se guardaron tanto en la columna como en el JSON</li>
    <li>Verifique en <a href="ver_consulta.php" target="_blank">ver_consulta.php</a> que el odontograma se muestra correctamente</li>
</ol>
