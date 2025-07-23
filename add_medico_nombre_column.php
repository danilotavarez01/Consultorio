<?php
require_once "config.php";

try {
    // Check if the medico_nombre column already exists
    $stmt = $conn->query("SHOW COLUMNS FROM configuracion LIKE 'medico_nombre'");
    if ($stmt->rowCount() == 0) {
        // Column does not exist, so add it
        $sql = "ALTER TABLE configuracion ADD COLUMN medico_nombre VARCHAR(100) DEFAULT 'Dr. Médico' AFTER email_contacto";
        $conn->exec($sql);
        echo "Column 'medico_nombre' added successfully to the configuracion table.";
    } else {
        echo "Column 'medico_nombre' already exists in the configuracion table.";
    }
} catch(PDOException $e) {
    echo "Database error: " . $e->getMessage();
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
