<?php
require_once "config.php";

try {
    // Muestra información sobre la base de datos MySQL
    echo "===== Información de la base de datos MySQL =====\n";
    
    // Verificar la conexión
    echo "Conexión a MySQL: " . ($conn ? "OK" : "Fallida") . "\n";
    echo "Versión del servidor: " . $conn->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
    
    // Listar tablas
    $tablas = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tablas en la base de datos:\n";
    foreach($tablas as $tabla) {
        echo "- $tabla\n";
    }
    
    // Verificar si existe la tabla citas
    $existeCitas = in_array('citas', $tablas);
    echo "\nTabla 'citas': " . ($existeCitas ? "Existe" : "No existe") . "\n";
    
    if($existeCitas) {
        // Mostrar estructura de la tabla
        echo "\nEstructura de la tabla 'citas':\n";
        $columnas = $conn->query("DESCRIBE citas")->fetchAll(PDO::FETCH_ASSOC);
        foreach($columnas as $columna) {
            echo "- " . $columna['Field'] . " (" . $columna['Type'] . ")\n";
        }
        
        // Contar registros
        $count = $conn->query("SELECT COUNT(*) FROM citas")->fetchColumn();
        echo "\nNúmero de registros en 'citas': " . $count . "\n";
    } else {
        // Crear la tabla si no existe
        echo "\nCreando tabla 'citas'...\n";
        $sql = "CREATE TABLE IF NOT EXISTS citas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            paciente_id INT NOT NULL,
            fecha DATE NOT NULL,
            hora TIME NOT NULL,
            doctor_id INT NOT NULL,
            estado ENUM('Pendiente', 'Confirmada', 'Cancelada', 'Completada') DEFAULT 'Pendiente',
            observaciones TEXT,
            INDEX idx_paciente (paciente_id),
            INDEX idx_doctor (doctor_id),
            FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE,
            FOREIGN KEY (doctor_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $conn->exec($sql);
        echo "Tabla 'citas' creada exitosamente.\n";
    }
    
    echo "\n===== Comprobando permisos de usuario =====\n";
    echo "Rol actual: " . (isset($_SESSION["rol"]) ? $_SESSION["rol"] : "No definido") . "\n";
    echo "Permiso 'manage_appointments': " . (function_exists('hasPermission') && hasPermission('manage_appointments') ? "SÍ" : "NO") . "\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
