<?php
// Script para agregar la columna dientes_seleccionados a la tabla historial_medico

require_once "config.php";

try {
    // Verificar si la columna ya existe
    $stmt = $conn->prepare("SHOW COLUMNS FROM historial_medico LIKE 'dientes_seleccionados'");
    $stmt->execute();
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        // La columna no existe, agregarla
        $sql = "ALTER TABLE historial_medico ADD COLUMN dientes_seleccionados TEXT NULL COMMENT 'Dientes seleccionados en el odontograma (formato: 11,12,13)'";
        $conn->exec($sql);
        echo "✓ Columna 'dientes_seleccionados' agregada exitosamente a la tabla historial_medico.<br>";
    } else {
        echo "✓ La columna 'dientes_seleccionados' ya existe en la tabla historial_medico.<br>";
    }
    
    // Verificar la estructura actual de la tabla
    echo "<br><strong>Estructura actual de la tabla historial_medico:</strong><br>";
    $stmt = $conn->query("DESCRIBE historial_medico");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
