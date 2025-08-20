<?php
require_once 'config.php';

try {
    echo "Columnas de la tabla pacientes:\n";
    $stmt = $conn->query('DESCRIBE pacientes');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    echo "\nColumnas de la tabla usuarios:\n";
    $stmt = $conn->query('DESCRIBE usuarios');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
