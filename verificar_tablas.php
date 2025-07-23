<?php
require_once 'config.php';

try {
    // Verificar si existe la base de datos
    $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'consultorio'");
    if ($result->fetch()) {
        echo "Base de datos 'consultorio' existe.\n\n";
        
        // Obtener lista de tablas
        $result = $conn->query("SHOW TABLES");
        $tables = $result->fetchAll(PDO::FETCH_COLUMN);
        
        echo "Tablas encontradas:\n";
        foreach ($tables as $table) {
            echo "- $table\n";
            
            // Mostrar estructura de la tabla
            $result = $conn->query("DESCRIBE $table");
            $columns = $result->fetchAll(PDO::FETCH_ASSOC);
            foreach ($columns as $column) {
                echo "  * {$column['Field']} ({$column['Type']})\n";
            }
            echo "\n";
        }
    } else {
        echo "La base de datos 'consultorio' no existe.\n";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
