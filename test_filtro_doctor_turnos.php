<?php
/**
 * Test para verificar el filtro por doctor en turnos
 */

require_once 'config.php';

echo "<h2>Test: Filtro por Doctor en Turnos</h2>";

try {
    // 1. Verificar configuración multi_medico
    echo "<h3>1. Verificando configuración multi_medico</h3>";
    $stmt = $conn->query("SELECT multi_medico, medico_nombre FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    $multi_medico = isset($config['multi_medico']) && $config['multi_medico'] == 1;
    
    echo "- multi_medico habilitado: " . ($multi_medico ? "✅ Sí" : "❌ No") . "<br>";
    echo "- medico_nombre por defecto: " . htmlspecialchars($config['medico_nombre'] ?? 'No configurado') . "<br>";
    
    // 2. Verificar doctores disponibles
    echo "<h3>2. Doctores disponibles en el sistema</h3>";
    if ($multi_medico) {
        $stmt = $conn->query("SELECT id, nombre, username FROM usuarios WHERE rol IN ('admin', 'doctor') ORDER BY nombre");
        $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($doctores) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Usuario</th></tr>";
            foreach ($doctores as $doctor) {
                echo "<tr>";
                echo "<td>" . $doctor['id'] . "</td>";
                echo "<td>" . htmlspecialchars($doctor['nombre']) . "</td>";
                echo "<td>" . htmlspecialchars($doctor['username']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "❌ No se encontraron doctores<br>";
        }
    } else {
        echo "ℹ️ Sistema configurado para un solo médico: " . htmlspecialchars($config['medico_nombre'] ?? 'No configurado') . "<br>";
    }
    
    // 3. Verificar estructura de turnos
    echo "<h3>3. Verificando estructura de tabla turnos</h3>";
    $columns = $conn->query("SHOW COLUMNS FROM turnos")->fetchAll(PDO::FETCH_ASSOC);
    $has_medico_id = false;
    $has_medico_nombre = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] == 'medico_id') $has_medico_id = true;
        if ($column['Field'] == 'medico_nombre') $has_medico_nombre = true;
    }
    
    echo "- Columna medico_id: " . ($has_medico_id ? "✅ Existe" : "❌ No existe") . "<br>";
    echo "- Columna medico_nombre: " . ($has_medico_nombre ? "✅ Existe" : "❌ No existe") . "<br>";
    
    // 4. Verificar turnos con información de médico
    echo "<h3>4. Turnos con información de médico (últimos 10)</h3>";
    $stmt = $conn->query("
        SELECT t.id, t.fecha_turno, t.hora_turno, t.medico_id, t.medico_nombre, t.estado,
               CONCAT(p.nombre, ' ', p.apellido) as paciente_nombre
        FROM turnos t
        JOIN pacientes p ON t.paciente_id = p.id
        ORDER BY t.fecha_turno DESC, t.hora_turno DESC
        LIMIT 10
    ");
    $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($turnos) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Fecha</th><th>Hora</th><th>Paciente</th><th>Médico ID</th><th>Médico Nombre</th><th>Estado</th></tr>";
        foreach ($turnos as $turno) {
            echo "<tr>";
            echo "<td>" . $turno['id'] . "</td>";
            echo "<td>" . $turno['fecha_turno'] . "</td>";
            echo "<td>" . $turno['hora_turno'] . "</td>";
            echo "<td>" . htmlspecialchars($turno['paciente_nombre']) . "</td>";
            echo "<td>" . ($turno['medico_id'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($turno['medico_nombre'] ?? 'NULL') . "</td>";
            echo "<td>" . $turno['estado'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No hay turnos registrados.<br>";
    }
    
    // 5. Probar filtros
    echo "<h3>5. URLs de prueba para filtros</h3>";
    $fecha_hoy = date('Y-m-d');
    
    echo "<ul>";
    echo "<li><a href='turnos.php?fecha=$fecha_hoy' target='_blank'>📅 Turnos de hoy</a></li>";
    echo "<li><a href='turnos.php?fecha=$fecha_hoy&estado=pendiente' target='_blank'>⏳ Turnos pendientes de hoy</a></li>";
    echo "<li><a href='turnos.php?fecha=$fecha_hoy&estado=atendido' target='_blank'>✅ Turnos atendidos de hoy</a></li>";
    
    if ($multi_medico && $doctores) {
        foreach ($doctores as $doctor) {
            echo "<li><a href='turnos.php?fecha=$fecha_hoy&medico=" . $doctor['id'] . "' target='_blank'>👨‍⚕️ Turnos de " . htmlspecialchars($doctor['nombre']) . " hoy</a></li>";
        }
    }
    echo "</ul>";
    
    // 6. Estadísticas por médico
    if ($multi_medico) {
        echo "<h3>6. Estadísticas por médico (hoy)</h3>";
        $stmt = $conn->query("
            SELECT 
                t.medico_id,
                t.medico_nombre,
                COUNT(*) as total_turnos,
                SUM(CASE WHEN t.estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN t.estado = 'en_consulta' THEN 1 ELSE 0 END) as en_consulta,
                SUM(CASE WHEN t.estado = 'atendido' THEN 1 ELSE 0 END) as atendidos,
                SUM(CASE WHEN t.estado = 'cancelado' THEN 1 ELSE 0 END) as cancelados
            FROM turnos t
            WHERE t.fecha_turno = CURDATE()
            GROUP BY t.medico_id, t.medico_nombre
            ORDER BY total_turnos DESC
        ");
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($stats) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Médico</th><th>Total</th><th>Pendientes</th><th>En Consulta</th><th>Atendidos</th><th>Cancelados</th></tr>";
            foreach ($stats as $stat) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($stat['medico_nombre'] ?? 'Sin asignar') . "</td>";
                echo "<td>" . $stat['total_turnos'] . "</td>";
                echo "<td>" . $stat['pendientes'] . "</td>";
                echo "<td>" . $stat['en_consulta'] . "</td>";
                echo "<td>" . $stat['atendidos'] . "</td>";
                echo "<td>" . $stat['cancelados'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No hay turnos para hoy.<br>";
        }
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>Instrucciones de prueba:</strong></p>";
echo "<ol>";
echo "<li>Ve a <a href='turnos.php' target='_blank'>turnos.php</a></li>";
echo "<li>Verifica que aparezca el filtro por médico (si multi_medico está habilitado)</li>";
echo "<li>Prueba seleccionar diferentes médicos en el filtro</li>";
echo "<li>Verifica que solo se muestren los turnos del médico seleccionado</li>";
echo "<li>Combina filtros: fecha + estado + médico</li>";
echo "<li>Usa los botones 'Filtrar' y 'Limpiar' para probar la funcionalidad</li>";
echo "</ol>";

echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h2, h3 { color: #333; }
    table { margin: 10px 0; }
    th, td { padding: 8px; text-align: left; }
    ul { margin: 10px 0; }
    a { color: #007bff; text-decoration: none; }
    a:hover { text-decoration: underline; }
</style>";
?>
