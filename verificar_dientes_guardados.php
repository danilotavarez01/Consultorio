<?php
// Script para verificar que los dientes seleccionados se guardan correctamente

require_once "config.php";

echo "<h3>Últimas consultas con dientes seleccionados:</h3>";

try {
    // Obtener las últimas 10 consultas que tienen dientes seleccionados
    $sql = "SELECT 
                hm.id,
                hm.fecha,
                CONCAT(p.nombre, ' ', p.apellido) as paciente,
                hm.dientes_seleccionados,
                hm.fecha_registro
            FROM historial_medico hm
            LEFT JOIN pacientes p ON hm.paciente_id = p.id
            WHERE hm.dientes_seleccionados IS NOT NULL 
            AND hm.dientes_seleccionados != ''
            ORDER BY hm.fecha_registro DESC
            LIMIT 10";
    
    $stmt = $conn->query($sql);
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($consultas) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>ID Consulta</th><th>Fecha</th><th>Paciente</th><th>Dientes Seleccionados</th><th>Fecha Registro</th>";
        echo "</tr>";
        
        foreach ($consultas as $consulta) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($consulta['id']) . "</td>";
            echo "<td>" . htmlspecialchars($consulta['fecha']) . "</td>";
            echo "<td>" . htmlspecialchars($consulta['paciente']) . "</td>";
            echo "<td><strong>" . htmlspecialchars($consulta['dientes_seleccionados']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($consulta['fecha_registro']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No se encontraron consultas con dientes seleccionados aún.</p>";
        echo "<p><em>Haz una prueba creando una nueva consulta con dientes seleccionados en el odontograma.</em></p>";
    }
    
    // Mostrar también el total de consultas
    $stmt = $conn->query("SELECT COUNT(*) as total FROM historial_medico");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<br><p><strong>Total de consultas en la base de datos:</strong> " . $total['total'] . "</p>";
    
    // Mostrar consultas recientes (últimas 5, con o sin dientes)
    echo "<h4>Últimas 5 consultas (todas):</h4>";
    $sql = "SELECT 
                hm.id,
                hm.fecha,
                CONCAT(p.nombre, ' ', p.apellido) as paciente,
                hm.dientes_seleccionados,
                CASE 
                    WHEN hm.dientes_seleccionados IS NULL OR hm.dientes_seleccionados = '' 
                    THEN 'Sin dientes'
                    ELSE 'Con dientes'
                END as estado_dientes
            FROM historial_medico hm
            LEFT JOIN pacientes p ON hm.paciente_id = p.id
            ORDER BY hm.fecha_registro DESC
            LIMIT 5";
    
    $stmt = $conn->query($sql);
    $consultasRecientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($consultasRecientes) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>ID</th><th>Fecha</th><th>Paciente</th><th>Estado Dientes</th><th>Dientes</th>";
        echo "</tr>";
        
        foreach ($consultasRecientes as $consulta) {
            $colorFila = ($consulta['dientes_seleccionados'] && $consulta['dientes_seleccionados'] != '') 
                ? 'background-color: #e8f5e8;' : '';
            
            echo "<tr style='$colorFila'>";
            echo "<td>" . htmlspecialchars($consulta['id']) . "</td>";
            echo "<td>" . htmlspecialchars($consulta['fecha']) . "</td>";
            echo "<td>" . htmlspecialchars($consulta['paciente']) . "</td>";
            echo "<td>" . htmlspecialchars($consulta['estado_dientes']) . "</td>";
            echo "<td>" . htmlspecialchars($consulta['dientes_seleccionados'] ?: 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<br><br>
<a href="nueva_consulta.php">← Volver a Nueva Consulta</a>
<a href="pacientes.php" style="margin-left: 20px;">← Volver a Pacientes</a>
