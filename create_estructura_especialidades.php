<?php
require_once "config.php";

try {
    $conn->beginTransaction();

    // 1. Tabla de especialidades
    $sql = "CREATE TABLE IF NOT EXISTS especialidades (
        id INT AUTO_INCREMENT PRIMARY KEY,
        codigo VARCHAR(20) NOT NULL UNIQUE,
        nombre VARCHAR(100) NOT NULL,
        descripcion TEXT,
        estado ENUM('activo', 'inactivo') DEFAULT 'activo',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->exec($sql);

    // 2. Tabla para los campos personalizados de cada especialidad
    $sql = "CREATE TABLE IF NOT EXISTS especialidad_campos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        especialidad_id INT NOT NULL,
        nombre_campo VARCHAR(50) NOT NULL,
        etiqueta VARCHAR(100) NOT NULL,
        tipo_campo ENUM('texto', 'numero', 'fecha', 'seleccion', 'checkbox', 'textarea') NOT NULL,
        opciones TEXT NULL,
        requerido BOOLEAN DEFAULT FALSE,
        orden INT DEFAULT 0,
        estado ENUM('activo', 'inactivo') DEFAULT 'activo',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (especialidad_id) REFERENCES especialidades(id) ON DELETE CASCADE,
        UNIQUE KEY unique_campo_especialidad (especialidad_id, nombre_campo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->exec($sql);

    // 3. Crear tabla para almacenar valores de campos personalizados
    $sql = "CREATE TABLE IF NOT EXISTS consulta_campos_valores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        consulta_id INT NOT NULL,
        campo_id INT NOT NULL,
        valor TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (consulta_id) REFERENCES historial_medico(id) ON DELETE CASCADE,
        FOREIGN KEY (campo_id) REFERENCES especialidad_campos(id) ON DELETE CASCADE,
        UNIQUE KEY unique_valor_campo (consulta_id, campo_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->exec($sql);

    // 4. Insertar especialidades básicas
    $especialidades = [
        ['codigo' => 'MG', 'nombre' => 'Medicina General', 'descripcion' => 'Atención médica general y preventiva'],
        ['codigo' => 'PED', 'nombre' => 'Pediatría', 'descripcion' => 'Especialidad médica que estudia al niño y sus enfermedades'],
        ['codigo' => 'GIN', 'nombre' => 'Ginecología', 'descripcion' => 'Especialidad médica de la salud femenina'],
        ['codigo' => 'CAR', 'nombre' => 'Cardiología', 'descripcion' => 'Especialidad que trata enfermedades del corazón'],
        ['codigo' => 'DER', 'nombre' => 'Dermatología', 'descripcion' => 'Especialidad en enfermedades de la piel'],
        ['codigo' => 'OFT', 'nombre' => 'Oftalmología', 'descripcion' => 'Especialidad en enfermedades de los ojos']
    ];

    $stmt = $conn->prepare("INSERT IGNORE INTO especialidades (codigo, nombre, descripcion) VALUES (?, ?, ?)");
    foreach ($especialidades as $esp) {
        $stmt->execute([$esp['codigo'], $esp['nombre'], $esp['descripcion']]);
    }

    // 5. Insertar campos personalizados para cada especialidad
    // Pediatría
    $especialidad_id = $conn->query("SELECT id FROM especialidades WHERE codigo = 'PED'")->fetch(PDO::FETCH_COLUMN);
    if ($especialidad_id) {
        $campos_pediatria = [
            ['nombre_campo' => 'peso', 'etiqueta' => 'Peso (kg)', 'tipo_campo' => 'numero', 'requerido' => true, 'orden' => 1],
            ['nombre_campo' => 'talla', 'etiqueta' => 'Talla (cm)', 'tipo_campo' => 'numero', 'requerido' => true, 'orden' => 2],
            ['nombre_campo' => 'perimetro_cefalico', 'etiqueta' => 'Perímetro Cefálico (cm)', 'tipo_campo' => 'numero', 'requerido' => true, 'orden' => 3],
            ['nombre_campo' => 'desarrollo', 'etiqueta' => 'Desarrollo', 'tipo_campo' => 'seleccion', 'opciones' => 'Normal,Retraso leve,Retraso moderado,Retraso severo', 'orden' => 4],
            ['nombre_campo' => 'vacunas_completas', 'etiqueta' => 'Vacunas al día', 'tipo_campo' => 'checkbox', 'orden' => 5]
        ];

        $stmt = $conn->prepare("INSERT IGNORE INTO especialidad_campos 
            (especialidad_id, nombre_campo, etiqueta, tipo_campo, opciones, requerido, orden) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($campos_pediatria as $campo) {
            $stmt->execute([
                $especialidad_id,
                $campo['nombre_campo'],
                $campo['etiqueta'],
                $campo['tipo_campo'],
                $campo['opciones'] ?? null,
                $campo['requerido'] ?? false,
                $campo['orden']
            ]);
        }
    }

    // Ginecología
    $especialidad_id = $conn->query("SELECT id FROM especialidades WHERE codigo = 'GIN'")->fetch(PDO::FETCH_COLUMN);
    if ($especialidad_id) {
        $campos_ginecologia = [
            ['nombre_campo' => 'ultima_menstruacion', 'etiqueta' => 'Fecha última menstruación', 'tipo_campo' => 'fecha', 'requerido' => true, 'orden' => 1],
            ['nombre_campo' => 'gestas', 'etiqueta' => 'Número de embarazos', 'tipo_campo' => 'numero', 'orden' => 2],
            ['nombre_campo' => 'partos', 'etiqueta' => 'Número de partos', 'tipo_campo' => 'numero', 'orden' => 3],
            ['nombre_campo' => 'cesareas', 'etiqueta' => 'Número de cesáreas', 'tipo_campo' => 'numero', 'orden' => 4],
            ['nombre_campo' => 'abortos', 'etiqueta' => 'Número de abortos', 'tipo_campo' => 'numero', 'orden' => 5],
            ['nombre_campo' => 'metodo_anticonceptivo', 'etiqueta' => 'Método anticonceptivo', 'tipo_campo' => 'seleccion', 'opciones' => 'Ninguno,DIU,Implante,Oral,Inyectable,Otro', 'orden' => 6],
            ['nombre_campo' => 'papanicolau', 'etiqueta' => 'Fecha último Papanicolau', 'tipo_campo' => 'fecha', 'orden' => 7]
        ];

        $stmt = $conn->prepare("INSERT IGNORE INTO especialidad_campos 
            (especialidad_id, nombre_campo, etiqueta, tipo_campo, opciones, requerido, orden) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($campos_ginecologia as $campo) {
            $stmt->execute([
                $especialidad_id,
                $campo['nombre_campo'],
                $campo['etiqueta'],
                $campo['tipo_campo'],
                $campo['opciones'] ?? null,
                $campo['requerido'] ?? false,
                $campo['orden']
            ]);
        }
    }

    $conn->commit();
    echo "Tablas y datos iniciales creados exitosamente.\n";
} catch(PDOException $e) {
    $conn->rollBack();
    echo "Error al crear las tablas: " . $e->getMessage() . "\n";
}
?>
