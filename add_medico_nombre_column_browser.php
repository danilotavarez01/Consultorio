<?php
// This script adds the medico_nombre column to the configuracion table
require_once "config.php";

try {
    echo "<h2>Adding 'medico_nombre' column to configuracion table</h2>";
    
    // Check if column exists
    $stmt = $conn->prepare("SHOW COLUMNS FROM configuracion LIKE 'medico_nombre'");
    $stmt->execute();
    $columnExists = $stmt->rowCount() > 0;
    
    if ($columnExists) {
        echo "<p>Column 'medico_nombre' already exists in configuracion table.</p>";
    } else {
        // Add the column
        $sql = "ALTER TABLE configuracion ADD COLUMN medico_nombre VARCHAR(100) DEFAULT 'Dr. Médico' AFTER email_contacto";
        $conn->exec($sql);
        echo "<p style='color:green'>Column 'medico_nombre' successfully added to configuracion table.</p>";
        
        // Set a default value for all rows
        $sql = "UPDATE configuracion SET medico_nombre = 'Dr. Médico' WHERE medico_nombre IS NULL";
        $count = $conn->exec($sql);
        echo "<p>Updated $count record(s) with default value.</p>";
    }
    
    // Show updated table structure
    $stmt = $conn->query("DESCRIBE configuracion");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Current configuracion table structure:</h3>";
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
    echo "<p><a href='configuracion.php'>Return to configuration page</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color:red'>Error:</h3>";
    echo "<p>{$e->getMessage()}</p>";
}
?>
