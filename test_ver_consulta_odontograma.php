<?php
// Script para probar la visualización de consultas con odontograma

require_once "config.php";

echo "<h2>🔍 Prueba de Visualización de Consultas con Odontograma</h2>";

try {
    // Buscar consultas que tengan dientes seleccionados
    $sql = "SELECT 
                hm.id,
                hm.fecha,
                CONCAT(p.nombre, ' ', p.apellido) as paciente,
                hm.dientes_seleccionados,
                hm.motivo_consulta
            FROM historial_medico hm
            LEFT JOIN pacientes p ON hm.paciente_id = p.id
            WHERE hm.dientes_seleccionados IS NOT NULL 
            AND hm.dientes_seleccionados != ''
            ORDER BY hm.fecha_registro DESC
            LIMIT 5";
    
    $stmt = $conn->query($sql);
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($consultas) > 0) {
        echo "<h3>Consultas con Dientes Seleccionados (para probar):</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>ID</th><th>Fecha</th><th>Paciente</th><th>Dientes</th><th>Motivo</th><th>Acciones</th>";
        echo "</tr>";
        
        foreach ($consultas as $consulta) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($consulta['id']) . "</td>";
            echo "<td>" . htmlspecialchars($consulta['fecha']) . "</td>";
            echo "<td>" . htmlspecialchars($consulta['paciente']) . "</td>";
            echo "<td><strong>" . htmlspecialchars($consulta['dientes_seleccionados']) . "</strong></td>";
            echo "<td>" . htmlspecialchars(substr($consulta['motivo_consulta'], 0, 30)) . "...</td>";
            echo "<td>";
            echo "<a href='ver_consulta.php?id=" . $consulta['id'] . "' target='_blank' ";
            echo "style='padding: 5px 10px; background: #007bff; color: white; text-decoration: none; border-radius: 3px; font-size: 12px;'>";
            echo "👁️ Ver con Odontograma</a>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<div style='background-color: #d1ecf1; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>🧪 Instrucciones de Prueba:</h4>";
        echo "<ol>";
        echo "<li><strong>Haz clic en 'Ver con Odontograma'</strong> en cualquiera de las consultas de arriba</li>";
        echo "<li><strong>Deberías ver:</strong>";
        echo "<ul>";
        echo "<li>Los detalles normales de la consulta</li>";
        echo "<li>Una sección 'Odontograma - Dientes Tratados'</li>";
        echo "<li>El odontograma visual con los dientes marcados en verde</li>";
        echo "<li>Una lista de los dientes seleccionados</li>";
        echo "</ul></li>";
        echo "<li><strong>Los dientes marcados</strong> aparecerán en <span style='color: #28a745; font-weight: bold;'>verde</span> y serán de solo lectura</li>";
        echo "<li><strong>Verifica</strong> que los números de dientes coincidan con los mostrados en la tabla</li>";
        echo "</ol>";
        echo "</div>";
        
        // Mostrar ejemplo específico
        $ejemploConsulta = $consultas[0];
        echo "<div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>📋 Ejemplo de Prueba:</h4>";
        echo "<p><strong>Consulta ID:</strong> " . htmlspecialchars($ejemploConsulta['id']) . "</p>";
        echo "<p><strong>Dientes que deberían aparecer marcados:</strong> <code>" . htmlspecialchars($ejemploConsulta['dientes_seleccionados']) . "</code></p>";
        echo "<p><strong>Enlace directo:</strong> ";
        echo "<a href='ver_consulta.php?id=" . $ejemploConsulta['id'] . "' target='_blank' ";
        echo "style='padding: 8px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>";
        echo "🚀 Probar Ahora</a></p>";
        echo "</div>";
        
    } else {
        echo "<div style='background-color: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>❌ No hay consultas con dientes para probar</h4>";
        echo "<p>Necesitas crear una consulta con dientes seleccionados primero.</p>";
        echo "<p><a href='test_formulario_actual.php' style='padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>🧪 Crear Consulta de Prueba</a></p>";
        echo "</div>";
    }
    
    echo "<h3>Enlaces Útiles:</h3>";
    echo "<p>";
    echo "<a href='verificar_dientes_guardados.php' style='padding: 10px 15px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📊 Ver Todas las Consultas</a>";
    echo "<a href='nueva_consulta.php?paciente_id=1' style='padding: 10px 15px; background: #17a2b8; color: white; text-decoration: none; border-radius: 5px;'>➕ Nueva Consulta</a>";
    echo "</p>";
    
} catch (PDOException $e) {
    echo "<div style='background-color: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h4>❌ Error:</h4>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #0056b3; }
h3 { color: #333; }
table { font-size: 14px; }
th, td { padding: 8px; text-align: left; }
</style>
