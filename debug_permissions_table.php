<?php
require_once 'config.php';

try {
    echo "Verificando estructura de receptionist_permissions...\n";
    $result = $conn->query("DESCRIBE receptionist_permissions");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']}: {$row['Type']} (Null: {$row['Null']}, Key: {$row['Key']})\n";
    }

    echo "\nVerificando usuarios existentes...\n";
    $result = $conn->query("SELECT id, nombre, rol FROM usuarios LIMIT 5");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "- ID: {$row['id']}, Nombre: {$row['nombre']}, Rol: {$row['rol']}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
