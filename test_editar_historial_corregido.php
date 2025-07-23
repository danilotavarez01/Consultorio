<?php
require_once "config.php";

echo "<h2>🧪 Test de Edición de Historial Médico - Post-Corrección</h2>";
echo "<div style='padding: 20px; font-family: Arial;'>";

// Verificar tabla historial_medico
try {
    $stmt = $conn->query("SELECT COUNT(*) as total FROM historial_medico");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p>✅ <strong>Tabla historial_medico:</strong> $total consultas registradas</p>";
} catch (Exception $e) {
    echo "<p>❌ <strong>Error en tabla historial_medico:</strong> " . $e->getMessage() . "</p>";
}

// Obtener algunas consultas para hacer pruebas de edición
try {
    $stmt = $conn->query("
        SELECT h.id, h.fecha, h.motivo_consulta, 
               CONCAT(p.nombre, ' ', p.apellido) as paciente,
               p.id as paciente_id
        FROM historial_medico h 
        JOIN pacientes p ON h.paciente_id = p.id 
        ORDER BY h.fecha DESC, h.id DESC 
        LIMIT 5
    ");
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($consultas) {
        echo "<h3>📋 Consultas disponibles para editar:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Fecha</th><th>Paciente</th><th>Motivo</th><th>Acciones</th></tr>";
        
        foreach ($consultas as $consulta) {
            echo "<tr>";
            echo "<td>" . $consulta['id'] . "</td>";
            echo "<td>" . date('d/m/Y', strtotime($consulta['fecha'])) . "</td>";
            echo "<td>" . htmlspecialchars($consulta['paciente']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($consulta['motivo_consulta'], 0, 30)) . "...</td>";
            echo "<td>";
            echo "<a href='editar_consulta.php?id=" . $consulta['id'] . "' target='_blank' style='margin-right: 10px;'>✏️ Editar</a>";
            echo "<a href='historial_medico.php?id=" . $consulta['paciente_id'] . "' target='_blank'>📋 Ver Historial</a>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>ℹ️ No hay consultas registradas para editar.</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ <strong>Error obteniendo consultas:</strong> " . $e->getMessage() . "</p>";
}

echo "<div style='margin-top: 30px; padding: 15px; background: #e8f5e8; border: 1px solid #4caf50;'>";
echo "<h3>✅ Estado de la Corrección - Editar Historial</h3>";
echo "<p><strong>Problema:</strong> Deslogueo al editar consultas del historial médico</p>";
echo "<p><strong>Causa:</strong> Falta de redirección después de procesamiento exitoso del formulario</p>";
echo "<p><strong>Solución:</strong> Implementado patrón POST-redirect-GET en editar_consulta.php</p>";
echo "<p><strong>Resultado esperado:</strong> Al editar y guardar consulta debe mostrar mensaje de éxito sin desloguear</p>";
echo "</div>";

echo "<div style='margin-top: 15px; padding: 15px; background: #fff3cd; border: 1px solid #ffc107;'>";
echo "<h3>🧪 Instrucciones de Prueba:</h3>";
echo "<ol>";
echo "<li>Haz clic en '✏️ Editar' en cualquier consulta de la tabla</li>";
echo "<li>Modifica algunos datos (motivo, diagnóstico, etc.)</li>";
echo "<li>Haz clic en 'Actualizar Consulta'</li>";
echo "<li>Deberías ver mensaje verde 'Consulta actualizada correctamente'</li>";
echo "<li>Verifica que sigues logueado y no fuiste redirigido al login</li>";
echo "</ol>";
echo "</div>";

echo "</div>";
?>
