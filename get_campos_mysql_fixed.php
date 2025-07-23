<?php
// Endpoint corregido para MySQL - Actualizado para resolver error XML
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Habilitar display de errores para debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // Log inicial
    error_log("=== get_campos_mysql_fixed.php iniciado ===");
    
    // Configuración específica para MySQL
    define('DB_SERVER', 'localhost');
    define('DB_PORT', 3306);
    define('DB_NAME', 'consultorio');
    define('DB_USER', 'root');
    define('DB_PASS', '820416Dts');
    
    // Conexión a MySQL con manejo de errores mejorado
    $dsn = "mysql:host=" . DB_SERVER . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8";
    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_TIMEOUT => 10,
        PDO::ATTR_PERSISTENT => false
    );
    
    $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
    error_log("Conexión MySQL establecida exitosamente");
    
    // Test básico de conexión específico para MySQL
    $test = $conn->query("SELECT 1 as test")->fetch(PDO::FETCH_ASSOC);
    if (!$test || intval($test['test']) !== 1) {
        throw new Exception("Test de conexión MySQL falló - Respuesta: " . json_encode($test));
    }
    error_log("Test de conexión MySQL verificado");
    
    // Verificar que las tablas existan
    $tables_check = $conn->query("SHOW TABLES LIKE 'configuracion'")->fetch();
    if (!$tables_check) {
        throw new Exception("Tabla 'configuracion' no existe en la base de datos MySQL");
    }
    
    // Obtener configuración
    $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = ?");
    $stmt->execute([1]);
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log("Configuración obtenida: " . json_encode($config));
    
    $campos = [];
    
    if ($config && $config['especialidad_id']) {
        error_log("Especialidad configurada: " . $config['especialidad_id']);
        
        // Verificar tabla especialidad_campos
        $tables_check = $conn->query("SHOW TABLES LIKE 'especialidad_campos'")->fetch();
        if (!$tables_check) {
            throw new Exception("Tabla 'especialidad_campos' no existe");
        }
        
        // Buscar campos (query optimizada para MySQL)
        $stmt = $conn->prepare("
            SELECT nombre_campo, etiqueta, tipo_campo, opciones, requerido 
            FROM especialidad_campos 
            WHERE especialidad_id = ? 
            ORDER BY orden ASC
        ");
        $stmt->execute([$config['especialidad_id']]);
        $campos_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Campos encontrados en MySQL: " . count($campos_db));
        
        // Procesar campos para MySQL
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
        
        error_log("Campos procesados para MySQL: " . count($campos));
    } else {
        error_log("No hay especialidad configurada en MySQL");
    }
    
    // Si no hay campos, usar de prueba
    if (empty($campos)) {
        error_log("Usando campos de prueba para MySQL");
        $campos = [
            'temperatura' => [
                'label' => 'Temperatura (°C)',
                'tipo' => 'number',
                'requerido' => true
            ],
            'presion_arterial' => [
                'label' => 'Presión Arterial',
                'tipo' => 'text',
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
            'database_type' => 'MySQL',
            'server_info' => $conn->getAttribute(PDO::ATTR_SERVER_VERSION)
        ]
    ];
    
    error_log("Respuesta MySQL generada exitosamente");
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    error_log("ERROR PDO MySQL: " . $e->getMessage());
    error_log("Código de error: " . $e->getCode());
    
    $error_response = [
        'success' => false,
        'error' => 'Error de base de datos MySQL: ' . $e->getMessage(),
        'error_code' => $e->getCode(),
        'campos' => [
            'temperatura' => [
                'label' => 'Temperatura (°C)',
                'tipo' => 'number',
                'requerido' => true
            ]
        ]
    ];
    
    echo json_encode($error_response);
    
} catch (Exception $e) {
    error_log("ERROR MySQL: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
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
}
?>
