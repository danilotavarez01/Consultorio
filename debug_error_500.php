<?php
// Script de debug para identificar el error 500
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Debug del Error 500</h1>";

try {
    echo "<h2>1. Verificando config.php...</h2>";
    require_once "config.php";
    echo "✅ config.php cargado correctamente<br>";
    
    echo "<h2>2. Verificando conexión a base de datos...</h2>";
    if (isset($conn)) {
        echo "✅ Variable \$conn existe<br>";
        
        // Test de conexión simple
        $stmt = $conn->query("SELECT 1 as test");
        $result = $stmt->fetch();
        
        if ($result['test'] == 1) {
            echo "✅ Conexión a base de datos funciona<br>";
        }
    } else {
        echo "❌ Variable \$conn no existe<br>";
    }
    
    echo "<h2>3. Verificando tabla configuracion...</h2>";
    $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        echo "✅ Configuración encontrada: especialidad_id = " . $config['especialidad_id'] . "<br>";
        
        if ($config['especialidad_id']) {
            echo "<h2>4. Verificando especialidad...</h2>";
            $stmt = $conn->prepare("SELECT id, nombre FROM especialidades WHERE id = ?");
            $stmt->execute([$config['especialidad_id']]);
            $especialidad = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($especialidad) {
                echo "✅ Especialidad encontrada: " . $especialidad['nombre'] . "<br>";
                
                echo "<h2>5. Verificando campos de especialidad...</h2>";
                $stmt = $conn->prepare("
                    SELECT nombre_campo, etiqueta, tipo_campo, opciones, requerido 
                    FROM especialidad_campos 
                    WHERE especialidad_id = ? 
                    ORDER BY orden
                ");
                $stmt->execute([$config['especialidad_id']]);
                $campos_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "✅ Campos encontrados: " . count($campos_db) . "<br>";
                
                if (count($campos_db) > 0) {
                    echo "<h2>6. Simulando el endpoint...</h2>";
                    
                    $campos = [];
                    foreach ($campos_db as $campo) {
                        $tipo = $campo['tipo_campo'];
                        
                        // Convertir tipos de DB a tipos HTML
                        switch ($tipo) {
                            case 'texto': $tipo = 'text'; break;
                            case 'numero': $tipo = 'number'; break;
                            case 'fecha': $tipo = 'date'; break;
                            case 'seleccion': $tipo = 'select'; break;
                            case 'textarea': $tipo = 'textarea'; break;
                            case 'checkbox': $tipo = 'checkbox'; break;
                        }
                        
                        $opciones = null;
                        if (!empty($campo['opciones'])) {
                            $opciones = explode(',', $campo['opciones']);
                        }
                        
                        $campos[$campo['nombre_campo']] = [
                            'label' => $campo['etiqueta'],
                            'tipo' => $tipo,
                            'requerido' => (bool)$campo['requerido'],
                            'opciones' => $opciones
                        ];
                    }
                    
                    $response = [
                        'success' => true,
                        'campos' => $campos
                    ];
                    
                    echo "✅ Respuesta simulada generada correctamente<br>";
                    echo "<h3>JSON de respuesta:</h3>";
                    echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                    
                } else {
                    echo "⚠️ No hay campos configurados para esta especialidad<br>";
                    echo "<p><a href='configurar_especialidades_completas.php'>🔧 Configurar Especialidades</a></p>";
                }
                
            } else {
                echo "❌ Especialidad no encontrada en la tabla especialidades<br>";
            }
        } else {
            echo "❌ No hay especialidad_id configurada<br>";
        }
    } else {
        echo "❌ No hay configuración en la tabla configuracion<br>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ ERROR DETECTADO:</h2>";
    echo "<div style='background: #ffe6e6; padding: 10px; border: 1px solid #ff9999; border-radius: 5px;'>";
    echo "<strong>Mensaje:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
    echo "<strong>Trace:</strong><br><pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "<h2>7. Test directo del endpoint get_campos_simple.php:</h2>";
echo "<p><a href='get_campos_simple.php' target='_blank'>🔗 Abrir get_campos_simple.php</a></p>";
?>
