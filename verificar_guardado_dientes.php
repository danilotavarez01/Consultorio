<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Función para limpiar y validar los números de dientes
function limpiarFormatoDientes($valor_dientes) {
    if (empty($valor_dientes)) {
        return '';
    }
    
    // Dividir por comas y limpiar
    $numeros = explode(',', $valor_dientes);
    $numeros_limpios = [];
    
    foreach ($numeros as $num) {
        $num = trim($num);
        // Solo incluir si es un número válido de diente (11-48)
        if (is_numeric($num) && intval($num) >= 11 && intval($num) <= 48) {
            $numeros_limpios[] = $num;
        }
    }
    
    // Ordenar numéricamente y unir con comas
    sort($numeros_limpios, SORT_NUMERIC);
    return implode(',', $numeros_limpios);
}

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Verificador de Guardado de Dientes</title>
    <link rel='stylesheet' href='assets/css/bootstrap.min.css'>
    <style>
        body { padding: 20px; font-family: Arial, sans-serif; }
        .good { background-color: #d4edda; border-color: #c3e6cb; }
        .bad { background-color: #f8d7da; border-color: #f5c6cb; }
        .warn { background-color: #fff3cd; border-color: #ffeeba; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 5px; }
        .consulta-card { margin-bottom: 20px; border: 1px solid #dee2e6; border-radius: 5px; }
        .consulta-header { background-color: #f8f9fa; padding: 10px; border-bottom: 1px solid #dee2e6; }
        .consulta-body { padding: 15px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1 class='mb-4'>Verificador de Guardado de Dientes</h1>
        <div class='alert alert-info'>
            <p>Esta herramienta verifica que los dientes seleccionados se guarden correctamente tanto en la columna dedicada
            como en el campo JSON de campos_adicionales.</p>
        </div>
        
        <div class='card mb-4'>
            <div class='card-header bg-info text-white'>
                <h4>Interpretando mensajes de debug</h4>
            </div>
            <div class='card-body'>
                <p>Cuando veas mensajes de debug como el siguiente:</p>
                <pre style='background:#f8f9fa; padding:15px; border:1px solid #e9ecef;'>
🔧 DEBUG: Procesamiento de campos

Especialidad ID: 26

Array campos_adicionales:

Array
(
    [observa] =&gt; qwerty
    [dientes_seleccionados] =&gt; 
)

JSON campos_adicionales: {&quot;observa&quot;:&quot;qwerty&quot;,&quot;dientes_seleccionados&quot;:&quot;&quot;}

Dientes para columna: ''</pre>
                <p>Esto significa:</p>
                <ul>
                    <li><strong>Campo vacío pero incluido correctamente:</strong> El campo <code>dientes_seleccionados</code> está vacío pero se incluye tanto en el array como en el JSON.</li>
                    <li><strong>Correcto funcionamiento:</strong> Aunque no se seleccionaron dientes (valor vacío), el campo se está guardando correctamente en ambas ubicaciones.</li>
                    <li><strong>Consistencia:</strong> El campo vacío en el JSON coincide con la columna vacía, lo cual es el comportamiento deseado.</li>
                </ul>
                <p class='text-success'><strong>Conclusión:</strong> El sistema está funcionando correctamente. El campo <code>dientes_seleccionados</code> se incluye siempre en el JSON, incluso cuando está vacío.</p>
            </div>
        </div>";

// Obtener las últimas 10 consultas
try {
    $sql = "SELECT h.id, h.fecha, h.dientes_seleccionados, h.campos_adicionales, 
                   p.nombre, p.apellido 
            FROM historial_medico h
            JOIN pacientes p ON h.paciente_id = p.id
            ORDER BY h.id DESC
            LIMIT 10";
            
    $stmt = $conn->query($sql);
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($consultas) > 0) {
        echo "<h3>Últimas {$stmt->rowCount()} consultas:</h3>";
        
        foreach ($consultas as $consulta) {
            echo "<div class='consulta-card'>";
            echo "<div class='consulta-header'>";
            echo "<h5>Consulta #{$consulta['id']} - {$consulta['nombre']} {$consulta['apellido']} - {$consulta['fecha']}</h5>";
            echo "</div>";
            echo "<div class='consulta-body'>";
            
            // Verificar columna de dientes seleccionados
            $dientes_columna = $consulta['dientes_seleccionados'];
            $dientes_columna_existe = isset($consulta['dientes_seleccionados']);
            
            // MEJORADO: Mostrar más detalles sobre el valor
            echo "<p><strong>Dientes seleccionados (columna):</strong> ";
            if ($dientes_columna_existe) {
                if ($dientes_columna === "") {
                    echo "<span class='text-warning'>cadena vacía \"\"</span>";
                } elseif ($dientes_columna === null) {
                    echo "<span class='text-muted'>NULL</span>";
                } elseif (strlen($dientes_columna) > 0) {
                    echo "<span class='text-success'>'" . htmlspecialchars($dientes_columna) . "'</span>";
                } else {
                    echo "<span class='text-muted'>valor desconocido</span>";
                }
                echo " <small class='text-muted'>(longitud: " . strlen($dientes_columna) . ")</small>";
            } else {
                echo "<span class='text-danger'>no definido</span>";
            }
            echo "</p>";
            
            // Verificar dientes en JSON
            $campos_adicionales = json_decode($consulta['campos_adicionales'], true) ?: [];
            $dientes_json_existe = isset($campos_adicionales['dientes_seleccionados']);
            $dientes_json = $dientes_json_existe ? $campos_adicionales['dientes_seleccionados'] : null;
            
            // MEJORADO: Mostrar más detalles sobre el valor en JSON
            echo "<p><strong>Dientes seleccionados (JSON):</strong> ";
            if ($dientes_json_existe) {
                if ($dientes_json === "") {
                    echo "<span class='text-warning'>cadena vacía \"\"</span>";
                } elseif ($dientes_json === null) {
                    echo "<span class='text-muted'>NULL</span>";
                } elseif (strlen($dientes_json) > 0) {
                    echo "<span class='text-success'>'" . htmlspecialchars($dientes_json) . "'</span>";
                } else {
                    echo "<span class='text-muted'>valor desconocido</span>";
                }
                echo " <small class='text-muted'>(longitud: " . strlen($dientes_json) . ")</small>";
            } else {
                echo "<span class='text-danger'>clave no existe en JSON</span>";
            }
            echo "</p>";
            
            // MEJORADO: Verificar coincidencia con lógica más precisa
            $ambos_existen = $dientes_columna_existe && $dientes_json_existe;
            $valores_coinciden = $dientes_columna === $dientes_json;
            $ambos_tienen_valor = !empty($dientes_columna) && !empty($dientes_json);
            $ambos_vacios_pero_existen = $dientes_columna_existe && $dientes_json_existe && 
                                        $dientes_columna === "" && $dientes_json === "";
                                        
            $coinciden = $ambos_existen && $valores_coinciden;
            $alguno_falta = ($dientes_columna_existe && !$dientes_json_existe) || 
                           (!$dientes_columna_existe && $dientes_json_existe);
            $valores_diferentes = $ambos_existen && !$valores_coinciden;
            
            // MEJORADO: Mensajes de estado más claros y precisos
            if ($coinciden && $ambos_tienen_valor) {
                echo "<div class='alert good'><strong>✅ CORRECTO:</strong> Los valores no vacíos coinciden en ambas ubicaciones.</div>";
            } elseif ($coinciden && $ambos_vacios_pero_existen) {
                echo "<div class='alert good'><strong>✅ CORRECTO (VACÍO):</strong> Ambos valores son cadenas vacías pero existen en ambas ubicaciones. Esto significa que no se seleccionaron dientes en el odontograma.</div>";
            } elseif ($alguno_falta) {
                echo "<div class='alert bad'><strong>❌ ERROR:</strong> La clave existe en una ubicación pero no en la otra.</div>";
            } elseif ($valores_diferentes) {
                echo "<div class='alert bad'><strong>❌ ERROR:</strong> Los valores existen pero son diferentes.</div>";
            } elseif (!$dientes_columna_existe && !$dientes_json_existe) {
                echo "<div class='alert warn'><strong>ℹ️ INFO:</strong> No hay dientes seleccionados en ninguna ubicación.</div>";
            } else {
                echo "<div class='alert warn'><strong>⚠️ CASO ESPECIAL:</strong> Situación no clasificada, revisar los valores.</div>";
            }
            
            // Mostrar recomendación según el caso
            if ($dientes_columna_existe && !$dientes_json_existe) {
                echo "<div class='alert warn'><strong>💡 SOLUCIÓN:</strong> Se debe incluir la clave 'dientes_seleccionados' en el JSON.</div>";
            } elseif ($valores_diferentes) {
                echo "<div class='alert warn'><strong>💡 SOLUCIÓN:</strong> El valor del JSON debe ser igual al de la columna.</div>";
            }
            
            // Mostrar JSON completo
            if (!empty($consulta['campos_adicionales'])) {
                echo "<p><strong>JSON completo:</strong></p>";
                echo "<pre>" . htmlspecialchars($consulta['campos_adicionales']) . "</pre>";
                echo "<p><strong>JSON decodificado:</strong></p>";
                echo "<pre>" . print_r($campos_adicionales, true) . "</pre>";
            } else {
                echo "<p><em>No hay datos JSON almacenados</em></p>";
            }
            
            echo "<div class='mt-2'>";
            echo "<a href='ver_consulta.php?id={$consulta['id']}' class='btn btn-sm btn-info mr-2' target='_blank'>Ver Consulta</a>";
            echo "</div>";
            
            // Añadir botón de corrección para consultas con problemas
            if (($dientes_columna_existe && !$dientes_json_existe) || $valores_diferentes) {
                echo "<div class='mt-3'>";
                echo "<form method='post'>";
                echo "<input type='hidden' name='consulta_id' value='{$consulta['id']}'>";
                echo "<input type='hidden' name='accion' value='corregir'>";
                echo "<button type='submit' class='btn btn-warning btn-sm'>Corregir esta consulta</button>";
                echo "</form>";
                echo "</div>";
            }
            
            echo "</div>"; // consulta-body
            echo "</div>"; // consulta-card
        }
    } else {
        echo "<div class='alert alert-warning'>No se encontraron consultas.</div>";
    }
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}

// Procesar solicitud de corrección si existe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] === 'corregir') {
    $consulta_id = $_POST['consulta_id'] ?? 0;
    
    if ($consulta_id > 0) {
        try {
            // 1. Obtener los datos actuales
            $stmt = $conn->prepare("SELECT dientes_seleccionados, campos_adicionales FROM historial_medico WHERE id = ?");
            $stmt->execute([$consulta_id]);
            $datos_actuales = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($datos_actuales) {
                // 2. Actualizar el JSON
                $campos_adicionales = json_decode($datos_actuales['campos_adicionales'], true) ?: [];
                $campos_adicionales['dientes_seleccionados'] = $datos_actuales['dientes_seleccionados'];
                $nuevo_json = json_encode($campos_adicionales);
                
                // 3. Guardar el JSON actualizado
                $stmt = $conn->prepare("UPDATE historial_medico SET campos_adicionales = ? WHERE id = ?");
                $stmt->execute([$nuevo_json, $consulta_id]);
                
                echo "<div class='alert alert-success'><strong>✅ CORREGIDO:</strong> La consulta #$consulta_id ha sido actualizada correctamente.</div>";
            }
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'><strong>❌ ERROR:</strong> " . $e->getMessage() . "</div>";
        }
    }
}

// Procesar corrección masiva si se solicita
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] === 'corregir_todas') {
    try {
        // 1. Seleccionar todas las consultas con dientes_seleccionados pero sin el mismo valor en JSON
        $stmt = $conn->prepare("SELECT id, dientes_seleccionados, campos_adicionales 
                               FROM historial_medico 
                               WHERE dientes_seleccionados IS NOT NULL");
        $stmt->execute();
        $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $corregidas = 0;
        
        foreach ($consultas as $c) {
            $campos_json = json_decode($c['campos_adicionales'], true) ?: [];
            $dientes_json = $campos_json['dientes_seleccionados'] ?? null;
            
            // Si no existe la clave en el JSON o es diferente al valor de la columna
            if (!isset($campos_json['dientes_seleccionados']) || $campos_json['dientes_seleccionados'] !== $c['dientes_seleccionados']) {
                $campos_json['dientes_seleccionados'] = $c['dientes_seleccionados'];
                $nuevo_json = json_encode($campos_json);
                
                $stmt_update = $conn->prepare("UPDATE historial_medico SET campos_adicionales = ? WHERE id = ?");
                $stmt_update->execute([$nuevo_json, $c['id']]);
                $corregidas++;
            }
        }
        
        if ($corregidas > 0) {
            echo "<div class='alert alert-success'><strong>✅ CORRECCIÓN MASIVA COMPLETADA:</strong> Se han corregido $corregidas consultas.</div>";
        } else {
            echo "<div class='alert alert-info'><strong>ℹ️ INFO:</strong> No fue necesario corregir ninguna consulta.</div>";
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'><strong>❌ ERROR:</strong> " . $e->getMessage() . "</div>";
    }
}

echo "<hr>
    <div class='mt-4'>
        <div class='card bg-light mb-3'>
            <div class='card-header'>
                <h4>Acciones</h4>
            </div>
            <div class='card-body'>
                <div class='mb-3'>
                    <h5>Herramientas de corrección:</h5>
                    <form method='post' onsubmit=\"return confirm('¿Estás seguro de que quieres corregir todas las consultas? Esta acción copiará los valores de la columna dientes_seleccionados al JSON.');\">
                        <input type='hidden' name='accion' value='corregir_todas'>
                        <button type='submit' class='btn btn-warning'>Corregir todas las consultas</button>
                        <small class='form-text text-muted'>Esta acción copiará el valor de la columna dientes_seleccionados al JSON para todas las consultas.</small>
                    </form>
                </div>
                <div>
                    <h5>Otras opciones:</h5>
                    <a href='nueva_consulta.php?paciente_id=1' class='btn btn-primary mr-2'>Crear Nueva Consulta de Prueba</a>
                    <a href='test_guardar_json.php' class='btn btn-success mr-2'>Ejecutar Test de Guardado JSON</a>
                    <a href='debug_dientes_json_final.php' class='btn btn-info mr-2'>Diagnóstico Avanzado</a>
                    <a href='index.php' class='btn btn-secondary'>Volver al Inicio</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// Resaltar los valores en JSON
document.addEventListener('DOMContentLoaded', function() {
    const pres = document.querySelectorAll('pre');
    pres.forEach(function(pre) {
        const content = pre.textContent;
        if (content.includes('dientes_seleccionados')) {
            const highlighted = content.replace(/(\"dientes_seleccionados\"\s*:\s*\"[^\"]*\")/g, 
                '<span style=\"background-color: #ffeb3b; padding: 2px;\">$1</span>');
            pre.innerHTML = highlighted;
        }
    });
});
</script>
</body>
</html>";
?>

