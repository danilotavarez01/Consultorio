<?php
// Script de configuración específico para MySQL
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Configuración Completa de MySQL para Sistema de Campos</h1>";

// Configuración de la base de datos
define('DB_SERVER', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'consultorio');
define('DB_USER', 'root');
define('DB_PASS', '820416Dts');

try {
    echo "<h2>1. Estableciendo conexión a MySQL...</h2>";
    
    // Primero conectar sin especificar base de datos
    $dsn = "mysql:host=" . DB_SERVER . ";port=" . DB_PORT . ";charset=utf8";
    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_TIMEOUT => 10
    );
    
    $conn_temp = new PDO($dsn, DB_USER, DB_PASS, $options);
    echo "<p style='color: green;'>✅ Conexión a MySQL establecida</p>";
    
    // Verificar si existe la base de datos
    echo "<h2>2. Verificando base de datos...</h2>";
    $stmt = $conn_temp->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    $db_exists = $stmt->fetch();
    
    if (!$db_exists) {
        echo "<p>⚠️ Creando base de datos '" . DB_NAME . "'...</p>";
        $conn_temp->exec("CREATE DATABASE " . DB_NAME . " CHARACTER SET utf8 COLLATE utf8_general_ci");
        echo "<p style='color: green;'>✅ Base de datos creada</p>";
    } else {
        echo "<p style='color: green;'>✅ Base de datos existe</p>";
    }
    
    // Conectar a la base de datos específica
    $dsn_with_db = "mysql:host=" . DB_SERVER . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8";
    $conn = new PDO($dsn_with_db, DB_USER, DB_PASS, $options);
    echo "<p style='color: green;'>✅ Conectado a base de datos '" . DB_NAME . "'</p>";
    
    $conn->beginTransaction();
    
    echo "<h2>3. Creando/Verificando tablas...</h2>";
    
    // Crear tabla especialidades
    echo "<h3>a) Tabla especialidades:</h3>";
    $conn->exec("
        CREATE TABLE IF NOT EXISTS especialidades (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(10) UNIQUE NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT,
            estado ENUM('activo', 'inactivo') DEFAULT 'activo',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");
    echo "<p style='color: green;'>✅ Tabla especialidades OK</p>";
    
    // Crear tabla especialidad_campos
    echo "<h3>b) Tabla especialidad_campos:</h3>";
    $conn->exec("
        CREATE TABLE IF NOT EXISTS especialidad_campos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            especialidad_id INT NOT NULL,
            nombre_campo VARCHAR(50) NOT NULL,
            etiqueta VARCHAR(100) NOT NULL,
            tipo_campo ENUM('texto', 'numero', 'fecha', 'seleccion', 'checkbox', 'textarea') NOT NULL,
            opciones TEXT,
            requerido BOOLEAN DEFAULT FALSE,
            orden INT DEFAULT 0,
            estado ENUM('activo', 'inactivo') DEFAULT 'activo',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (especialidad_id) REFERENCES especialidades(id) ON DELETE CASCADE,
            UNIQUE KEY unique_campo_especialidad (especialidad_id, nombre_campo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");
    echo "<p style='color: green;'>✅ Tabla especialidad_campos OK</p>";
    
    // Crear tabla configuracion
    echo "<h3>c) Tabla configuracion:</h3>";
    $conn->exec("
        CREATE TABLE IF NOT EXISTS configuracion (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre_consultorio VARCHAR(100) DEFAULT 'Consultorio Médico',
            especialidad_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (especialidad_id) REFERENCES especialidades(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");
    echo "<p style='color: green;'>✅ Tabla configuracion OK</p>";
    
    echo "<h2>4. Insertando datos iniciales...</h2>";
    
    // Insertar especialidades
    echo "<h3>a) Especialidades:</h3>";
    $especialidades = [
        ['MG', 'Medicina General', 'Especialidad médica básica y general'],
        ['PED', 'Pediatría', 'Especialidad médica que estudia al niño y sus enfermedades'],
        ['GIN', 'Ginecología', 'Especialidad médica de la salud femenina']
    ];
    
    foreach ($especialidades as $esp) {
        $stmt = $conn->prepare("INSERT IGNORE INTO especialidades (codigo, nombre, descripcion) VALUES (?, ?, ?)");
        $stmt->execute($esp);
        if ($stmt->rowCount() > 0) {
            echo "<p>✅ Especialidad agregada: {$esp[1]}</p>";
        } else {
            echo "<p>ℹ️ Especialidad ya existe: {$esp[1]}</p>";
        }
    }
    
    // Obtener ID de Medicina General
    $stmt = $conn->prepare("SELECT id FROM especialidades WHERE codigo = 'MG'");
    $stmt->execute();
    $mg = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($mg) {
        echo "<h3>b) Configuración global:</h3>";
        // Verificar si ya existe configuración
        $stmt = $conn->query("SELECT COUNT(*) as count FROM configuracion WHERE id = 1");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($count['count'] == 0) {
            $conn->exec("INSERT INTO configuracion (id, nombre_consultorio, especialidad_id) VALUES (1, 'Consultorio Médico', {$mg['id']})");
            echo "<p style='color: green;'>✅ Configuración inicial creada</p>";
        } else {
            $stmt = $conn->prepare("UPDATE configuracion SET especialidad_id = ? WHERE id = 1");
            $stmt->execute([$mg['id']]);
            echo "<p style='color: green;'>✅ Configuración actualizada</p>";
        }
        
        echo "<h3>c) Campos para Medicina General:</h3>";
        // Limpiar campos existentes
        $stmt = $conn->prepare("DELETE FROM especialidad_campos WHERE especialidad_id = ?");
        $stmt->execute([$mg['id']]);
        
        // Insertar campos básicos
        $campos = [
            ['temperatura', 'Temperatura (°C)', 'numero', null, 1, 1],
            ['presion_arterial', 'Presión Arterial', 'texto', null, 1, 2],
            ['frecuencia_respiratoria', 'Frecuencia Respiratoria (rpm)', 'numero', null, 0, 3],
            ['saturacion_oxigeno', 'Saturación de Oxígeno (%)', 'numero', null, 0, 4],
            ['sintomas_generales', 'Síntomas Generales', 'textarea', null, 0, 5],
            ['tipo_consulta', 'Tipo de Consulta', 'seleccion', 'Primera vez,Control,Seguimiento,Urgencia', 1, 6]
        ];
        
        $stmt = $conn->prepare("
            INSERT INTO especialidad_campos 
            (especialidad_id, nombre_campo, etiqueta, tipo_campo, opciones, requerido, orden) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($campos as $campo) {
            $stmt->execute(array_merge([$mg['id']], $campo));
        }
        
        echo "<p style='color: green;'>✅ " . count($campos) . " campos configurados para Medicina General</p>";
        
        // Mostrar campos insertados
        echo "<h4>Campos configurados:</h4>";
        echo "<ul>";
        foreach ($campos as $campo) {
            echo "<li><strong>{$campo[1]}</strong> ({$campo[0]}) - Tipo: {$campo[2]}</li>";
        }
        echo "</ul>";
    }
    
    $conn->commit();
    
    echo "<h2>5. Verificación final...</h2>";
    
    // Test final del endpoint
    echo "<h3>a) Test del endpoint:</h3>";
    $test_url = 'http://localhost:83/get_campos_mysql_fixed.php';
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'method' => 'GET'
        ]
    ]);
    
    $response = @file_get_contents($test_url, false, $context);
    
    if ($response !== false) {
        $json_data = json_decode($response, true);
        if ($json_data && isset($json_data['success']) && $json_data['success']) {
            echo "<p style='color: green;'>✅ Endpoint funcionando correctamente</p>";
            echo "<p>📊 Campos devueltos: " . count($json_data['campos']) . "</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Endpoint responde pero con error: " . ($json_data['error'] ?? 'Error desconocido') . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No se pudo conectar al endpoint</p>";
    }
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎉 ¡Configuración MySQL Completada!</h3>";
    echo "<p>El sistema está configurado y listo para usar con MySQL.</p>";
    echo "<h4>📋 Próximos pasos:</h4>";
    echo "<ol>";
    echo "<li><a href='get_campos_mysql_fixed.php' target='_blank' style='color: #007bff;'>🔌 Probar endpoint MySQL</a></li>";
    echo "<li><a href='nueva_consulta.php?paciente_id=1' target='_blank' style='color: #007bff;'>📝 Probar formulario de consulta</a></li>";
    echo "<li><a href='test_mysql_connection.php' target='_blank' style='color: #007bff;'>🔍 Verificar conexión MySQL</a></li>";
    echo "</ol>";
    echo "</div>";
    
} catch (PDOException $e) {
    if (isset($conn)) $conn->rollBack();
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ Error de MySQL:</h3>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Código:</strong> " . $e->getCode() . "</p>";
    
    if ($e->getCode() == 1045) {
        echo "<p><strong>Solución:</strong> Verifica las credenciales de MySQL</p>";
    } elseif ($e->getCode() == 2002) {
        echo "<p><strong>Solución:</strong> Verifica que MySQL esté ejecutándose</p>";
    }
    echo "</div>";
    
} catch (Exception $e) {
    if (isset($conn)) $conn->rollBack();
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ Error general:</h3>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
