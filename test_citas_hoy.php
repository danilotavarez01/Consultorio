<?php
require_once "config.php";

try {
    // Verificar si la tabla citas existe
    $tableExists = $conn->query("SHOW TABLES LIKE 'citas'")->rowCount() > 0;
    if (!$tableExists) {
        echo "La tabla 'citas' no existe. Por favor, crea la tabla primero utilizando create_citas_table.php\n";
        exit;
    }
    
    // Imprimir la fecha actual para verificación
    echo "Fecha actual: " . date('Y-m-d') . " (" . date('d/m/Y') . ")\n";

    // Consultar las citas para hoy
    $query = "SELECT 
                c.id,
                c.hora,
                c.estado,
                c.observaciones,
                CONCAT(p.nombre, ' ', p.apellido) as paciente,
                u.nombre as doctor,
                p.id as paciente_id,
                u.id as doctor_id
              FROM citas c
              JOIN pacientes p ON c.paciente_id = p.id
              JOIN usuarios u ON c.doctor_id = u.id
              WHERE c.fecha = CURDATE()
              ORDER BY c.hora ASC";
    
    $stmt = $conn->query($query);
    $count = $stmt->rowCount();
    
    if ($count > 0) {
        echo "Se encontraron {$count} citas para hoy (" . date('d/m/Y') . "):\n\n";
        echo "ID | Hora | Paciente | Doctor | Estado | Observaciones\n";
        echo str_repeat('-', 80) . "\n";
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "{$row['id']} | {$row['hora']} | {$row['paciente']} | {$row['doctor']} | {$row['estado']} | {$row['observaciones']}\n";
        }
    } else {
        echo "No hay citas programadas para hoy (" . date('d/m/Y') . ").\n";
        
        // Insertar una cita de prueba si no hay ninguna
        echo "\n¿Deseas insertar una cita de prueba para hoy? (S/N): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        if (trim(strtoupper($line)) === 'S') {
            // Obtener un paciente y un doctor
            $pacienteStmt = $conn->query("SELECT id FROM pacientes LIMIT 1");
            $doctorStmt = $conn->query("SELECT id FROM usuarios WHERE rol = 'doctor' LIMIT 1");
            
            if ($pacienteStmt->rowCount() > 0 && $doctorStmt->rowCount() > 0) {
                $paciente = $pacienteStmt->fetch(PDO::FETCH_ASSOC);
                $doctor = $doctorStmt->fetch(PDO::FETCH_ASSOC);
                
                // Insertar cita de prueba
                $insertSql = "INSERT INTO citas (paciente_id, fecha, hora, doctor_id, estado, observaciones) 
                             VALUES (?, CURDATE(), '10:30:00', ?, 'Confirmada', 'Cita de prueba para hoy')";
                $insertStmt = $conn->prepare($insertSql);
                $insertStmt->execute([$paciente['id'], $doctor['id']]);
                
                echo "¡Cita de prueba insertada con éxito!\n";
            } else {
                echo "No se pudo insertar la cita de prueba. Asegúrate de tener al menos un paciente y un doctor en la base de datos.\n";
            }
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
