<?php
// This script checks the configuration table and displays the logo if available
// It also ensures the medico_nombre column exists

require_once "config.php";

try {
    echo "<h2>Checking configuracion table structure</h2>";
    
    // Check if the medico_nombre column exists
    $stmt = $conn->prepare("SHOW COLUMNS FROM configuracion LIKE 'medico_nombre'");
    $stmt->execute();
    $medico_nombre_exists = $stmt->rowCount() > 0;
    
    echo "<p>medico_nombre column exists: " . ($medico_nombre_exists ? "Yes" : "No") . "</p>";
    
    // Add the column if it doesn't exist
    if (!$medico_nombre_exists) {
        echo "<p>Adding medico_nombre column...</p>";
        $sql = "ALTER TABLE configuracion ADD COLUMN medico_nombre VARCHAR(100) DEFAULT 'Dr. Médico' AFTER email_contacto";
        $conn->exec($sql);
        echo "<p>Column added successfully.</p>";
    }
    
    // Check logo and other configuration
    $stmt = $conn->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Current Configuration:</h2>";
    echo "<ul>";
    echo "<li>ID: " . $config['id'] . "</li>";
    echo "<li>Nombre Consultorio: " . htmlspecialchars($config['nombre_consultorio'] ?? 'No definido') . "</li>";
    echo "<li>Médico Nombre: " . htmlspecialchars($config['medico_nombre'] ?? 'No definido') . "</li>";
    echo "<li>Logo: " . (empty($config['logo']) ? "No hay logo guardado" : "Logo encontrado") . "</li>";
    echo "</ul>";
    
    // Display the logo if available
    if (!empty($config['logo'])) {
        $logo_path = 'data:image/png;base64,' . base64_encode($config['logo']);
        echo "<h3>Logo actual:</h3>";
        echo "<img src='" . $logo_path . "' alt='Logo' style='max-width: 200px;'>";
    } else {
        echo "<p>No hay logo guardado en la configuración.</p>";
    }
    
    echo "<p><a href='configuracion.php'>Ir a la página de configuración</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color:red'>Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
