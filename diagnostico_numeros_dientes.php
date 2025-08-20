<?php
// Script para diagnosticar específicamente los números de los dientes en consultas recientes
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Función para validar y formatear números de dientes
function validarNumerosDientes($str) {
    if (empty($str)) return [];
    
    // Dividir por comas y limpiar
    $numeros = array_map('trim', explode(',', $str));
    
    // Filtrar solo números válidos (11-48)
    $validos = [];
    foreach ($numeros as $num) {
        if (is_numeric($num) && intval($num) >= 11 && intval($num) <= 48) {
            $validos[] = intval($num);
        }
    }
    
    // Eliminar duplicados y ordenar
    $validos = array_unique($validos);
    sort($validos);
    
    return $validos;
}

// Función para corregir una consulta
function corregirConsulta($id, $conn) {
    try {
        // 1. Leer datos actuales
        $stmt = $conn->prepare("SELECT campos_adicionales, dientes_seleccionados FROM historial_medico WHERE id = ?");
        $stmt->execute([$id]);
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($datos) {
            $campos_json = json_decode($datos['campos_adicionales'], true) ?: [];
            $dientes_columna = $datos['dientes_seleccionados'] ?? '';
            
            // 2. Formatear los dientes de la columna (limpiar y validar)
            $numeros_validos = validarNumerosDientes($dientes_columna);
            $dientes_limpios = implode(',', $numeros_validos);
            
            // 3. Actualizar el JSON con los dientes_seleccionados correctos
            $campos_json['dientes_seleccionados'] = $dientes_limpios;
            $nuevo_json = json_encode($campos_json);
            
            // 4. Actualizar la tabla
            $stmt = $conn->prepare("UPDATE historial_medico SET campos_adicionales = ? WHERE id = ?");
            $stmt->execute([$nuevo_json, $id]);
            
            return [
                'success' => true,
                'mensaje' => "Consulta #$id corregida con éxito",
                'dientes_originales' => $dientes_columna,
                'dientes_formateados' => $dientes_limpios,
                'numeros_validos' => $numeros_validos
            ];
        } else {
            return [
                'success' => false,
                'mensaje' => "No se encontró la consulta #$id"
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'mensaje' => "Error: " . $e->getMessage()
        ];
    }
}

// Procesar acciones
$mensaje = '';
$tipo_mensaje = '';
$resultado_correccion = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['corregir']) && isset($_POST['id'])) {
        $resultado_correccion = corregirConsulta($_POST['id'], $conn);
        $mensaje = $resultado_correccion['mensaje'];
        $tipo_mensaje = $resultado_correccion['success'] ? 'success' : 'danger';
    }
    else if (isset($_POST['corregir_todas'])) {
        try {
            $stmt = $conn->prepare("SELECT id FROM historial_medico WHERE dientes_seleccionados IS NOT NULL AND dientes_seleccionados != ''");
            $stmt->execute();
            $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $total = count($consultas);
            $corregidas = 0;
            
            foreach ($consultas as $consulta) {
                $resultado = corregirConsulta($consulta['id'], $conn);
                if ($resultado['success']) {
                    $corregidas++;
                }
            }
            
            $mensaje = "Se han corregido $corregidas de $total consultas";
            $tipo_mensaje = 'success';
        } catch (Exception $e) {
            $mensaje = "Error al corregir consultas: " . $e->getMessage();
            $tipo_mensaje = 'danger';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Diagnóstico - Números de Dientes</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .tooth-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 5px;
            margin: 10px 0;
        }
        .tooth-box {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border: 2px solid #ccc;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.2s;
        }
        .tooth-selected {
            border-color: #ff6347;
            background-color: #ffebea;
            color: #e74c3c;
            transform: scale(1.1);
            box-shadow: 0 0 5px rgba(231, 76, 60, 0.5);
        }
        .cuadrante {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .cuadrante h5 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="my-4">Diagnóstico de Números de Dientes</h1>
        
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <?php if ($resultado_correccion && $resultado_correccion['success']): ?>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    Consulta Corregida
                </div>
                <div class="card-body">
                    <p><strong>Dientes originales:</strong> <?php echo $resultado_correccion['dientes_originales']; ?></p>
                    <p><strong>Dientes formateados:</strong> <?php echo $resultado_correccion['dientes_formateados']; ?></p>
                    
                    <h5>Visualización de dientes:</h5>
                    <div class="mb-4">
                        <?php 
                        // Agrupar por cuadrantes
                        $cuadrante1 = [];
                        $cuadrante2 = [];
                        $cuadrante3 = [];
                        $cuadrante4 = [];
                        
                        foreach ($resultado_correccion['numeros_validos'] as $num) {
                            if ($num >= 11 && $num <= 18) $cuadrante1[] = $num;
                            else if ($num >= 21 && $num <= 28) $cuadrante2[] = $num;
                            else if ($num >= 31 && $num <= 38) $cuadrante3[] = $num;
                            else if ($num >= 41 && $num <= 48) $cuadrante4[] = $num;
                        }
                        
                        // Función para mostrar cuadrante
                        function mostrarCuadrante($nombre, $numeros, $inicio, $fin) {
                            echo "<div class='cuadrante'>";
                            echo "<h5>$nombre</h5>";
                            echo "<div class='tooth-grid'>";
                            
                            for ($i = $inicio; $i <= $fin; $i++) {
                                $selected = in_array($i, $numeros) ? 'tooth-selected' : '';
                                echo "<div class='tooth-box $selected'>$i</div>";
                            }
                            
                            echo "</div></div>";
                        }
                        
                        mostrarCuadrante('Cuadrante 1 (Superior Derecho)', $cuadrante1, 18, 11);
                        mostrarCuadrante('Cuadrante 2 (Superior Izquierdo)', $cuadrante2, 21, 28);
                        mostrarCuadrante('Cuadrante 3 (Inferior Izquierdo)', $cuadrante3, 31, 38);
                        mostrarCuadrante('Cuadrante 4 (Inferior Derecho)', $cuadrante4, 48, 41);
                        ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h4>Últimas consultas con dientes seleccionados</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Paciente</th>
                                <th>Dientes en Columna</th>
                                <th>Dientes en JSON</th>
                                <th>Visualización</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            try {
                                $sql = "SELECT h.id, h.fecha, h.campos_adicionales, h.dientes_seleccionados, 
                                        p.nombre, p.apellido 
                                        FROM historial_medico h
                                        LEFT JOIN pacientes p ON h.paciente_id = p.id
                                        WHERE h.dientes_seleccionados IS NOT NULL 
                                        AND h.dientes_seleccionados != ''
                                        ORDER BY h.id DESC
                                        LIMIT 20";
                                        
                                $stmt = $conn->prepare($sql);
                                $stmt->execute();
                                $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($consultas as $consulta) {
                                    $dientes_columna = $consulta['dientes_seleccionados'] ?? '';
                                    $campos_json = json_decode($consulta['campos_adicionales'], true) ?? [];
                                    $dientes_json = $campos_json['dientes_seleccionados'] ?? '';
                                    
                                    $numeros_columna = validarNumerosDientes($dientes_columna);
                                    $numeros_json = validarNumerosDientes($dientes_json);
                                    
                                    // Determinar estado
                                    $problema = false;
                                    $mensaje_problema = '';
                                    
                                    if (!isset($campos_json['dientes_seleccionados'])) {
                                        $problema = true;
                                        $mensaje_problema = 'Campo ausente en JSON';
                                    } else if (empty($dientes_json) && !empty($dientes_columna)) {
                                        $problema = true;
                                        $mensaje_problema = 'JSON vacío';
                                    } else if ($dientes_columna !== $dientes_json) {
                                        $problema = true;
                                        $mensaje_problema = 'Valores diferentes';
                                    }
                                    
                                    // Visualización de dientes (primeros 5 para no sobrecargar)
                                    $visualizacion = '';
                                    $numeros_mostrar = array_slice($numeros_columna, 0, 5);
                                    foreach ($numeros_mostrar as $num) {
                                        $visualizacion .= "<span class='badge badge-primary mr-1'>$num</span>";
                                    }
                                    if (count($numeros_columna) > 5) {
                                        $visualizacion .= "<span class='badge badge-secondary'>+" . (count($numeros_columna) - 5) . "</span>";
                                    }
                                    
                                    echo "<tr class='" . ($problema ? 'table-danger' : 'table-success') . "'>";
                                    echo "<td>{$consulta['id']}</td>";
                                    echo "<td>{$consulta['fecha']}</td>";
                                    echo "<td>{$consulta['nombre']} {$consulta['apellido']}</td>";
                                    echo "<td>{$dientes_columna}</td>";
                                    echo "<td>{$dientes_json}</td>";
                                    echo "<td>$visualizacion</td>";
                                    echo "<td>" . ($problema ? "<span class='text-danger'>$mensaje_problema</span>" : "<span class='text-success'>Correcto</span>") . "</td>";
                                    echo "<td>
                                        <form method='post'>
                                            <input type='hidden' name='id' value='{$consulta['id']}'>
                                            <button type='submit' name='corregir' class='btn btn-sm " . ($problema ? 'btn-warning' : 'btn-secondary') . "'>
                                                " . ($problema ? 'Corregir' : 'Ver detalle') . "
                                            </button>
                                        </form>
                                    </td>";
                                    echo "</tr>";
                                }
                                
                                if (count($consultas) == 0) {
                                    echo "<tr><td colspan='8' class='text-center'>No se encontraron consultas con dientes seleccionados</td></tr>";
                                }
                                
                            } catch (Exception $e) {
                                echo "<tr><td colspan='8' class='text-center text-danger'>Error: {$e->getMessage()}</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    <form method="post" onsubmit="return confirm('¿Estás seguro de corregir todas las consultas? Esta acción no se puede deshacer.')">
                        <button type="submit" name="corregir_todas" class="btn btn-warning">
                            Corregir Todas las Consultas
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="debug_dientes_json_final.php" class="btn btn-info">Ver Diagnóstico Avanzado</a>
            <a href="nueva_consulta.php?paciente_id=1" class="btn btn-primary">Crear Nueva Consulta</a>
            <a href="index.php" class="btn btn-secondary">Volver al Inicio</a>
        </div>
    </div>
</body>
</html>

