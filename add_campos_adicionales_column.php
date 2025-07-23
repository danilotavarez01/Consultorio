<?php
// Script to add the campos_adicionales column to historial_medico table
require_once "config.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Adding campos_adicionales column</h1>";

try {
    // Check if the column already exists
    $stmt = $conn->prepare("SHOW COLUMNS FROM historial_medico LIKE 'campos_adicionales'");
    $stmt->execute();
    $columnExists = $stmt->rowCount() > 0;
    
    if ($columnExists) {
        echo "<p style='color:green'>Column 'campos_adicionales' already exists in the historial_medico table.</p>";
    } else {
        // Add the column
        $sql = "ALTER TABLE historial_medico ADD COLUMN campos_adicionales TEXT NULL AFTER observaciones";
        $conn->exec($sql);
        echo "<p style='color:green'>Column 'campos_adicionales' successfully added to the historial_medico table.</p>";
    }
    
    // Now check if the especialidad_id column exists too
    $stmt = $conn->prepare("SHOW COLUMNS FROM historial_medico LIKE 'especialidad_id'");
    $stmt->execute();
    $columnExists = $stmt->rowCount() > 0;
    
    if ($columnExists) {
        echo "<p style='color:green'>Column 'especialidad_id' already exists in the historial_medico table.</p>";
    } else {
        // Add the column
        $sql = "ALTER TABLE historial_medico ADD COLUMN especialidad_id INT NULL AFTER campos_adicionales";
        $conn->exec($sql);
        echo "<p style='color:green'>Column 'especialidad_id' successfully added to the historial_medico table.</p>";
    }
    
    echo "<h2>Current table structure:</h2>";
    $columns = $conn->query("DESCRIBE historial_medico")->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
    echo "<p><a href='nueva_consulta.php'>Return to new consultation page</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color:red'>Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
