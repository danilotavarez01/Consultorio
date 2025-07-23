<?php
/**
 * Diagnóstico de la tabla configuración
 */

require_once 'config.php';

echo "<h2>Diagnóstico: Tabla Configuración</h2>";

try {
    // 1. Verificar si la tabla existe
    echo "<h3>1. Verificando existencia de tabla</h3>";
    $stmt = $conn->query("SHOW TABLES LIKE 'configuracion'");
    $tabla_existe = $stmt->rowCount() > 0;
    
    echo "- Tabla 'configuracion' existe: " . ($tabla_existe ? "✅ Sí" : "❌ No") . "<br>";
    
    if (!$tabla_existe) {
        echo "<p style='color: red;'>⚠️ La tabla 'configuracion' no existe. Creando tabla...</p>";
        
        // Crear la tabla si no existe
        $sql_create = "CREATE TABLE IF NOT EXISTS configuracion (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre_consultorio VARCHAR(255) DEFAULT 'Consultorio Médico',
            email_contacto VARCHAR(255) NULL,
            telefono VARCHAR(50) NULL,
            direccion TEXT NULL,
            duracion_cita INT DEFAULT 30,
            hora_inicio TIME DEFAULT '09:00:00',
            hora_fin TIME DEFAULT '18:00:00',
            especialidad_id INT NULL,
            dias_laborables VARCHAR(20) DEFAULT '1,2,3,4,5',
            intervalo_citas INT DEFAULT 30,
            require_https TINYINT(1) DEFAULT 0,
            modo_mantenimiento TINYINT(1) DEFAULT 0,
            moneda VARCHAR(10) DEFAULT '$',
            zona_horaria VARCHAR(50) DEFAULT 'America/Santo_Domingo',
            formato_fecha VARCHAR(20) DEFAULT 'Y-m-d',
            idioma VARCHAR(10) DEFAULT 'es',
            tema_color VARCHAR(20) DEFAULT 'light',
            mostrar_alertas_stock TINYINT(1) DEFAULT 1,
            notificaciones_email TINYINT(1) DEFAULT 0,
            medico_nombre VARCHAR(255) DEFAULT 'Dr. Médico',
            multi_medico TINYINT(1) DEFAULT 0,
            whatsapp_server VARCHAR(255) DEFAULT 'https://api.whatsapp.com',
            logo LONGBLOB NULL,
            updated_by VARCHAR(50) NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        
        $conn->exec($sql_create);
        echo "✅ Tabla 'configuracion' creada.<br>";
        
        // Insertar registro inicial
        $sql_insert = "INSERT INTO configuracion (id) VALUES (1) ON DUPLICATE KEY UPDATE id=1";
        $conn->exec($sql_insert);
        echo "✅ Registro inicial insertado.<br>";
    }
    
    // 2. Verificar estructura de la tabla
    echo "<h3>2. Verificando estructura de tabla</h3>";
    $stmt = $conn->query("DESCRIBE configuracion");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th></tr>";
    foreach ($columnas as $columna) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($columna['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($columna['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($columna['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($columna['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($columna['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 3. Verificar datos en la tabla
    echo "<h3>3. Verificando datos en la tabla</h3>";
    $stmt = $conn->query("SELECT COUNT(*) as total FROM configuracion");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "- Total de registros: " . $count['total'] . "<br>";
    
    if ($count['total'] == 0) {
        echo "<p style='color: orange;'>⚠️ No hay registros en la tabla. Insertando registro por defecto...</p>";
        
        $sql_insert = "INSERT INTO configuracion (
            id, nombre_consultorio, medico_nombre, duracion_cita, 
            hora_inicio, hora_fin, dias_laborables, intervalo_citas,
            moneda, zona_horaria, formato_fecha, idioma, tema_color,
            mostrar_alertas_stock, multi_medico, whatsapp_server
        ) VALUES (
            1, 'Consultorio Médico', 'Dr. Médico', 30, 
            '09:00:00', '18:00:00', '1,2,3,4,5', 30,
            'RD$', 'America/Santo_Domingo', 'Y-m-d', 'es', 'light',
            1, 0, 'https://api.whatsapp.com'
        )";
        
        $conn->exec($sql_insert);
        echo "✅ Registro por defecto insertado.<br>";
    }
    
    // 4. Mostrar datos actuales
    echo "<h3>4. Datos actuales en la tabla</h3>";
    $stmt = $conn->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Valor</th></tr>";
        foreach ($config as $campo => $valor) {
            if ($campo !== 'logo') { // No mostrar el BLOB del logo
                echo "<tr>";
                echo "<td>" . htmlspecialchars($campo) . "</td>";
                echo "<td>" . htmlspecialchars($valor ?? 'NULL') . "</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
        
        echo "<br><strong>✅ Los datos se están cargando correctamente.</strong>";
    } else {
        echo "<p style='color: red;'>❌ No se pudo cargar el registro con id = 1</p>";
    }
    
    // 5. Probar la consulta específica que usa configuracion.php
    echo "<h3>5. Probando consulta específica de configuracion.php</h3>";
    try {
        $stmt = $conn->query("SELECT * FROM configuracion WHERE id = 1");
        $test_config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($test_config) {
            echo "✅ La consulta funciona correctamente.<br>";
            echo "- nombre_consultorio: " . htmlspecialchars($test_config['nombre_consultorio'] ?? 'NULL') . "<br>";
            echo "- medico_nombre: " . htmlspecialchars($test_config['medico_nombre'] ?? 'NULL') . "<br>";
            echo "- multi_medico: " . ($test_config['multi_medico'] ?? 'NULL') . "<br>";
        } else {
            echo "❌ La consulta no devuelve resultados.<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error en la consulta: " . $e->getMessage() . "<br>";
    }

} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>Instrucciones:</strong></p>";
echo "<ol>";
echo "<li>Si la tabla no existía, ya fue creada con datos por defecto</li>";
echo "<li>Ve a <a href='configuracion.php' target='_blank'>configuracion.php</a> para verificar</li>";
echo "<li>Si aún no carga, revisa los errores de PHP en el navegador</li>";
echo "</ol>";

echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h2, h3 { color: #333; }
    table { margin: 10px 0; }
    th, td { padding: 8px; text-align: left; }
    a { color: #007bff; }
</style>";
?>
