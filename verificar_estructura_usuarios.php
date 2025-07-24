<?php
// Script para verificar la estructura de la tabla usuarios
require_once 'session_config.php';
session_start();
require_once "config.php";

echo "<h3>🔍 Verificar Estructura de Tabla Usuarios</h3>";

try {
    // 1. Mostrar estructura de la tabla usuarios
    echo "<h4>📋 Estructura de la tabla 'usuarios':</h4>";
    $stmt = $conn->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td style='padding: 5px;'>" . $column['Field'] . "</td>";
        echo "<td style='padding: 5px;'>" . $column['Type'] . "</td>";
        echo "<td style='padding: 5px;'>" . $column['Null'] . "</td>";
        echo "<td style='padding: 5px;'>" . $column['Key'] . "</td>";
        echo "<td style='padding: 5px;'>" . ($column['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 2. Verificar si existe la columna apellido
    $hasApellido = false;
    $hasNombre = false;
    $hasUsername = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'apellido') $hasApellido = true;
        if ($column['Field'] === 'nombre') $hasNombre = true;
        if ($column['Field'] === 'username') $hasUsername = true;
    }
    
    echo "<h4>✅ Verificación de Columnas:</h4>";
    echo "<ul>";
    echo "<li><strong>Columna 'nombre':</strong> " . ($hasNombre ? "✅ Existe" : "❌ No existe") . "</li>";
    echo "<li><strong>Columna 'apellido':</strong> " . ($hasApellido ? "✅ Existe" : "❌ No existe") . "</li>";
    echo "<li><strong>Columna 'username':</strong> " . ($hasUsername ? "✅ Existe" : "❌ No existe") . "</li>";
    echo "</ul>";
    
    // 3. Mostrar algunos registros de ejemplo
    echo "<h4>📊 Registros de Ejemplo:</h4>";
    $stmt = $conn->query("SELECT * FROM usuarios LIMIT 5");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($usuarios) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; overflow-x: auto;'>";
        // Header dinámico basado en las columnas reales
        echo "<tr style='background: #f8f9fa;'>";
        foreach (array_keys($usuarios[0]) as $columnName) {
            echo "<th style='padding: 5px;'>" . htmlspecialchars($columnName) . "</th>";
        }
        echo "</tr>";
        
        // Datos
        foreach ($usuarios as $usuario) {
            echo "<tr>";
            foreach ($usuario as $value) {
                echo "<td style='padding: 5px;'>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>No hay usuarios en la tabla.</p>";
    }
    
    // 4. Proponer consulta corregida
    echo "<h4>🔧 Consulta Sugerida para el Historial:</h4>";
    
    if ($hasNombre && $hasApellido) {
        $consultaSugerida = "SELECT hm.*, u.nombre as medico_nombre, u.apellido as medico_apellido 
        FROM historial_medico hm 
        LEFT JOIN usuarios u ON hm.doctor_id = u.id 
        WHERE hm.paciente_id = ? 
        ORDER BY hm.fecha DESC";
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<p><strong>✅ Consulta correcta (nombre y apellido disponibles):</strong></p>";
    } elseif ($hasNombre && !$hasApellido) {
        $consultaSugerida = "SELECT hm.*, u.nombre as medico_nombre 
        FROM historial_medico hm 
        LEFT JOIN usuarios u ON hm.doctor_id = u.id 
        WHERE hm.paciente_id = ? 
        ORDER BY hm.fecha DESC";
        echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px;'>";
        echo "<p><strong>⚠️ Consulta alternativa (solo nombre disponible):</strong></p>";
    } elseif ($hasUsername && !$hasNombre) {
        $consultaSugerida = "SELECT hm.*, u.username as medico_nombre 
        FROM historial_medico hm 
        LEFT JOIN usuarios u ON hm.doctor_id = u.id 
        WHERE hm.paciente_id = ? 
        ORDER BY hm.fecha DESC";
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<p><strong>❌ Consulta de emergencia (usando username):</strong></p>";
    } else {
        $consultaSugerida = "SELECT hm.*, CONCAT('Usuario ID: ', hm.doctor_id) as medico_nombre 
        FROM historial_medico hm 
        WHERE hm.paciente_id = ? 
        ORDER BY hm.fecha DESC";
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<p><strong>❌ Consulta básica (sin JOIN, solo ID):</strong></p>";
    }
    
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto;'>";
    echo htmlspecialchars($consultaSugerida);
    echo "</pre>";
    echo "</div>";
    
    // 5. Código PHP sugerido para mostrar el médico
    echo "<h4>💻 Código PHP Sugerido:</h4>";
    
    if ($hasNombre && $hasApellido) {
        $codigoPhp = "<?php 
if (!empty(\$registro['medico_nombre']) || !empty(\$registro['medico_apellido'])) {
    echo htmlspecialchars(trim(\$registro['medico_nombre'] . ' ' . \$registro['medico_apellido']));
} else {
    echo 'No especificado';
}
?>";
    } elseif ($hasNombre && !$hasApellido) {
        $codigoPhp = "<?php 
if (!empty(\$registro['medico_nombre'])) {
    echo htmlspecialchars(\$registro['medico_nombre']);
} else {
    echo 'No especificado';
}
?>";
    } elseif ($hasUsername && !$hasNombre) {
        $codigoPhp = "<?php 
if (!empty(\$registro['medico_nombre'])) {
    echo htmlspecialchars(\$registro['medico_nombre']); // username
} else {
    echo 'No especificado';
}
?>";
    } else {
        $codigoPhp = "<?php 
echo \$registro['medico_nombre'] ?? 'No especificado';
?>";
    }
    
    echo "<pre style='background: #e2e3e5; padding: 10px; border-radius: 3px; overflow-x: auto;'>";
    echo htmlspecialchars($codigoPhp);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h5>❌ Error:</h5>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
