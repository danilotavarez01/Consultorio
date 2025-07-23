<?php
// Script para diagnosticar específicamente el problema de dientes_seleccionados en el JSON
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){            // Función para validar y formatear números de dientes
            function validarNumerosDientes($str) {
                if (empty($str)) return [];
                
                // Dividir por comas y limpiar
                $numeros = array_map('trim', explode(',', $str));
                
                // Filtrar solo números válidos (11-48)
                $validos = [];
                foreach ($numeros as $num) {
                    if (is_numeric($num) && $num >= 11 && $num <= 48) {
                        $validos[] = (int)$num;
                    }
                }
                
                // Eliminar duplicados y ordenar
                $validos = array_unique($validos);
                sort($validos);
                
                return $validos;
            }
            
            // Obtener arrays de números para comparación
            $numeros_columna = validarNumerosDientes($dientes_columna);
            $numeros_json = validarNumerosDientes($dientes_json);
            
            // Verificar si hay problema
            if (!empty($dientes_columna) && !isset($campos['dientes_seleccionados'])) {
                $problema = true;
                $mensaje_problema = 'Dientes presentes en columna pero ausentes en JSON';
            } elseif (!empty($dientes_columna) && empty($dientes_json)) {
                $problema = true;
                $mensaje_problema = 'Dientes presentes en columna pero vacíos en JSON';
            } elseif (!empty($dientes_columna) && $dientes_columna !== $dientes_json) {
                $problema = true;
                $mensaje_problema = 'Valores diferentes en columna y JSON';
                
                // Encontrar diferencias específicas
                $diff_columna = array_diff($numeros_columna, $numeros_json);
                $diff_json = array_diff($numeros_json, $numeros_columna);
                
                if (!empty($diff_columna)) {
                    $mensaje_problema .= "<br>Números en columna pero no en JSON: " . implode(", ", $diff_columna);
                }
                if (!empty($diff_json)) {
                    $mensaje_problema .= "<br>Números en JSON pero no en columna: " . implode(", ", $diff_json);
                }
            }r("location: login.php");
    exit;
}

