<?php
require_once "config.php";

try {
    // First check if the column exists
    $stmt = $conn->prepare("SHOW COLUMNS FROM configuracion LIKE 'medico_nombre'");
    $stmt->execute();
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        // Add the column if it doesn't exist
        $sql = "ALTER TABLE configuracion ADD COLUMN medico_nombre VARCHAR(100) DEFAULT 'Dr. Médico' AFTER email_contacto";
        $conn->exec($sql);
        echo "Column medico_nombre has been added successfully.";
    } else {
        echo "Column medico_nombre already exists.";
    }
    
    // Update the medico_nombre value if it's NULL
    $stmt = $conn->prepare("UPDATE configuracion SET medico_nombre = 'Dr. Médico' WHERE (medico_nombre IS NULL OR medico_nombre = '') AND id = 1");
    $stmt->execute();
    $rowsUpdated = $stmt->rowCount();
    
    if ($rowsUpdated > 0) {
        echo "<br>Default value set for medico_nombre.";
    } else {
        echo "<br>Value for medico_nombre already exists.";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<br><br>Now try to go back to <a href='configuracion.php'>configuracion.php</a>";
?>
