<?php
// Script para crear la tabla procedimientos en la base de datos consultorio
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'consultorio';
$username = 'root';
$password = '820416Dts';

echo "=== Creación de Tabla Procedimientos ===\n\n";

try {
    // Crear conexión PDO
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
    ]);
    
    echo "✅ Conexión exitosa a la base de datos '$dbname'\n\n";
    
    // SQL para crear la tabla procedimientos
    $sql = "
    DROP TABLE IF EXISTS `procedimientos`;
    CREATE TABLE `procedimientos` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
    ";
    
    // Ejecutar la creación de tabla
    $pdo->exec($sql);
    echo "✅ Tabla 'procedimientos' creada exitosamente\n\n";
    
    // Mostrar estructura de la tabla
    echo "📋 Estructura de la tabla 'procedimientos':\n";
    echo "--------------------------------------------\n";
    $stmt = $pdo->query("DESCRIBE procedimientos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo sprintf("%-20s %-30s %-10s %-10s %-10s\n", 
            $column['Field'], 
            $column['Type'], 
            $column['Null'], 
            $column['Key'], 
            $column['Default']
        );
    }
    
    echo "\n📋 Índices de la tabla:\n";
    echo "----------------------\n";
    $stmt = $pdo->query("SHOW INDEX FROM procedimientos");
    $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($indexes as $index) {
        echo sprintf("%-20s %-15s %-15s\n", 
            $index['Key_name'], 
            $index['Column_name'], 
            $index['Index_type']
        );
    }
    
    // Insertar algunos datos de ejemplo
    echo "\n💾 Insertando datos de ejemplo...\n";
    $ejemplos = [
        ['PR001', 'Consulta General', 'Consulta médica general', 15.00, 25.00, 'procedimiento'],
        ['PR002', 'Limpieza Dental', 'Profilaxis dental completa', 20.00, 35.00, 'procedimiento'],
        ['UT001', 'Jeringa 5ml', 'Jeringa desechable de 5ml', 0.50, 1.00, 'utensilio'],
        ['MT001', 'Algodón', 'Algodón estéril', 2.00, 3.50, 'material'],
        ['MD001', 'Paracetamol 500mg', 'Analgésico y antipirético', 0.10, 0.25, 'medicamento']
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO procedimientos (codigo, nombre, descripcion, precio_costo, precio_venta, categoria) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($ejemplos as $ejemplo) {
        $stmt->execute($ejemplo);
        echo "✅ Insertado: {$ejemplo[0]} - {$ejemplo[1]}\n";
    }
    
    // Mostrar resumen
    echo "\n📊 Resumen final:\n";
    echo "================\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM procedimientos");
    $total = $stmt->fetch()['total'];
    echo "Total de procedimientos: $total\n";
    
    $stmt = $pdo->query("SELECT categoria, COUNT(*) as count FROM procedimientos GROUP BY categoria");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nPor categoría:\n";
    foreach ($categorias as $cat) {
        echo "- {$cat['categoria']}: {$cat['count']}\n";
    }
    
    echo "\n🎉 ¡Tabla procedimientos creada y configurada exitosamente!\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Código de error: " . $e->getCode() . "\n";
}

echo "\n=== Fin del proceso ===\n";
?>
