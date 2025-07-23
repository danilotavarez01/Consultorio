<?php
// Script de diagnóstico específico para MySQL
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnóstico de Conexión MySQL</h1>";

// Configuración de la base de datos
define('DB_SERVER', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'consultorio');
define('DB_USER', 'root');
define('DB_PASS', '820416Dts');

echo "<h2>1. Configuración de Base de Datos:</h2>";
echo "<ul>";
echo "<li><strong>Servidor:</strong> " . DB_SERVER . "</li>";
echo "<li><strong>Puerto:</strong> " . DB_PORT . "</li>";
echo "<li><strong>Base de Datos:</strong> " . DB_NAME . "</li>";
echo "<li><strong>Usuario:</strong> " . DB_USER . "</li>";
echo "<li><strong>Contraseña:</strong> " . (DB_PASS ? '[CONFIGURADA]' : '[NO CONFIGURADA]') . "</li>";
echo "</ul>";

echo "<h2>2. Pruebas de Conexión:</h2>";

try {
    echo "<h3>a) Conexión sin base de datos específica:</h3>";
    $dsn = "mysql:host=" . DB_SERVER . ";port=" . DB_PORT . ";charset=utf8";
    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_TIMEOUT => 15
    );
    
    $conn_test = new PDO($dsn, DB_USER, DB_PASS, $options);
    echo "<p style='color: green;'>✅ Conexión al servidor MySQL exitosa</p>";
    
    echo "<h3>b) Verificar si existe la base de datos:</h3>";
    $stmt = $conn_test->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    $db_exists = $stmt->fetch();
    
    if ($db_exists) {
        echo "<p style='color: green;'>✅ Base de datos '" . DB_NAME . "' existe</p>";
        
        echo "<h3>c) Conexión a la base de datos específica:</h3>";
        $dsn_with_db = "mysql:host=" . DB_SERVER . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8";
        $conn_with_db = new PDO($dsn_with_db, DB_USER, DB_PASS, $options);
        echo "<p style='color: green;'>✅ Conexión a la base de datos '" . DB_NAME . "' exitosa</p>";
        
        echo "<h3>d) Test de consulta simple:</h3>";
        $test_result = $conn_with_db->query("SELECT 1 as test")->fetch(PDO::FETCH_ASSOC);
        if ($test_result && $test_result['test'] == 1) {
            echo "<p style='color: green;'>✅ Test de consulta exitoso: " . json_encode($test_result) . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Test de consulta falló</p>";
        }
        
        echo "<h3>e) Verificar tablas necesarias:</h3>";
        $tables_to_check = ['configuracion', 'especialidades', 'especialidad_campos'];
        
        foreach ($tables_to_check as $table) {
            $stmt = $conn_with_db->query("SHOW TABLES LIKE '$table'");
            $table_exists = $stmt->fetch();
            
            if ($table_exists) {
                echo "<p style='color: green;'>✅ Tabla '$table' existe</p>";
                
                // Contar registros
                $count_stmt = $conn_with_db->query("SELECT COUNT(*) as count FROM $table");
                $count = $count_stmt->fetch(PDO::FETCH_ASSOC);
                echo "<p style='margin-left: 20px;'>📊 Registros: {$count['count']}</p>";
                
            } else {
                echo "<p style='color: red;'>❌ Tabla '$table' NO existe</p>";
            }
        }
        
        echo "<h3>f) Test de configuración específica:</h3>";
        try {
            $config_stmt = $conn_with_db->query("SELECT * FROM configuracion WHERE id = 1");
            $config = $config_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($config) {
                echo "<p style='color: green;'>✅ Configuración encontrada:</p>";
                echo "<pre style='background: #f8f9fa; padding: 10px; border: 1px solid #dee2e6;'>";
                print_r($config);
                echo "</pre>";
            } else {
                echo "<p style='color: orange;'>⚠️ No hay configuración en la tabla (ID = 1)</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error al consultar configuración: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Base de datos '" . DB_NAME . "' NO existe</p>";
        
        echo "<h3>Bases de datos disponibles:</h3>";
        $stmt = $conn_test->query("SHOW DATABASES");
        $databases = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<ul>";
        foreach ($databases as $db) {
            echo "<li>" . $db['Database'] . "</li>";
        }
        echo "</ul>";
        
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>💡 Solución:</h4>";
        echo "<p>Necesitas crear la base de datos. Ejecuta:</p>";
        echo "<code>CREATE DATABASE " . DB_NAME . " CHARACTER SET utf8 COLLATE utf8_general_ci;</code>";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error de conexión PDO: " . $e->getMessage() . "</p>";
    echo "<p><strong>Código de error:</strong> " . $e->getCode() . "</p>";
    
    if ($e->getCode() == 1045) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h4>🔐 Error de Autenticación</h4>";
        echo "<p>Las credenciales de usuario/contraseña son incorrectas.</p>";
        echo "<p><strong>Verifica:</strong></p>";
        echo "<ul>";
        echo "<li>Usuario: <code>" . DB_USER . "</code></li>";
        echo "<li>Contraseña configurada correctamente</li>";
        echo "<li>Que el usuario tenga permisos en MySQL</li>";
        echo "</ul>";
        echo "</div>";
    } elseif ($e->getCode() == 2002) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h4>🔌 Error de Conexión</h4>";
        echo "<p>No se puede conectar al servidor MySQL.</p>";
        echo "<p><strong>Verifica:</strong></p>";
        echo "<ul>";
        echo "<li>Que MySQL esté ejecutándose</li>";
        echo "<li>Que el puerto " . DB_PORT . " esté abierto</li>";
        echo "<li>Que el servidor " . DB_SERVER . " sea correcto</li>";
        echo "</ul>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>";
}

echo "<h2>3. Información del Sistema:</h2>";
echo "<ul>";
echo "<li><strong>PHP Version:</strong> " . PHP_VERSION . "</li>";
echo "<li><strong>PDO MySQL:</strong> " . (extension_loaded('pdo_mysql') ? '✅ Disponible' : '❌ No disponible') . "</li>";
echo "<li><strong>MySQL Client:</strong> " . (function_exists('mysql_get_client_info') ? mysql_get_client_info() : 'N/A') . "</li>";
echo "</ul>";
?>
