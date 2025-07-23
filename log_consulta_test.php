<?php
require_once "config.php";

// Define log file
$log_file = __DIR__ . '/consulta_test_log.txt';

// Function to log messages
function log_message($message) {
    global $log_file;
    file_put_contents($log_file, $message . "\n", FILE_APPEND);
}

// Clear log file
file_put_contents($log_file, "Test started at " . date('Y-m-d H:i:s') . "\n");

try {
    log_message("Checking historial_medico table structure");
    
    // Check table structure
    $stmt = $conn->query("DESCRIBE historial_medico");
    $columns = [];
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[$row['Field']] = $row['Type'];
    }
    
    log_message("Columns found: " . print_r($columns, true));
    
    // Check for required columns
    $required_columns = ['campos_adicionales', 'especialidad_id'];
    $missing = [];
    foreach ($required_columns as $col) {
        if (!isset($columns[$col])) {
            $missing[] = $col;
        }
    }
    
    if (!empty($missing)) {
        log_message("ERROR: Missing columns: " . implode(", ", $missing));
    } else {
        log_message("All required columns are present");
    }
    
    // Get a valid patient ID
    $stmt = $conn->query("SELECT id FROM pacientes LIMIT 1");
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    $paciente_id = $paciente ? $paciente['id'] : 1;
    
    log_message("Using patient ID: " . $paciente_id);
    
    // Test data
    $test_data = [
        'paciente_id' => $paciente_id,
        'doctor_id' => 1,
        'fecha' => date('Y-m-d'),
        'motivo_consulta' => 'Test motivo consulta',
        'diagnostico' => 'Test diagnóstico',
        'tratamiento' => 'Test tratamiento',
        'observaciones' => 'Test observaciones',
        'campos_adicionales' => json_encode(['temp' => '36.5', 'presion' => '120/80']),
        'especialidad_id' => 1
    ];
    
    log_message("Preparing to insert test data: " . print_r($test_data, true));
    
    // Build SQL
    $fields = implode(", ", array_keys($test_data));
    $placeholders = implode(", ", array_fill(0, count($test_data), '?'));
    
    $sql = "INSERT INTO historial_medico ($fields) VALUES ($placeholders)";
    log_message("SQL: $sql");
    
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute(array_values($test_data));
    
    if ($result) {
        $id = $conn->lastInsertId();
        log_message("SUCCESS: Record inserted with ID: $id");
        
        // Verify insertion
        $stmt = $conn->prepare("SELECT * FROM historial_medico WHERE id = ?");
        $stmt->execute([$id]);
        $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
        
        log_message("Inserted data: " . print_r($consulta, true));
    } else {
        log_message("ERROR: Failed to insert record");
    }
    
    log_message("Test completed successfully!");
    
} catch (Exception $e) {
    log_message("EXCEPTION: " . $e->getMessage());
    log_message("Stack trace: " . $e->getTraceAsString());
}

// Output to browser that log file is ready
echo "Test completed. Check log file: consulta_test_log.txt";
?>
