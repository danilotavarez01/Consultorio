<?php
require_once "config.php";

// Comprobar la estructura de la tabla historial_medico
try {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Mostrar la estructura de la tabla historial_medico
    $stmt = $conn->query("DESCRIBE historial_medico");
    echo "<h2>Estructura de la tabla historial_medico:</h2>";
    echo "<pre>";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
