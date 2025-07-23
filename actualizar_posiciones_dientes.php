<?php
include 'config.php';

// Verificar conexión
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Título de la página
echo "<html><head>";
echo "<title>Actualizar Estructura de Datos de Posiciones de Dientes</title>";
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
echo "<h1 class='mt-3 mb-4'>Actualizar Estructura de Datos de Posiciones de Dientes</h1>";

// Panel de información
echo "<div class='alert alert-info'>";
echo "<h4>Acerca de esta herramienta</h4>";
echo "<p>Este script actualiza la estructura de datos para guardar las posiciones de los dientes en el odontograma. Actualiza:</p>";
echo "<ul>
    <li>Modifica el campo JSON de campos_adicionales para incluir las posiciones</li>
    <li>Corrige registros existentes si es posible</li>
</ul>";
echo "</div>";

// Verificar si se solicitó actualizar
$actualizar = isset($_GET['actualizar']) && $_GET['actualizar'] === '1';

if ($actualizar) {
    echo "<h2>Actualizando estructura de datos...</h2>";
    
    // 1. Primero, verifiquemos los registros existentes que tienen dientes seleccionados
    echo "<div class='debug-panel'>";
    echo "<h3>Paso 1: Verificando registros existentes</h3>";
    
    $sql_check = "SELECT id, dientes_seleccionados, campos_adicionales FROM historial_medico 
                 WHERE dientes_seleccionados IS NOT NULL AND dientes_seleccionados != ''";
    $result_check = $conn->query($sql_check);
    
    if ($result_check && $result_check->rowCount() > 0) {
        $total_records = $result_check->rowCount();
        $records_updated = 0;
        $records_skipped = 0;
        
        echo "<p>Encontrados {$total_records} registros con dientes seleccionados.</p>";
        
        while ($record = $result_check->fetch(PDO::FETCH_ASSOC)) {
            $id = $record['id'];
            $dientes = $record['dientes_seleccionados'];
            $campos_json = $record['campos_adicionales'];
            
            // Decodificar el JSON existente
            $campos_array = json_decode($campos_json, true);
            
            // Si no es un array válido, inicializarlo
            if (!is_array($campos_array)) {
                $campos_array = [];
            }
            
            // Verificar si ya tiene posiciones_dientes
            if (!isset($campos_array['posiciones_dientes'])) {
                // Crear un array de posiciones con valores predeterminados (simular posiciones)
                $posiciones = [];
                $dientes_array = explode(',', $dientes);
                
                // Para cada diente, asignar una posición simulada basada en su número
                foreach ($dientes_array as $diente) {
                    // Limpieza básica del número de diente
                    $diente = trim($diente);
                    if (empty($diente) || !is_numeric($diente)) continue;
                    
                    // Asignar posiciones simuladas según el cuadrante del diente
                    // Estos valores son aproximaciones basadas en la estructura SVG
                    $cuadrante = intval($diente / 10);
                    $posicion = $diente % 10;
                    
                    switch ($cuadrante) {
                        case 1: // Superior derecha
                            $x = 290 - ($posicion * 40);
                            $y = 120;
                            break;
                        case 2: // Superior izquierda
                            $x = 330 + (($posicion - 1) * 40);
                            $y = 120;
                            break;
                        case 3: // Inferior izquierda
                            $x = 330 + ((8 - $posicion) * 40);
                            $y = 320;
                            break;
                        case 4: // Inferior derecha
                            $x = 290 - ((8 - $posicion) * 40);
                            $y = 320;
                            break;
                        default:
                            $x = 0;
                            $y = 0;
                    }
                    
                    $posiciones[$diente] = [
                        'x' => $x,
                        'y' => $y
                    ];
                }
                
                // Agregar las posiciones al array
                $campos_array['posiciones_dientes'] = $posiciones;
                
                // Asegurarse que dientes_seleccionados exista en el JSON
                $campos_array['dientes_seleccionados'] = $dientes;
                
                // Convertir de nuevo a JSON
                $nuevo_json = json_encode($campos_array);
                
                // Actualizar el registro
                $sql_update = "UPDATE historial_medico SET campos_adicionales = ? WHERE id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $result_update = $stmt_update->execute([$nuevo_json, $id]);
                
                if ($result_update) {
                    echo "<div class='alert alert-success'>✓ Registro #$id actualizado correctamente</div>";
                    $records_updated++;
                } else {
                    echo "<div class='alert alert-danger'>✗ Error al actualizar registro #$id</div>";
                    $records_skipped++;
                }
            } else {
                echo "<div class='alert alert-warning'>⚠ Registro #$id ya tiene posiciones_dientes, omitido.</div>";
                $records_skipped++;
            }
        }
        
        echo "<div class='alert alert-info mt-3'>";
        echo "<strong>Resumen:</strong><br>";
        echo "Total registros procesados: $total_records<br>";
        echo "Registros actualizados: $records_updated<br>";
        echo "Registros omitidos: $records_skipped<br>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-warning'>No se encontraron registros con dientes seleccionados para actualizar.</div>";
    }
    
    echo "</div>"; // Fin panel debug
    
    // 2. Instrucciones para actualizar nueva_consulta.php
    echo "<h3 class='mt-4'>Instrucciones de implementación:</h3>";
    echo "<div class='card'>";
    echo "<div class='card-body'>";
    echo "<p>Para completar la implementación, es necesario modificar los siguientes archivos:</p>";
    
    echo "<h5>1. En nueva_consulta.php:</h5>";
    echo "<pre>// Agregar este código para capturar las posiciones de los dientes
// dentro del procesamiento del formulario

// Inicializar array para posiciones de dientes
\$posiciones_dientes = [];

// Si hay dientes seleccionados, procesar sus posiciones
if (!empty(\$_POST['dientes_seleccionados'])) {
    \$dientes_array = explode(',', \$_POST['dientes_seleccionados']);
    
    // Si tenemos las posiciones en POST
    if (isset(\$_POST['posiciones_dientes']) && !empty(\$_POST['posiciones_dientes'])) {
        \$posiciones_json = json_decode(\$_POST['posiciones_dientes'], true);
        if (is_array(\$posiciones_json)) {
            \$posiciones_dientes = \$posiciones_json;
        }
    }
}

// Agregar las posiciones al array de campos adicionales
\$campos_adicionales['posiciones_dientes'] = \$posiciones_dientes;</pre>";
    
    echo "<h5>2. En el JavaScript de nueva_consulta.php:</h5>";
    echo "<pre>// Agregar este código para escuchar el evento personalizado de clic en diente

// Campo oculto para almacenar las posiciones
\$('form').append('&lt;input type=\"hidden\" name=\"posiciones_dientes\" id=\"posiciones_dientes\" value=\"{}\"&gt;');

// Inicializar objeto para almacenar posiciones
let posicionesDientes = {};

// Escuchar el evento personalizado del odontograma
document.addEventListener('dienteClic', function(e) {
    const detalle = e.detail;
    const numero = detalle.numero;
    const posX = detalle.posX;
    const posY = detalle.posY;
    
    // Si el diente está seleccionado, guardamos su posición
    if (detalle.seleccionado) {
        posicionesDientes[numero] = { x: posX, y: posY };
    } else {
        // Si se deseleccionó, eliminamos su posición
        if (posicionesDientes[numero]) {
            delete posicionesDientes[numero];
        }
    }
    
    // Actualizar el campo oculto
    $('#posiciones_dientes').val(JSON.stringify(posicionesDientes));
    console.log('Posiciones actualizadas:', posicionesDientes);
});</pre>";

    echo "<h5>3. En ver_consulta.php:</h5>";
    echo "<pre>// Agregar este código para mostrar las posiciones guardadas
// dentro del bloque donde se muestra la información de dientes

// Mostrar posiciones de dientes si existen
if (isset(\$campos_json['posiciones_dientes']) && !empty(\$campos_json['posiciones_dientes'])) {
    echo \"&lt;h5 class='mt-3'&gt;Posiciones de dientes:&lt;/h5&gt;\";
    echo \"&lt;div class='table-responsive'&gt;\";
    echo \"&lt;table class='table table-sm table-bordered'&gt;\";
    echo \"&lt;thead&gt;&lt;tr&gt;&lt;th&gt;Diente&lt;/th&gt;&lt;th&gt;Posición X&lt;/th&gt;&lt;th&gt;Posición Y&lt;/th&gt;&lt;/tr&gt;&lt;/thead&gt;\";
    echo \"&lt;tbody&gt;\";
    
    foreach (\$campos_json['posiciones_dientes'] as \$diente =&gt; \$posicion) {
        \$x = isset(\$posicion['x']) ? \$posicion['x'] : 'N/A';
        \$y = isset(\$posicion['y']) ? \$posicion['y'] : 'N/A';
        echo \"&lt;tr&gt;&lt;td&gt;\$diente&lt;/td&gt;&lt;td&gt;\$x&lt;/td&gt;&lt;td&gt;\$y&lt;/td&gt;&lt;/tr&gt;\";
    }
    
    echo \"&lt;/tbody&gt;&lt;/table&gt;\";
    echo \"&lt;/div&gt;\";
}</pre>";

    echo "</div>";
    echo "</div>";
    
    echo "<div class='alert alert-success mt-4'>";
    echo "<h4>Próximos pasos</h4>";
    echo "<p>1. Implementa los cambios en los archivos mencionados.</p>";
    echo "<p>2. Prueba la funcionalidad creando una nueva consulta y seleccionando dientes.</p>";
    echo "<p>3. Verifica que las posiciones se guarden correctamente usando la herramienta de diagnóstico.</p>";
    echo "<a href='diagnostico_posiciones_dientes.php' class='btn btn-info'>Ir a herramienta de diagnóstico</a>";
    echo "</div>";

} else {
    // Mostrar información y botón para iniciar la actualización
    echo "<div class='alert alert-warning'>";
    echo "<h4>Atención</h4>";
    echo "<p>Esta herramienta actualizará la estructura de datos para incluir las posiciones de los dientes en el odontograma.</p>";
    echo "<p><strong>Recomendación:</strong> Haga una copia de seguridad de la base de datos antes de continuar.</p>";
    echo "<a href='?actualizar=1' class='btn btn-danger' onclick='return confirm(\"¿Está seguro de querer actualizar la estructura de datos?\")'>Iniciar actualización</a>";
    echo "</div>";
    
    echo "<h3 class='mt-4'>¿Qué hace esta herramienta?</h3>";
    echo "<ol>
        <li>Verifica todos los registros con dientes seleccionados en la tabla historial_medico</li>
        <li>Para cada registro, comprueba si ya tiene posiciones de dientes guardadas</li>
        <li>Si no tiene posiciones, crea posiciones estimadas basadas en los números de los dientes</li>
        <li>Actualiza el campo JSON campos_adicionales para incluir las posiciones</li>
    </ol>";
}

echo "</div>"; // Container
echo "</body></html>";
?>
