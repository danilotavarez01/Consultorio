<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'consultorio';
$username = 'root';
$password = '820416Dts';

echo "Intentando conectar a MySQL...<br>";

try {
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "¡Conexión exitosa a MySQL!<br>";
    
    // Intentar crear la base de datos
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    $conn->exec($sql);
    echo "Base de datos '$dbname' creada o ya existente.<br>";
    
    // Conectar a la base de datos específica
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    echo "Conectado a la base de datos '$dbname'<br>";
    
    // Mostrar las tablas existentes
    $result = $conn->query("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<br>Tablas existentes:<br>";
    foreach($tables as $table) {
        echo "- $table<br>";
    }
    
} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
echo "Test de acceso al servidor web";
?>
