<?php
require_once "config.php";

header('Content-Type: application/json');

// Log para debugging
error_log("=== INICIO get_campos_especialidad.php ===");

try {
    // Primero verificar si existen las tablas necesarias
    $stmt = $conn->query("SHOW TABLES LIKE 'configuracion'");
    if ($stmt->rowCount() == 0) {
        error_log("ERROR: Tabla configuracion no existe");
        echo json_encode([
            'success' => false,
            'message' => 'Tabla configuracion no existe'
        ]);
        exit;
    }

    $stmt = $conn->query("SHOW TABLES LIKE 'especialidad_campos'");
    if ($stmt->rowCount() == 0) {
        error_log("ERROR: Tabla especialidad_campos no existe");
        echo json_encode([
            'success' => false,
            'message' => 'Tabla especialidad_campos no existe'
        ]);
        exit;
    }

    // Obtener la especialidad configurada
    $stmt = $conn->query("SELECT especialidad_id FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Configuracion obtenida: " . print_r($config, true));

    if (!$config || !$config['especialidad_id']) {
        error_log("No hay especialidad configurada. Devolviendo campos de prueba");
        
        echo json_encode([
            'success' => true,
            'campos' => [
                'temperatura' => [
                    'label' => 'Temperatura (°C)',
                    'tipo' => 'number',
                    'requerido' => true
                ],
                'tension_arterial' => [
                    'label' => 'Tensión Arterial',
                    'tipo' => 'text',
                    'requerido' => true
                ],
                'notas_especialidad' => [
                    'label' => 'Notas de la Especialidad',
                    'tipo' => 'textarea',
                    'requerido' => false
                ]
            ],
            'debug' => 'Sin especialidad configurada - campos de prueba'
        ]);
        exit;
    }

    // Buscar campos de la especialidad
    $stmt = $conn->prepare("
        SELECT nombre_campo, etiqueta, tipo_campo, opciones, requerido, orden
        FROM especialidad_campos 
        WHERE especialidad_id = ? AND estado = 'activo'
        ORDER BY orden
    ");
    $stmt->execute([$config['especialidad_id']]);
    $campos_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Campos encontrados en DB: " . count($campos_db));
    error_log("Campos: " . print_r($campos_db, true));

    if (empty($campos_db)) {
        error_log("No se encontraron campos en la base de datos. Devolviendo campos de prueba");
        
        echo json_encode([
            'success' => true,
            'campos' => [
                'temperatura' => [
                    'label' => 'Temperatura (°C)',
                    'tipo' => 'number',
                    'requerido' => true
                ],
                'tension_arterial' => [
                    'label' => 'Tensión Arterial',
                    'tipo' => 'text',
                    'requerido' => true
                ],
                'notas_especialidad' => [
                    'label' => 'Notas de la Especialidad',
                    'tipo' => 'textarea',
                    'requerido' => false
                ]
            ],
            'debug' => 'Especialidad ID: ' . $config['especialidad_id'] . ' - Sin campos definidos - campos de prueba'
        ]);
        exit;
    }

    // Formatear campos de la base de datos
    $campos_formateados = [];
    
    foreach ($campos_db as $campo) {
        $tipo = $campo['tipo_campo'];
        
        // Convertir tipos de campo de la DB a tipos HTML
        switch ($tipo) {
            case 'texto':
                $tipo = 'text';
                break;
            case 'numero':
                $tipo = 'number';
                break;
            case 'fecha':
                $tipo = 'date';
                break;
            case 'seleccion':
                $tipo = 'select';
                break;
            case 'checkbox':
                $tipo = 'checkbox';
                break;
            case 'textarea':
                $tipo = 'textarea';
                break;
        }
        
        $opciones = null;
        if (!empty($campo['opciones'])) {
            $opciones = explode(',', $campo['opciones']);
        }
        
        $campos_formateados[$campo['nombre_campo']] = [
            'label' => $campo['etiqueta'],
            'tipo' => $tipo,
            'requerido' => (bool)$campo['requerido'],
            'opciones' => $opciones
        ];
    }

    error_log("Campos formateados: " . print_r($campos_formateados, true));

    echo json_encode([
        'success' => true,
        'campos' => $campos_formateados,
        'debug' => 'Especialidad ID: ' . $config['especialidad_id'] . ' - ' . count($campos_formateados) . ' campos de la DB'
    ]);

} catch (Exception $e) {
    error_log("ERROR en get_campos_especialidad.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => true,
        'campos' => [
            'temperatura' => [
                'label' => 'Temperatura (°C)',
                'tipo' => 'number',
                'requerido' => true
            ],
            'tension_arterial' => [
                'label' => 'Tensión Arterial',
                'tipo' => 'text',
                'requerido' => true
            ],
            'notas_especialidad' => [
                'label' => 'Notas de la Especialidad',
                'tipo' => 'textarea',
                'requerido' => false
            ]
        ],
        'debug' => 'Error: ' . $e->getMessage() . ' - campos de prueba'
    ]);
}

error_log("=== FIN get_campos_especialidad.php ===");
?>
