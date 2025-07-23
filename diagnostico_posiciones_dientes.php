<?php
include 'config.php';

// Verificar conexión
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Título de la página
echo "<html><head>";
echo "<title>Diagnóstico de Posiciones de Dientes</title>";
echo "<link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>";
echo "<style>
    body { padding: 20px; font-family: Arial, sans-serif; }
    .debug-panel { background: #f8f9fa; border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
    .tooth-pos { display: inline-block; background: #e3f2fd; padding: 5px; margin: 2px; border-radius: 3px; border: 1px solid #90caf9; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow: auto; max-height: 400px; }
    .corrected { background-color: #d4edda; }
    .error { background-color: #f8d7da; }
    .warning { background-color: #fff3cd; }
</style>";
echo "</head><body>";
echo "<div class='container'>";
echo "<h1 class='mt-3 mb-4'>Diagnóstico de Posiciones de Dientes</h1>";

// Panel de información
echo "<div class='debug-panel mb-4'>";
echo "<h3>Información sobre este diagnóstico</h3>";
echo "<p>Esta herramienta verifica y muestra cómo se están guardando las posiciones de los dientes seleccionados en el odontograma.</p>";
echo "<p>Para cada consulta médica, se muestra:</p>";
echo "<ul>
    <li>ID de la consulta y fecha</li>
    <li>Los números de dientes seleccionados</li>
    <li>Las posiciones X/Y de cada diente en el odontograma SVG</li>
    <li>Estado del JSON de campos adicionales</li>
</ul>";
echo "</div>";

// Verificar la estructura de campos_adicionales en consultas existentes
echo "<h2>Análisis de Posiciones de Dientes en Consultas Existentes</h2>";

// Obtener todas las consultas que tienen dientes seleccionados
$sql = "SELECT 
            h.id, 
            h.fecha,
            h.dientes_seleccionados,
            h.campos_adicionales,
            p.nombre AS paciente
        FROM 
            historial_medico h
            INNER JOIN pacientes p ON h.paciente_id = p.id
        WHERE 
            h.dientes_seleccionados IS NOT NULL AND h.dientes_seleccionados != ''
        ORDER BY 
            h.id DESC
        LIMIT 20";

$result = $conn->query($sql);

if ($result && $result->rowCount() > 0) {
    echo "<div class='table-responsive'>";
    echo "<table class='table table-bordered table-hover'>";
    echo "<thead class='thead-light'><tr>
            <th>ID</th>
            <th>Paciente</th>
            <th>Fecha</th>
            <th>Dientes Seleccionados</th>
            <th>Posiciones Guardadas</th>
            <th>Estado JSON</th>
            <th>Acciones</th>
          </tr></thead>";
    echo "<tbody>";
    
    while ($consulta = $result->fetch(PDO::FETCH_ASSOC)) {
        $id = $consulta['id'];
        $fecha = $consulta['fecha'];
        $paciente = htmlspecialchars($consulta['paciente']);
        $dientes_seleccionados = $consulta['dientes_seleccionados'];
        $campos_adicionales = $consulta['campos_adicionales'];
        
        // Decodificar el JSON
        $campos_json = json_decode($campos_adicionales, true);
        
        // Extraer los dientes del JSON
        $dientes_en_json = isset($campos_json['dientes_seleccionados']) ? $campos_json['dientes_seleccionados'] : '';
        
        // Extraer posiciones si existen
        $posiciones_en_json = isset($campos_json['posiciones_dientes']) ? $campos_json['posiciones_dientes'] : [];
        
        echo "<tr>";
        echo "<td>$id</td>";
        echo "<td>$paciente</td>";
        echo "<td>$fecha</td>";
        
        // Mostrar dientes seleccionados
        echo "<td>";
        if (!empty($dientes_seleccionados)) {
            $dientes_array = explode(',', $dientes_seleccionados);
            foreach ($dientes_array as $diente) {
                echo "<span class='tooth-pos'>$diente</span> ";
            }
        } else {
            echo "<span class='text-muted'>Ninguno</span>";
        }
        echo "</td>";
        
        // Mostrar posiciones guardadas
        echo "<td>";
        if (!empty($posiciones_en_json) && is_array($posiciones_en_json)) {
            echo "<ul style='list-style-type:none; padding-left:0'>";
            foreach ($posiciones_en_json as $diente => $posicion) {
                $x = isset($posicion['x']) ? number_format($posicion['x'], 1) : 'N/A';
                $y = isset($posicion['y']) ? number_format($posicion['y'], 1) : 'N/A';
                echo "<li><span class='tooth-pos'>Diente $diente: X=$x, Y=$y</span></li>";
            }
            echo "</ul>";
        } else {
            echo "<span class='text-warning'>No hay posiciones guardadas</span>";
        }
        echo "</td>";
        
        // Estado del JSON
        echo "<td>";
        if ($campos_json) {
            $json_status = "OK";
            $json_class = "text-success";
            
            // Verificar consistencia
            if (isset($campos_json['dientes_seleccionados']) && $campos_json['dientes_seleccionados'] !== $dientes_seleccionados) {
                $json_status = "Inconsistente con columna";
                $json_class = "text-warning";
            }
            
            if (empty($posiciones_en_json)) {
                $json_status .= " (Sin posiciones)";
                $json_class = "text-warning";
            }
            
            echo "<span class='$json_class'>$json_status</span>";
        } else {
            echo "<span class='text-danger'>JSON inválido o no presente</span>";
        }
        echo "</td>";
        
        // Acciones
        echo "<td>";
        echo "<a href='ver_consulta.php?id=$id' class='btn btn-sm btn-info mr-1' target='_blank'>Ver</a>";
        echo "</td>";
        
        echo "</tr>";
    }
    
    echo "</tbody></table>";
    echo "</div>";
} else {
    echo "<div class='alert alert-info'>No se encontraron consultas con dientes seleccionados.</div>";
}

// Formulario para simular una actualización
echo "<h2 class='mt-5'>Simulación de Corrección</h2>";
echo "<div class='card'>";
echo "<div class='card-body'>";
echo "<p>Esta sección simula cómo se guardaría la información con las mejoras implementadas:</p>";

// Crear una simulación de datos
$simulacion = [
    'dientes_seleccionados' => '11,21,24,36,47',
    'posiciones' => [
        '11' => ['x' => 290, 'y' => 120],
        '21' => ['x' => 330, 'y' => 120],
        '24' => ['x' => 450, 'y' => 120],
        '36' => ['x' => 530, 'y' => 420],
        '47' => ['x' => 570, 'y' => 420]
    ]
];

echo "<div class='debug-panel'>";
echo "<h4>Datos de simulación:</h4>";
echo "<pre>" . print_r($simulacion, true) . "</pre>";

// Simulación de procesamiento
$campos_adicionales = [
    'dientes_seleccionados' => $simulacion['dientes_seleccionados'],
    'posiciones_dientes' => $simulacion['posiciones']
];

echo "<h4>JSON resultante:</h4>";
echo "<pre>" . json_encode($campos_adicionales, JSON_PRETTY_PRINT) . "</pre>";
echo "</div>";

echo "</div>";
echo "</div>";

// Información sobre las actualizaciones necesarias
echo "<div class='alert alert-primary mt-5'>";
echo "<h4>Próximos pasos para implementar:</h4>";
echo "<ol>
    <li>Actualizar <code>nueva_consulta.php</code> para capturar y guardar posiciones de dientes</li>
    <li>Modificar <code>odontograma_svg.php</code> para emitir eventos con posiciones exactas</li>
    <li>Crear un visualizador de posiciones en <code>ver_consulta.php</code></li>
</ol>";
echo "</div>";

echo "</div>"; // Container
echo "</body></html>";
?>
