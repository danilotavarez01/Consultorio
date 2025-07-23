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
    <title>Importar Estructura de Base de Datos</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='container mt-4'>";

echo "<h2>Importar Estructura desde database_structure.sql</h2>";

// Verificar que las constantes estén definidas (redundante pero para seguridad)
if (!defined('DB_SERVER') || !defined('DB_PORT') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
    echo "<div class='alert alert-danger'>";
    echo "<h5>❌ Error de Configuración</h5>";
    echo "<p>Error crítico: Las constantes de base de datos desaparecieron después de incluir config.php</p>";
    echo "</div>";
    echo "</body></html>";
    exit;
}

try {
    // Verificar conexión
    if (!isset($pdo) || !$pdo) {
        throw new Exception("No se pudo establecer conexión con la base de datos");
    }
    
    $stmt = $pdo->query("SELECT DATABASE() as db_name");
    $db_info = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<div class='alert alert-info'>Conectado a: <strong>" . $db_info['db_name'] . "</strong></div>";
    
    // Verificar si el archivo SQL existe
    $sql_file = __DIR__ . '/database_structure.sql';
    if (!file_exists($sql_file)) {
        throw new Exception("No se encontró el archivo database_structure.sql en " . $sql_file);
    }
    
    echo "<div class='alert alert-info'>Archivo SQL encontrado: " . $sql_file . "</div>";
    
    // Leer el archivo SQL
    $sql_content = file_get_contents($sql_file);
    if (!$sql_content) {
        throw new Exception("No se pudo leer el contenido del archivo SQL");
    }
    
    // Separar las sentencias SQL
    $sql_statements = array_filter(
        array_map('trim', explode(';', $sql_content)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt) && !preg_match('/^\/\*/', $stmt);
        }
    );
    
    echo "<div class='alert alert-info'>Encontradas " . count($sql_statements) . " sentencias SQL para ejecutar</div>";
    
    // Ejecutar las sentencias
    $executed = 0;
    $errors = 0;
    
    echo "<div class='card'>";
    echo "<div class='card-header'><h5>Progreso de Ejecución</h5></div>";
    echo "<div class='card-body'>";
    
    foreach ($sql_statements as $i => $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        try {
            // Mostrar solo el inicio de la sentencia para no saturar la pantalla
            $display_stmt = strlen($statement) > 50 ? substr($statement, 0, 50) . '...' : $statement;
            echo "<small class='text-muted'>Ejecutando: " . htmlspecialchars($display_stmt) . "</small><br>";
            
            $pdo->exec($statement);
            $executed++;
            
        } catch (PDOException $e) {
            $errors++;
            // Solo mostrar errores que no sean "tabla ya existe" o similares
            if (!preg_match('/already exists|doesn\'t exist/', $e->getMessage())) {
                echo "<div class='alert alert-warning'>Error en sentencia " . ($i + 1) . ": " . $e->getMessage() . "</div>";
            }
        }
    }
    
    echo "</div></div>";
    
    echo "<div class='alert alert-success'>";
    echo "<h5>✅ Importación Completada</h5>";
    echo "<p>Sentencias ejecutadas: <strong>$executed</strong></p>";
    if ($errors > 0) {
        echo "<p>Errores menores: <strong>$errors</strong> (principalmente tablas que ya existían)</p>";
    }
    echo "</div>";
    
    // Verificar el resultado
    echo "<h4>Verificación del Resultado</h4>";
    
    // Listar tablas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<div class='alert alert-info'>Tablas en la base de datos (" . count($tables) . "): " . implode(', ', $tables) . "</div>";
    
    // Verificar específicamente la tabla procedimientos
    if (in_array('procedimientos', $tables)) {
        echo "<div class='alert alert-success'>✅ Tabla 'procedimientos' creada correctamente</div>";
        
        // Mostrar estructura
        $stmt = $pdo->query("DESCRIBE procedimientos");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table class='table table-sm table-bordered'>";
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
        
    } else {
        echo "<div class='alert alert-warning'>⚠️ La tabla 'procedimientos' no se creó. Puede intentar crearla manualmente.</div>";
    }
    
    echo "<hr>";
    echo "<div class='d-flex gap-2'>";
    echo "<a href='setup_procedimientos.php' class='btn btn-primary'>Configurar Permisos</a>";
    echo "<a href='initial_data_import.php' class='btn btn-info'>Importar Datos Iniciales</a>";
    echo "<a href='procedimientos.php' class='btn btn-success'>Ir a Gestión de Procedimientos</a>";
    echo "<a href='index.php' class='btn btn-secondary'>Volver al Inicio</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h5>❌ Error</h5>";
    echo "<strong>Mensaje:</strong> " . $e->getMessage() . "<br>";
    echo "</div>";
    
    echo "<div class='d-flex gap-2'>";
    echo "<a href='diagnostico_db.php' class='btn btn-warning'>Diagnóstico de DB</a>";
    echo "<a href='index.php' class='btn btn-secondary'>Volver al Inicio</a>";
    echo "</div>";
}

echo "</body></html>";
?>
