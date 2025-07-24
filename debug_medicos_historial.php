<?php
// Debug para verificar datos de médicos en historial médico
require_once 'session_config.php';
session_start();
require_once "config.php";

echo "<h3>🔍 Debug - Médicos en Historial Médico</h3>";

try {
    // 1. Verificar estructura de la tabla historial_medico
    echo "<h4>📋 Estructura de la tabla historial_medico:</h4>";
    $stmt = $conn->query("DESCRIBE historial_medico");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 2. Verificar registros en historial_medico con doctor_id
    echo "<h4>📊 Registros en historial_medico:</h4>";
    $stmt = $conn->query("SELECT id, paciente_id, doctor_id, fecha, motivo_consulta FROM historial_medico ORDER BY fecha DESC LIMIT 10");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($registros) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Paciente ID</th><th>Doctor ID</th><th>Fecha</th><th>Motivo</th></tr>";
        foreach ($registros as $registro) {
            $doctorIdColor = empty($registro['doctor_id']) ? 'color: red;' : 'color: green;';
            echo "<tr>";
            echo "<td>" . $registro['id'] . "</td>";
            echo "<td>" . $registro['paciente_id'] . "</td>";
            echo "<td style='$doctorIdColor'>" . ($registro['doctor_id'] ?: 'NULL') . "</td>";
            echo "<td>" . $registro['fecha'] . "</td>";
            echo "<td>" . htmlspecialchars(substr($registro['motivo_consulta'], 0, 50)) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>No hay registros en el historial médico.</p>";
    }
    
    // 3. Verificar usuarios (médicos)
    echo "<h4>👨‍⚕️ Usuarios/Médicos en el sistema:</h4>";
    $stmt = $conn->query("SELECT id, nombre, apellido, username, rol FROM usuarios ORDER BY id");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($usuarios) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Username</th><th>Rol</th></tr>";
        foreach ($usuarios as $usuario) {
            echo "<tr>";
            echo "<td>" . $usuario['id'] . "</td>";
            echo "<td>" . htmlspecialchars($usuario['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($usuario['apellido']) . "</td>";
            echo "<td>" . htmlspecialchars($usuario['username']) . "</td>";
            echo "<td>" . htmlspecialchars($usuario['rol']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>No hay usuarios en el sistema.</p>";
    }
    
    // 4. Probar la consulta JOIN
    echo "<h4>🔗 Prueba de consulta JOIN (historial + médicos):</h4>";
    $stmt = $conn->query("
        SELECT hm.id, hm.paciente_id, hm.doctor_id, hm.fecha, hm.motivo_consulta,
               u.nombre as medico_nombre, u.apellido as medico_apellido,
               CONCAT(u.nombre, ' ', u.apellido) as medico_completo
        FROM historial_medico hm 
        LEFT JOIN usuarios u ON hm.doctor_id = u.id 
        ORDER BY hm.fecha DESC 
        LIMIT 10
    ");
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($resultados) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Hist ID</th><th>Paciente ID</th><th>Doctor ID</th><th>Fecha</th><th>Médico Nombre</th><th>Médico Apellido</th><th>Médico Completo</th></tr>";
        foreach ($resultados as $resultado) {
            $medicoColor = empty($resultado['medico_completo']) ? 'color: red;' : 'color: green;';
            echo "<tr>";
            echo "<td>" . $resultado['id'] . "</td>";
            echo "<td>" . $resultado['paciente_id'] . "</td>";
            echo "<td>" . ($resultado['doctor_id'] ?: 'NULL') . "</td>";
            echo "<td>" . $resultado['fecha'] . "</td>";
            echo "<td>" . htmlspecialchars($resultado['medico_nombre'] ?: 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($resultado['medico_apellido'] ?: 'NULL') . "</td>";
            echo "<td style='$medicoColor'>" . htmlspecialchars($resultado['medico_completo'] ?: 'No especificado') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>No hay resultados en la consulta JOIN.</p>";
    }
    
    // 5. Verificar sesión actual
    echo "<h4>👤 Sesión actual:</h4>";
    if (isset($_SESSION['id']) && isset($_SESSION['username'])) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>ID de usuario actual:</strong> " . $_SESSION['id'] . "<br>";
        echo "<strong>Username:</strong> " . htmlspecialchars($_SESSION['username']) . "<br>";
        echo "<strong>Rol:</strong> " . ($_SESSION['rol'] ?? 'No definido') . "<br>";
        echo "</div>";
        
        // Buscar este usuario en la tabla usuarios
        $stmt = $conn->prepare("SELECT nombre, apellido FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['id']]);
        $usuarioActual = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuarioActual) {
            echo "<div style='background: #d1ecf1; padding: 10px; border-radius: 5px; margin-top: 10px;'>";
            echo "<strong>Nombre completo:</strong> " . htmlspecialchars($usuarioActual['nombre'] . ' ' . $usuarioActual['apellido']);
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "No hay sesión activa o faltan datos de usuario.";
        echo "</div>";
    }
    
    // 6. Recomendaciones
    echo "<h4>💡 Diagnóstico y Recomendaciones:</h4>";
    
    $problemasEncontrados = [];
    $solucionesRecomendadas = [];
    
    // Verificar si hay registros sin doctor_id
    $stmt = $conn->query("SELECT COUNT(*) as count FROM historial_medico WHERE doctor_id IS NULL OR doctor_id = ''");
    $sinDoctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sinDoctor['count'] > 0) {
        $problemasEncontrados[] = "Hay " . $sinDoctor['count'] . " registros sin doctor_id";
        $solucionesRecomendadas[] = "Actualizar registros antiguos con doctor_id válidos";
    }
    
    // Verificar si hay doctor_id que no corresponden a usuarios existentes
    $stmt = $conn->query("
        SELECT DISTINCT hm.doctor_id 
        FROM historial_medico hm 
        LEFT JOIN usuarios u ON hm.doctor_id = u.id 
        WHERE hm.doctor_id IS NOT NULL AND u.id IS NULL
    ");
    $doctoresInexistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($doctoresInexistentes) > 0) {
        $idsInexistentes = array_column($doctoresInexistentes, 'doctor_id');
        $problemasEncontrados[] = "Hay doctor_id que no existen en usuarios: " . implode(', ', $idsInexistentes);
        $solucionesRecomendadas[] = "Verificar y corregir los doctor_id inválidos";
    }
    
    if (count($problemasEncontrados) > 0) {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>";
        echo "<h5>⚠️ Problemas encontrados:</h5>";
        echo "<ul>";
        foreach ($problemasEncontrados as $problema) {
            echo "<li>" . $problema . "</li>";
        }
        echo "</ul>";
        echo "</div>";
        
        echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; border-left: 4px solid #17a2b8; margin-top: 10px;'>";
        echo "<h5>🔧 Soluciones recomendadas:</h5>";
        echo "<ul>";
        foreach ($solucionesRecomendadas as $solucion) {
            echo "<li>" . $solucion . "</li>";
        }
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;'>";
        echo "<h5>✅ Todo parece estar en orden</h5>";
        echo "<p>Los registros del historial médico tienen doctor_id válidos y se pueden relacionar correctamente con los usuarios.</p>";
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
echo "<a href='ver_paciente.php?id=1' style='margin-right: 10px;'>Ver Paciente (ID 1)</a>";
echo "<a href='nueva_consulta.php?paciente_id=1' style='margin-right: 10px;'>Nueva Consulta</a>";
echo "<a href='pacientes.php' style='margin-right: 10px;'>Lista de Pacientes</a>";
echo "</div>";
?>
