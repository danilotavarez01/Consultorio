<?php
// Script simplificado para crear la tabla procedimientos
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$username = 'root';
$password = '820416Dts';

echo "=== Verificando y creando base de datos ===\n\n";

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
    
    // Crear tabla procedimientos
    echo "🔧 Creando tabla procedimientos...\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS procedimientos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        codigo VARCHAR(20) UNIQUE,
        nombre VARCHAR(255) NOT NULL,
        descripcion TEXT,
        precio_costo DECIMAL(10,2) DEFAULT 0.00,
        precio_venta DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        categoria ENUM('procedimiento','utensilio','material','medicamento') DEFAULT 'procedimiento',
        activo TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
        $stmt = $pdo->query("DESCRIBE procedimientos");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "- {$row['Field']}: {$row['Type']}\n";
        }
        
        // Insertar datos de ejemplo
        echo "\n💾 Insertando datos de ejemplo...\n";
        
        $ejemplos = [
            "INSERT IGNORE INTO procedimientos (codigo, nombre, descripcion, precio_costo, precio_venta, categoria) VALUES 
             ('PR001', 'Consulta General', 'Consulta médica general', 15.00, 25.00, 'procedimiento')",
            "INSERT IGNORE INTO procedimientos (codigo, nombre, descripcion, precio_costo, precio_venta, categoria) VALUES 
             ('PR002', 'Limpieza Dental', 'Profilaxis dental completa', 20.00, 35.00, 'procedimiento')",
            "INSERT IGNORE INTO procedimientos (codigo, nombre, descripcion, precio_costo, precio_venta, categoria) VALUES 
             ('UT001', 'Jeringa 5ml', 'Jeringa desechable de 5ml', 0.50, 1.00, 'utensilio')",
            "INSERT IGNORE INTO procedimientos (codigo, nombre, descripcion, precio_costo, precio_venta, categoria) VALUES 
             ('MT001', 'Algodón', 'Algodón estéril', 2.00, 3.50, 'material')",
            "INSERT IGNORE INTO procedimientos (codigo, nombre, descripcion, precio_costo, precio_venta, categoria) VALUES 
             ('MD001', 'Paracetamol 500mg', 'Analgésico y antipirético', 0.10, 0.25, 'medicamento')"
        ];
        
        foreach ($ejemplos as $sql_ejemplo) {
            $pdo->exec($sql_ejemplo);
        }
        
        // Mostrar resumen
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM procedimientos");
        $total = $stmt->fetch()['total'];
        echo "✅ Insertados $total procedimientos de ejemplo\n";
        
        echo "\n🎉 ¡Proceso completado exitosamente!\n";
        echo "La tabla 'procedimientos' está lista para usar.\n";
        
    } else {
        echo "❌ Error: No se pudo crear la tabla 'procedimientos'\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
}

echo "\n=== Fin ===\n";
?>
