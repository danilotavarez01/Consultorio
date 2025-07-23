<?php
// This script checks if the medico_nombre column exists in the configuracion table
// If not, it adds the column and displays the table structure before and after
require_once "config.php";

// Output as plain text for better readability
header("Content-Type: text/plain");

try {
    echo "Step 1: Checking if the medico_nombre column exists\n";
    
    // Check if the medico_nombre column already exists
    $stmt = $conn->query("SHOW COLUMNS FROM configuracion");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current configuracion table structure:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})\n";
    }
    
    // Check specifically for medico_nombre
    $found = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'medico_nombre') {
            $found = true;
            break;
        }
    }
    
    if ($found) {
        echo "\nThe medico_nombre column already exists in the configuracion table.\n";
    } else {
        echo "\nThe medico_nombre column does NOT exist. Adding it now...\n";
        
        // Add the column
        $sql = "ALTER TABLE configuracion ADD COLUMN medico_nombre VARCHAR(100) DEFAULT 'Dr. Médico' AFTER email_contacto";
        $conn->exec($sql);
        
        echo "Column added successfully.\n";
        
        // Check the table structure again
        $stmt = $conn->query("SHOW COLUMNS FROM configuracion");
        $newColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nUpdated configuracion table structure:\n";
        foreach ($newColumns as $column) {
            echo "- {$column['Field']} ({$column['Type']})\n";
        }
    }
    
    echo "\nStep 2: Checking current value in the configuracion table\n";
    
    // Now check if there's a value in the table
    $stmt = $conn->query("SELECT medico_nombre FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config && isset($config['medico_nombre'])) {
        echo "Current value for medico_nombre: {$config['medico_nombre']}\n";
    } else {
        echo "No value found for medico_nombre. Setting default value...\n";
        $sql = "UPDATE configuracion SET medico_nombre = 'Dr. Médico' WHERE id = 1";
        $conn->exec($sql);
        echo "Default value set.\n";
    }
    
    echo "\nOperation completed successfully.\n";
    echo "You can now return to the configuration page: <a href='configuracion.php'>Return to configuration page</a>";
    
} catch(PDOException $e) {
    echo "Database error: " . $e->getMessage();
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
