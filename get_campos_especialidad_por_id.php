<?php
// Endpoint para obtener campos de especialidad por ID
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Log para debugging
error_log("get_campos_especialidad_por_id.php - Iniciando");

try {
    require_once "config.php";
    
    // Verificar que se envió el ID de especialidad
    if (!isset($_GET['especialidad_id']) || empty($_GET['especialidad_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de especialidad no proporcionado'
        ]);
        exit;
    }
    
    $especialidad_id = (int)$_GET['especialidad_id'];
    error_log("Cargando campos para especialidad ID: " . $especialidad_id);
    
    // Verificar que la especialidad existe
    $stmt = $conn->prepare("SELECT id, codigo, nombre FROM especialidades WHERE id = ? AND estado = 'activo'");
    $stmt->execute([$especialidad_id]);
    $especialidad = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$especialidad) {
        echo json_encode([
            'success' => false,
            'message' => 'Especialidad no encontrada o inactiva'
        ]);
        exit;
    }
    
    // Buscar campos de la especialidad
    $stmt = $conn->prepare("
        SELECT nombre_campo, etiqueta, tipo_campo, opciones, requerido, orden
        FROM especialidad_campos 
        WHERE especialidad_id = ? 
        ORDER BY orden, id
    ");
    $stmt->execute([$especialidad_id]);
    $campos_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Campos encontrados en DB: " . count($campos_db));
    
    $campos = [];
    
    // Formatear campos para el frontend
    foreach ($campos_db as $campo) {
        $tipo = $campo['tipo_campo'];
        
        // Convertir tipos de DB a tipos HTML
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
            $opciones = explode(',', trim($campo['opciones']));
            // Limpiar espacios en blanco
            $opciones = array_map('trim', $opciones);
        }
        
        $campos[$campo['nombre_campo']] = [
            'label' => $campo['etiqueta'],
            'tipo' => $tipo,
            'requerido' => (bool)$campo['requerido'],
            'opciones' => $opciones,
            'orden' => (int)$campo['orden']
        ];
    }
    
    // Si no hay campos específicos, devolver campos básicos por defecto
    if (empty($campos)) {
        error_log("No hay campos específicos, devolviendo campos básicos");
        
        $campos = [
            'observaciones_especialidad' => [
                'label' => 'Observaciones de ' . $especialidad['nombre'],
                'tipo' => 'textarea',
                'requerido' => false,
                'orden' => 1
            ],
            'seguimiento_requerido' => [
                'label' => 'Requiere seguimiento',
                'tipo' => 'checkbox',
                'requerido' => false,
                'orden' => 2
            ]
        ];
    }
    
    $response = [
        'success' => true,
        'campos' => $campos,
        'especialidad' => [
            'id' => $especialidad['id'],
            'codigo' => $especialidad['codigo'],
            'nombre' => $especialidad['nombre']
        ],
        'debug' => [
            'especialidad_id' => $especialidad_id,
            'campos_count' => count($campos),
            'campos_db_count' => count($campos_db),
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ];
    
    error_log("Respuesta exitosa: " . count($campos) . " campos");
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error en get_campos_especialidad_por_id.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => $e->getMessage(),
        'debug' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'file' => __FILE__
        ]
    ]);
}
?>
