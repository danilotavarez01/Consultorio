<?php
// Script de respuesta simple para probar el procesamiento JSON y detectar posibles errores
header('Content-Type: application/json');

// Simulamos datos del formulario
$datos_prueba = [
    'observa' => 'valor de prueba',
    'dientes_seleccionados' => '18,17,16'
];

// Procesamiento del JSON como en nueva_consulta.php
$campos_sistema = [
    'action', 'paciente_id', 'doctor_id', 'fecha', 'motivo_consulta', 
    'diagnostico', 'tratamiento', 'observaciones', 'dientes_seleccionados'
];

$campos_adicionales = [];

foreach ($datos_prueba as $key => $value) {
    // Si el campo comienza con 'campo_' es un campo dinámico
    if (strpos($key, 'campo_') === 0) {
        $campo_nombre = substr($key, 6); // Remover el prefijo 'campo_'
        $campos_adicionales[$campo_nombre] = $value;
    }
    // También capturar otros campos que no sean del sistema (como 'observa')
    elseif (!in_array($key, $campos_sistema) && !empty(trim($value))) {
        $campos_adicionales[$key] = $value;
    }
}

// Agregar los dientes seleccionados al array de campos adicionales
if (isset($datos_prueba['dientes_seleccionados']) && !empty($datos_prueba['dientes_seleccionados'])) {
    $campos_adicionales['dientes_seleccionados'] = $datos_prueba['dientes_seleccionados'];
}

// Convertir el array a JSON
$campos_adicionales_json = !empty($campos_adicionales) ? json_encode($campos_adicionales) : null;

// Responder con los resultados
echo json_encode([
    'status' => 'success',
    'datos_originales' => $datos_prueba,
    'campos_adicionales' => $campos_adicionales,
    'json_resultado' => $campos_adicionales_json,
    'json_decodificado' => json_decode($campos_adicionales_json),
    'php_version' => PHP_VERSION
]);
?>
