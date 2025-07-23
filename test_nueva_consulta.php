<?php
require_once "config.php";

echo "<h1>Test Nueva Consulta</h1>";

try {
    $conn->beginTransaction();
    
    echo "<h2>1. Verificando estructura de la tabla</h2>";
    $stmt = $conn->query("DESCRIBE historial_medico");
    $columns = [];
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $row['Field'];
    }
    
    echo "<p>Columnas encontradas: " . implode(", ", $columns) . "</p>";
    
    // Verificar que las columnas necesarias existan
    $required_columns = ['campos_adicionales', 'especialidad_id'];
    $missing_columns = [];
    foreach ($required_columns as $col) {
        if (!in_array($col, $columns)) {
            $missing_columns[] = $col;
        }
    }
    
    if (count($missing_columns) > 0) {
        echo "<p style='color:red'>Faltan columnas: " . implode(", ", $missing_columns) . "</p>";
    } else {
        echo "<p style='color:green'>Todas las columnas necesarias están presentes.</p>";
    }
    
    echo "<h2>2. Haciendo prueba de inserción</h2>";
    
    // Datos de prueba
    $paciente_id = 1; // Asegúrate de que este ID de paciente exista
    $doctor_id = 1;
    $fecha = date('Y-m-d');
    $motivo_consulta = "Prueba de consulta";
    $diagnostico = "Diagnóstico de prueba";
    $tratamiento = "Tratamiento de prueba";
    $observaciones = "Observaciones de prueba";
    
    // Campos adicionales como JSON
    $campos_adicionales = json_encode(['temperatura' => '36.5', 'presion_arterial' => '120/80']);
    
    // Obtener ID de la especialidad configurada
    $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    $especialidad_id = $config['especialidad_id'] ?? null;
    
    echo "<p>ID de especialidad configurada: " . ($especialidad_id ?? "No configurada") . "</p>";
    
    // Insertar consulta de prueba
    $sql = "INSERT INTO historial_medico (
                paciente_id, 
                doctor_id, 
                fecha, 
                motivo_consulta, 
                diagnostico, 
                tratamiento, 
                observaciones,
                campos_adicionales,
                especialidad_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $paciente_id,
        $doctor_id,
        $fecha,
        $motivo_consulta,
        $diagnostico,
        $tratamiento,
        $observaciones,
        $campos_adicionales,
        $especialidad_id
    ]);
    
    $consulta_id = $conn->lastInsertId();
    echo "<p style='color:green'>Consulta creada exitosamente con ID: " . $consulta_id . "</p>";
    
    // Probar la recuperación
    echo "<h2>3. Verificando la consulta insertada</h2>";
    
    $stmt = $conn->prepare("SELECT * FROM historial_medico WHERE id = ?");
    $stmt->execute([$consulta_id]);
    $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($consulta);
    echo "</pre>";
    
    $conn->commit();
    
    echo "<h2>Resumen</h2>";
    echo "<p style='color:green'>Prueba completada con éxito. El error ha sido corregido.</p>";
    
    echo "<p><a href='nueva_consulta.php' class='btn btn-primary'>Probar formulario de nueva consulta</a></p>";
    
} catch (Exception $e) {
    $conn->rollBack();
    echo "<h2>Error</h2>";
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
