<?php
require_once "config.php";

echo "<h2>🧪 Test de Turnos con Multi-Médico</h2>";
echo "<div style='padding: 20px; font-family: Arial;'>";

// Verificar configuración actual
try {
    $stmt = $conn->query("SELECT multi_medico, medico_nombre FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    $multi_medico = isset($config['multi_medico']) && $config['multi_medico'] == 1;
    
    echo "<h3>⚙️ Configuración Actual</h3>";
    echo "<p><strong>Multi-médico habilitado:</strong> " . ($multi_medico ? '✅ SÍ' : '❌ NO') . "</p>";
    echo "<p><strong>Médico por defecto:</strong> " . htmlspecialchars($config['medico_nombre'] ?? 'No configurado') . "</p>";
    
    // Verificar estructura de tabla turnos
    echo "<h3>🗃️ Estructura de Tabla Turnos</h3>";
    $stmt = $conn->query("SHOW COLUMNS FROM turnos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $tiene_medico_id = false;
    $tiene_medico_nombre = false;
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Columna</th><th>Tipo</th><th>Estado</th></tr>";
    
    foreach ($columns as $column) {
        $estado = '';
        if ($column['Field'] == 'medico_id') {
            $tiene_medico_id = true;
            $estado = '✅ Columna para ID de médico';
        } elseif ($column['Field'] == 'medico_nombre') {
            $tiene_medico_nombre = true;
            $estado = '✅ Columna para nombre de médico';
        }
        
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $estado . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><strong>Soporte para médicos:</strong> " . 
         (($tiene_medico_id && $tiene_medico_nombre) ? '✅ Completo' : 
          ($tiene_medico_id || $tiene_medico_nombre) ? '⚠️ Parcial' : '❌ No implementado') . "</p>";
    
    // Obtener doctores disponibles si multi_medico está habilitado
    if ($multi_medico) {
        echo "<h3>👥 Médicos Disponibles</h3>";
        $stmt = $conn->query("SELECT id, nombre, username, rol FROM usuarios WHERE rol IN ('admin', 'doctor') ORDER BY nombre");
        $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($doctores) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Rol</th></tr>";
            foreach ($doctores as $doctor) {
                echo "<tr>";
                echo "<td>" . $doctor['id'] . "</td>";
                echo "<td>" . htmlspecialchars($doctor['nombre']) . "</td>";
                echo "<td>" . htmlspecialchars($doctor['username']) . "</td>";
                echo "<td>" . htmlspecialchars($doctor['rol']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>❌ No hay médicos/administradores registrados</p>";
        }
    }
    
    // Verificar turnos existentes
    echo "<h3>📋 Análisis de Turnos Existentes</h3>";
    $stmt = $conn->query("SELECT COUNT(*) as total FROM turnos");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p><strong>Total de turnos:</strong> $total</p>";
    
    if ($total > 0) {
        // Verificar cuántos tienen médico asignado
        $stmt = $conn->query("SELECT 
                               COUNT(*) as con_medico_id,
                               SUM(CASE WHEN medico_id IS NOT NULL THEN 1 ELSE 0 END) as con_id,
                               SUM(CASE WHEN medico_nombre IS NOT NULL THEN 1 ELSE 0 END) as con_nombre
                             FROM turnos");
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Turnos con médico ID:</strong> " . ($stats['con_id'] ?? 0) . "</p>";
        echo "<p><strong>Turnos con médico nombre:</strong> " . ($stats['con_nombre'] ?? 0) . "</p>";
        
        // Mostrar algunos turnos recientes
        echo "<h4>🔍 Últimos 5 Turnos</h4>";
        $stmt = $conn->query("SELECT t.id, t.fecha_turno, t.hora_turno, t.medico_nombre, t.medico_id, 
                                     CONCAT(p.nombre, ' ', p.apellido) as paciente
                             FROM turnos t 
                             LEFT JOIN pacientes p ON t.paciente_id = p.id 
                             ORDER BY t.fecha_turno DESC, t.hora_turno DESC 
                             LIMIT 5");
        $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($turnos) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Fecha</th><th>Hora</th><th>Paciente</th><th>Médico ID</th><th>Médico Nombre</th></tr>";
            foreach ($turnos as $turno) {
                echo "<tr>";
                echo "<td>" . $turno['id'] . "</td>";
                echo "<td>" . $turno['fecha_turno'] . "</td>";
                echo "<td>" . $turno['hora_turno'] . "</td>";
                echo "<td>" . htmlspecialchars($turno['paciente']) . "</td>";
                echo "<td>" . ($turno['medico_id'] ?? 'NULL') . "</td>";
                echo "<td>" . htmlspecialchars($turno['medico_nombre'] ?? 'No asignado') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    echo "<h3>🔗 Enlaces de Prueba</h3>";
    echo "<p><a href='turnos.php' target='_blank' style='background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>📅 Ir a Turnos</a></p>";
    echo "<p><a href='configuracion.php' target='_blank' style='background: #6c757d; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>⚙️ Ir a Configuración</a></p>";
    
    echo "<div style='margin-top: 30px; padding: 15px; background: #e8f5e8; border: 1px solid #4caf50;'>";
    echo "<h3>✅ Instrucciones de Prueba</h3>";
    echo "<ol>";
    echo "<li><strong>Configuración habilitada:</strong> Crear turno debe mostrar selector de médicos</li>";
    echo "<li><strong>Configuración deshabilitada:</strong> Crear turno debe mostrar médico por defecto</li>";
    echo "<li><strong>Cambiar configuración:</strong> Ir a configuración y cambiar 'Habilitar múltiples médicos'</li>";
    echo "<li><strong>Verificar tabla:</strong> La columna 'Médico' debe aparecer/desaparecer según configuración</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</div>";
?>
