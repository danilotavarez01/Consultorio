<?php
require_once 'config.php';

try {
    // Crear tabla seguro_medico
    $sql = "CREATE TABLE IF NOT EXISTS seguro_medico (
        id INT AUTO_INCREMENT PRIMARY KEY,
        descripcion VARCHAR(255) NOT NULL,
        activo TINYINT(1) DEFAULT 1,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "✅ Tabla 'seguro_medico' creada exitosamente.<br>";
    
    // Insertar algunos seguros médicos por defecto
    $seguros_default = [
        'ARS Humano',
        'ARS Palic',
        'ARS Universal',
        'ARS Futuro',
        'ARS Senasa',
        'Seguros Reservas',
        'Mapfre',
        'Sin Seguro',
        'Particular'
    ];
    
    $stmt = $conn->prepare("INSERT INTO seguro_medico (descripcion) VALUES (?)");
    
    foreach ($seguros_default as $seguro) {
        // Verificar si ya existe para evitar duplicados
        $check = $conn->prepare("SELECT id FROM seguro_medico WHERE descripcion = ?");
        $check->execute([$seguro]);
        
        if ($check->rowCount() == 0) {
            $stmt->execute([$seguro]);
            echo "✅ Seguro médico '$seguro' agregado.<br>";
        } else {
            echo "ℹ️ Seguro médico '$seguro' ya existe.<br>";
        }
    }
    
    // Verificar si la columna seguro_medico en pacientes existe y es del tipo correcto
    $result = $conn->query("SHOW COLUMNS FROM pacientes LIKE 'seguro_medico'");
    $column_exists = $result->rowCount() > 0;
    
    if ($column_exists) {
        $column_info = $result->fetch(PDO::FETCH_ASSOC);
        echo "ℹ️ Columna 'seguro_medico' en tabla 'pacientes' ya existe: " . $column_info['Type'] . "<br>";
        
        // Si es VARCHAR, cambiarla a INT para referencia de clave foránea
        if (strpos($column_info['Type'], 'varchar') !== false || strpos($column_info['Type'], 'text') !== false) {
            echo "🔄 Convirtiendo columna 'seguro_medico' de VARCHAR a INT...<br>";
            
            // Crear una nueva columna temporal
            $conn->exec("ALTER TABLE pacientes ADD COLUMN seguro_medico_id INT NULL AFTER seguro_medico");
            
            // Migrar los datos existentes
            $stmt = $conn->prepare("SELECT id, seguro_medico FROM pacientes WHERE seguro_medico IS NOT NULL AND seguro_medico != ''");
            $stmt->execute();
            $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $update_stmt = $conn->prepare("UPDATE pacientes SET seguro_medico_id = ? WHERE id = ?");
            $find_seguro_stmt = $conn->prepare("SELECT id FROM seguro_medico WHERE descripcion = ? LIMIT 1");
            
            foreach ($pacientes as $paciente) {
                $find_seguro_stmt->execute([$paciente['seguro_medico']]);
                $seguro = $find_seguro_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($seguro) {
                    $update_stmt->execute([$seguro['id'], $paciente['id']]);
                    echo "✅ Paciente ID {$paciente['id']}: '{$paciente['seguro_medico']}' → ID {$seguro['id']}<br>";
                } else {
                    // Crear el seguro si no existe
                    $insert_seguro = $conn->prepare("INSERT INTO seguro_medico (descripcion) VALUES (?)");
                    $insert_seguro->execute([$paciente['seguro_medico']]);
                    $nuevo_seguro_id = $conn->lastInsertId();
                    
                    $update_stmt->execute([$nuevo_seguro_id, $paciente['id']]);
                    echo "➕ Nuevo seguro creado: '{$paciente['seguro_medico']}' (ID: $nuevo_seguro_id)<br>";
                }
            }
            
            // Eliminar la columna antigua y renombrar la nueva
            $conn->exec("ALTER TABLE pacientes DROP COLUMN seguro_medico");
            $conn->exec("ALTER TABLE pacientes CHANGE seguro_medico_id seguro_medico_id INT NULL");
            
            echo "✅ Migración de datos completada.<br>";
        }
    } else {
        // Crear la columna si no existe
        $conn->exec("ALTER TABLE pacientes ADD COLUMN seguro_medico_id INT NULL");
        echo "✅ Columna 'seguro_medico_id' agregada a la tabla 'pacientes'.<br>";
    }
    
    // Agregar clave foránea si no existe
    $foreign_keys = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
                                  WHERE TABLE_SCHEMA = DATABASE() 
                                  AND TABLE_NAME = 'pacientes' 
                                  AND CONSTRAINT_NAME LIKE '%seguro%'");
    
    if ($foreign_keys->rowCount() == 0) {
        $conn->exec("ALTER TABLE pacientes ADD CONSTRAINT fk_pacientes_seguro_medico 
                     FOREIGN KEY (seguro_medico_id) REFERENCES seguro_medico(id) ON DELETE SET NULL");
        echo "✅ Clave foránea agregada entre 'pacientes' y 'seguro_medico'.<br>";
    } else {
        echo "ℹ️ Clave foránea ya existe.<br>";
    }
    
    echo "<br><strong>🎉 Configuración de seguro médico completada exitosamente!</strong><br>";
    echo "<a href='seguro_medico.php'>👉 Ir al módulo de Seguros Médicos</a><br>";
    echo "<a href='pacientes.php'>👉 Ver pacientes actualizados</a>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
