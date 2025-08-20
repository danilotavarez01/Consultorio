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
    <title>Setup Permisos Procedimientos</title>
    <link href='assets/css/bootstrap-5.1.3.min.css' rel='stylesheet'>
</head>
<body class='container mt-4'>";

echo "<h2>Configuración de Permisos para Procedimientos</h2>";

// Verificar que las constantes estén definidas
if (!defined('DB_SERVER') || !defined('DB_PORT') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
    echo "<div class='alert alert-danger'>";
    echo "<h5>❌ Error de Configuración</h5>";
    echo "<p>Las constantes de base de datos no están definidas. Verifique config.php</p>";
    echo "</div>";
    echo "</body></html>";
    exit;
}

try {
    // Verificar conexión a la base de datos
    if (!isset($pdo) || !$pdo) {
        throw new Exception("No se pudo establecer conexión con la base de datos");
    }
    
    // Verificar que estamos conectados a la base de datos correcta
    $stmt = $pdo->query("SELECT DATABASE() as db_name");
    $db_info = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<div class='alert alert-info'>Conectado a la base de datos: <strong>" . $db_info['db_name'] . "</strong></div>";
    
    // Verificar si el permiso ya existe
    $stmt = $pdo->prepare("SELECT id FROM permisos WHERE nombre = 'gestionar_catalogos'");
    $stmt->execute();
    $permiso = $stmt->fetch();
    
    if (!$permiso) {
        // Insertar el permiso
        $stmt = $pdo->prepare("INSERT INTO permisos (nombre, descripcion) VALUES (?, ?)");
        $stmt->execute(['gestionar_catalogos', 'Gestionar catálogos y procedimientos']);
        $permiso_id = $pdo->lastInsertId();
        echo "Permiso 'gestionar_catalogos' creado con ID: $permiso_id<br>";
        
        // Asignar el permiso al usuario admin (ID = 1)
        $stmt = $pdo->prepare("INSERT INTO usuario_permisos (usuario_id, permiso_id) VALUES (1, ?)");
        $stmt->execute([$permiso_id]);
        echo "Permiso asignado al usuario admin<br>";
    } else {
        echo "El permiso 'gestionar_catalogos' ya existe<br>";
        
        // Verificar si el admin ya tiene este permiso
        $stmt = $pdo->prepare("SELECT * FROM usuario_permisos WHERE usuario_id = 1 AND permiso_id = ?");
        $stmt->execute([$permiso['id']]);
        $asignacion = $stmt->fetch();
        
        if (!$asignacion) {
            $stmt = $pdo->prepare("INSERT INTO usuario_permisos (usuario_id, permiso_id) VALUES (1, ?)");
            $stmt->execute([$permiso['id']]);
            echo "Permiso asignado al usuario admin<br>";
        } else {
            echo "El usuario admin ya tiene este permiso<br>";
        }
    }
    
    echo "<h3>Configuración completada</h3>";
    echo "<p><a href='procedimientos.php' class='btn btn-primary'>Ir a Gestión de Procedimientos</a></p>";
    echo "<p><a href='index.php' class='btn btn-secondary'>Volver al Inicio</a></p>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error de base de datos: " . $e->getMessage() . "</div>";
    echo "<div class='alert alert-warning'>Código de error: " . $e->getCode() . "</div>";
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}

echo "</body></html>";

