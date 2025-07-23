<?php
require_once "config.php";

try {
    $stmt = $conn->query("SHOW CREATE TABLE pacientes");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "CREATE statement para la tabla pacientes:\n";
    print_r($row);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
