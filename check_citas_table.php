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
        echo "La tabla 'citas' NO existe en la base de datos.\n";
        
        // Intentar crear la tabla
        $conn->exec("CREATE TABLE IF NOT EXISTS citas (
            id INT(11) NOT NULL AUTO_INCREMENT,
            fecha DATE NOT NULL,
            hora TIME NOT NULL,
            paciente_id INT(11) NOT NULL,
            doctor_id INT(11) NOT NULL,
            estado ENUM('Pendiente', 'Confirmada', 'Cancelada', 'Completada') DEFAULT 'Pendiente',
            observaciones TEXT,
            PRIMARY KEY (id),
            KEY paciente_id (paciente_id),
            KEY doctor_id (doctor_id),
            CONSTRAINT fk_citas_paciente FOREIGN KEY (paciente_id) REFERENCES pacientes (id) ON DELETE CASCADE,
            CONSTRAINT fk_citas_doctor FOREIGN KEY (doctor_id) REFERENCES usuarios (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        
        echo "¡Tabla 'citas' creada exitosamente!\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
