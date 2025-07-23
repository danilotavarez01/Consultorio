<?php
require_once "config.php";

echo "<h1>🔍 VERIFICACIÓN DE TABLAS EN LA BASE DE DATOS</h1>";
echo "<p>Conectando a la base de datos: <strong>" . DB_NAME . "</strong></p>";

try {
    // Obtener todas las tablas de la base de datos
    echo "<h2>📋 TABLAS EXISTENTES:</h2>";
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<p style='color: red;'>⚠️ No se encontraron tablas en la base de datos.</p>";
    } else {
        echo "<ol>";
        foreach ($tables as $table) {
            echo "<li><strong>$table</strong></li>";
        }
        echo "</ol>";
        echo "<p><strong>Total de tablas encontradas: " . count($tables) . "</strong></p>";
    }
    
    echo "<hr>";
    echo "<h2>🔧 ESTRUCTURA DETALLADA DE CADA TABLA:</h2>";
    
    foreach ($tables as $table) {
        echo "<h3>📄 Tabla: <code>$table</code></h3>";
        
        // Mostrar estructura de la tabla
        $stmt = $conn->query("DESCRIBE `$table`");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th>";
        echo "</tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td><strong>" . $column['Field'] . "</strong></td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $column['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Mostrar CREATE TABLE statement
        echo "<details>";
        echo "<summary>Ver CREATE TABLE completo</summary>";
        echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto;'>";
        $stmt = $conn->query("SHOW CREATE TABLE `$table`");
        $create_table = $stmt->fetch(PDO::FETCH_ASSOC);
        echo htmlspecialchars($create_table['Create Table']);
        echo "</pre>";
        echo "</details>";
        
        echo "<hr style='margin: 10px 0;'>";
    }
    
    echo "<h2>📊 RESUMEN DE VERIFICACIÓN:</h2>";
    
    // Tablas que deberían existir según el sistema
    $expected_tables = [
        'usuarios',
        'especialidades', 
        'especialidad_campos',
        'pacientes',
        'historial_medico',
        'consulta_campos_valores',
        'enfermedades',
        'paciente_enfermedades', 
        'citas',
        'configuracion',
        'permisos',
        'usuario_permisos',
        'whatsapp_config'
    ];
    
    echo "<h3>✅ Tablas esperadas vs encontradas:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Tabla Esperada</th><th>Estado</th><th>Comentario</th>";
    echo "</tr>";
    
    foreach ($expected_tables as $expected) {
        $exists = in_array($expected, $tables);
        $status = $exists ? "✅ EXISTE" : "❌ FALTANTE";
        $comment = $exists ? "OK" : "Necesita ser creada";
        
        echo "<tr>";
        echo "<td><strong>$expected</strong></td>";
        echo "<td style='color: " . ($exists ? 'green' : 'red') . ";'>$status</td>";
        echo "<td>$comment</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Tablas adicionales no esperadas
    $extra_tables = array_diff($tables, $expected_tables);
    if (!empty($extra_tables)) {
        echo "<h3>📋 Tablas adicionales encontradas:</h3>";
        echo "<ul>";
        foreach ($extra_tables as $extra) {
            echo "<li><strong>$extra</strong> - Tabla adicional no incluida en el script estándar</li>";
        }
        echo "</ul>";
    }
    
    echo "<h2>🎯 RECOMENDACIONES:</h2>";
    $missing_tables = array_diff($expected_tables, $tables);
    if (!empty($missing_tables)) {
        echo "<div style='background: #ffeeee; padding: 15px; border: 1px solid #ff0000;'>";
        echo "<h3>⚠️ Tablas faltantes que deben agregarse al script SQL:</h3>";
        echo "<ul>";
        foreach ($missing_tables as $missing) {
            echo "<li><strong>$missing</strong></li>";
        }
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='background: #eeffee; padding: 15px; border: 1px solid #00ff00;'>";
        echo "<h3>✅ Todas las tablas esperadas están presentes</h3>";
        echo "</div>";
    }
    
    if (!empty($extra_tables)) {
        echo "<div style='background: #ffffee; padding: 15px; border: 1px solid #ffaa00;'>";
        echo "<h3>📋 Tablas adicionales encontradas que podrían agregarse al script:</h3>";
        echo "<ul>";
        foreach ($extra_tables as $extra) {
            echo "<li><strong>$extra</strong></li>";
        }
        echo "</ul>";
        echo "</div>";
    }

} catch (PDOException $e) {
    echo "<div style='background: #ffeeee; padding: 15px; border: 1px solid #ff0000;'>";
    echo "<h3>❌ Error de conexión a la base de datos:</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Código:</strong> " . $e->getCode() . "</p>";
    echo "</div>";
}
?>
