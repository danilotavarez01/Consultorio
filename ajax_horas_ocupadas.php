<?php
require_once "config.php";
header('Content-Type: application/json');

// Verificar que sean peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$doctor_id = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : 0;
$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : '';
$cita_actual_id = isset($_POST['cita_id']) ? intval($_POST['cita_id']) : 0;

// Validar parámetros
if (!$doctor_id || !$fecha) {
    echo json_encode(['success' => false, 'error' => 'Parámetros inválidos']);
    exit;
}

try {
    // Horas posibles de 08:00 a 18:00 cada 30 minutos
    $horas_posibles = [];
    for ($h = 8; $h <= 18; $h++) {
        foreach ([0, 30] as $min) {
            if ($h == 18 && $min > 0) break; // No incluir 18:30
            $horas_posibles[] = sprintf('%02d:%02d', $h, $min);
        }
    }

    // Consultar horas ocupadas (excluyendo citas canceladas)
    $sql = "SELECT TIME_FORMAT(hora, '%H:%i') as hora FROM citas WHERE doctor_id = ? AND fecha = ? AND estado != 'Cancelada'";
    $params = [$doctor_id, $fecha];
    
    // Si estamos editando una cita, excluirla de la consulta
    if ($cita_actual_id > 0) {
        $sql .= " AND id != ?";
        $params[] = $cita_actual_id;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $horas_ocupadas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // También verificar turnos si existe la tabla
    try {
        $tabla_turnos_existe = $conn->query("SHOW TABLES LIKE 'turnos'")->rowCount() > 0;
        if ($tabla_turnos_existe) {
            $stmt_turnos = $conn->prepare("SELECT TIME_FORMAT(hora, '%H:%i') as hora FROM turnos WHERE doctor_id = ? AND fecha = ?");
            $stmt_turnos->execute([$doctor_id, $fecha]);
            $turnos_ocupados = $stmt_turnos->fetchAll(PDO::FETCH_COLUMN);
            $horas_ocupadas = array_merge($horas_ocupadas, $turnos_ocupados);
        }
    } catch (Exception $e) {
        // Continuar sin turnos si hay error
    }

    // Crear resultado
    $result = [];
    $disponibles = 0;
    $ocupadas = 0;
    
    foreach ($horas_posibles as $hora) {
        $ocupada = in_array($hora, $horas_ocupadas);
        if ($ocupada) {
            $ocupadas++;
        } else {
            $disponibles++;
        }
        
        $result[] = [
            'hora' => $hora,
            'valor' => $hora,
            'ocupada' => $ocupada,
            'disponible' => !$ocupada,
            'texto' => $ocupada ? $hora . ' (Ocupada)' : $hora
        ];
    }

    // Obtener información del doctor
    $stmt_doctor = $conn->prepare("SELECT nombre FROM usuarios WHERE id = ?");
    $stmt_doctor->execute([$doctor_id]);
    $doctor = $stmt_doctor->fetch(PDO::FETCH_ASSOC);
    
    if (!$doctor) {
        // Si no existe el doctor, obtener de configuración (para modo single doctor)
        $stmt_config = $conn->prepare("SELECT medico_nombre FROM configuracion WHERE id = 1");
        $stmt_config->execute();
        $config = $stmt_config->fetch(PDO::FETCH_ASSOC);
        $doctor_nombre = $config['medico_nombre'] ?? 'Médico Tratante';
    } else {
        $doctor_nombre = $doctor['nombre'];
    }

    // Formatear fecha
    $fecha_formateada = date('d/m/Y', strtotime($fecha));
    $dias = ['Sunday' => 'Domingo', 'Monday' => 'Lunes', 'Tuesday' => 'Martes', 
             'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 
             'Saturday' => 'Sábado'];
    $dia_semana = $dias[date('l', strtotime($fecha))];

    echo json_encode([
        'success' => true,
        'horas' => $result,
        'doctor' => $doctor_nombre,
        'fecha' => $fecha,
        'fecha_formateada' => $fecha_formateada,
        'dia_semana' => $dia_semana,
        'total_disponibles' => $disponibles,
        'total_ocupadas' => $ocupadas,
        'total_horas' => count($horas_posibles)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>