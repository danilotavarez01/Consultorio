<?php
// Endpoint simple para campos de especialidad
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Log para debugging
error_log("get_campos_simple.php - Iniciando");

try {
    require_once "config.php";
    
    // Obtener la especialidad configurada
    $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $campos = [];
    
    if ($config && $config['especialidad_id']) {
        error_log("Especialidad configurada: " . $config['especialidad_id']);
          // Buscar campos de la especialidad (sin filtro de estado)
        $stmt = $conn->prepare("
            SELECT nombre_campo, etiqueta, tipo_campo, opciones, requerido 
            FROM especialidad_campos 
            WHERE especialidad_id = ? 
            ORDER BY orden
        ");        $stmt->execute([$config['especialidad_id']]);
        $campos_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Campos encontrados en DB: " . count($campos_db));
        if (count($campos_db) > 0) {
            error_log("Primer campo: " . json_encode($campos_db[0]));
        }
        
        // Formatear campos
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
    }
    
    // Si no hay campos en DB, usar campos de prueba
    if (empty($campos)) {
        error_log("No hay campos en DB, usando campos de prueba");
        
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
            ],
            'tipo_consulta' => [
                'label' => 'Tipo de Consulta',
                'tipo' => 'select',
                'requerido' => true,
                'opciones' => ['Primera vez', 'Seguimiento', 'Control', 'Urgencia']
            ],
            'requiere_cita_seguimiento' => [
                'label' => 'Requiere cita de seguimiento',
                'tipo' => 'checkbox',
                'requerido' => false
            ]
        ];
    }
      $response = [
        'success' => true,
        'campos' => $campos,
        'debug' => [
            'especialidad_id' => $config['especialidad_id'] ?? null,
            'campos_count' => count($campos),
            'source' => empty($campos_db) ? 'prueba' : 'database',
            'campos_db_count' => isset($campos_db) ? count($campos_db) : 0,
            'query_executed' => isset($campos_db),
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ];
    
    error_log("Respuesta: " . json_encode($response));
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error en get_campos_simple.php: " . $e->getMessage());
    
    // En caso de error, devolver campos de prueba
    echo json_encode([
        'success' => true,
        'campos' => [
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
        ],
        'error' => $e->getMessage()
    ]);
}
?>
