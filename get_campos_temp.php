<?php
require_once "config.php";
require_once "permissions.php";

// Respuesta de prueba con campos fijos
header('Content-Type: application/json');
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
    ]
]);
?>
