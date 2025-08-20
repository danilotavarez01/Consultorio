<?php
require_once 'config.php';

try {
    // Verificar si la tabla seguro_medico existe
    $stmt = $conn->query("SHOW TABLES LIKE 'seguro_medico'");
    
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabla 'seguro_medico' existe.<br><br>";
        
        // Mostrar estructura de la tabla
        echo "<h3>📋 Estructura de la tabla:</h3>";
        $stmt = $conn->query("DESCRIBE seguro_medico");
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background-color: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th></tr>";
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "<td>{$row['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table><br>";
        
        // Mostrar datos en la tabla
        $stmt = $conn->query("SELECT * FROM seguro_medico ORDER BY id");
        $seguros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>📊 Datos en la tabla (" . count($seguros) . " registros):</h3>";
        
        if (count($seguros) > 0) {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr style='background-color: #f0f0f0;'><th>ID</th><th>Descripción</th><th>Activo</th><th>Fecha Creación</th></tr>";
            
            foreach ($seguros as $seguro) {
                $activo = $seguro['activo'] ? '✅ Sí' : '❌ No';
                echo "<tr>";
                echo "<td>{$seguro['id']}</td>";
                echo "<td>{$seguro['descripcion']}</td>";
                echo "<td>$activo</td>";
                echo "<td>{$seguro['fecha_creacion']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>⚠️ La tabla está vacía. No hay seguros médicos registrados.</p>";
        }
        
        // Verificar relación con pacientes
        echo "<br><h3>🔗 Verificación de relación con pacientes:</h3>";
        
        $stmt = $conn->query("SHOW COLUMNS FROM pacientes LIKE 'seguro_medico%'");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($columns) > 0) {
            echo "✅ Columnas relacionadas encontradas en tabla 'pacientes':<br>";
            foreach ($columns as $column) {
                echo "- {$column['Field']} ({$column['Type']})<br>";
            }
        } else {
            echo "⚠️ No se encontraron columnas relacionadas en tabla 'pacientes'.<br>";
            echo "💡 Es necesario ejecutar el script de migración completo.";
        }
        
    } else {
        echo "❌ La tabla 'seguro_medico' NO existe.<br>";
        echo "💡 Es necesario ejecutar el script de creación: <a href='create_seguro_medico_table.php'>create_seguro_medico_table.php</a>";
    }
    
    echo "<br><br>";
    echo "<hr>";
    echo "<p><strong>🔧 Acciones disponibles:</strong></p>";
    echo "<a href='create_seguro_medico_table.php' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px; margin: 5px;'>🔄 Ejecutar Script de Creación</a>";
    echo "<a href='seguro_medico.php' style='background: #28a745; color: white; padding: 10px; text-decoration: none; border-radius: 5px; margin: 5px;'>📋 Ir al Módulo</a>";
    echo "<a href='add_seguros_permission.php' style='background: #ffc107; color: black; padding: 10px; text-decoration: none; border-radius: 5px; margin: 5px;'>🔑 Configurar Permisos</a>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
