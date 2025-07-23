<?php
/**
 * Test para verificar que el botón "Agregar a Turnos" 
 * de las citas incluye correctamente la información del doctor
 */

require_once 'config.php';

echo "<h2>Test: Agregar Turno desde Cita con Doctor</h2>";

try {
    // 1. Verificar estructura de tabla turnos
    echo "<h3>1. Verificando estructura de tabla turnos</h3>";
    $columns = $conn->query("SHOW COLUMNS FROM turnos")->fetchAll(PDO::FETCH_ASSOC);
    $has_medico_id = false;
    $has_medico_nombre = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] == 'medico_id') $has_medico_id = true;
        if ($column['Field'] == 'medico_nombre') $has_medico_nombre = true;
    }
    
    echo "- Columna medico_id: " . ($has_medico_id ? "✅ Existe" : "❌ No existe") . "<br>";
    echo "- Columna medico_nombre: " . ($has_medico_nombre ? "✅ Existe" : "❌ No existe") . "<br>";
    
    // 2. Verificar configuración multi_medico
    echo "<h3>2. Verificando configuración multi_medico</h3>";
    $stmt = $conn->query("SELECT multi_medico, medico_nombre FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    $multi_medico = isset($config['multi_medico']) && $config['multi_medico'] == 1;
    
    echo "- multi_medico habilitado: " . ($multi_medico ? "✅ Sí" : "❌ No") . "<br>";
    echo "- medico_nombre por defecto: " . htmlspecialchars($config['medico_nombre'] ?? 'No configurado') . "<br>";
    
    // 3. Buscar una cita de ejemplo
    echo "<h3>3. Buscando cita de ejemplo</h3>";
    $stmt = $conn->query("
        SELECT c.*, 
               CONCAT(p.nombre, ' ', p.apellido) as paciente_nombre,
               u.nombre as doctor_nombre
        FROM citas c 
        JOIN pacientes p ON c.paciente_id = p.id 
        JOIN usuarios u ON c.doctor_id = u.id 
        LIMIT 1
    ");
    $cita_ejemplo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cita_ejemplo) {
        echo "✅ Cita encontrada:<br>";
        echo "- ID: " . $cita_ejemplo['id'] . "<br>";
        echo "- Paciente: " . htmlspecialchars($cita_ejemplo['paciente_nombre']) . "<br>";
        echo "- Doctor: " . htmlspecialchars($cita_ejemplo['doctor_nombre']) . "<br>";
        echo "- Fecha: " . $cita_ejemplo['fecha'] . "<br>";
        echo "- Hora: " . $cita_ejemplo['hora'] . "<br>";
        
        // 4. Mostrar URL que se generaría
        echo "<h3>4. URL generada para agregar a turnos</h3>";
        $url = "turnos.php?agregar_desde_cita=" . $cita_ejemplo['id'] . 
               "&paciente_id=" . $cita_ejemplo['paciente_id'] . 
               "&fecha=" . $cita_ejemplo['fecha'] . 
               "&doctor_id=" . $cita_ejemplo['doctor_id'];
        echo "URL: <code>" . htmlspecialchars($url) . "</code><br>";
        echo "<a href='" . $url . "' class='btn btn-primary' target='_blank'>🔗 Probar agregar a turnos</a><br>";
        
    } else {
        echo "❌ No se encontraron citas para probar<br>";
    }
    
    // 5. Verificar turnos existentes con información de médico
    echo "<h3>5. Verificando turnos con información de médico</h3>";
    $stmt = $conn->query("
        SELECT COUNT(*) as total,
               COUNT(medico_id) as con_medico_id,
               COUNT(medico_nombre) as con_medico_nombre
        FROM turnos
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "- Total de turnos: " . $stats['total'] . "<br>";
    echo "- Con medico_id: " . $stats['con_medico_id'] . "<br>";
    echo "- Con medico_nombre: " . $stats['con_medico_nombre'] . "<br>";
    
    // 6. Mostrar algunos turnos recientes
    echo "<h3>6. Turnos recientes</h3>";
    $stmt = $conn->query("
        SELECT t.id, t.fecha_turno, t.hora_turno, t.medico_id, t.medico_nombre,
               CONCAT(p.nombre, ' ', p.apellido) as paciente_nombre
        FROM turnos t
        JOIN pacientes p ON t.paciente_id = p.id
        ORDER BY t.fecha_turno DESC, t.hora_turno DESC
        LIMIT 5
    ");
    $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($turnos) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Fecha</th><th>Hora</th><th>Paciente</th><th>Médico ID</th><th>Médico Nombre</th></tr>";
        foreach ($turnos as $turno) {
            echo "<tr>";
            echo "<td>" . $turno['id'] . "</td>";
            echo "<td>" . $turno['fecha_turno'] . "</td>";
            echo "<td>" . $turno['hora_turno'] . "</td>";
            echo "<td>" . htmlspecialchars($turno['paciente_nombre']) . "</td>";
            echo "<td>" . ($turno['medico_id'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($turno['medico_nombre'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No hay turnos registrados.<br>";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>Instrucciones de prueba:</strong></p>";
echo "<ol>";
echo "<li>Ve a <a href='Citas.php' target='_blank'>Citas.php</a></li>";
echo "<li>Busca una cita y haz clic en 'Agregar a Turnos'</li>";
echo "<li>Verifica que el turno creado tenga la información del médico</li>";
echo "<li>Ve a <a href='turnos.php' target='_blank'>turnos.php</a> para ver el resultado</li>";
echo "</ol>";

echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h2, h3 { color: #333; }
    table { margin: 10px 0; }
    th, td { padding: 8px; text-align: left; }
    .btn { 
        display: inline-block; 
        padding: 8px 16px; 
        background: #007bff; 
        color: white; 
        text-decoration: none; 
        border-radius: 4px; 
        margin: 5px 0;
    }
    code { 
        background: #f8f9fa; 
        padding: 2px 4px; 
        border-radius: 3px; 
        font-family: monospace;
        font-size: 90%;
    }
</style>";
?>
