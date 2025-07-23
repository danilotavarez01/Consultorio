<?php
require_once "config.php";

try {
    // Eliminar tabla si existe
    $conn->exec("DROP TABLE IF EXISTS configuracion");
    
    // Crear la tabla de configuración con el nuevo campo logo
    $sql = "CREATE TABLE configuracion (
        id INT PRIMARY KEY,
        nombre_consultorio VARCHAR(255) NOT NULL DEFAULT 'Consultorio Médico',
        email_contacto VARCHAR(255),
        logo VARCHAR(255),
        duracion_cita INT DEFAULT 30,
        hora_inicio TIME DEFAULT '09:00:00',
        hora_fin TIME DEFAULT '18:00:00',
        require_https BOOLEAN DEFAULT FALSE,
        modo_mantenimiento BOOLEAN DEFAULT FALSE,
        telefono VARCHAR(50),
        direccion TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by VARCHAR(50)
    )";

    $conn->exec($sql);
    
    // Insertar configuración inicial
    $sql = "INSERT INTO configuracion (id, nombre_consultorio, email_contacto, logo, duracion_cita, hora_inicio, hora_fin) 
            VALUES (1, 'Consultorio Médico', NULL, NULL, 30, '09:00:00', '18:00:00')";
    
    $conn->exec($sql);
    
    echo "Tabla de configuración recreada exitosamente con el campo logo.\n";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
