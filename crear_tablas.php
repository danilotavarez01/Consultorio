<?php
header('Content-Type: text/plain; charset=utf-8');

$host = 'localhost';
$dbname = 'consultorio';
$username = 'root';
$password = '820416Dts';

echo "Iniciando proceso de creación de base de datos...\n\n";

try {
    // Crear conexión sin base de datos
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear base de datos si no existe
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $conn->exec($sql);
    echo "Base de datos '$dbname' creada o ya existente.\n";
    
    // Seleccionar la base de datos
    $conn->exec("USE $dbname");
    
    // Leer y ejecutar el archivo SQL
    $sql = file_get_contents('database.sql');
    
    // Dividir el archivo en consultas individuales
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    // Ejecutar cada consulta
    foreach($queries as $query) {
        if (!empty($query)) {
            $conn->exec($query);
        }
    }
    
    echo "Todas las tablas han sido creadas exitosamente.\n";
    
    // Verificar las tablas creadas
    $result = $conn->query("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\nTablas creadas:\n";
    foreach($tables as $table) {
        echo "- $table\n";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
