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
    <title>Corrección Procedimientos</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='container mt-4'>";

echo "<h2>Corrección de Tabla Procedimientos</h2>";

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
    
    // Verificar si la tabla procedimientos existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'procedimientos'");
    $tabla_existe = $stmt->rowCount() > 0;
    
    if ($tabla_existe) {
        echo "<div class='alert alert-info'>La tabla 'procedimientos' existe. Verificando estructura...</div>";
        
        // Verificar si la columna código existe
        $stmt = $pdo->query("SHOW COLUMNS FROM procedimientos LIKE 'codigo'");
        $codigo_existe = $stmt->rowCount() > 0;
        
        if (!$codigo_existe) {
            echo "<div class='alert alert-warning'>Agregando columna 'codigo'...</div>";
            $pdo->exec("ALTER TABLE procedimientos ADD COLUMN codigo varchar(20) DEFAULT NULL AFTER id");
            $pdo->exec("ALTER TABLE procedimientos ADD UNIQUE KEY codigo (codigo)");
            echo "<div class='alert alert-success'>Columna 'codigo' agregada exitosamente</div>";
        } else {
            echo "<div class='alert alert-info'>La columna 'codigo' ya existe</div>";
        }
        
        // Verificar si la categoría 'medicamento' está disponible
        $stmt = $pdo->query("SHOW COLUMNS FROM procedimientos LIKE 'categoria'");
        $categoria_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($categoria_info && !strpos($categoria_info['Type'], 'medicamento')) {
            echo "<div class='alert alert-warning'>Actualizando enum de categoría para incluir 'medicamento'...</div>";
            $pdo->exec("ALTER TABLE procedimientos MODIFY categoria enum('procedimiento','utensilio','material','medicamento') DEFAULT 'procedimiento'");
            echo "<div class='alert alert-success'>Categoría 'medicamento' agregada exitosamente</div>";
        } else {
            echo "<div class='alert alert-info'>La categoría 'medicamento' ya está disponible</div>";
        }
        
        // Verificar que precio_venta tenga NOT NULL
        $stmt = $pdo->query("SHOW COLUMNS FROM procedimientos LIKE 'precio_venta'");
        $precio_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($precio_info && $precio_info['Null'] === 'YES') {
            echo "<div class='alert alert-warning'>Actualizando campo precio_venta para que sea obligatorio...</div>";
            $pdo->exec("UPDATE procedimientos SET precio_venta = 0.00 WHERE precio_venta IS NULL");
            $pdo->exec("ALTER TABLE procedimientos MODIFY precio_venta decimal(10,2) NOT NULL DEFAULT '0.00'");
            echo "<div class='alert alert-success'>Campo precio_venta actualizado</div>";
        }
        
        echo "<h4>Estructura actualizada de la tabla:</h4>";
        $stmt = $pdo->query("DESCRIBE procedimientos");
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table class='table table-bordered'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Key</th><th>Default</th></tr>";
        foreach ($columnas as $col) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . $col['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Mostrar índices
        echo "<h4>Índices de la tabla:</h4>";
        $stmt = $pdo->query("SHOW INDEX FROM procedimientos");
        $indices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table class='table table-bordered'>";
        echo "<tr><th>Key Name</th><th>Column</th><th>Unique</th></tr>";
        foreach ($indices as $index) {
            echo "<tr>";
            echo "<td>" . $index['Key_name'] . "</td>";
            echo "<td>" . $index['Column_name'] . "</td>";
            echo "<td>" . ($index['Non_unique'] ? 'No' : 'Sí') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<div class='alert alert-danger'>La tabla 'procedimientos' no existe. Ejecute primero 'verificar_procedimientos.php'</div>";
    }
    
    echo "<hr>";
    echo "<h3>Operaciones Completadas</h3>";
    echo "<p><a href='procedimientos.php' class='btn btn-primary'>Ir a Gestión de Procedimientos</a></p>";
    echo "<p><a href='verificar_procedimientos.php' class='btn btn-info'>Verificar Tabla</a></p>";
    echo "<p><a href='index.php' class='btn btn-secondary'>Volver al Inicio</a></p>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error de base de datos: " . $e->getMessage() . "</div>";
    echo "<div class='alert alert-warning'>Código de error: " . $e->getCode() . "</div>";
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}

echo "</body></html>";
