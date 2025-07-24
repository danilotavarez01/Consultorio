<?php
// Script para crear/insertar datos en la tabla configuracion si no existen
echo "<h3>🔧 Reparar Configuración - Nombre del Consultorio</h3>";

// Incluir configuración de base de datos
require_once __DIR__ . '/config.php';

try {
    // Usar la conexión de config.php
    global $pdo, $conn;
    $conexion = isset($pdo) ? $pdo : $conn;
    
    if (!$conexion) {
        throw new Exception("No se pudo obtener la conexión a la base de datos");
    }
    
    echo "<div style='color: green;'>✅ Conexión a base de datos exitosa (usando config.php)</div>";
    echo "<div style='color: blue;'>📊 Base de datos: " . DB_NAME . "</div>";
    
    // 1. Verificar si la tabla configuracion existe
    $stmt = $conexion->query("SHOW TABLES LIKE 'configuracion'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "<div style='color: red;'>❌ La tabla 'configuracion' no existe</div>";
        echo "<p>Por favor ejecuta primero los scripts de creación de base de datos.</p>";
        exit;
    }
    
    echo "<div style='color: green;'>✅ La tabla 'configuracion' existe</div>";
    
    // 2. Verificar si la columna nombre_consultorio existe
    $stmt = $conexion->query("SHOW COLUMNS FROM configuracion LIKE 'nombre_consultorio'");
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        echo "<div style='color: orange;'>⚠️ La columna 'nombre_consultorio' no existe. Creándola...</div>";
        
        $conexion->exec("ALTER TABLE configuracion ADD COLUMN nombre_consultorio VARCHAR(255) DEFAULT 'Consultorio Médico'");
        echo "<div style='color: green;'>✅ Columna 'nombre_consultorio' creada exitosamente</div>";
    } else {
        echo "<div style='color: green;'>✅ La columna 'nombre_consultorio' existe</div>";
    }
    
    // 3. Verificar si existe el registro con id = 1
    $stmt = $conexion->prepare("SELECT COUNT(*) as count FROM configuracion WHERE id = 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        echo "<div style='color: orange;'>⚠️ No existe registro con id = 1. Creándolo...</div>";
        
        // Insertar registro base
        $stmt = $conexion->prepare("INSERT INTO configuracion (id, nombre_consultorio) VALUES (1, ?)");
        $nombreDefault = "Mi Consultorio Médico"; // Puedes cambiar este nombre
        $stmt->execute([$nombreDefault]);
        
        echo "<div style='color: green;'>✅ Registro creado con nombre: '$nombreDefault'</div>";
    } else {
        echo "<div style='color: green;'>✅ El registro con id = 1 existe</div>";
        
        // Verificar si tiene nombre_consultorio
        $stmt = $conexion->prepare("SELECT nombre_consultorio FROM configuracion WHERE id = 1");
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($config && ($config['nombre_consultorio'] === null || $config['nombre_consultorio'] === '')) {
            echo "<div style='color: orange;'>⚠️ El campo 'nombre_consultorio' está vacío. Actualizándolo...</div>";
            
            $stmt = $conexion->prepare("UPDATE configuracion SET nombre_consultorio = ? WHERE id = 1");
            $nombreDefault = "Mi Consultorio Médico";
            $stmt->execute([$nombreDefault]);
            
            echo "<div style='color: green;'>✅ Campo actualizado con: '$nombreDefault'</div>";
        } else {
            echo "<div style='color: green;'>✅ El campo 'nombre_consultorio' tiene valor: '" . htmlspecialchars($config['nombre_consultorio']) . "'</div>";
        }
    }
    
    // 4. Verificación final
    echo "<hr>";
    echo "<h4>🔍 Verificación Final:</h4>";
    
    $stmt = $conexion->prepare("SELECT * FROM configuracion WHERE id = 1");
    $stmt->execute();
    $finalConfig = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($finalConfig) {
        echo "<div style='color: green;'>✅ Configuración final obtenida correctamente</div>";
        echo "<strong>Nombre del consultorio:</strong> <span style='font-size: 18px; color: #007bff;'>" . 
             htmlspecialchars($finalConfig['nombre_consultorio']) . "</span><br>";
        
        echo "<h5>📋 Todos los campos de configuración:</h5>";
        echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
        foreach ($finalConfig as $key => $value) {
            echo "<strong>$key:</strong> " . htmlspecialchars($value ?? 'NULL') . "<br>";
        }
        echo "</div>";
    } else {
        echo "<div style='color: red;'>❌ Error: No se pudo obtener la configuración</div>";
    }
    
    echo "<hr>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;'>";
    echo "<h5>✅ ¡Proceso Completado!</h5>";
    echo "<p>Ahora puedes:</p>";
    echo "<ul>";
    echo "<li><a href='test_header_completo.php'>Probar el header nuevamente</a></li>";
    echo "<li><a href='configuracion.php'>Ir a configuración para cambiar el nombre</a></li>";
    echo "<li>El header debería mostrar ahora el nombre desde la base de datos</li>";
    echo "</ul>";
    echo "</div>";
    
    // 5. Opcional: Permitir cambiar el nombre desde aquí
    echo "<hr>";
    echo "<h4>🎯 Cambiar Nombre del Consultorio:</h4>";
    echo "<form method='POST' style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo "<div style='margin-bottom: 10px;'>";
    echo "<label for='nuevo_nombre'><strong>Nuevo nombre del consultorio:</strong></label><br>";
    echo "<input type='text' id='nuevo_nombre' name='nuevo_nombre' value='" . 
         htmlspecialchars($finalConfig['nombre_consultorio']) . "' style='width: 100%; padding: 8px; margin-top: 5px;'>";
    echo "</div>";
    echo "<button type='submit' name='actualizar' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer;'>Actualizar Nombre</button>";
    echo "</form>";
    
    // Procesar actualización si se envió el formulario
    if (isset($_POST['actualizar']) && isset($_POST['nuevo_nombre'])) {
        $nuevoNombre = trim($_POST['nuevo_nombre']);
        if (!empty($nuevoNombre)) {
            $stmt = $conexion->prepare("UPDATE configuracion SET nombre_consultorio = ? WHERE id = 1");
            $stmt->execute([$nuevoNombre]);
            
            echo "<div style='color: green; margin-top: 10px;'>✅ Nombre actualizado a: '$nuevoNombre'</div>";
            echo "<script>setTimeout(() => location.reload(), 1000);</script>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Error: " . $e->getMessage() . "</div>";
    echo "<p>Verifica que la base de datos esté funcionando correctamente.</p>";
}
?>
