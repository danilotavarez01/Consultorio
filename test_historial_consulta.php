<?php
// Test específico para la consulta del historial médico
require_once 'session_config.php';
session_start();
require_once "config.php";

echo "<h3>🧪 Test de Consulta Historial Médico</h3>";

// Test con un paciente de ejemplo (usar ID 1 si existe)
$test_paciente_id = 1;

try {
    // 1. Verificar estructura de tabla usuarios
    echo "<h4>📋 Estructura de tabla usuarios:</h4>";
    $columnasUsuarios = [];
    $stmtColumns = $conn->query("DESCRIBE usuarios");
    $columns = $stmtColumns->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<ul>";
    foreach ($columns as $column) {
        $columnasUsuarios[] = $column['Field'];
        echo "<li><strong>" . $column['Field'] . "</strong> (" . $column['Type'] . ")</li>";
    }
    echo "</ul>";
    
    // 2. Construir consulta dinámica
    echo "<h4>🔧 Consulta que se va a usar:</h4>";
    
    if (in_array('nombre', $columnasUsuarios) && in_array('apellido', $columnasUsuarios)) {
        $sql = "SELECT hm.*, u.nombre as medico_nombre, u.apellido as medico_apellido 
                FROM historial_medico hm 
                LEFT JOIN usuarios u ON hm.doctor_id = u.id 
                WHERE hm.paciente_id = ? 
                ORDER BY hm.fecha DESC";
        echo "<p style='color: green;'>✅ Usando consulta con nombre y apellido</p>";
    } elseif (in_array('nombre', $columnasUsuarios)) {
        $sql = "SELECT hm.*, u.nombre as medico_nombre 
                FROM historial_medico hm 
                LEFT JOIN usuarios u ON hm.doctor_id = u.id 
                WHERE hm.paciente_id = ? 
                ORDER BY hm.fecha DESC";
        echo "<p style='color: orange;'>⚠️ Usando consulta solo con nombre</p>";
    } elseif (in_array('username', $columnasUsuarios)) {
        $sql = "SELECT hm.*, u.username as medico_nombre 
                FROM historial_medico hm 
                LEFT JOIN usuarios u ON hm.doctor_id = u.id 
                WHERE hm.paciente_id = ? 
                ORDER BY hm.fecha DESC";
        echo "<p style='color: orange;'>⚠️ Usando consulta con username</p>";
    } else {
        $sql = "SELECT hm.*, CONCAT('Doctor ID: ', hm.doctor_id) as medico_nombre 
                FROM historial_medico hm 
                WHERE hm.paciente_id = ? 
                ORDER BY hm.fecha DESC";
        echo "<p style='color: red;'>❌ Usando consulta sin JOIN</p>";
    }
    
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
    echo htmlspecialchars($sql);
    echo "</pre>";
    
    // 3. Probar la consulta
    echo "<h4>🚀 Ejecutar consulta de prueba:</h4>";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$test_paciente_id]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Registros encontrados:</strong> " . count($resultados) . "</p>";
    
    if (count($resultados) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f8f9fa;'>";
        foreach (array_keys($resultados[0]) as $columnName) {
            echo "<th style='padding: 5px;'>" . htmlspecialchars($columnName) . "</th>";
        }
        echo "</tr>";
        
        foreach ($resultados as $row) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td style='padding: 5px;'>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // 4. Test de la lógica de mostrar médico
        echo "<h4>👨‍⚕️ Test lógica mostrar médico:</h4>";
        
        foreach ($resultados as $index => $registro) {
            echo "<p><strong>Registro " . ($index + 1) . ":</strong> ";
            
            if (!empty($registro['medico_apellido'])) {
                echo htmlspecialchars(trim($registro['medico_nombre'] . ' ' . $registro['medico_apellido']));
                echo " <em>(nombre + apellido)</em>";
            } elseif (!empty($registro['medico_nombre'])) {
                echo htmlspecialchars($registro['medico_nombre']);
                echo " <em>(solo nombre/username)</em>";
            } else {
                echo 'No especificado';
                echo " <em>(sin datos)</em>";
            }
            echo "</p>";
        }
        
    } else {
        echo "<p style='color: orange;'>⚠️ No hay registros de historial médico para el paciente ID: $test_paciente_id</p>";
        
        // Verificar si existen pacientes
        echo "<h4>👥 Pacientes disponibles:</h4>";
        $stmtPacientes = $conn->query("SELECT id, nombre, apellido FROM pacientes LIMIT 5");
        $pacientes = $stmtPacientes->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($pacientes) > 0) {
            echo "<ul>";
            foreach ($pacientes as $paciente) {
                echo "<li>ID: " . $paciente['id'] . " - " . htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']) . "</li>";
            }
            echo "</ul>";
            echo "<p>💡 Puede cambiar la variable \$test_paciente_id en este script para probar con otro paciente.</p>";
        } else {
            echo "<p style='color: red;'>❌ No hay pacientes en la base de datos.</p>";
        }
    }
    
    echo "<h4>✅ Test completado exitosamente</h4>";
    echo "<p style='color: green; font-weight: bold;'>La consulta funciona correctamente ahora.</p>";
    
} catch (PDOException $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h5>❌ Error de Base de Datos:</h5>";
    echo "<p><strong>Código:</strong> " . $e->getCode() . "</p>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h5>❌ Error General:</h5>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
