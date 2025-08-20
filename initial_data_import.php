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
    <title>Importar Datos Iniciales</title>
    <link href='assets/css/bootstrap-5.1.3.min.css' rel='stylesheet'>
</head>
<body class='container mt-4'>";

echo "<h2>Importar Datos Iniciales</h2>";

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
    
    // Verificar si el archivo de datos iniciales existe
    $data_file = __DIR__ . '/initial_data.sql';
    if (!file_exists($data_file)) {
        throw new Exception("No se encontró el archivo initial_data.sql en " . $data_file);
    }
    
    echo "<div class='alert alert-info'>Archivo de datos encontrado: " . $data_file . "</div>";
    
    // Leer el archivo de datos
    $sql_content = file_get_contents($data_file);
    if (!$sql_content) {
        throw new Exception("No se pudo leer el contenido del archivo de datos");
    }
    
    // Separar las sentencias SQL
    $sql_statements = array_filter(
        array_map('trim', explode(';', $sql_content)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt) && !preg_match('/^\/\*/', $stmt);
        }
    );
    
    echo "<div class='alert alert-info'>Encontradas " . count($sql_statements) . " sentencias de datos para ejecutar</div>";
    
    // Ejecutar las sentencias
    $executed = 0;
    $errors = 0;
    
    echo "<div class='card'>";
    echo "<div class='card-header'><h5>Progreso de Importación</h5></div>";
    echo "<div class='card-body'>";
    
    foreach ($sql_statements as $i => $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        try {
            $display_stmt = strlen($statement) > 60 ? substr($statement, 0, 60) . '...' : $statement;
            echo "<small class='text-muted'>Ejecutando: " . htmlspecialchars($display_stmt) . "</small><br>";
            
            $pdo->exec($statement);
            $executed++;
            
        } catch (PDOException $e) {
            $errors++;
            // Solo mostrar errores importantes
            if (!preg_match('/Duplicate entry|already exists/', $e->getMessage())) {
                echo "<div class='alert alert-warning'>Error: " . $e->getMessage() . "</div>";
            } else {
                echo "<small class='text-warning'>Info: Datos ya existían</small><br>";
            }
        }
    }
    
    echo "</div></div>";
    
    echo "<div class='alert alert-success'>";
    echo "<h5>✅ Importación de Datos Completada</h5>";
    echo "<p>Sentencias ejecutadas: <strong>$executed</strong></p>";
    if ($errors > 0) {
        echo "<p>Advertencias: <strong>$errors</strong> (principalmente datos duplicados)</p>";
    }
    echo "</div>";
    
    // Verificar los datos importados
    echo "<h4>Verificación de Datos Importados</h4>";
    
    // Verificar usuario admin
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM usuarios WHERE username = 'admin'");
    $admin_count = $stmt->fetch()['count'];
    echo "<div class='alert alert-" . ($admin_count > 0 ? 'success' : 'warning') . "'>";
    echo ($admin_count > 0 ? '✅' : '⚠️') . " Usuario administrador: " . ($admin_count > 0 ? 'Creado' : 'No encontrado');
    echo "</div>";
    
    // Verificar permisos
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM permisos");
    $permisos_count = $stmt->fetch()['count'];
    echo "<div class='alert alert-info'>📋 Permisos en sistema: <strong>$permisos_count</strong></div>";
    
    // Verificar especialidades
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM especialidades");
    $esp_count = $stmt->fetch()['count'];
    echo "<div class='alert alert-info'>🏥 Especialidades: <strong>$esp_count</strong></div>";
    
    // Verificar procedimientos
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM procedimientos");
        $proc_count = $stmt->fetch()['count'];
        echo "<div class='alert alert-info'>🦷 Procedimientos: <strong>$proc_count</strong></div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-warning'>⚠️ Tabla procedimientos no existe o está vacía</div>";
    }
    
    echo "<hr>";
    echo "<div class='alert alert-info'>";
    echo "<h6>Credenciales de Acceso por Defecto:</h6>";
    echo "<strong>Usuario:</strong> admin<br>";
    echo "<strong>Password:</strong> password<br>";
    echo "<em>Se recomienda cambiar estas credenciales después del primer acceso.</em>";
    echo "</div>";
    
    echo "<div class='d-flex gap-2'>";
    echo "<a href='procedimientos.php' class='btn btn-primary'>Ir a Gestión de Procedimientos</a>";
    echo "<a href='login.php' class='btn btn-success'>Ir al Login</a>";
    echo "<a href='index.php' class='btn btn-secondary'>Volver al Inicio</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h5>❌ Error</h5>";
    echo "<strong>Mensaje:</strong> " . $e->getMessage() . "<br>";
    echo "</div>";
    
    echo "<div class='d-flex gap-2'>";
    echo "<a href='importar_estructura.php' class='btn btn-warning'>Importar Estructura Primero</a>";
    echo "<a href='diagnostico_db.php' class='btn btn-info'>Diagnóstico de DB</a>";
    echo "<a href='index.php' class='btn btn-secondary'>Volver al Inicio</a>";
    echo "</div>";
}

echo "</body></html>";
?>

