<?php
// Endpoint de emergencia para campos dinámicos
// Este archivo siempre devuelve campos predefinidos sin acceder a la base de datos
// ACTUALIZADO: 2025-06-20 - Versión Ultra-resiliente sin dependencias

// Asegurarnos que no hay errores que se muestren
error_reporting(0);
ini_set('display_errors', 0);

// Headers para evitar problemas de CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Log para diagnóstico
$logMessage = "get_campos_emergencia.php ejecutado: " . date('Y-m-d H:i:s');

// Intentar escribir log pero no fallar si no se puede
@file_put_contents(__DIR__ . '/consulta_test_log.txt', $logMessage . "\n", FILE_APPEND);

// Declaramos los campos fijos ampliados que siempre se mostrarán
// independientemente de problemas con la base de datos
$respuesta = [
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
        'frecuencia_cardiaca' => [
            'label' => 'Frecuencia Cardíaca (lpm)',
            'tipo' => 'number',
            'requerido' => false
        ],
        'peso' => [
            'label' => 'Peso (kg)',
            'tipo' => 'number',
            'requerido' => false
        ],
        'altura' => [
            'label' => 'Altura (cm)',
            'tipo' => 'number',
            'requerido' => false
        ],
        'diagnostico' => [
            'label' => 'Diagnóstico',
            'tipo' => 'textarea',
            'requerido' => true
        ],
        'observaciones' => [
            'label' => 'Observaciones',
            'tipo' => 'textarea',
            'requerido' => false
        ]
    ],
    'mensaje' => 'Campos de emergencia cargados correctamente',
    'origen' => 'get_campos_emergencia.php',
    'fecha' => date('Y-m-d H:i:s'),
    'version' => '2.0-emergencia'
];

// Devolver la respuesta
echo json_encode($respuesta);
exit; // Asegurar que el script termine aquí
?>
