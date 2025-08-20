<?php
// Verificación completa del odontograma en nueva consulta
header('Content-Type: text/html; charset=utf-8');
require_once "config.php";

function verificarEspecialidad($conn) {
    try {
        $stmt = $conn->prepare("SELECT e.nombre, e.id FROM configuracion c 
                              JOIN especialidades e ON c.especialidad_id = e.id 
                              WHERE c.id = 1");
        $stmt->execute();
        $especialidad = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($especialidad) {
            // Verificar si la especialidad actual debería mostrar el odontograma
            $nombreEspecialidad = strtolower(trim($especialidad['nombre']));
            $especialidadesOdontologicas = ['odontologia', 'odontología', 'dental', 
                                          'odontologica', 'odontológica', 'dentista', 
                                          'odontopediatria', 'odontopediatría'];
            
            $deberiaActivar = in_array($nombreEspecialidad, $especialidadesOdontologicas) || 
                            strpos($nombreEspecialidad, 'odonto') !== false ||
                            strpos($nombreEspecialidad, 'dental') !== false;
                
            return [
                'status' => true,
                'especialidad' => $especialidad['nombre'],
                'id' => $especialidad['id'],
                'activaOdontograma' => $deberiaActivar
            ];
        } else {
            return [
                'status' => false,
                'mensaje' => 'No se encontró configuración de especialidad'
            ];
        }
    } catch (Exception $e) {
        return [
            'status' => false,
            'mensaje' => 'Error al verificar especialidad: ' . $e->getMessage()
        ];
    }
}

// Ejecutar diagnóstico cuando se solicite
$resultados = verificarEspecialidad($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación del Odontograma</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .card { margin-bottom: 20px; }
        .result-box {
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .success { background-color: #d4edda; }
        .warning { background-color: #fff3cd; }
        .error { background-color: #f8d7da; }
        .info { background-color: #d1ecf1; }
        .steps { margin-top: 15px; padding-left: 20px; }
        .steps li { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="my-4">Verificación del Odontograma</h1>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Estado Actual de la Configuración</h5>
            </div>
            <div class="card-body">
                <?php if ($resultados['status']): ?>
                    <div class="result-box info">
                        <strong>Especialidad Configurada:</strong> <?php echo htmlspecialchars($resultados['especialidad']); ?> (ID: <?php echo $resultados['id']; ?>)
                    </div>
                    
                    <div class="result-box <?php echo $resultados['activaOdontograma'] ? 'success' : 'warning'; ?>">
                        <strong>¿Esta especialidad activa el odontograma?</strong> 
                        <?php echo $resultados['activaOdontograma'] ? 'SÍ' : 'NO'; ?>
                    </div>
                    
                    <?php if (!$resultados['activaOdontograma']): ?>
                    <div class="alert alert-info mt-3">
                        <p><strong>Nota:</strong> El odontograma solo aparece cuando la especialidad configurada es específicamente "Odontología" o contiene las palabras "odonto" o "dental".</p>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="result-box error">
                        <strong>Error:</strong> <?php echo $resultados['mensaje']; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Pasos para Verificar el Funcionamiento</h5>
            </div>
            <div class="card-body">
                <ol class="steps">
                    <li>
                        <strong>Cambiar la especialidad:</strong> 
                        <a href="test_especialidad_odontologia.php" class="btn btn-sm btn-outline-primary">Usar test_especialidad_odontologia.php</a>
                    </li>
                    <li>
                        <strong>Probar la consulta:</strong> 
                        <a href="nueva_consulta.php?paciente_id=1" class="btn btn-sm btn-outline-primary">Abrir nueva_consulta.php con un paciente</a>
                    </li>
                    <li>
                        <strong>Verificar que:</strong>
                        <ul>
                            <li>El odontograma aparece SOLO cuando la especialidad es "Odontología" o similares</li>
                            <li>El odontograma NO aparece cuando la especialidad es diferente</li>
                            <li>Los dientes se pueden seleccionar correctamente en el odontograma</li>
                            <li>La lista de dientes seleccionados se actualiza al hacer clic en los dientes</li>
                        </ul>
                    </li>
                </ol>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Diagnóstico de Archivos</h5>
            </div>
            <div class="card-body">
                <h6>Archivos principales:</h6>
                <ul>
                    <li><strong>forzar_odontograma.php:</strong> Contiene la lógica de detección de especialidad y el HTML/JS del odontograma</li>
                    <li><strong>nueva_consulta.php:</strong> Incluye forzar_odontograma.php y tiene la función mostrarCamposDinamicosYOdontograma()</li>
                </ul>
                
                <h6>Variables clave:</h6>
                <ul>
                    <li><code>window.MOSTRAR_ODONTOGRAMA</code>: Variable global que indica si mostrar el odontograma</li>
                    <li><code>insertarOdontograma()</code>: Función que inserta el HTML del odontograma</li>
                </ul>
                
                <div class="mt-3">
                    <a href="diagnostico_odontograma.php" class="btn btn-sm btn-primary">Ver Diagnóstico Detallado</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            console.log("Página de verificación cargada");
            
            // Si estamos en una página que tiene odontograma, mostrar información adicional
            if (typeof window !== 'undefined' && typeof window.MOSTRAR_ODONTOGRAMA !== 'undefined') {
                $('body').append(
                    '<div class="container mt-4">' +
                    '<div class="alert alert-info">' +
                    '<strong>Detección en vivo:</strong> window.MOSTRAR_ODONTOGRAMA = ' + 
                    window.MOSTRAR_ODONTOGRAMA +
                    '</div></div>'
                );
            }
        });
    </script>
</body>
</html>

