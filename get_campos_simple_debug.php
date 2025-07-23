<?php
// Endpoint simplificado y con debug mejorado
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Habilitar display de errores para debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // Log inicial
    error_log("=== get_campos_simple_debug.php iniciado ===");
    
    require_once "config.php";
    error_log("Config.php cargado exitosamente");
      // Test básico de conexión para MySQL
    $test = $conn->query("SELECT 1 as test")->fetch(PDO::FETCH_ASSOC);
    if (!$test || $test['test'] != 1) {
        throw new Exception("Test de conexión falló - Respuesta: " . json_encode($test));
    }
    error_log("Conexión a BD verificada");
    
    // Obtener configuración
    $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log("Configuración obtenida: " . json_encode($config));
    
    $campos = [];
    
    if ($config && $config['especialidad_id']) {
        error_log("Especialidad configurada: " . $config['especialidad_id']);
        
        // Buscar campos (query simplificada)
        $stmt = $conn->prepare("
            SELECT nombre_campo, etiqueta, tipo_campo, opciones, requerido 
            FROM especialidad_campos 
            WHERE especialidad_id = ? 
            ORDER BY orden
        ");
        $stmt->execute([$config['especialidad_id']]);
        $campos_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Campos encontrados en BD: " . count($campos_db));
        
        // Procesar campos
        foreach ($campos_db as $campo) {
            $tipo = $campo['tipo_campo'];
            
            // Convertir tipos
            switch ($tipo) {
                case 'texto': $tipo = 'text'; break;
                case 'numero': $tipo = 'number'; break;
                case 'fecha': $tipo = 'date'; break;
                case 'seleccion': $tipo = 'select'; break;
                case 'textarea': $tipo = 'textarea'; break;
                case 'checkbox': $tipo = 'checkbox'; break;
                default: $tipo = 'text'; break;
            }
            
            $opciones = null;
            if (!empty($campo['opciones'])) {
                $opciones = explode(',', trim($campo['opciones']));
                $opciones = array_map('trim', $opciones);
            }
            
            $campos[$campo['nombre_campo']] = [
                'label' => $campo['etiqueta'],
                'tipo' => $tipo,
                'requerido' => (bool)$campo['requerido'],
                'opciones' => $opciones
            ];
        }
        
        error_log("Campos procesados: " . count($campos));
    }
    
    // Si no hay campos, usar de prueba
    if (empty($campos)) {
        error_log("Usando campos de prueba");
        $campos = [
            'temperatura' => [
                'label' => 'Temperatura (°C)',
                'tipo' => 'number',
                'requerido' => true
            ],
            'observaciones_especialidad' => [
                'label' => 'Observaciones de la Especialidad',
                'tipo' => 'textarea',
                'requerido' => false
            ]
        ];
    }
    
    $response = [
        'success' => true,
        'campos' => $campos,
        'debug_info' => [
            'especialidad_id' => $config['especialidad_id'] ?? null,
            'campos_count' => count($campos),
            'timestamp' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION
        ]
    ];
    
    error_log("Respuesta generada exitosamente");
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("ERROR en get_campos_simple_debug.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Respuesta de error con campos básicos
    $error_response = [
        'success' => false,
        'error' => $e->getMessage(),
        'campos' => [
            'temperatura' => [
                'label' => 'Temperatura (°C)',
                'tipo' => 'number',
                'requerido' => true
            ]
        ]
    ];
    
    echo json_encode($error_response);
} catch (Throwable $t) {
    error_log("FATAL ERROR: " . $t->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error fatal del servidor',
        'campos' => []
    ]);
}
?>
