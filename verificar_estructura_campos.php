<?php
require_once "config.php";

echo "<h2>Verificación de Estructura de Tabla especialidad_campos</h2>";

try {
    // Verificar estructura de la tabla
    $stmt = $conn->prepare("DESCRIBE especialidad_campos");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Columnas de la tabla especialidad_campos:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $tiene_estado = false;
    foreach ($columnas as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
        
        if ($col['Field'] == 'estado') {
            $tiene_estado = true;
        }
    }
    echo "</table>";
    
    if (!$tiene_estado) {
        echo "<p style='color: orange;'><strong>IMPORTANTE:</strong> La tabla no tiene columna 'estado'. El endpoint está buscando estado = 'activo' que no existe.</p>";
    }
    
    // Mostrar todos los datos sin filtro de estado
    echo "<h3>Datos en la tabla (sin filtro de estado):</h3>";
    $stmt = $conn->prepare("SELECT * FROM especialidad_campos ORDER BY especialidad_id, orden");
    $stmt->execute();
    $todos_campos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($todos_campos) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr>";
        foreach ($columnas as $col) {
            echo "<th>{$col['Field']}</th>";
        }
        echo "</tr>";
        
        foreach ($todos_campos as $campo) {
            echo "<tr>";
            foreach ($columnas as $col) {
                echo "<td>" . ($campo[$col['Field']] ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>No hay datos en la tabla especialidad_campos</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
