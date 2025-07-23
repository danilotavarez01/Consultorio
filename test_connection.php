<?php
// Cargar configuración
if (!file_exists('config.php')) {
    die("Error: El archivo config.php no existe.");
}
require_once 'config.php';

try {
    // Verificar si MySQL está ejecutándose
    $socket = @fsockopen(DB_SERVER, DB_PORT, $errno, $errstr, 5);
    if (!$socket) {
        die("Error: No se puede conectar al puerto MySQL (" . DB_PORT . "). Verifica que MySQL esté ejecutándose.\n" .
            "Error específico: " . $errno . " - " . $errstr . "\n" .
            "Detalles de conexión intentada:\n" .
            "Host: " . DB_SERVER . "\n" .
            "Puerto: " . DB_PORT . "\n" .
            "Usuario: " . DB_USER . "\n" .
            "Base de datos: " . DB_NAME . "\n" .
            "Sugerencias:\n" .
            "- Verifica que el servicio MySQL esté activo\n" .
            "- Comprueba que el puerto " . DB_PORT . " esté abierto\n" .
            "- Revisa que el host " . DB_SERVER . " sea accesible");
    }
    fclose($socket);

    // Crear el DSN antes para poder mostrarlo en caso de error
    $dsn = "mysql:host=" . DB_SERVER . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    
    // Intentar la conexión PDO con un timeout
    $options = array(
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    );
    
    $conn = new PDO($dsn, DB_USER, DB_PASS, $options);

    // Intentar una consulta simple
    $stmt = $conn->query('SELECT NOW() as time');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "¡Conexión exitosa a la base de datos!" . PHP_EOL;
    echo "Hora del servidor: " . $result['time'] . PHP_EOL;
    
    // Mostrar información de la base de datos
    echo "Nombre de la base de datos: " . DB_NAME . PHP_EOL;
    echo "Servidor: " . DB_SERVER . ":" . DB_PORT . PHP_EOL;
    
    // Verificar las tablas existentes
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tablas encontradas: " . PHP_EOL;
    if (empty($tables)) {
        echo "- No hay tablas en la base de datos." . PHP_EOL;
    } else {
        foreach ($tables as $table) {
            echo "- " . $table . PHP_EOL;
        }
    }
    
} catch(PDOException $e) {
    echo "Error en la conexión a la base de datos:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "\nDetalles de conexión intentada:\n";
    echo "DSN: " . $dsn . "\n";
    echo "Usuario: " . DB_USER . "\n";
    echo "\nSugerencias:\n";
    echo "- Verifica que las credenciales sean correctas\n";
    echo "- Comprueba que la base de datos '" . DB_NAME . "' exista\n";
    echo "- Asegúrate que el usuario tenga permisos suficientes\n";
    die();
}
?>