// Función para colorear JSON
function formatJSON($json) {
    $result = '';
    $level = 0;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;
    $json_length = strlen($json);

    for($i = 0; $i < $json_length; $i++) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if($ends_line_level !== NULL){
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if($in_escape) {
            $in_escape = false;
        } else if($char === '"') {
            $in_quotes = !$in_quotes;
        } else if(!$in_quotes) {
            switch($char) {
                case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;
                case '{': case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;
                case ':':
                    $post = " ";
                    break;
                case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        } else if($char === '\\') {
            $in_escape = true;
        }
        if($new_line_level !== NULL) {
            $result .= "\n".str_repeat("    ", $new_line_level);
        }
        $result .= $char.$post;
    }
    return $result;
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

// Para pruebas y solución
$prueba_correccion = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'probar_correccion' && isset($_POST['consulta_id'])) {
        $prueba_correccion = true;
        $consulta_id = $_POST['consulta_id'];
        
        try {
            // 1. Leer el valor actual
            $stmt = $conn->prepare("SELECT campos_adicionales, dientes_seleccionados FROM historial_medico WHERE id = ?");
            $stmt->execute([$consulta_id]);
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($datos) {
                $campos_adicionales = json_decode($datos['campos_adicionales'], true) ?: [];
                $dientes_columna = $datos['dientes_seleccionados'];
                
                // 2. Actualizar el JSON con los dientes_seleccionados (limpiados y formateados)
                $dientes_limpios = limpiarFormatoDientes($dientes_columna);
                $campos_adicionales['dientes_seleccionados'] = $dientes_limpios;
                $nuevo_json = json_encode($campos_adicionales);
                
                // 3. Guardar el JSON actualizado
                $stmt = $conn->prepare("UPDATE historial_medico SET campos_adicionales = ? WHERE id = ?");
                $stmt->execute([$nuevo_json, $consulta_id]);
                
                $mensaje_exito = "✅ Corrección aplicada correctamente a la consulta #" . $consulta_id;
            } else {
                $mensaje_error = "⚠️ No se encontró la consulta #" . $consulta_id;
            }
        } catch (Exception $e) {
            $mensaje_error = "❌ Error: " . $e->getMessage();
        }
    }
}

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Debug Final - Dientes en JSON</title>
    <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>
    <style>
        body { padding: 20px; font-family: Arial, sans-serif; }
        .json-viewer { 
            font-family: monospace; 
            white-space: pre;
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 5px; 
            max-height: 300px; 
            overflow: auto;
        }
        .json-highlight {
            background-color: #ffc107;
            padding: 0 3px;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .badge-problema { background-color: #dc3545; color: white; }
        .badge-correcto { background-color: #28a745; color: white; }
    </style>
</head>
<body>
    <div class='container'>
        <h1 class='mb-4'>Diagnóstico Final: Dientes en JSON</h1>";

if (isset($mensaje_exito)) {
    echo "<div class='alert alert-success'>" . $mensaje_exito . "</div>";
}

if (isset($mensaje_error)) {
    echo "<div class='alert alert-danger'>" . $mensaje_error . "</div>";
}

echo "<div class='card mb-4'>
        <div class='card-header bg-primary text-white'>
            Análisis del Problema
        </div>
        <div class='card-body'>
            <h5>¿Qué está pasando?</h5>
            <p>El campo <code>dientes_seleccionados</code> no se está incluyendo correctamente en el JSON de <code>campos_adicionales</code>.</p>
            
            <h5>Causas posibles:</h5>
            <ol>
                <li><strong>Clave ausente en el JSON</strong> - No se añade la clave al array <code>campos_adicionales</code>.</li>
                <li><strong>Valor vacío</strong> - La clave existe pero con valor vacío.</li>
                <li><strong>Sincronización incorrecta</strong> - El valor no se captura del odontograma antes del envío.</li>
                <li><strong>Problema en el odontograma</strong> - El odontograma no actualiza correctamente los dientes seleccionados.</li>
            </ol>
            
            <h5>Solución implementada:</h5>
            <ol>
                <li>Forzar la inclusión de la clave <code>dientes_seleccionados</code> en el JSON, incluso si está vacía.</li>
                <li>Mejorar la sincronización entre el odontograma y el campo oculto del formulario.</li>
                <li>Capturar los dientes de todas las fuentes posibles antes del envío.</li>
            </ol>
        </div>
    </div>";

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
            $dientes_columna = $consulta['dientes_seleccionados'] ?? '';
            $json_completo = $consulta['campos_adicionales'] ?? '';
            $campos = json_decode($consulta['campos_adicionales'], true) ?? [];
            $dientes_json = $campos['dientes_seleccionados'] ?? null;
            
            $problema = false;
            $mensaje_problema = '';
            
            // MEJORADO: Verificar si hay problema con mayor detalle
            if (!empty($dientes_columna) && !isset($campos['dientes_seleccionados'])) {
                $problema = true;
                $mensaje_problema = 'Dientes presentes en columna pero ausentes en JSON';
            } elseif (!empty($dientes_columna) && empty($dientes_json)) {
                $problema = true;
                $mensaje_problema = 'Dientes presentes en columna pero vacíos en JSON';
            } elseif (!empty($dientes_columna) && $dientes_columna !== $dientes_json) {
                $problema = true;
                $mensaje_problema = 'Valores diferentes en columna y JSON';
            }
            
            // Analizar el formato de los dientes para verificar que sean números correctos
            if (!empty($dientes_columna)) {
                $numeros_dientes = explode(',', $dientes_columna);
                $son_numeros_validos = true;
                
                // Verificar que todos los valores sean números de dientes válidos (11-48)
                foreach ($numeros_dientes as $num) {
                    $num = trim($num);
                    if (!is_numeric($num) || intval($num) < 11 || intval($num) > 48) {
                        $son_numeros_validos = false;
                        break;
                    }
                }
                
                if (!$son_numeros_validos) {
                    $problema = true;
                    $mensaje_problema = 'El formato de los dientes no es válido. Deben ser números entre 11-48';
                }
            }
            
            // Estilo condicional para la tarjeta
            $card_border = $problema ? 'border-danger' : 'border-success';
            $card_header_class = $problema ? 'bg-danger text-white' : 'bg-success text-white';
            $badge_class = $problema ? 'badge-problema' : 'badge-correcto';
            $badge_text = $problema ? 'PROBLEMA' : 'CORRECTO';
            
            echo "<div class='card $card_border mb-3'>";
            echo "<div class='card-header $card_header_class'>";
            echo "<div class='d-flex justify-content-between align-items-center'>";
            echo "<h5 class='mb-0'>Consulta #{$consulta['id']} - {$consulta['nombre']} {$consulta['apellido']}</h5>";
            echo "<span class='badge $badge_class'>$badge_text</span>";
            echo "</div>";
            echo "</div>";
            
            echo "<div class='card-body'>";
            
            if ($problema) {
                echo "<div class='alert alert-danger'><strong>Problema detectado:</strong> $mensaje_problema</div>";
            }
            
            echo "<div class='row'>";
            
            // Columna izquierda: Valores actuales
            echo "<div class='col-md-6'>";
            echo "<h5>Valores actuales:</h5>";
            
            // MEJORADO: Mostrar dientes with más detalle
            echo "<p><strong>Dientes (columna):</strong> "; 
            if (!empty($dientes_columna)) {
                echo "<span class='text-success'>" . htmlspecialchars($dientes_columna) . "</span>";
                
                // Mostrar visualización de los números de dientes
                echo "<div class='mt-2 mb-3 p-2' style='background:#f8f9fa; border-radius:4px; border:1px solid #dee2e6;'>";
                echo "<small class='text-muted d-block mb-1'>Visualización de dientes:</small>";
                
                $numeros_dientes = explode(',', $dientes_columna);
                foreach ($numeros_dientes as $num) {
                    $num = trim($num);
                    if (is_numeric($num) && intval($num) >= 11 && intval($num) <= 48) {
                        echo "<span class='badge badge-info mr-1 mb-1' style='font-size:14px;'>" . $num . "</span> ";
                    } else {
                        echo "<span class='badge badge-danger mr-1 mb-1' style='font-size:14px;'>" . $num . "?</span> ";
                    }
                }
                echo "</div>";
            } else {
                echo "<span class='text-muted'>(vacío)</span>";
            }
            echo "</p>";
            
            // MEJORADO: Mostrar dientes JSON con más detalle
            echo "<p><strong>Dientes (JSON):</strong> ";
            if (isset($campos['dientes_seleccionados'])) {
                if (!empty($dientes_json)) {
                    echo "<span class='text-success'>" . htmlspecialchars($dientes_json) . "</span>";
                    
                    // Mostrar visualización de los números de dientes
                    echo "<div class='mt-2 mb-3 p-2' style='background:#f8f9fa; border-radius:4px; border:1px solid #dee2e6;'>";
                    echo "<small class='text-muted d-block mb-1'>Visualización de dientes en JSON:</small>";
                    
                    $numeros_dientes = explode(',', $dientes_json);
                    foreach ($numeros_dientes as $num) {
                        $num = trim($num);
                        if (is_numeric($num) && intval($num) >= 11 && intval($num) <= 48) {
                            echo "<span class='badge badge-primary mr-1 mb-1' style='font-size:14px;'>" . $num . "</span> ";
                        } else {
                            echo "<span class='badge badge-danger mr-1 mb-1' style='font-size:14px;'>" . $num . "?</span> ";
                        }
                    }
                    echo "</div>";
                } else {
                    echo "<span class='text-muted'>(valor vacío)</span>";
                }
            } else {
                echo "<span class='text-danger'>(clave no existe)</span>";
            }
            echo "</p>";
            
            // Mostrar el JSON formateado y resaltar la parte de dientes
            echo "<h5>JSON actual:</h5>";
            echo "<div class='json-viewer'>";
            
            if (!empty($json_completo)) {
                $json_formateado = formatJSON($json_completo);
                
                if (isset($campos['dientes_seleccionados'])) {
                    // Resaltar la parte de dientes_seleccionados
                    $pattern = '/"dientes_seleccionados"\s*:\s*"[^"]*"/';
                    $json_formateado = preg_replace($pattern, '<span class="json-highlight">$0</span>', $json_formateado);
                }
                
                echo htmlspecialchars_decode(htmlspecialchars($json_formateado));
            } else {
                echo "<span class='text-muted'>(JSON vacío)</span>";
            }
            echo "</div>";
            echo "</div>";
            
            // Columna derecha: Acciones y corrección
            echo "<div class='col-md-6'>";
            
            if ($problema) {
                echo "<div class='card border-info'>";
                echo "<div class='card-header bg-info text-white'><h5 class='mb-0'>Aplicar corrección</h5></div>";
                echo "<div class='card-body'>";
                echo "<p>Esta acción copiará el valor de la columna <code>dientes_seleccionados</code> al JSON.</p>";
                echo "<form method='post'>";
                echo "<input type='hidden' name='consulta_id' value='{$consulta['id']}'>";
                echo "<input type='hidden' name='accion' value='probar_correccion'>";
                echo "<button type='submit' class='btn btn-info'>Corregir JSON</button>";
                echo "</form>";
                
                // Mostrar cómo quedaría el JSON corregido
                $campos_corregidos = $campos;
                $campos_corregidos['dientes_seleccionados'] = $dientes_columna;
                $json_corregido = json_encode($campos_corregidos);
                
                echo "<h5 class='mt-3'>JSON corregido:</h5>";
                echo "<div class='json-viewer'>";
                $json_corregido_formateado = formatJSON($json_corregido);
                $pattern = '/"dientes_seleccionados"\s*:\s*"[^"]*"/';
                $json_corregido_formateado = preg_replace($pattern, '<span class="json-highlight">$0</span>', $json_corregido_formateado);
                echo htmlspecialchars_decode(htmlspecialchars($json_corregido_formateado));
                echo "</div>";
                
                echo "</div>"; // card-body
                echo "</div>"; // card
            } else {
                echo "<div class='alert alert-success'>";
                echo "<h5>✅ Todo correcto</h5>";
                echo "<p>Los dientes están correctamente guardados tanto en la columna como en el JSON.</p>";
                echo "</div>";
            }
            
            echo "<div class='mt-3'>";
            echo "<a href='ver_consulta.php?id={$consulta['id']}' target='_blank' class='btn btn-sm btn-primary mr-2'>Ver consulta</a>";
            echo "</div>";
            
            echo "</div>"; // col-md-6
            echo "</div>"; // row
            echo "</div>"; // card-body
            echo "</div>"; // card
        }
    } else {
        echo "<div class='alert alert-warning'>No se encontraron consultas.</div>";
    }
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}

