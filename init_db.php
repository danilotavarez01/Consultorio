<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

try {
    // Leer el contenido del archivo SQL
    $sql = file_get_contents('database.sql');
    
    // Dividir el archivo en consultas individuales
    $queries = explode(';', $sql);
    
    // Ejecutar cada consulta
    foreach($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            try {
                $conn->exec($query);
                echo "Consulta ejecutada con éxito: " . substr($query, 0, 50) . "...<br>\n";
            } catch (PDOException $e) {
                // Si la tabla ya existe, ignorar el error
                if ($e->getCode() != '42S01') {
                    echo "Error en consulta: " . $e->getMessage() . "<br>\n";
                }
            }
        }
    }
    
    echo "<br>Verificando tablas creadas:<br>\n";
    $result = $conn->query("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    
    foreach($tables as $table) {
        echo "- $table<br>\n";
        // Mostrar estructura de cada tabla
        $result = $conn->query("DESCRIBE $table");
        $columns = $result->fetchAll(PDO::FETCH_ASSOC);
        foreach($columns as $column) {
            echo "&nbsp;&nbsp;&nbsp;* {$column['Field']} ({$column['Type']})<br>\n";
        }
        echo "<br>\n";
    }
    
    echo "<br>Proceso completado.<br>\n";
    
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
