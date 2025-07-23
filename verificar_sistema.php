<?php
require_once "config.php";

echo "<h1>Verificación de Base de Datos</h1>";

try {
    // Test de conexión básica
    echo "<h2>1. Test de Conexión:</h2>";
    $test_query = $conn->query("SELECT 1 as test");
    $result = $test_query->fetch();
    if ($result['test'] == 1) {
        echo "<p>✅ Conexión a BD exitosa</p>";
    }
    
    // Verificar que existen las tablas necesarias
    echo "<h2>2. Verificación de Tablas:</h2>";
    $tables = ['especialidades', 'especialidad_campos', 'configuracion'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SHOW TABLES LIKE '$table'");
            $exists = $stmt->fetch();
            
            if ($exists) {
                echo "<p>✅ Tabla '$table' existe</p>";
                
                // Contar registros
                $count_stmt = $conn->query("SELECT COUNT(*) as total FROM $table");
                $count = $count_stmt->fetch();
                echo "<p>&nbsp;&nbsp;&nbsp;📊 Registros: {$count['total']}</p>";
                
            } else {
                echo "<p>❌ Tabla '$table' NO existe</p>";
            }
        } catch (Exception $e) {
            echo "<p>❌ Error verificando tabla '$table': " . $e->getMessage() . "</p>";
        }
    }
    
    // Verificar estructura de especialidad_campos
    echo "<h2>3. Estructura de especialidad_campos:</h2>";
    try {
        $stmt = $conn->query("DESCRIBE especialidad_campos");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>{$col['Field']}</td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } catch (Exception $e) {
        echo "<p>❌ Error verificando estructura: " . $e->getMessage() . "</p>";
    }
    
    // Test directo del endpoint con curl
    echo "<h2>4. Test HTTP del Endpoint:</h2>";
    $url = "http://localhost:83/Consultorio2/get_campos_especialidad_por_id.php?especialidad_id=1";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "<p><strong>URL:</strong> $url</p>";
    echo "<p><strong>HTTP Code:</strong> $http_code</p>";
    
    if ($error) {
        echo "<p>❌ Error cURL: $error</p>";
    } else {
        echo "<p>✅ Respuesta obtenida</p>";
        echo "<h4>Respuesta del endpoint:</h4>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        
        $json_data = json_decode($response, true);
        if ($json_data) {
            echo "<p>✅ JSON válido</p>";
            if (isset($json_data['success']) && $json_data['success']) {
                echo "<p>✅ Endpoint funcionando correctamente</p>";
            } else {
                echo "<p>❌ Endpoint reporta error: " . ($json_data['message'] ?? 'Sin mensaje') . "</p>";
            }
        } else {
            echo "<p>❌ Respuesta no es JSON válido</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR GENERAL: " . $e->getMessage() . "</p>";
}
?>
