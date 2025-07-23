<?php
require_once "config.php";

try {
    $tableExists = $conn->query("SHOW TABLES LIKE 'citas'")->rowCount() > 0;
    if ($tableExists) {
        echo "La tabla 'citas' ya existe en la base de datos.\n";
        
        // Mostrar la estructura de la tabla
        $columns = $conn->query("SHOW COLUMNS FROM citas");
        echo "Estructura de la tabla 'citas':\n";
        while ($column = $columns->fetch(PDO::FETCH_ASSOC)) {
            echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
        }
    } else {
        echo "La tabla 'citas' NO existe en la base de datos. Creándola...\n";
        
        // Intentar crear la tabla con el esquema correcto
        $sql = "CREATE TABLE IF NOT EXISTS citas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            fecha DATETIME NOT NULL,
            id_paciente INT NOT NULL,
            id_doctor INT NOT NULL,
            motivo VARCHAR(255),
            estado VARCHAR(50) DEFAULT 'Pendiente',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $conn->exec($sql);
        echo "¡Tabla 'citas' creada exitosamente!\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
