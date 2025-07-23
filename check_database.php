<?php
require_once "config.php";

try {
    // Listar todas las tablas
    $result = $conn->query("SHOW TABLES");
    echo "Tablas en la base de datos:\n";
    $tables = [];
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
        echo "- " . $row[0] . "\n";
    }
    
    echo "\n";
    
    // Si la tabla citas no existe, vamos a crearla
    if (!in_array('citas', $tables)) {
        echo "La tabla 'citas' no existe. Creándola...\n";
        
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
        echo "¡Tabla 'citas' creada exitosamente!\n";
    } else {
        echo "La tabla 'citas' ya existe.\n";
        
        // Mostrar la estructura de la tabla
        $columns = $conn->query("SHOW COLUMNS FROM citas");
        echo "Estructura de la tabla 'citas':\n";
        while ($column = $columns->fetch(PDO::FETCH_ASSOC)) {
            echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
        }
    }
    
    // También verificamos si las tablas referenciadas existen y su estructura
    if (in_array('pacientes', $tables)) {
        echo "\nTabla 'pacientes' existe.\n";
        $columns = $conn->query("SHOW COLUMNS FROM pacientes");
        echo "Estructura de la tabla 'pacientes':\n";
        while ($column = $columns->fetch(PDO::FETCH_ASSOC)) {
            echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
        }
    }
    
    if (in_array('usuarios', $tables)) {
        echo "\nTabla 'usuarios' existe.\n";
        $columns = $conn->query("SHOW COLUMNS FROM usuarios");
        echo "Estructura de la tabla 'usuarios':\n";
        while ($column = $columns->fetch(PDO::FETCH_ASSOC)) {
            echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