echo "<hr>
    <div class='mt-4'>
        <div class='card bg-light mb-3'>
            <div class='card-header'>
                <h4>Acciones Masivas</h4>
            </div>
            <div class='card-body'>
                <form method='post' action='verificar_guardado_dientes.php' onsubmit=\"return confirm('¿Estás seguro de que quieres corregir todas las consultas?');\">
                    <input type='hidden' name='accion' value='corregir_todas'>
                    <button type='submit' class='btn btn-warning mb-3'>Corregir TODAS las consultas</button>
                    <p class='text-muted'>Esta acción copiará el valor de la columna dientes_seleccionados al JSON para todas las consultas en la base de datos.</p>
                </form>
            </div>
        </div>
        
        <div class='mt-3'>
            <a href='verificar_guardado_dientes.php' class='btn btn-info'>Volver al Verificador</a>
            <a href='nueva_consulta.php?paciente_id=1' class='btn btn-primary ml-2'>Crear Nueva Consulta</a>
            <a href='index.php' class='btn btn-secondary ml-2'>Volver al Inicio</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Resaltar secciones de JSON con dientes_seleccionados
    const jsonViewers = document.querySelectorAll('.json-viewer');
    jsonViewers.forEach(function(viewer) {
        const content = viewer.innerHTML;
        if (content.includes('dientes_seleccionados')) {
            // Ya está resaltado por PHP
        }
    });
});
</script>
</body>
</html>";
?>
