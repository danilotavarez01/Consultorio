<?php
// Activar reportes de errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir configuración
$config_path = __DIR__ . '/config.php';
if (!file_exists($config_path)) {
    die("Error: No se encuentra el archivo config.php en: " . $config_path);
}

require_once $config_path;

// Verificar inmediatamente que las constantes estén definidas
$required_constants = ['DB_SERVER', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'];
$missing_constants = [];

foreach ($required_constants as $constant) {
    if (!defined($constant)) {
        $missing_constants[] = $constant;
    }
}

if (!empty($missing_constants)) {
    die("Error: Las siguientes constantes no están definidas: " . implode(', ', $missing_constants));
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico de Base de Datos</title>
    <link href='assets/css/bootstrap-5.1.3.min.css' rel='stylesheet'>
</head>
<body class='container mt-4'>";

echo "<h2>Diagnóstico de Conexión a Base de Datos</h2>";

// Mostrar información de configuración (sin password)
echo "<div class='card mb-4'>";
echo "<div class='card-header'><h5>Configuración</h5></div>";
echo "<div class='card-body'>";

echo "<strong>Configuración de conexión:</strong><br>";
echo "Servidor: " . (defined('DB_SERVER') ? DB_SERVER : 'NO DEFINIDO') . "<br>";
echo "Puerto: " . (defined('DB_PORT') ? DB_PORT : 'NO DEFINIDO') . "<br>";
echo "Base de datos: " . (defined('DB_NAME') ? DB_NAME : 'NO DEFINIDO') . "<br>";
echo "Usuario: " . (defined('DB_USER') ? DB_USER : 'NO DEFINIDO') . "<br>";
echo "Password: " . (defined('DB_PASS') ? '****** (configurado)' : 'NO DEFINIDO') . "<br>";

echo "</div></div>";

// Verificar que las constantes estén definidas (redundante pero para seguridad)
if (!defined('DB_SERVER') || !defined('DB_PORT') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
    echo "<div class='alert alert-danger'>";
    echo "<h5>❌ Error de Configuración</h5>";
    echo "<p>Error crítico: Las constantes de base de datos desaparecieron después de incluir config.php</p>";
    echo "<p>Esto puede indicar un problema con el archivo config.php</p>";
    echo "</div>";
    echo "</body></html>";
    exit;
}

try {
    // Intentar conexión paso a paso
    echo "<div class='alert alert-info'>🔄 Intentando conexión paso a paso...</div>";
    
    // Paso 1: Conexión sin base de datos específica
    echo "<strong>Paso 1: Conexión al servidor MySQL</strong><br>";
    $dsn1 = "mysql:host=" . DB_SERVER . ";port=" . DB_PORT . ";charset=utf8";
    $pdo1 = new PDO($dsn1, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
    ]);
    echo "<div class='alert alert-success'>✅ Conexión al servidor MySQL exitosa</div>";
    
    // Paso 2: Listar bases de datos
    echo "<strong>Paso 2: Verificando bases de datos disponibles</strong><br>";
    $stmt = $pdo1->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<div class='alert alert-info'>Bases de datos encontradas: " . implode(', ', $databases) . "</div>";
    
    // Paso 3: Verificar si existe la base de datos
    if (in_array(DB_NAME, $databases)) {
        echo "<div class='alert alert-success'>✅ La base de datos '" . DB_NAME . "' existe</div>";
    } else {
        echo "<div class='alert alert-warning'>⚠️ La base de datos '" . DB_NAME . "' no existe. Creando...</div>";
        $pdo1->exec("CREATE DATABASE " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        echo "<div class='alert alert-success'>✅ Base de datos '" . DB_NAME . "' creada exitosamente</div>";
    }
    
    // Paso 4: Conexión a la base de datos específica
    echo "<strong>Paso 4: Conexión a la base de datos específica</strong><br>";
    $dsn2 = "mysql:host=" . DB_SERVER . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8";
    $pdo2 = new PDO($dsn2, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
    ]);
    echo "<div class='alert alert-success'>✅ Conexión a la base de datos '" . DB_NAME . "' exitosa</div>";
    
    // Paso 5: Verificar base de datos actual
    $stmt = $pdo2->query("SELECT DATABASE() as current_db");
    $current_db = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<div class='alert alert-info'>Base de datos actual: <strong>" . $current_db['current_db'] . "</strong></div>";
    
    // Paso 6: Listar tablas
    echo "<strong>Paso 6: Verificando tablas existentes</strong><br>";
    $stmt = $pdo2->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<div class='alert alert-warning'>⚠️ No hay tablas en la base de datos</div>";
    } else {
        echo "<div class='alert alert-info'>Tablas encontradas (" . count($tables) . "): " . implode(', ', $tables) . "</div>";
        
        // Verificar específicamente la tabla procedimientos
        if (in_array('procedimientos', $tables)) {
            echo "<div class='alert alert-success'>✅ La tabla 'procedimientos' existe</div>";
            
            // Mostrar estructura de la tabla
            $stmt = $pdo2->query("DESCRIBE procedimientos");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<strong>Estructura de la tabla 'procedimientos':</strong><br>";
            echo "<table class='table table-sm table-bordered mt-2'>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Key</th><th>Default</th></tr>";
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td>" . $col['Field'] . "</td>";
                echo "<td>" . $col['Type'] . "</td>";
                echo "<td>" . $col['Null'] . "</td>";
                echo "<td>" . $col['Key'] . "</td>";
                echo "<td>" . $col['Default'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Contar registros
            $stmt = $pdo2->query("SELECT COUNT(*) as total FROM procedimientos");
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<div class='alert alert-info'>Registros en la tabla: <strong>" . $count['total'] . "</strong></div>";
            
        } else {
            echo "<div class='alert alert-warning'>⚠️ La tabla 'procedimientos' no existe</div>";
        }
    }
    
    echo "<hr>";
    echo "<div class='alert alert-success'>";
    echo "<h5>✅ Diagnóstico Completado Exitosamente</h5>";
    echo "<p>La conexión a la base de datos está funcionando correctamente.</p>";
    echo "</div>";
    
    echo "<div class='d-flex gap-2'>";
    if (!in_array('procedimientos', $tables ?? [])) {
        echo "<a href='verificar_procedimientos.php' class='btn btn-primary'>Crear Tabla Procedimientos</a>";
    } else {
        echo "<a href='procedimientos.php' class='btn btn-primary'>Ir a Gestión de Procedimientos</a>";
    }
    echo "<a href='setup_procedimientos.php' class='btn btn-info'>Configurar Permisos</a>";
    echo "<a href='index.php' class='btn btn-secondary'>Volver al Inicio</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h5>❌ Error de Base de Datos</h5>";
    echo "<strong>Mensaje:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Código:</strong> " . $e->getCode() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
    
    echo "<div class='alert alert-warning'>";
    echo "<h6>Posibles soluciones:</h6>";
    echo "<ul>";
    echo "<li>Verificar que MySQL/MariaDB esté ejecutándose</li>";
    echo "<li>Revisar las credenciales en config.php</li>";
    echo "<li>Verificar que el puerto " . DB_PORT . " esté disponible</li>";
    echo "<li>Revisar permisos del usuario de base de datos</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h5>❌ Error General</h5>";
    echo "<strong>Mensaje:</strong> " . $e->getMessage() . "<br>";
    echo "</div>";
}

echo "</body></html>";
?>

