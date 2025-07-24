<?php
// Script para crear datos de prueba en el historial médico
require_once 'session_config.php';
session_start();
require_once "config.php";

echo "<h3>🧪 Crear Datos de Prueba - Historial Médico</h3>";

try {
    // 1. Verificar si ya existen datos de prueba
    $stmt = $conn->query("SELECT COUNT(*) as total FROM historial_medico");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Registros actuales en historial_medico:</strong> " . $result['total'] . "</p>";
    
    // 2. Obtener algunos pacientes y doctores
    $stmtPacientes = $conn->query("SELECT id, nombre, apellido FROM pacientes LIMIT 3");
    $pacientes = $stmtPacientes->fetchAll(PDO::FETCH_ASSOC);
    
    $stmtDoctores = $conn->query("SELECT id, nombre, username FROM usuarios WHERE rol = 'doctor' LIMIT 3");
    $doctores = $stmtDoctores->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>👥 Pacientes disponibles:</h4>";
    echo "<ul>";
    foreach ($pacientes as $paciente) {
        echo "<li>ID: " . $paciente['id'] . " - " . htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']) . "</li>";
    }
    echo "</ul>";
    
    echo "<h4>👨‍⚕️ Doctores disponibles:</h4>";
    echo "<ul>";
    foreach ($doctores as $doctor) {
        echo "<li>ID: " . $doctor['id'] . " - " . htmlspecialchars($doctor['nombre']) . " (" . $doctor['username'] . ")</li>";
    }
    echo "</ul>";
    
    // 3. Crear algunos registros de prueba si no existen muchos
    if ($result['total'] < 3 && count($pacientes) > 0 && count($doctores) > 0) {
        echo "<h4>➕ Creando registros de prueba...</h4>";
        
        $registrosCreados = 0;
        
        foreach ($pacientes as $index => $paciente) {
            if ($index < count($doctores)) {
                $doctor = $doctores[$index];
                
                $sql = "INSERT INTO historial_medico 
                        (paciente_id, doctor_id, fecha, motivo_consulta, diagnostico, tratamiento, observaciones) 
                        VALUES (?, ?, NOW(), ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    $paciente['id'],
                    $doctor['id'],
                    "Consulta de control general",
                    "Paciente en buen estado de salud",
                    "Control preventivo mensual",
                    "Paciente evaluado por Dr. " . $doctor['nombre']
                ]);
                
                $registrosCreados++;
                echo "<p>✅ Creado registro para paciente " . htmlspecialchars($paciente['nombre']) . 
                     " con doctor " . htmlspecialchars($doctor['nombre']) . "</p>";
            }
        }
        
        echo "<p><strong>Total de registros creados:</strong> $registrosCreados</p>";
    }
    
    // 4. Probar la consulta con datos reales
    echo "<h4>🔍 Probar consulta con datos reales:</h4>";
    
    if (count($pacientes) > 0) {
        $paciente_test = $pacientes[0];
        
        $sql = "SELECT hm.*, u.nombre as medico_nombre 
                FROM historial_medico hm 
                LEFT JOIN usuarios u ON hm.doctor_id = u.id 
                WHERE hm.paciente_id = ? 
                ORDER BY hm.fecha DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$paciente_test['id']]);
        $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Consultando historial para:</strong> " . 
             htmlspecialchars($paciente_test['nombre'] . ' ' . $paciente_test['apellido']) . 
             " (ID: " . $paciente_test['id'] . ")</p>";
        
        echo "<p><strong>Registros encontrados:</strong> " . count($historial) . "</p>";
        
        if (count($historial) > 0) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
            echo "<tr style='background: #f8f9fa;'>";
            echo "<th style='padding: 8px;'>Fecha</th>";
            echo "<th style='padding: 8px;'>Motivo</th>";
            echo "<th style='padding: 8px;'>Diagnóstico</th>";
            echo "<th style='padding: 8px;'>Médico</th>";
            echo "</tr>";
            
            foreach ($historial as $registro) {
                echo "<tr>";
                echo "<td style='padding: 8px;'>" . htmlspecialchars($registro['fecha']) . "</td>";
                echo "<td style='padding: 8px;'>" . htmlspecialchars($registro['motivo_consulta']) . "</td>";
                echo "<td style='padding: 8px;'>" . htmlspecialchars($registro['diagnostico']) . "</td>";
                echo "<td style='padding: 8px;'>";
                if (!empty($registro['medico_nombre'])) {
                    echo htmlspecialchars($registro['medico_nombre']);
                    echo " <span style='color: green;'>✅</span>";
                } else {
                    echo 'No especificado';
                    echo " <span style='color: red;'>❌</span>";
                }
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<p style='color: green; font-weight: bold;'>🎉 ¡La consulta funciona correctamente! Los nombres de médicos se muestran.</p>";
            
            // Enlace para probar en ver_paciente.php
            echo "<p><a href='ver_paciente.php?id=" . $paciente_test['id'] . "' target='_blank' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🔗 Probar en ver_paciente.php</a></p>";
            
        } else {
            echo "<p style='color: orange;'>⚠️ No hay registros de historial para este paciente.</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h5>❌ Error:</h5>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}
?>
