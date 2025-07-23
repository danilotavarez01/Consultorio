<?php
// Script de prueba rápida para crear una consulta con dientes seleccionados

require_once "config.php";
session_start();

echo "<h2>🧪 Prueba Rápida del Sistema</h2>";

// Simular datos de sesión si no existen
if (!isset($_SESSION['id'])) {
    $_SESSION['id'] = 1; // ID de prueba
    echo "<p>⚠️ Simulando sesión de usuario con ID: 1</p>";
}

try {
    // Verificar que existe al menos un paciente
    $stmt = $conn->query("SELECT COUNT(*) as total FROM pacientes");
    $totalPacientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($totalPacientes == 0) {
        echo "<p>❌ No hay pacientes en la base de datos. Crea un paciente primero.</p>";
        exit;
    }
    
    // Obtener el primer paciente
    $stmt = $conn->query("SELECT * FROM pacientes LIMIT 1");
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$paciente) {
        echo "<p>❌ No se pudo obtener un paciente para la prueba.</p>";
        exit;
    }
    
    echo "<p>✅ Paciente de prueba: " . htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']) . "</p>";
    
    // Obtener especialidad configurada
    $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    $especialidad_id = $config['especialidad_id'] ?? 1;
    
    echo "<p>✅ Especialidad ID: $especialidad_id</p>";
    
    // Datos de prueba para la consulta
    $datosConsulta = [
        'paciente_id' => $paciente['id'],
        'doctor_id' => $_SESSION['id'],
        'fecha' => date('Y-m-d'),
        'motivo_consulta' => 'Prueba del sistema de odontograma',
        'diagnostico' => 'Diagnóstico de prueba',
        'tratamiento' => 'Tratamiento de prueba',
        'observaciones' => 'Observaciones de prueba',
        'dientes_seleccionados' => '11,12,21,22,31,32' // Dientes de prueba
    ];
    
    echo "<h3>Datos de la consulta de prueba:</h3>";
    echo "<ul>";
    foreach ($datosConsulta as $campo => $valor) {
        echo "<li><strong>$campo:</strong> " . htmlspecialchars($valor) . "</li>";
    }
    echo "</ul>";
    
    // Intentar insertar la consulta
    $conn->beginTransaction();
    
    $sql = "INSERT INTO historial_medico (
                paciente_id, 
                doctor_id, 
                fecha, 
                motivo_consulta, 
                diagnostico, 
                tratamiento, 
                observaciones,
                campos_adicionales,
                especialidad_id,
                dientes_seleccionados
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $datosConsulta['paciente_id'],
        $datosConsulta['doctor_id'],
        $datosConsulta['fecha'],
        $datosConsulta['motivo_consulta'],
        $datosConsulta['diagnostico'],
        $datosConsulta['tratamiento'],
        $datosConsulta['observaciones'],
        null, // campos_adicionales
        $especialidad_id,
        $datosConsulta['dientes_seleccionados']
    ]);
    
    $consulta_id = $conn->lastInsertId();
    $conn->commit();
    
    echo "<h3>✅ ¡Éxito!</h3>";
    echo "<p>Consulta creada exitosamente con ID: <strong>$consulta_id</strong></p>";
    echo "<p>Dientes guardados: <strong>" . htmlspecialchars($datosConsulta['dientes_seleccionados']) . "</strong></p>";
    
    echo "<h3>Enlaces:</h3>";
    echo "<p>";
    echo "<a href='verificar_dientes_guardados.php' style='padding: 10px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📊 Ver Consultas Guardadas</a>";
    echo "<a href='nueva_consulta.php?paciente_id=" . $paciente['id'] . "' style='padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>🧪 Probar Nueva Consulta</a>";
    echo "</p>";
    
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "<h3>❌ Error:</h3>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    
    echo "<h4>Información de depuración:</h4>";
    echo "<pre>";
    echo "Error SQL: " . $e->getMessage() . "\n";
    echo "Código de error: " . $e->getCode() . "\n";
    echo "</pre>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #0056b3; }
h3 { color: #333; }
</style>
