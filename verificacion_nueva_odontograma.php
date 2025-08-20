<?php
// Archivo de verificación para diagnosticar el odontograma profesional
require_once "config.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación del Odontograma Profesional</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        h1, h2 {
            color: #0056b3;
        }
        .card {
            margin-bottom: 20px;
        }
        .status-ok {
            color: #28a745;
            font-weight: bold;
        }
        .status-warning {
            color: #ffc107;
            font-weight: bold;
        }
        .status-error {
            color: #dc3545;
            font-weight: bold;
        }
        pre {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
            max-height: 300px;
            overflow: auto;
        }
        .check-item {
            padding: 10px;
            border-left: 4px solid #e9ecef;
            margin-bottom: 10px;
        }
        .check-item.success {
            border-left-color: #28a745;
            background-color: #f0fff0;
        }
        .check-item.warning {
            border-left-color: #ffc107;
            background-color: #fffcf0;
        }
        .check-item.error {
            border-left-color: #dc3545;
            background-color: #fff0f0;
        }
        .nav-pills .nav-link.active {
            background-color: #0056b3;
        }
        .code-snippet {
            font-family: monospace;
            background-color: #f8f9fa;
            padding: 2px 5px;
            border-radius: 3px;
            border: 1px solid #dee2e6;
        }
        .iframe-container {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background-color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Verificación del Odontograma Profesional</h1>
        <p class="lead text-center mb-4">Esta herramienta diagnostica la implementación del nuevo odontograma profesional y ayuda a resolver problemas.</p>
        
        <ul class="nav nav-pills mb-4 justify-content-center" id="diagnosticoTabs">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#resumen">Resumen</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#verificaciones">Verificaciones</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#archivos">Archivos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#visualizacion">Visualización</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#solucion">Solución de Problemas</a>
            </li>
        </ul>
        
        <div class="tab-content">
            <!-- RESUMEN -->
            <div class="tab-pane fade show active" id="resumen">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h5 mb-0">Estado General del Odontograma</h2>
                    </div>
                    <div class="card-body">
                        <?php
                        // Verificar existencia de archivos clave
                        $archivosPrincipales = [
                            'forzar_odontograma.php' => file_exists('forzar_odontograma.php'),
                            'nueva_consulta.php' => file_exists('nueva_consulta.php'),
                            'Dientes2.html' => file_exists('Dientes2.html')
                        ];
                        
                        // Verificar especialidades odontológicas
                        $especialidadesOdontologicas = [];
                        try {
                            $stmt = $conn->prepare("SELECT id, nombre FROM especialidades WHERE LOWER(nombre) LIKE '%odonto%' OR LOWER(nombre) LIKE '%dental%'");
                            $stmt->execute();
                            $especialidadesOdontologicas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        } catch (Exception $e) {
                            // Error handling
                        }
                        
                        // Verificar la especialidad configurada en el sistema
                        $especialidadConfiguracion = null;
                        try {
                            $stmt = $conn->prepare("SELECT e.id, e.nombre FROM configuracion c JOIN especialidades e ON c.especialidad_id = e.id WHERE c.id = 1");
                            $stmt->execute();
                            $especialidadConfiguracion = $stmt->fetch(PDO::FETCH_ASSOC);
                        } catch (Exception $e) {
                            // Error handling
                        }
                        
                        // Determinar si la especialidad configurada es odontología
                        $esOdontologia = false;
                        if ($especialidadConfiguracion) {
                            $nombreEspecialidad = strtolower(trim($especialidadConfiguracion['nombre']));
                            $esOdontologia = (
                                strpos($nombreEspecialidad, 'odonto') !== false ||
                                strpos($nombreEspecialidad, 'dental') !== false ||
                                strpos($nombreEspecialidad, 'dentista') !== false
                            );
                        }
                        ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Archivos del Sistema</h4>
                                <ul class="list-group">
                                    <?php foreach ($archivosPrincipales as $archivo => $existe): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <?= $archivo ?>
                                            <?php if ($existe): ?>
                                                <span class="badge badge-success">Presente</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">No encontrado</span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            
                            <div class="col-md-6">
                                <h4>Configuración del Sistema</h4>
                                <div class="card mb-3">
                                    <div class="card-header">Especialidad Configurada</div>
                                    <div class="card-body">
                                        <?php if ($especialidadConfiguracion): ?>
                                            <p><strong>Nombre:</strong> <?= htmlspecialchars($especialidadConfiguracion['nombre']) ?></p>
                                            <p><strong>Es odontológica:</strong> <?= $esOdontologia ? '<span class="text-success">Sí</span>' : '<span class="text-danger">No</span>' ?></p>
                                            <p><strong>Mostrar odontograma:</strong> <?= $esOdontologia ? '<span class="text-success">Sí</span>' : '<span class="text-danger">No</span>' ?></p>
                                        <?php else: ?>
                                            <p class="text-danger">No se pudo determinar la especialidad configurada.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <h4>Especialidades Odontológicas Disponibles</h4>
                                <?php if (count($especialidadesOdontologicas) > 0): ?>
                                    <ul class="list-group">
                                        <?php foreach ($especialidadesOdontologicas as $esp): ?>
                                            <li class="list-group-item">
                                                <?= htmlspecialchars($esp['nombre']) ?> 
                                                <?php if ($especialidadConfiguracion && $esp['id'] == $especialidadConfiguracion['id']): ?>
                                                    <span class="badge badge-primary">Activa</span>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <div class="alert alert-warning">No se encontraron especialidades odontológicas.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <h5>Acciones Rápidas</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="test_odontograma.php" class="btn btn-primary btn-block mb-2" target="_blank">Probar Odontograma</a>
                                </div>
                                <div class="col-md-6">
                                    <a href="nueva_consulta.php" class="btn btn-success btn-block mb-2" target="_blank">Ir a Nueva Consulta</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- VERIFICACIONES -->
            <div class="tab-pane fade" id="verificaciones">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h2 class="h5 mb-0">Verificación de Componentes</h2>
                    </div>
                    <div class="card-body">
                        <?php
                        // Verificar contenido del archivo forzar_odontograma.php
                        $contenidoForzarOdontograma = '';
                        $tieneCodigoSVG = false;
                        $tieneFuncionInsertarOdontograma = false;
                        
                        if (file_exists('forzar_odontograma.php')) {
                            $contenidoForzarOdontograma = file_get_contents('forzar_odontograma.php');
                            $tieneCodigoSVG = strpos($contenidoForzarOdontograma, '<svg') !== false;
                            $tieneFuncionInsertarOdontograma = strpos($contenidoForzarOdontograma, 'function insertarOdontograma') !== false;
                        }
                        
                        // Verificar inclusión en nueva_consulta.php
                        $contenidoNuevaConsulta = '';
                        $tieneInclusionOdontograma = false;
                        
                        if (file_exists('nueva_consulta.php')) {
                            $contenidoNuevaConsulta = file_get_contents('nueva_consulta.php');
                            $tieneInclusionOdontograma = strpos($contenidoNuevaConsulta, "include_once 'forzar_odontograma.php'") !== false;
                        }
                        
                        // Verificar funcionalidades clave
                        $tieneDrawTooth = strpos($contenidoForzarOdontograma, 'function drawTooth') !== false;
                        $tieneActualizarListaDientes = strpos($contenidoForzarOdontograma, 'function actualizarListaDientes') !== false;
                        $tieneGetToothPosition = strpos($contenidoForzarOdontograma, 'function getToothPosition') !== false;
                        ?>
                        
                        <h4>Verificación del Código</h4>
                        <div class="check-item <?= $tieneCodigoSVG ? 'success' : 'error' ?>">
                            <h5><?= $tieneCodigoSVG ? '✓' : '✗' ?> Código SVG</h5>
                            <p>El archivo forzar_odontograma.php <?= $tieneCodigoSVG ? 'contiene' : 'no contiene' ?> código SVG para el dibujo profesional del odontograma.</p>
                        </div>
                        
                        <div class="check-item <?= $tieneFuncionInsertarOdontograma ? 'success' : 'error' ?>">
                            <h5><?= $tieneFuncionInsertarOdontograma ? '✓' : '✗' ?> Función insertarOdontograma</h5>
                            <p>El archivo forzar_odontograma.php <?= $tieneFuncionInsertarOdontograma ? 'contiene' : 'no contiene' ?> la función insertarOdontograma que se encarga de crear el odontograma.</p>
                        </div>
                        
                        <div class="check-item <?= $tieneInclusionOdontograma ? 'success' : 'error' ?>">
                            <h5><?= $tieneInclusionOdontograma ? '✓' : '✗' ?> Inclusión en nueva_consulta.php</h5>
                            <p>El archivo nueva_consulta.php <?= $tieneInclusionOdontograma ? 'incluye' : 'no incluye' ?> el archivo forzar_odontograma.php.</p>
                        </div>
                        
                        <h4>Funciones Clave del Odontograma</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="check-item <?= $tieneDrawTooth ? 'success' : 'error' ?>">
                                    <h5><?= $tieneDrawTooth ? '✓' : '✗' ?> drawTooth</h5>
                                    <p>Función para dibujar cada diente del odontograma.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="check-item <?= $tieneGetToothPosition ? 'success' : 'error' ?>">
                                    <h5><?= $tieneGetToothPosition ? '✓' : '✗' ?> getToothPosition</h5>
                                    <p>Función para calcular posiciones anatómicas de los dientes.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="check-item <?= $tieneActualizarListaDientes ? 'success' : 'error' ?>">
                                    <h5><?= $tieneActualizarListaDientes ? '✓' : '✗' ?> actualizarListaDientes</h5>
                                    <p>Función para registrar dientes seleccionados.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ARCHIVOS -->
            <div class="tab-pane fade" id="archivos">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h2 class="h5 mb-0">Archivos del Odontograma</h2>
                    </div>
                    <div class="card-body">
                        <h4>Archivos Relacionados</h4>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Archivo</th>
                                    <th>Propósito</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="code-snippet">forzar_odontograma.php</span></td>
                                    <td>Contiene la lógica de detección de especialidad y el HTML/JS del odontograma</td>
                                    <td><?= file_exists('forzar_odontograma.php') ? '<span class="badge badge-success">OK</span>' : '<span class="badge badge-danger">No encontrado</span>' ?></td>
                                </tr>
                                <tr>
                                    <td><span class="code-snippet">nueva_consulta.php</span></td>
                                    <td>Incluye forzar_odontograma.php y tiene la función mostrarCamposDinamicosYOdontograma()</td>
                                    <td><?= file_exists('nueva_consulta.php') ? '<span class="badge badge-success">OK</span>' : '<span class="badge badge-danger">No encontrado</span>' ?></td>
                                </tr>
                                <tr>
                                    <td><span class="code-snippet">Dientes2.html</span></td>
                                    <td>Archivo de referencia para el diseño profesional del odontograma</td>
                                    <td><?= file_exists('Dientes2.html') ? '<span class="badge badge-success">OK</span>' : '<span class="badge badge-danger">No encontrado</span>' ?></td>
                                </tr>
                                <tr>
                                    <td><span class="code-snippet">test_odontograma.php</span></td>
                                    <td>Página de pruebas para verificar el funcionamiento del odontograma</td>
                                    <td><?= file_exists('test_odontograma.php') ? '<span class="badge badge-success">OK</span>' : '<span class="badge badge-warning">Opcional</span>' ?></td>
                                </tr>
                                <tr>
                                    <td><span class="code-snippet">verificacion_odontograma.php</span></td>
                                    <td>Herramienta de diagnóstico y verificación</td>
                                    <td><?= file_exists('verificacion_odontograma.php') ? '<span class="badge badge-success">OK</span>' : '<span class="badge badge-warning">Opcional</span>' ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- VISUALIZACIÓN -->
            <div class="tab-pane fade" id="visualizacion">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h2 class="h5 mb-0">Visualización del Odontograma</h2>
                    </div>
                    <div class="card-body">
                        <h4>Vista Previa del Diseño de Referencia</h4>
                        <p>El siguiente es el diseño profesional que debería mostrarse en la nueva consulta:</p>
                        
                        <div class="iframe-container">
                            <?php if (file_exists('Dientes2.html')): ?>
                                <iframe src="Dientes2.html" style="width: 100%; height: 500px; border: 1px solid #ddd;"></iframe>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    No se encontró el archivo de referencia 'Dientes2.html'.
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <hr>
                        
                        <h4>Prueba de Integración</h4>
                        <p>Puede probar el odontograma integrado con el formulario de Nueva Consulta:</p>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <a href="nueva_consulta.php" target="_blank" class="btn btn-lg btn-primary btn-block mb-2">Abrir Nueva Consulta</a>
                                <p class="text-muted small">Abre el formulario de nueva consulta donde debería mostrarse el odontograma si la especialidad configurada es odontología.</p>
                            </div>
                            <div class="col-md-6">
                                <a href="test_odontograma.php" target="_blank" class="btn btn-lg btn-info btn-block mb-2">Probar Odontograma</a>
                                <p class="text-muted small">Abre la página de pruebas donde puede probar el odontograma con diferentes especialidades.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SOLUCIÓN DE PROBLEMAS -->
            <div class="tab-pane fade" id="solucion">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h2 class="h5 mb-0">Solución de Problemas</h2>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="accordionProblemas">
                            <div class="card">
                                <div class="card-header" id="headingOne">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                            El odontograma no aparece en el formulario de Nueva Consulta
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionProblemas">
                                    <div class="card-body">
                                        <ol>
                                            <li>Verifique que la especialidad configurada sea de tipo odontológico.</li>
                                            <li>Asegúrese de que el archivo <span class="code-snippet">forzar_odontograma.php</span> está incluido en <span class="code-snippet">nueva_consulta.php</span>.</li>
                                            <li>Verifique que el archivo <span class="code-snippet">forzar_odontograma.php</span> detecta correctamente la especialidad.</li>
                                            <li>Confirme que existe un elemento con ID <span class="code-snippet">campos_dinamicos</span> en <span class="code-snippet">nueva_consulta.php</span>.</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header" id="headingTwo">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                            El odontograma no se ve correctamente
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionProblemas">
                                    <div class="card-body">
                                        <ol>
                                            <li>Verifique que los archivos CSS y JavaScript necesarios están siendo cargados.</li>
                                            <li>Compruebe que no hay errores JavaScript en la consola del navegador.</li>
                                            <li>Asegúrese de que el SVG se está renderizando correctamente.</li>
                                            <li>Verifique que las funciones <span class="code-snippet">drawTooth</span> y <span class="code-snippet">getToothPosition</span> están funcionando.</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header" id="headingThree">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                            No se guardan los dientes seleccionados
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordionProblemas">
                                    <div class="card-body">
                                        <ol>
                                            <li>Confirme que la función <span class="code-snippet">actualizarListaDientes</span> se está ejecutando al hacer clic en un diente.</li>
                                            <li>Verifique que el campo oculto <span class="code-snippet">dientes_seleccionados</span> se está actualizando correctamente.</li>
                                            <li>Asegúrese de que el formulario envía el campo <span class="code-snippet">dientes_seleccionados</span> al servidor.</li>
                                            <li>Confirme que el servidor procesa correctamente el valor del campo.</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts necesarios -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>

