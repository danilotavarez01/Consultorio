<?php
require_once 'config.php';

echo "Verificando estructura de tablas...\n";

try {
    $stmt = $conn->query('SHOW COLUMNS FROM permisos');
    echo "Columnas de la tabla permisos:\n";
    while ($row = $stmt->fetch()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} catch (PDOException $e) {
    echo "Error al consultar permisos: " . $e->getMessage() . "\n";
}

echo "\n";

try {
    $stmt = $conn->query('SHOW COLUMNS FROM usuario_permisos');
    echo "Columnas de la tabla usuario_permisos:\n";
    while ($row = $stmt->fetch()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} catch (PDOException $e) {
    echo "Error al consultar usuario_permisos: " . $e->getMessage() . "\n";
}
?>
