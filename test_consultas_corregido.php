<?php
require_once "config.php";

echo "<h2>🧪 Test de Consultas - Verificación Post-Corrección</h2>";
echo "<div style='padding: 20px; font-family: Arial;'>";

// Verificar tabla historial_medico
try {
    $stmt = $conn->query("SELECT COUNT(*) as total FROM historial_medico");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p>✅ <strong>Tabla historial_medico:</strong> $total consultas registradas</p>";
} catch (Exception $e) {
    echo "<p>❌ <strong>Error en tabla historial_medico:</strong> " . $e->getMessage() . "</p>";
}

// Obtener algunos pacientes para hacer pruebas
try {
    $stmt = $conn->query("SELECT id, nombre, apellido FROM pacientes ORDER BY id DESC LIMIT 5");
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($pacientes) {
        echo "<h3>👥 Pacientes disponibles para probar consultas:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Acciones</th></tr>";
        
        foreach ($pacientes as $paciente) {
            echo "<tr>";
            echo "<td>" . $paciente['id'] . "</td>";
            echo "<td>" . htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']) . "</td>";
            echo "<td>";
            echo "<a href='nueva_consulta.php?paciente_id=" . $paciente['id'] . "' target='_blank' style='margin-right: 10px;'>Nueva Consulta</a>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p>❌ <strong>Error obteniendo pacientes:</strong> " . $e->getMessage() . "</p>";
}

// Verificar últimas consultas
try {
    $stmt = $conn->query("
        SELECT h.id, h.fecha, h.motivo_consulta, 
               CONCAT(p.nombre, ' ', p.apellido) as paciente
        FROM historial_medico h 
        JOIN pacientes p ON h.paciente_id = p.id 
        ORDER BY h.fecha DESC, h.id DESC 
        LIMIT 5
    ");
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($consultas) {
        echo "<h3>📋 Últimas consultas registradas:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Fecha</th><th>Paciente</th><th>Motivo</th></tr>";
        
        foreach ($consultas as $consulta) {
            echo "<tr>";
            echo "<td>" . $consulta['id'] . "</td>";
            echo "<td>" . date('d/m/Y', strtotime($consulta['fecha'])) . "</td>";
            echo "<td>" . htmlspecialchars($consulta['paciente']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($consulta['motivo_consulta'], 0, 50)) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>ℹ️ No hay consultas registradas aún.</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ <strong>Error obteniendo consultas:</strong> " . $e->getMessage() . "</p>";
}

echo "<div style='margin-top: 30px; padding: 15px; background: #e8f5e8; border: 1px solid #4caf50;'>";
echo "<h3>✅ Estado de la Corrección</h3>";
echo "<p><strong>Problema:</strong> Deslogueo al guardar consultas</p>";
echo "<p><strong>Causa:</strong> Headers already sent por outputs HTML antes de redirección</p>";
echo "<p><strong>Solución:</strong> Eliminados todos los echo problemáticos en nueva_consulta.php</p>";
echo "<p><strong>Resultado esperado:</strong> Al guardar consulta debe redirigir a imprimir_receta.php sin desloguear</p>";
echo "</div>";

echo "</div>";
?>
