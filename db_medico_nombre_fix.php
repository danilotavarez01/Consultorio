<?php
// Turn on error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$host = 'localhost';
$port = 3306;
$dbname = 'consultorio';
$username = 'root';
$password = '820416Dts';

try {
    echo "Connecting to database...<br>";
    
    // Create connection with PDO
    $conn = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", 
        $username, 
        $password,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
    
    echo "Connected successfully!<br>";
    
    // Check for the medico_nombre column
    $stmt = $conn->prepare("SHOW COLUMNS FROM configuracion LIKE 'medico_nombre'");
    $stmt->execute();
    $columnExists = $stmt->rowCount() > 0;
    
    echo "Column medico_nombre exists: " . ($columnExists ? "Yes" : "No") . "<br>";
    
    // Add the column if it doesn't exist
    if (!$columnExists) {
        $sql = "ALTER TABLE configuracion ADD COLUMN medico_nombre VARCHAR(100) DEFAULT 'Dr. Médico' AFTER email_contacto";
        $conn->exec($sql);
        echo "Column medico_nombre has been added successfully.<br>";
    }
    
    // Now check the value
    $stmt = $conn->query("SELECT id, nombre_consultorio, medico_nombre FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Current configuration:<br>";
    echo "- ID: " . $config['id'] . "<br>";
    echo "- Consultorio: " . $config['nombre_consultorio'] . "<br>";
    echo "- Médico Nombre: " . ($config['medico_nombre'] ?? 'NULL') . "<br>";
    
    // Update the value if it's NULL
    if (!isset($config['medico_nombre']) || empty($config['medico_nombre'])) {
        $stmt = $conn->prepare("UPDATE configuracion SET medico_nombre = 'Dr. Médico' WHERE id = 1");
        $stmt->execute();
        echo "Default value set for medico_nombre.<br>";
    }
    
} catch (PDOException $e) {
    echo "Database Connection Error: " . $e->getMessage() . "<br>";
}

echo "<br>Return to <a href='configuracion.php'>configuration page</a>";
?>
