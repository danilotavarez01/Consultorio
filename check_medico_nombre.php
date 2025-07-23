<?php
require_once "config.php";

// Output as plain text for clarity
header("Content-Type: text/plain");

try {
    echo "Checking configuracion table structure...\n\n";
    
    // Get column information
    $stmt = $conn->query("DESCRIBE configuracion");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Table columns:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})\n";
    }
    
    // Check if medico_nombre exists
    $medico_nombre_exists = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'medico_nombre') {
            $medico_nombre_exists = true;
            break;
        }
    }
    
    echo "\nDoes medico_nombre exist? " . ($medico_nombre_exists ? "Yes" : "No") . "\n";
    
    // Get current value
    if ($medico_nombre_exists) {
        $stmt = $conn->query("SELECT id, nombre_consultorio, medico_nombre FROM configuracion WHERE id = 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "\nCurrent configuration values:\n";
        echo "- ID: {$config['id']}\n";
        echo "- Consultorio: {$config['nombre_consultorio']}\n";
        echo "- Médico Nombre: " . (isset($config['medico_nombre']) ? $config['medico_nombre'] : "NULL") . "\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
