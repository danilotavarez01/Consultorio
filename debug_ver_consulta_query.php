<?php
// Test directo para verificar la consulta en ver_consulta.php
session_start();
require_once "config.php";

echo "<h2>🔍 Debug de Consulta en ver_consulta.php</h2>";

// Simular la misma consulta que usa ver_consulta.php
if (isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    // Usar un ID de prueba
    $id = 31; // Sabemos que este ID tiene dientes seleccionados
}

echo "<p><strong>Consultando ID:</strong> $id</p>";

try {
    // Primera prueba: la consulta exacta que usa ver_consulta.php
    echo "<h3>1️⃣ Consulta PDO (como en ver_consulta.php):</h3>";
    $sql = "SELECT h.*, p.nombre, p.apellido, p.dni, p.id as paciente_id 
            FROM historial_medico h 
            JOIN pacientes p ON h.paciente_id = p.id 
            WHERE h.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $consulta_pdo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($consulta_pdo) {
        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
        echo "<p>✅ Consulta PDO exitosa</p>";
        echo "<p><strong>Campos encontrados:</strong> " . count($consulta_pdo) . "</p>";
        echo "<p><strong>Nombre:</strong> " . ($consulta_pdo['nombre'] ?? 'NO ENCONTRADO') . "</p>";
        echo "<p><strong>Dientes seleccionados:</strong> '" . ($consulta_pdo['dientes_seleccionados'] ?? 'NO ENCONTRADO') . "'</p>";
        echo "<p><strong>Dientes vacío?:</strong> " . (empty($consulta_pdo['dientes_seleccionados']) ? 'SÍ' : 'NO') . "</p>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ No se encontró la consulta con PDO</p>";
    }
    
    // Segunda prueba: consulta directa con MySQLi
    echo "<h3>2️⃣ Consulta MySQLi (verificación):</h3>";
    $sql_mysqli = "SELECT h.*, p.nombre, p.apellido, p.dni, p.id as paciente_id 
                   FROM historial_medico h 
                   JOIN pacientes p ON h.paciente_id = p.id 
                   WHERE h.id = $id";
    $result = $mysqli->query($sql_mysqli);
    
    if ($result && $consulta_mysqli = $result->fetch_assoc()) {
        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
        echo "<p>✅ Consulta MySQLi exitosa</p>";
        echo "<p><strong>Campos encontrados:</strong> " . count($consulta_mysqli) . "</p>";
        echo "<p><strong>Nombre:</strong> " . ($consulta_mysqli['nombre'] ?? 'NO ENCONTRADO') . "</p>";
        echo "<p><strong>Dientes seleccionados:</strong> '" . ($consulta_mysqli['dientes_seleccionados'] ?? 'NO ENCONTRADO') . "'</p>";
        echo "<p><strong>Dientes vacío?:</strong> " . (empty($consulta_mysqli['dientes_seleccionados']) ? 'SÍ' : 'NO') . "</p>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ No se encontró la consulta con MySQLi</p>";
    }
    
    // Tercera prueba: verificar estructura de la tabla
    echo "<h3>3️⃣ Estructura de tabla historial_medico:</h3>";
    $result = $mysqli->query("DESCRIBE historial_medico");
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
        while ($row = $result->fetch_assoc()) {
            $highlight = ($row['Field'] == 'dientes_seleccionados') ? "style='background: yellow;'" : "";
            echo "<tr $highlight>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Cuarta prueba: consulta solo del campo dientes_seleccionados
    echo "<h3>4️⃣ Consulta específica del campo dientes_seleccionados:</h3>";
    $result = $mysqli->query("SELECT id, dientes_seleccionados FROM historial_medico WHERE id = $id");
    if ($result && $row = $result->fetch_assoc()) {
        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
        echo "<p>✅ Campo específico encontrado</p>";
        echo "<p><strong>ID:</strong> " . $row['id'] . "</p>";
        echo "<p><strong>Dientes seleccionados:</strong> '" . $row['dientes_seleccionados'] . "'</p>";
        echo "<p><strong>Longitud:</strong> " . strlen($row['dientes_seleccionados']) . " caracteres</p>";
        echo "<p><strong>Es NULL?:</strong> " . (is_null($row['dientes_seleccionados']) ? 'SÍ' : 'NO') . "</p>";
        echo "<p><strong>Está vacío?:</strong> " . (empty($row['dientes_seleccionados']) ? 'SÍ' : 'NO') . "</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='ver_consulta.php?id=$id'>🔗 Ver consulta original</a></p>";
echo "<p><a href='?id=30'>🔗 Probar con ID 30</a> | <a href='?id=31'>🔗 Probar con ID 31</a></p>";
?>
