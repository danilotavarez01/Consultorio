<?php
require_once "config.php";

echo "<h3>Estructura de la tabla historial_medico:</h3>";

try {
    $stmt = $conn->query("DESCRIBE historial_medico");
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Campo</th><th>Tipo</th><th>Permite NULL</th><th>Clave</th><th>Default</th><th>Extra</th>";
    echo "</tr>";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $nullStyle = ($row['Null'] == 'NO') ? 'color: red; font-weight: bold;' : '';
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td style='$nullStyle'>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?: 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><p><strong>Campos que NO permiten NULL (obligatorios):</strong></p>";
    $stmt = $conn->query("DESCRIBE historial_medico");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['Null'] == 'NO' && $row['Extra'] != 'auto_increment') {
            echo "- <span style='color: red; font-weight: bold;'>" . htmlspecialchars($row['Field']) . "</span><br>";
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
