<?php
// Incluir configuración de sesiones ANTES de session_start()
require_once __DIR__ . '/session_config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de la base de datos
define('DB_SERVER', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'consultorio');
define('DB_USER', 'root');
define('DB_PASS', '820416Dts');
define('BASE_URL', (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . 
    (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost') . '/');

// Validar configuración
if (!defined('DB_SERVER') || !defined('DB_PORT') || !defined('DB_NAME') || !defined('DB_USER')) {
    die("Error: Faltan parámetros de configuración necesarios.");
}

try {
    // Encode special characters in password
    $encoded_pass = urlencode(DB_PASS);
    
    $dsn = "mysql:host=" . DB_SERVER . ";port=" . DB_PORT . ";charset=utf8";
    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_TIMEOUT => 15,
        PDO::ATTR_PERSISTENT => false,
        PDO::MYSQL_ATTR_COMPRESS => true
        // Removed unsupported MYSQL_ATTR_MAX_BUFFER_SIZE
    );
    
    // Retry connection up to 3 times
    $max_retries = 3;
    while($max_retries-- > 0) {
        try {
            $pdo = new PDO($dsn, DB_USER, $encoded_pass, $options);
            break;
        } catch(PDOException $e) {
            if($max_retries === 0) throw $e;
            usleep(500000); // Wait 0.5 seconds before retry
        }
    }
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    
    // Connect to the specific database
    $dsn = "mysql:host=" . DB_SERVER . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    $conn = $pdo; // Mantener compatibilidad
} catch(PDOException $e) {
    die("Error en la conexión: " . $e->getMessage() . 
        "<br>Código de error: " . $e->getCode() . 
        "<br>Trace: " . $e->getTraceAsString() . 
        "<br>Intentando conectar a: " . $dsn . 
        "<br><br>Verifique que el servidor MySQL esté en ejecución y accesible.");
}

define('PERM_MANAGE_TURNOS', 'gestion_turnos');
define('PERM_MANAGE_CITAS', 'gestion_citas');
?>