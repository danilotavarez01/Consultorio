<?php
require_once 'config.php';

echo "<h2>🏥 Configuración de Seguros Médicos</h2>";

try {
    echo "<h3>📝 Paso 1: Crear tabla seguro_medico</h3>";
    
    // Crear tabla seguro_medico
    $sql = "CREATE TABLE IF NOT EXISTS seguro_medico (
        id INT AUTO_INCREMENT PRIMARY KEY,
        descripcion VARCHAR(255) NOT NULL,
        activo TINYINT(1) DEFAULT 1,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "✅ Tabla 'seguro_medico' creada exitosamente.<br><br>";
    
    echo "<h3>📊 Paso 2: Insertar seguros médicos predeterminados</h3>";
    
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
    
    $stmt = $conn->prepare("INSERT IGNORE INTO seguro_medico (descripcion) VALUES (?)");
    
    foreach ($seguros_default as $seguro) {
        $stmt->execute([$seguro]);
        if ($stmt->rowCount() > 0) {
            echo "✅ Seguro médico '$seguro' agregado.<br>";
        } else {
            echo "ℹ️ Seguro médico '$seguro' ya existe.<br>";
        }
    }
    
    echo "<br><h3>🔗 Paso 3: Verificar/crear columna en pacientes</h3>";
    
    // Verificar si la columna seguro_medico_id existe en pacientes
    $result = $conn->query("SHOW COLUMNS FROM pacientes LIKE 'seguro_medico_id'");
    
    if ($result->rowCount() == 0) {
        // Crear la columna si no existe
        $conn->exec("ALTER TABLE pacientes ADD COLUMN seguro_medico_id INT NULL");
        echo "✅ Columna 'seguro_medico_id' agregada a la tabla 'pacientes'.<br>";
        
        // Agregar clave foránea
        $conn->exec("ALTER TABLE pacientes ADD CONSTRAINT fk_pacientes_seguro_medico 
                     FOREIGN KEY (seguro_medico_id) REFERENCES seguro_medico(id) ON DELETE SET NULL");
        echo "✅ Clave foránea agregada entre 'pacientes' y 'seguro_medico'.<br>";
    } else {
        echo "ℹ️ Columna 'seguro_medico_id' ya existe en la tabla 'pacientes'.<br>";
    }
    
    echo "<br><h3>📈 Paso 4: Verificar resultados</h3>";
    
    // Verificar datos
    $stmt = $conn->query("SELECT COUNT(*) as total FROM seguro_medico");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "✅ Total de seguros médicos en la base de datos: <strong>$total</strong><br>";
    
    echo "<br><div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
    echo "<strong>🎉 ¡Configuración completada exitosamente!</strong><br>";
    echo "El módulo de seguros médicos está listo para usar.";
    echo "</div>";
    
    echo "<br><p><strong>🔧 Próximos pasos:</strong></p>";
    echo "<a href='add_seguros_permission.php' style='background: #ffc107; color: black; padding: 10px; text-decoration: none; border-radius: 5px; margin: 5px;'>🔑 Configurar Permisos</a>";
    echo "<a href='seguro_medico.php' style='background: #28a745; color: white; padding: 10px; text-decoration: none; border-radius: 5px; margin: 5px;'>📋 Ir al Módulo</a>";
    
} catch(PDOException $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "❌ <strong>Error:</strong> " . $e->getMessage();
    echo "</div>";
}
?>
