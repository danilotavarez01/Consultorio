<?php
require_once "config.php";

try {
    // Crear la tabla de configuración    
    $sql = "CREATE TABLE IF NOT EXISTS configuracion (
        id INT PRIMARY KEY,
        nombre_consultorio VARCHAR(255) NOT NULL DEFAULT 'Consultorio Médico',
        email_contacto VARCHAR(255),
        logo MEDIUMBLOB,
        duracion_cita INT DEFAULT 30,
        hora_inicio TIME DEFAULT '09:00:00',
        hora_fin TIME DEFAULT '18:00:00',
        dias_laborables VARCHAR(100) DEFAULT '1,2,3,4,5',
        intervalo_citas INT DEFAULT 30,
        require_https BOOLEAN DEFAULT FALSE,
        modo_mantenimiento BOOLEAN DEFAULT FALSE,
        telefono VARCHAR(50),
        direccion TEXT,
        moneda VARCHAR(10) DEFAULT '$',
        zona_horaria VARCHAR(100) DEFAULT 'America/Santo_Domingo',
        formato_fecha VARCHAR(20) DEFAULT 'Y-m-d',
        idioma VARCHAR(10) DEFAULT 'es',
        tema_color VARCHAR(20) DEFAULT 'light',
        mostrar_alertas_stock BOOLEAN DEFAULT TRUE,
        notificaciones_email BOOLEAN DEFAULT FALSE,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by VARCHAR(50)
    )";

    $conn->exec($sql);
    
    // Insertar configuración inicial
    $sql = "INSERT IGNORE INTO configuracion (id, nombre_consultorio, email_contacto, duracion_cita, hora_inicio, hora_fin) 
            VALUES (1, 'Consultorio Médico', NULL, 30, '09:00:00', '18:00:00')";
    
    $conn->exec($sql);
    
    echo "Tabla de configuración creada exitosamente.\n";
} catch(PDOException $e) {
    echo "Error al crear la tabla de configuración: " . $e->getMessage() . "\n";
}
?>
