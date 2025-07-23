<?php
require_once "config.php";

try {
    // Crear la tabla de especialidades
    $sql = "CREATE TABLE IF NOT EXISTS especialidades (
        id INT AUTO_INCREMENT PRIMARY KEY,
        codigo VARCHAR(20) NOT NULL UNIQUE,
        nombre VARCHAR(100) NOT NULL,
        descripcion TEXT,
        estado ENUM('activo', 'inactivo') DEFAULT 'activo',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "Tabla especialidades creada.\n";

    // Insertar especialidades básicas
    $sql = "INSERT IGNORE INTO especialidades (codigo, nombre, descripcion) VALUES 
        ('MG', 'Medicina General', 'Atención médica general y preventiva'),
        ('PED', 'Pediatría', 'Especialidad médica que estudia al niño y sus enfermedades'),
        ('GIN', 'Ginecología', 'Especialidad médica de la salud femenina'),
        ('CAR', 'Cardiología', 'Especialidad que trata enfermedades del corazón'),
        ('DER', 'Dermatología', 'Especialidad en enfermedades de la piel'),
        ('OFT', 'Oftalmología', 'Especialidad en enfermedades de los ojos')";
    
    $conn->exec($sql);
    echo "Especialidades básicas insertadas.\n";

    // Mostrar la estructura de la tabla
    $result = $conn->query("DESCRIBE especialidades");
    echo "\nEstructura de la tabla especialidades:\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']} - {$row['Default']}\n";
    }

    // Mostrar las especialidades insertadas
    $result = $conn->query("SELECT * FROM especialidades");
    echo "\nEspecialidades insertadas:\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['codigo']} - {$row['nombre']} - {$row['descripcion']}\n";
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
