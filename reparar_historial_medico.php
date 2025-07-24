<?php
// Script para actualizar registros del historial médico sin doctor_id
require_once 'session_config.php';
session_start();
require_once "config.php";

echo "<h3>🔧 Reparar Registros de Historial Médico</h3>";

try {
    // 1. Contar registros sin doctor_id
    $stmt = $conn->query("SELECT COUNT(*) as count FROM historial_medico WHERE doctor_id IS NULL OR doctor_id = '' OR doctor_id = 0");
    $sinDoctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h4>📊 Estado Actual:</h4>";
    echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Registros sin doctor_id:</strong> " . $sinDoctor['count'];
    echo "</div>";
    
    if ($sinDoctor['count'] > 0) {
        // 2. Mostrar opciones para reparar
        if (isset($_POST['reparar_registros'])) {
            $doctorIdPorDefecto = $_POST['doctor_id_defecto'] ?? 1;
            
            echo "<h4>🛠️ Ejecutando Reparación:</h4>";
            
            // Actualizar registros sin doctor_id
            $stmt = $conn->prepare("UPDATE historial_medico SET doctor_id = ? WHERE doctor_id IS NULL OR doctor_id = '' OR doctor_id = 0");
            $stmt->execute([$doctorIdPorDefecto]);
            $registrosActualizados = $stmt->rowCount();
            
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;'>";
            echo "<h5>✅ Reparación Completada</h5>";
            echo "<p><strong>Registros actualizados:</strong> " . $registrosActualizados . "</p>";
            echo "<p><strong>Doctor ID asignado:</strong> " . $doctorIdPorDefecto . "</p>";
            echo "</div>";
            
            // Refrescar el conteo
            $stmt = $conn->query("SELECT COUNT(*) as count FROM historial_medico WHERE doctor_id IS NULL OR doctor_id = '' OR doctor_id = 0");
            $sinDoctorDespues = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<div style='background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>Registros sin doctor_id después de la reparación:</strong> " . $sinDoctorDespues['count'];
            echo "</div>";
        } else {
            // 3. Mostrar formulario para reparar
            echo "<h4>🔧 Reparar Registros:</h4>";
            
            // Obtener lista de usuarios para seleccionar doctor por defecto
            $stmt = $conn->query("SELECT id, nombre, apellido, username FROM usuarios ORDER BY id");
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<form method='POST' style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>";
            echo "<h5>⚠️ Asignar Doctor por Defecto</h5>";
            echo "<p>Los registros sin doctor_id serán actualizados con el médico que selecciones:</p>";
            
            echo "<div style='margin: 10px 0;'>";
            echo "<label for='doctor_id_defecto'><strong>Seleccionar médico por defecto:</strong></label><br>";
            echo "<select name='doctor_id_defecto' id='doctor_id_defecto' style='padding: 5px; margin: 5px 0;'>";
            
            foreach ($usuarios as $usuario) {
                $selected = ($usuario['id'] == 1) ? 'selected' : ''; // Por defecto seleccionar ID 1
                echo "<option value='" . $usuario['id'] . "' $selected>";
                echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido'] . ' (' . $usuario['username'] . ')');
                echo "</option>";
            }
            echo "</select>";
            echo "</div>";
            
            echo "<div style='margin: 10px 0;'>";
            echo "<input type='checkbox' id='confirmar' name='confirmar' required> ";
            echo "<label for='confirmar'>Confirmo que quiero actualizar " . $sinDoctor['count'] . " registros</label>";
            echo "</div>";
            
            echo "<button type='submit' name='reparar_registros' style='background: #ffc107; border: none; padding: 10px 20px; border-radius: 3px; cursor: pointer;'>";
            echo "Reparar Registros";
            echo "</button>";
            echo "</form>";
        }
    }
    
    // 4. Mostrar registros recientes para verificar
    echo "<h4>📋 Registros Recientes del Historial (con médicos):</h4>";
    $stmt = $conn->query("
        SELECT hm.id, hm.paciente_id, hm.doctor_id, hm.fecha, hm.motivo_consulta,
               u.nombre as medico_nombre, u.apellido as medico_apellido,
               p.nombre as paciente_nombre, p.apellido as paciente_apellido
        FROM historial_medico hm 
        LEFT JOIN usuarios u ON hm.doctor_id = u.id 
        LEFT JOIN pacientes p ON hm.paciente_id = p.id
        ORDER BY hm.fecha DESC 
        LIMIT 15
    ");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($registros) > 0) {
        echo "<div style='overflow-x: auto;'>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th style='padding: 8px;'>ID</th>";
        echo "<th style='padding: 8px;'>Paciente</th>";
        echo "<th style='padding: 8px;'>Médico</th>";
        echo "<th style='padding: 8px;'>Fecha</th>";
        echo "<th style='padding: 8px;'>Motivo</th>";
        echo "</tr>";
        
        foreach ($registros as $registro) {
            $medicoCompleto = trim($registro['medico_nombre'] . ' ' . $registro['medico_apellido']);
            $pacienteCompleto = trim($registro['paciente_nombre'] . ' ' . $registro['paciente_apellido']);
            $medicoColor = empty($medicoCompleto) ? 'background: #f8d7da; color: #721c24;' : 'background: #d4edda; color: #155724;';
            
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . $registro['id'] . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($pacienteCompleto ?: 'ID: ' . $registro['paciente_id']) . "</td>";
            echo "<td style='padding: 8px; $medicoColor'>" . htmlspecialchars($medicoCompleto ?: 'Sin médico (ID: ' . ($registro['doctor_id'] ?: 'NULL') . ')') . "</td>";
            echo "<td style='padding: 8px;'>" . date('d/m/Y', strtotime($registro['fecha'])) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars(substr($registro['motivo_consulta'], 0, 40)) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    } else {
        echo "<p style='color: orange;'>No hay registros en el historial médico.</p>";
    }
    
    // 5. Información de la sesión actual
    echo "<h4>👤 Información de Sesión Actual:</h4>";
    if (isset($_SESSION['id'])) {
        $stmt = $conn->prepare("SELECT nombre, apellido, username FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['id']]);
        $usuarioActual = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px;'>";
        echo "<p><strong>ID de Usuario:</strong> " . $_SESSION['id'] . "</p>";
        if ($usuarioActual) {
            echo "<p><strong>Nombre:</strong> " . htmlspecialchars($usuarioActual['nombre'] . ' ' . $usuarioActual['apellido']) . "</p>";
            echo "<p><strong>Username:</strong> " . htmlspecialchars($usuarioActual['username']) . "</p>";
        }
        echo "<p><em>Este será el doctor_id que se use en nuevas consultas.</em></p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
        echo "<p><strong>⚠️ No hay sesión activa</strong></p>";
        echo "<p>Esto podría causar problemas al crear nuevas consultas.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h5>❌ Error:</h5>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<div style='margin-top: 20px; padding: 15px; background: #e2e3e5; border-radius: 5px;'>";
echo "<h5>🔗 Enlaces útiles:</h5>";
echo "<a href='ver_paciente.php?id=1' class='btn btn-primary btn-sm' style='background: #007bff; color: white; padding: 8px 12px; text-decoration: none; border-radius: 3px; margin-right: 10px;'>Ver Paciente</a>";
echo "<a href='debug_medicos_historial.php' class='btn btn-info btn-sm' style='background: #17a2b8; color: white; padding: 8px 12px; text-decoration: none; border-radius: 3px; margin-right: 10px;'>Debug Médicos</a>";
echo "<a href='nueva_consulta.php?paciente_id=1' class='btn btn-success btn-sm' style='background: #28a745; color: white; padding: 8px 12px; text-decoration: none; border-radius: 3px;'>Nueva Consulta</a>";
echo "</div>";
?>
