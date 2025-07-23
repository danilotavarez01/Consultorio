<?php
require_once "config.php";

try {
    $stmt = $conn->query("DESCRIBE pacientes");
    echo "Estructura de la tabla pacientes:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
