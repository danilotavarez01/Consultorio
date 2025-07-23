<?php
// Script to check the historial_medico table structure
require_once "config.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Historial Médico Table Structure</h1>";

try {
    // Check if the table exists
    $tables = $conn->query("SHOW TABLES LIKE 'historial_medico'")->fetchAll();
    if (count($tables) === 0) {
        echo "<p style='color:red'>The table 'historial_medico' doesn't exist!</p>";
        exit;
    }
    
    // Show table structure
    $columns = $conn->query("DESCRIBE historial_medico")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Current columns:</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        foreach ($column as $key => $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    // Check if the campos_adicionales column exists
    $hasCamposAdicionales = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'campos_adicionales') {
            $hasCamposAdicionales = true;
            break;
        }
    }
    
    if (!$hasCamposAdicionales) {
        echo "<p style='color:red'>The 'campos_adicionales' column is missing from the table!</p>";
        echo "<p>You need to run the <a href='add_campos_adicionales_column.php'>add_campos_adicionales_column.php</a> script to add it.</p>";
    } else {
        echo "<p style='color:green'>The 'campos_adicionales' column exists in the table.</p>";
    }
    
    // Show sample data
    $count = $conn->query("SELECT COUNT(*) FROM historial_medico")->fetchColumn();
    echo "<h2>Data count: $count rows</h2>";
    
    if ($count > 0) {
        $sampleData = $conn->query("SELECT * FROM historial_medico LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        echo "<h3>Sample record:</h3>";
        echo "<pre>";
        print_r($sampleData);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<h3 style='color:red'>Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}

echo "<p><a href='nueva_consulta.php'>Return to new consultation page</a></p>";
?>
