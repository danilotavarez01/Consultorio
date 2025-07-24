<?php
// Debug para verificar por qué no se muestra el nombre del consultorio
session_start();

echo "<h3>Debug - Nombre del Consultorio</h3>";

// Función para obtener la configuración completa (igual que en header.php)
function obtenerConfiguracionHeader() {
    try {
        // Configuración de la base de datos
        $host = 'localhost';
        $dbname = 'consultorio_medico';
        $username = 'root';
        $password = '';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Consultar toda la configuración
        $stmt = $pdo->query("SELECT * FROM configuracion WHERE id = 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $config ? $config : array();
        
    } catch (Exception $e) {
        echo "<div style='color: red;'>Error en obtenerConfiguracionHeader(): " . $e->getMessage() . "</div>";
        return array();
    }
}

echo "<h4>1. Verificando conexión y datos:</h4>";

// Obtener la configuración
$configHeader = obtenerConfiguracionHeader();

echo "<strong>Configuración obtenida:</strong><br>";
if (empty($configHeader)) {
    echo "<span style='color: red;'>⚠️ No se obtuvo configuración (array vacío)</span><br>";
} else {
    echo "<span style='color: green;'>✅ Configuración obtenida correctamente</span><br>";
    echo "<pre>" . print_r($configHeader, true) . "</pre>";
}

echo "<h4>2. Verificando campo nombre_consultorio:</h4>";
if (isset($configHeader['nombre_consultorio'])) {
    echo "<strong>Campo existe:</strong> <span style='color: green;'>✅ Sí</span><br>";
    echo "<strong>Valor:</strong> '" . htmlspecialchars($configHeader['nombre_consultorio']) . "'<br>";
    echo "<strong>Es null:</strong> " . (is_null($configHeader['nombre_consultorio']) ? "Sí" : "No") . "<br>";
    echo "<strong>Está vacío:</strong> " . (empty($configHeader['nombre_consultorio']) ? "Sí" : "No") . "<br>";
    echo "<strong>Longitud:</strong> " . strlen($configHeader['nombre_consultorio']) . " caracteres<br>";
} else {
    echo "<span style='color: red;'>⚠️ El campo 'nombre_consultorio' no existe en la configuración</span><br>";
}

echo "<h4>3. Simulando lógica del header:</h4>";

// Simular la misma lógica que usa el header
if (function_exists('getNombreConsultorio')) {
    echo "<strong>Función getNombreConsultorio existe:</strong> <span style='color: green;'>✅ Sí</span><br>";
    try {
        $nombreConsultorio = getNombreConsultorio($configHeader);
        echo "<strong>Resultado de getNombreConsultorio():</strong> '" . htmlspecialchars($nombreConsultorio) . "'<br>";
    } catch (Exception $e) {
        echo "<span style='color: red;'>Error al llamar getNombreConsultorio(): " . $e->getMessage() . "</span><br>";
    }
} else {
    echo "<strong>Función getNombreConsultorio existe:</strong> <span style='color: orange;'>❌ No</span><br>";
    echo "<strong>Usando lógica alternativa...</strong><br>";
    
    $nombreConsultorio = isset($configHeader['nombre_consultorio']) && 
                        $configHeader['nombre_consultorio'] !== null && 
                        $configHeader['nombre_consultorio'] !== '' 
                        ? $configHeader['nombre_consultorio'] 
                        : 'Consultorio Médico';
                        
    echo "<strong>Resultado de lógica alternativa:</strong> '" . htmlspecialchars($nombreConsultorio) . "'<br>";
}

echo "<h4>4. Verificando estructura de tabla:</h4>";
try {
    $host = 'localhost';
    $dbname = 'consultorio_medico';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar si la tabla existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'configuracion'");
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "<strong>Tabla 'configuracion' existe:</strong> <span style='color: green;'>✅ Sí</span><br>";
        
        // Verificar columnas
        $stmt = $pdo->query("DESCRIBE configuracion");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<strong>Columnas de la tabla:</strong><br>";
        foreach ($columns as $column) {
            $isNombreConsultorio = $column['Field'] === 'nombre_consultorio' ? ' <-- ¡AQUÍ!' : '';
            echo "- " . $column['Field'] . " (" . $column['Type'] . ")" . $isNombreConsultorio . "<br>";
        }
        
        // Verificar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM configuracion");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<strong>Total de registros:</strong> " . $count['total'] . "<br>";
        
        if ($count['total'] > 0) {
            $stmt = $pdo->query("SELECT id, nombre_consultorio FROM configuracion LIMIT 5");
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<strong>Primeros registros (id, nombre_consultorio):</strong><br>";
            foreach ($records as $record) {
                echo "- ID: " . $record['id'] . " | Nombre: '" . htmlspecialchars($record['nombre_consultorio'] ?? 'NULL') . "'<br>";
            }
        }
        
    } else {
        echo "<strong>Tabla 'configuracion' existe:</strong> <span style='color: red;'>❌ No</span><br>";
    }
    
} catch (Exception $e) {
    echo "<span style='color: red;'>Error verificando estructura: " . $e->getMessage() . "</span><br>";
}

echo "<h4>5. Resultado final esperado:</h4>";
echo "<div style='border: 2px solid #007bff; padding: 10px; background: #f8f9fa;'>";
echo "<strong>Nombre que debería mostrar el header:</strong> <span style='font-size: 18px; color: #007bff;'>" . htmlspecialchars($nombreConsultorio ?? 'ERROR') . "</span>";
echo "</div>";
?>
