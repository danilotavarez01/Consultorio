<?php
require_once "config.php";

try {    // Crear la tabla de especialidades
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

    // Insertar especialidades básicas    // Insertar especialidades básicas
    $sql = "INSERT IGNORE INTO especialidades (codigo, nombre, descripcion) VALUES 
        ('MG', 'Medicina General', 'Atención médica general y preventiva'),
        ('PED', 'Pediatría', 'Especialidad médica que estudia al niño y sus enfermedades'),
        ('GIN', 'Ginecología', 'Especialidad médica de la salud femenina'),
        ('CAR', 'Cardiología', 'Especialidad que trata enfermedades del corazón'),
        ('DER', 'Dermatología', 'Especialidad en enfermedades de la piel'),
        ('OFT', 'Oftalmología', 'Especialidad en enfermedades de los ojos')";
    
    $conn->exec($sql);
                'motivo_consulta' => ['tipo' => 'text', 'label' => 'Motivo de Consulta', 'requerido' => true],
                'sintomas' => ['tipo' => 'textarea', 'label' => 'Síntomas', 'requerido' => true],
                'diagnostico' => ['tipo' => 'textarea', 'label' => 'Diagnóstico', 'requerido' => true],
                'tratamiento' => ['tipo' => 'textarea', 'label' => 'Tratamiento', 'requerido' => true]
            
        ,
        [
            'nombre' => 'Pediatría',
            'descripcion' => 'Especialista en niños',
            'campos_consulta' => json_encode([
                'motivo_consulta' => ['tipo' => 'text', 'label' => 'Motivo de Consulta', 'requerido' => true],
                'peso' => ['tipo' => 'number', 'label' => 'Peso (kg)', 'requerido' => true],
                'talla' => ['tipo' => 'number', 'label' => 'Talla (cm)', 'requerido' => true],
                'temperatura' => ['tipo' => 'number', 'label' => 'Temperatura (°C)', 'requerido' => true],
                'desarrollo' => ['tipo' => 'select', 'label' => 'Desarrollo', 'opciones' => ['Normal', 'Retraso leve', 'Retraso moderado', 'Retraso severo']],
                'vacunas' => ['tipo' => 'checkbox', 'label' => 'Vacunas al día'],
                'sintomas' => ['tipo' => 'textarea', 'label' => 'Síntomas', 'requerido' => true],
                'diagnostico' => ['tipo' => 'textarea', 'label' => 'Diagnóstico', 'requerido' => true],
                'tratamiento' => ['tipo' => 'textarea', 'label' => 'Tratamiento', 'requerido' => true]
            ])
        ],
        [
            'nombre' => 'Ginecología',
            'descripcion' => 'Especialista en salud femenina',
            'campos_consulta' => json_encode([
                'motivo_consulta' => ['tipo' => 'text', 'label' => 'Motivo de Consulta', 'requerido' => true],
                'ultima_menstruacion' => ['tipo' => 'date', 'label' => 'Última Menstruación', 'requerido' => true],
                'gestas' => ['tipo' => 'number', 'label' => 'Número de Embarazos'],
                'partos' => ['tipo' => 'number', 'label' => 'Número de Partos'],
                'cesareas' => ['tipo' => 'number', 'label' => 'Número de Cesáreas'],
                'abortos' => ['tipo' => 'number', 'label' => 'Número de Abortos'],
                'metodo_anticonceptivo' => ['tipo' => 'select', 'label' => 'Método Anticonceptivo', 'opciones' => ['Ninguno', 'DIU', 'Implante', 'Oral', 'Inyectable', 'Otro']],
                'sintomas' => ['tipo' => 'textarea', 'label' => 'Síntomas', 'requerido' => true],
                'diagnostico' => ['tipo' => 'textarea', 'label' => 'Diagnóstico', 'requerido' => true],
                'tratamiento' => ['tipo' => 'textarea', 'label' => 'Tratamiento', 'requerido' => true]
            ])
        ]
    ];

    $stmt = $conn->prepare("INSERT IGNORE INTO especialidades (nombre, descripcion, campos_consulta) VALUES (?, ?, ?)");
    foreach ($especialidades as $esp) {
        $stmt->execute([$esp['nombre'], $esp['descripcion'], $esp['campos_consulta']]);
    }

    echo "Tabla de especialidades creada exitosamente.\n";
} catch(PDOException $e) {
    echo "Error al crear la tabla de especialidades: " . $e->getMessage() . "\n";
}
?>
