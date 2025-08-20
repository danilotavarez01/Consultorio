<?php
require_once 'config.php';

try {
    echo "Probando consulta de pacientes con doctor de última cita...\n";
    
    $stmt = $conn->query("
        SELECT p.id, p.nombre, p.apellido, p.seguro_medico, 
               u.nombre as doctor_ultima_cita, u.id as doctor_id,
               c.fecha as fecha_ultima_cita
        FROM pacientes p
        LEFT JOIN citas c ON p.id = c.paciente_id
        LEFT JOIN usuarios u ON c.doctor_id = u.id
        LEFT JOIN (
            SELECT paciente_id, MAX(CONCAT(fecha, ' ', hora)) as max_fecha_hora
            FROM citas
            GROUP BY paciente_id
        ) ultima_cita ON p.id = ultima_cita.paciente_id 
                       AND CONCAT(c.fecha, ' ', c.hora) = ultima_cita.max_fecha_hora
        WHERE c.id IS NULL OR CONCAT(c.fecha, ' ', c.hora) = ultima_cita.max_fecha_hora
        ORDER BY p.nombre, p.apellido
        LIMIT 5
    ");
    
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ Consulta ejecutada exitosamente!\n";
    echo "📊 Resultados encontrados: " . count($pacientes) . "\n\n";
    
    foreach ($pacientes as $paciente) {
        echo "👤 " . $paciente['nombre'] . " " . $paciente['apellido'] . "\n";
        echo "   🏥 Seguro: " . ($paciente['seguro_medico'] ?: 'Sin seguro') . "\n";
        echo "   👨‍⚕️ Último doctor: " . ($paciente['doctor_ultima_cita'] ?: 'Sin citas') . "\n";
        echo "   📅 Fecha última cita: " . ($paciente['fecha_ultima_cita'] ?: 'N/A') . "\n";
        echo "   ────────────────────────\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error en la consulta: " . $e->getMessage() . "\n";
}
?>
