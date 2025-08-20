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
    <title>Setup Procedimientos</title>
    <link href='assets/css/bootstrap-5.1.3.min.css' rel='stylesheet'>
</head>
<body class='container mt-4'>";

echo "<h2>Verificación y Creación de Tabla Procedimientos</h2>";

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
    
    if (!$tabla_existe) {
        echo "<div class='alert alert-warning'>La tabla 'procedimientos' no existe. Creando...</div>";
        
        // Crear la tabla
        $sql = "CREATE TABLE `procedimientos` (
            `id` int NOT NULL AUTO_INCREMENT,
            `codigo` varchar(20) DEFAULT NULL,
            `nombre` varchar(255) NOT NULL,
            `descripcion` text,
            `precio_costo` decimal(10,2) DEFAULT '0.00',
            `precio_venta` decimal(10,2) NOT NULL DEFAULT '0.00',
            `categoria` enum('procedimiento','utensilio','material','medicamento') DEFAULT 'procedimiento',
            `activo` tinyint(1) DEFAULT '1',
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `codigo` (`codigo`),
            KEY `idx_categoria` (`categoria`),
            KEY `idx_activo` (`activo`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        $pdo->exec($sql);
        echo "<div class='alert alert-success'>Tabla 'procedimientos' creada exitosamente</div>";
        
        // Insertar datos de ejemplo
        $procedimientos_ejemplo = [
            ['PROC001', 'Limpieza Dental', 'Profilaxis dental completa', 10.00, 50.00, 1, 'procedimiento'],
            ['PROC002', 'Obturación Simple', 'Relleno de caries superficial', 15.00, 75.00, 1, 'procedimiento'],
            ['PROC003', 'Extracción Simple', 'Extracción de pieza dental', 20.00, 100.00, 1, 'procedimiento'],
            ['PROC004', 'Tratamiento de Conducto', 'Endodoncia completa', 50.00, 250.00, 1, 'procedimiento'],
            ['PROC005', 'Corona Dental', 'Prótesis dental fija', 100.00, 500.00, 1, 'procedimiento'],
            ['PROC006', 'Implante Dental', 'Implante de titanio', 200.00, 800.00, 1, 'procedimiento'],
            ['UTEN001', 'Brackets Metálicos', 'Aparato de ortodoncia', 150.00, 600.00, 1, 'utensilio'],
            ['MAT001', 'Resina Composite', 'Material de obturación', 5.00, 20.00, 1, 'material'],
            ['MED001', 'Anestesia Local', 'Lidocaína con epinefrina', 2.00, 10.00, 1, 'medicamento'],
            ['UTEN002', 'Gasas Estériles', 'Material de curación', 1.00, 5.00, 1, 'utensilio']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO procedimientos (codigo, nombre, descripcion, precio_costo, precio_venta, activo, categoria) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($procedimientos_ejemplo as $proc) {
            $stmt->execute($proc);
        }
        
        echo "<div class='alert alert-success'>Se han insertado " . count($procedimientos_ejemplo) . " procedimientos de ejemplo</div>";
        
    } else {
        echo "<div class='alert alert-info'>La tabla 'procedimientos' ya existe</div>";
        
        // Verificar estructura
        $stmt = $pdo->query("DESCRIBE procedimientos");
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Estructura de la tabla:</h4>";
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
    }
    
    // Mostrar registros existentes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM procedimientos");
    $total = $stmt->fetch()['total'];
    
    echo "<h4>Registros en la tabla: $total</h4>";
    
    if ($total > 0) {
        $stmt = $pdo->query("SELECT * FROM procedimientos ORDER BY categoria, nombre LIMIT 10");
        $procedimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table class='table table-striped'>";
        echo "<tr><th>ID</th><th>Código</th><th>Nombre</th><th>Categoría</th><th>Precio Venta</th><th>Estado</th></tr>";
        foreach ($procedimientos as $proc) {
            echo "<tr>";
            echo "<td>" . $proc['id'] . "</td>";
            echo "<td>" . ($proc['codigo'] ?? '<em>Sin código</em>') . "</td>";
            echo "<td>" . $proc['nombre'] . "</td>";
            echo "<td>" . ucfirst($proc['categoria']) . "</td>";
            echo "<td>$" . number_format($proc['precio_venta'], 2) . "</td>";
            echo "<td>" . ($proc['activo'] ? 'Activo' : 'Inactivo') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        if ($total > 10) {
            echo "<p><em>Mostrando los primeros 10 de $total registros</em></p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>Operaciones Completadas</h3>";
    echo "<p><a href='procedimientos.php' class='btn btn-primary'>Ir a Gestión de Procedimientos</a></p>";
    echo "<p><a href='setup_procedimientos.php' class='btn btn-info'>Configurar Permisos</a></p>";
    echo "<p><a href='index.php' class='btn btn-secondary'>Volver al Inicio</a></p>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error de base de datos: " . $e->getMessage() . "</div>";
    echo "<div class='alert alert-warning'>Código de error: " . $e->getCode() . "</div>";
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}

echo "</body></html>";

