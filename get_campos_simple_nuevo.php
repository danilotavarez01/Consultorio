<?php
// Archivo simplificado para obtener campos de especialidad sin errores XML
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Evitar cualquier salida antes del JSON
ob_clean(); // Limpiar cualquier buffer de salida

// Cargar configuración
require_once "config.php";

try {
    // Obtener ID de la especialidad desde la configuración
    $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    $especialidad_id = $config['especialidad_id'] ?? null;
    
    $campos = [];
    
    // Si hay especialidad configurada, buscar sus campos
    if ($especialidad_id) {
        $stmt = $conn->prepare("
            SELECT nombre_campo, etiqueta, tipo_campo, opciones, requerido 
            FROM especialidad_campos 
            WHERE especialidad_id = ? 
            ORDER BY orden ASC
        ");
        $stmt->execute([$especialidad_id]);
        $campos_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Procesar campos
        foreach ($campos_db as $campo) {
            // Prefijo para evitar conflictos con los campos base
            $nombre_campo = 'campo_' . $campo['nombre_campo'];
            
            // Convertir tipo interno a tipo HTML
            $tipo_html = 'text';
            switch ($campo['tipo_campo']) {
                case 'numero': $tipo_html = 'number'; break;
                case 'fecha': $tipo_html = 'date'; break;
                case 'textarea': $tipo_html = 'textarea'; break;
                case 'checkbox': $tipo_html = 'checkbox'; break;
                case 'seleccion': $tipo_html = 'select'; break;
            }
            
            // Procesar opciones para select
            $opciones = [];
            if ($tipo_html === 'select' && !empty($campo['opciones'])) {
                $opciones = array_map('trim', explode(',', $campo['opciones']));
            }
            
            // Añadir al array de campos
            $campos[$nombre_campo] = [
                'label' => $campo['etiqueta'],
                'tipo' => $tipo_html,
                'requerido' => (bool)$campo['requerido'],
                'opciones' => $opciones
            ];
        }
    }
    
    // Si no hay campos, usar algunos de ejemplo
    if (empty($campos)) {
        $campos = [
            'campo_temperatura' => [
                'label' => 'Temperatura (°C)',
                'tipo' => 'number',
                'requerido' => true,
                'opciones' => []
            ],
            'campo_saturacion' => [
                'label' => 'Saturación de Oxígeno (%)',
                'tipo' => 'number',
                'requerido' => false,
                'opciones' => []
            ],
            'campo_notas_adicionales' => [
                'label' => 'Notas Adicionales',
                'tipo' => 'textarea',
                'requerido' => false,
                'opciones' => []
            ]
        ];
    }
    
    // Preparar respuesta
    $response = [
        'success' => true,
        'campos' => $campos,
        'info' => [
            'especialidad_id' => $especialidad_id,
            'campos_count' => count($campos)
        ]
    ];
    
    // Enviar respuesta
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Error
    $response = [
        'success' => false,
        'error' => $e->getMessage(),
        'campos' => [
            'campo_error' => [
                'label' => 'Error al cargar campos',
                'tipo' => 'text',
                'requerido' => false,
                'opciones' => []
            ]
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}
?>
