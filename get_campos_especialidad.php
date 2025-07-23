<?php
require_once "config.php";
require_once "permissions.php";

// Para debugging
error_log("Iniciando get_campos_especialidad.php");

header('Content-Type: application/json');

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Método no permitido: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar que se haya enviado el ID del doctor
if (!isset($_POST['doctor_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID del doctor no proporcionado']);
    exit;
}

try {
    error_log("Obteniendo especialidad configurada");
    
    // Obtener la especialidad configurada para el consultorio
    $stmt = $conn->prepare("
        SELECT c.especialidad_id 
        FROM configuracion c 
        WHERE c.id = 1
    ");
    
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Especialidad de configuración: " . ($result['especialidad_id'] ?? 'no encontrada'));

    if ($result && $result['especialidad_id']) {
        // Obtener los campos de la especialidad desde la tabla especialidad_campos
        $stmt = $conn->prepare("
            SELECT nombre_campo, etiqueta as label, 
                   CASE 
                     WHEN tipo_campo = 'texto' THEN 'text'
                     WHEN tipo_campo = 'numero' THEN 'number'
                     WHEN tipo_campo = 'fecha' THEN 'date'
                     WHEN tipo_campo = 'seleccion' THEN 'select'
                     WHEN tipo_campo = 'checkbox' THEN 'checkbox'
                     WHEN tipo_campo = 'textarea' THEN 'textarea'
                     ELSE tipo_campo
                   END as tipo,
                   opciones, requerido
            FROM especialidad_campos 
            WHERE especialidad_id = ? AND estado = 'activo'
            ORDER BY orden
        ");
        
        $stmt->execute([$result['especialidad_id']]);
        $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Campos encontrados: " . count($campos));
        
        // Si no hay campos, usar datos de prueba
        if (empty($campos)) {
            error_log("No se encontraron campos. Usando datos de prueba");
            
            // Respuesta de prueba
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
                    'notas_adicionales' => [
                        'label' => 'Notas Adicionales',
                        'tipo' => 'textarea',
                        'requerido' => false
                    ],
                    'tipo_consulta' => [
                        'label' => 'Tipo de Consulta',
                        'tipo' => 'select',
                        'requerido' => true,
                        'opciones' => ['Primera vez', 'Seguimiento', 'Urgencia', 'Control']
                    ],
                    'requiere_seguimiento' => [
                        'label' => 'Requiere seguimiento',
                        'tipo' => 'checkbox',
                        'requerido' => false
                    ]
                ],
                'message' => 'Usando datos de prueba porque no se encontraron campos'
            ]);
            exit;
        }
        
        // Formatear los campos para la respuesta JSON
        $campos_formateados = [];
        foreach($campos as $campo) {
            $opciones = null;
            if (!empty($campo['opciones'])) {
                $opciones = explode(',', $campo['opciones']);
            }
            
            $campos_formateados[$campo['nombre_campo']] = [
                'label' => $campo['label'],
                'tipo' => $campo['tipo'],
                'requerido' => (bool)$campo['requerido'],
                'opciones' => $opciones
            ];
        }
        
        // Devolver los campos de la especialidad
        echo json_encode([
            'success' => true,
            'campos' => $campos_formateados
        ]);
    } else {
        error_log("No se encontró especialidad configurada. Usando datos de prueba");
        
        // Respuesta de prueba si no hay especialidad configurada
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
                'notas_adicionales' => [
                    'label' => 'Notas Adicionales',
                    'tipo' => 'textarea',
                    'requerido' => false
                ],
                'tipo_consulta' => [
                    'label' => 'Tipo de Consulta',
                    'tipo' => 'select',
                    'requerido' => true,
                    'opciones' => ['Primera vez', 'Seguimiento', 'Urgencia', 'Control']
                ],
                'requiere_seguimiento' => [
                    'label' => 'Requiere seguimiento',
                    'tipo' => 'checkbox',
                    'requerido' => false
                ]
            ],
            'message' => 'Usando datos de prueba porque no hay especialidad configurada'
        ]);
    }
} catch(PDOException $e) {
    error_log("Error de base de datos: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>
