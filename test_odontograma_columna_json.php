<?php
// Script para diagnosticar y verificar que las consultas están guardando correctamente los dientes en el JSON
// y en la columna dedicada
session_start();
require_once "config.php";

// Función para mostrar valores de forma segura
function mostrarValor($valor) {
    if (is_null($valor)) return '<span style="color:#999;font-style:italic;">NULL</span>';
    if ($valor === '') return '<span style="color:#999;font-style:italic;">Cadena vacía</span>';
    return htmlspecialchars($valor);
}

echo '<html><head>';
echo '<title>Test de Dientes Seleccionados en JSON y Columna</title>';
echo '<link rel="stylesheet" href="assets/css/bootstrap.min.css">';
echo '<style>
    body { padding: 20px; font-family: Arial, sans-serif; }
    .test-section { margin-bottom: 30px; padding: 15px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    pre { background: #f8f9fa; padding: 10px; border-radius: 4px; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th, .data-table td { padding: 8px 12px; text-align: left; border: 1px solid #dee2e6; }
    .data-table th { background: #e9ecef; }
    .highlight { background: #fff3cd; padding: 3px; border-radius: 3px; }
    .success { background: #d4edda; color: #155724; padding: 3px; border-radius: 3px; }
    .fail { background: #f8d7da; color: #721c24; padding: 3px; border-radius: 3px; }
</style>';
echo '</head><body>';
echo '<div class="container">';
echo '<h1>Test de Dientes Seleccionados en JSON y Columna</h1>';
echo '<p class="lead">Este script verifica que los dientes seleccionados se están guardando correctamente tanto en el JSON como en la columna dedicada.</p>';

// 1. Verificar las últimas 10 consultas para ver si tienen dientes guardados
try {
    echo '<div class="test-section bg-light">';
    echo '<h2>Verificación de consultas recientes</h2>';
    
    $stmt = $conn->query("
        SELECT h.id, h.dientes_seleccionados, h.campos_adicionales, 
               h.fecha, p.nombre, p.apellido
        FROM historial_medico h
        JOIN pacientes p ON h.paciente_id = p.id
        ORDER BY h.id DESC
        LIMIT 10
    ");
    
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($consultas) === 0) {
        echo '<div class="alert alert-info">No se encontraron consultas en la base de datos.</div>';
    } else {
        echo '<table class="data-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Paciente</th>';
        echo '<th>Fecha</th>';
        echo '<th>Dientes (columna)</th>';
        echo '<th>Dientes (JSON)</th>';
        echo '<th>JSON completo</th>';
        echo '<th>Estado</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($consultas as $consulta) {
            echo '<tr>';
            echo '<td>' . $consulta['id'] . '</td>';
            echo '<td>' . htmlspecialchars($consulta['nombre'] . ' ' . $consulta['apellido']) . '</td>';
            echo '<td>' . date('d/m/Y', strtotime($consulta['fecha'])) . '</td>';
            
            // Dientes en columna
            echo '<td>' . mostrarValor($consulta['dientes_seleccionados']) . '</td>';
            
            // Dientes en JSON
            $campos_json = json_decode($consulta['campos_adicionales'], true);
            $dientes_json = isset($campos_json['dientes_seleccionados']) ? $campos_json['dientes_seleccionados'] : null;
            echo '<td>' . mostrarValor($dientes_json) . '</td>';
            
            // JSON completo
            echo '<td><small>' . mostrarValor($consulta['campos_adicionales']) . '</small></td>';
            
            // Estado de sincronización
            $tiene_columna = !empty($consulta['dientes_seleccionados']);
            $tiene_json = isset($campos_json['dientes_seleccionados']) && !empty($campos_json['dientes_seleccionados']);
            $sincronizados = $tiene_columna && $tiene_json && $consulta['dientes_seleccionados'] === $dientes_json;
            
            if ($tiene_columna && $tiene_json && $sincronizados) {
                echo '<td><span class="success">✓ Sincronizados</span></td>';
            } elseif (!$tiene_columna && !$tiene_json) {
                echo '<td>No hay dientes</td>';
            } elseif ($tiene_columna && !$tiene_json) {
                echo '<td><span class="fail">✗ Solo en columna</span></td>';
            } elseif (!$tiene_columna && $tiene_json) {
                echo '<td><span class="fail">✗ Solo en JSON</span></td>';
            } else {
                echo '<td><span class="fail">✗ Valores diferentes</span></td>';
            }
            
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
    }
    
    echo '</div>';
    
    // 2. Mostrar un ejemplo de cómo debería guardarse correctamente
    echo '<div class="test-section bg-white">';
    echo '<h2>Ejemplo correcto de guardado</h2>';
    
    $ejemplo_dientes = '18,17,16';
    $ejemplo_array = ['observa' => 'jajaja', 'dientes_seleccionados' => $ejemplo_dientes];
    $ejemplo_json = json_encode($ejemplo_array);
    
    echo '<div class="card mb-3">';
    echo '<div class="card-header">Datos de ejemplo</div>';
    echo '<div class="card-body">';
    echo '<p><strong>Dientes seleccionados (valor):</strong> ' . $ejemplo_dientes . '</p>';
    echo '<p><strong>Campos adicionales (array):</strong></p>';
    echo '<pre>' . print_r($ejemplo_array, true) . '</pre>';
    echo '<p><strong>Campos adicionales (JSON):</strong></p>';
    echo '<pre>' . $ejemplo_json . '</pre>';
    echo '</div>';
    echo '</div>';
    
    echo '<p class="alert alert-info">
        <strong>Nota importante:</strong> Para que funcione correctamente, los dientes seleccionados deben guardarse tanto 
        en la columna <code>dientes_seleccionados</code> como en el campo <code>dientes_seleccionados</code> 
        dentro del JSON de <code>campos_adicionales</code>.
    </p>';
    
    echo '</div>';
    
    // 3. Botón para ir a la página de test de JSON
    echo '<div class="text-center mb-4">';
    echo '<a href="test_guardar_json.php" class="btn btn-primary">Probar guardado de JSON</a>';
    echo ' <a href="nueva_consulta.php?paciente_id=1" class="btn btn-success">Ir a Nueva Consulta</a>';
    echo '</div>';
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">';
    echo '<h3>Error</h3>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
}

echo '</div>';
echo '</body></html>';
?>

