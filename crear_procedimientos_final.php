<?php
// Script para crear tabla procedimientos compatible con MySQL más antiguo
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$username = 'root';
$password = '820416Dts';

echo "=== Creando tabla procedimientos (Compatible) ===\n\n";

try {
    // Conectar sin especificar base de datos
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "✅ Conexión al servidor MySQL exitosa\n";
    
    // Crear base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS consultorio CHARACTER SET utf8 COLLATE utf8_general_ci");
    echo "✅ Base de datos 'consultorio' verificada/creada\n";
    
    // Usar la base de datos
    $pdo->exec("USE consultorio");
    echo "✅ Usando base de datos 'consultorio'\n\n";
    
    // Eliminar tabla si existe
    $pdo->exec("DROP TABLE IF EXISTS procedimientos");
    echo "🗑️ Tabla anterior eliminada (si existía)\n";
    
    // Crear tabla procedimientos (compatible con MySQL más antiguo)
    echo "🔧 Creando tabla procedimientos...\n";
    
    $sql = "CREATE TABLE procedimientos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        codigo VARCHAR(20) UNIQUE,
        nombre VARCHAR(255) NOT NULL,
        descripcion TEXT,
        precio_costo DECIMAL(10,2) DEFAULT 0.00,
        precio_venta DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        categoria ENUM('procedimiento','utensilio','material','medicamento') DEFAULT 'procedimiento',
        activo TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT NULL,
        INDEX idx_categoria (categoria),
        INDEX idx_activo (activo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
    
    $pdo->exec($sql);
    echo "✅ Tabla 'procedimientos' creada exitosamente\n\n";
    
    // Verificar que la tabla existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'procedimientos'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Verificación: La tabla 'procedimientos' existe\n\n";
        
        // Mostrar estructura
        echo "📋 Estructura de la tabla:\n";
        echo "-------------------------\n";
        $stmt = $pdo->query("DESCRIBE procedimientos");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo sprintf("%-20s %-30s\n", $row['Field'], $row['Type']);
        }
        
        // Insertar datos de ejemplo
        echo "\n💾 Insertando datos de ejemplo...\n";
        
        $stmt = $pdo->prepare("INSERT INTO procedimientos (codigo, nombre, descripcion, precio_costo, precio_venta, categoria) VALUES (?, ?, ?, ?, ?, ?)");
        
        $ejemplos = [
            ['PR001', 'Consulta General', 'Consulta médica general', 15.00, 25.00, 'procedimiento'],
            ['PR002', 'Limpieza Dental', 'Profilaxis dental completa', 20.00, 35.00, 'procedimiento'],
            ['PR003', 'Extracción Dental', 'Extracción de pieza dental', 30.00, 50.00, 'procedimiento'],
            ['UT001', 'Jeringa 5ml', 'Jeringa desechable de 5ml', 0.50, 1.00, 'utensilio'],
            ['UT002', 'Guantes Latex', 'Guantes desechables de látex', 0.25, 0.50, 'utensilio'],
            ['MT001', 'Algodón', 'Algodón estéril', 2.00, 3.50, 'material'],
            ['MT002', 'Gasa Estéril', 'Gasa estéril 5x5cm', 1.50, 3.00, 'material'],
            ['MD001', 'Paracetamol 500mg', 'Analgésico y antipirético', 0.10, 0.25, 'medicamento'],
            ['MD002', 'Ibuprofeno 400mg', 'Antiinflamatorio', 0.15, 0.35, 'medicamento']
        ];
        
        foreach ($ejemplos as $ejemplo) {
            $stmt->execute($ejemplo);
            echo "✅ Insertado: {$ejemplo[0]} - {$ejemplo[1]}\n";
        }
        
        // Mostrar resumen
        echo "\n📊 Resumen final:\n";
        echo "================\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM procedimientos");
        $total = $stmt->fetch()['total'];
        echo "Total de procedimientos: $total\n\n";
        
        $stmt = $pdo->query("SELECT categoria, COUNT(*) as count FROM procedimientos GROUP BY categoria");
        echo "Por categoría:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "- {$row['categoria']}: {$row['count']}\n";
        }
        
        // Mostrar algunos registros
        echo "\n📋 Primeros 5 registros:\n";
        echo "----------------------\n";
        $stmt = $pdo->query("SELECT codigo, nombre, categoria, precio_venta FROM procedimientos LIMIT 5");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo sprintf("%-8s %-25s %-15s $%.2f\n", $row['codigo'], $row['nombre'], $row['categoria'], $row['precio_venta']);
        }
        
        echo "\n🎉 ¡Tabla procedimientos creada y configurada exitosamente!\n";
        echo "La tabla está lista para usar en el sistema de facturación.\n";
        
    } else {
        echo "❌ Error: No se pudo crear la tabla 'procedimientos'\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
}

echo "\n=== Fin del proceso ===\n";
?>
