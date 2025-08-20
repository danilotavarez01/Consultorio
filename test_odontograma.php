<?php
// Test file to verify odontogram implementation
// This mimics the essential parts of nueva_consulta.php to test the odontogram
// Modificado para probar la corrección del orden de los dientes en el odontograma SVG mejorado

// Include database configuration
require_once "config.php";

// Function to check if a table exists
function tableExists($tableName, $conn) {
    try {
        $result = $conn->query("SHOW TABLES LIKE '{$tableName}'");
        return $result->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

// Simulate selecting an especialidad
$especialidad_id = isset($_GET['especialidad']) ? intval($_GET['especialidad']) : 0;
$es_odontologia = false;

// Check if especialidad is odontología
if ($especialidad_id > 0) {
    try {
        $stmt = $conn->prepare("SELECT nombre FROM especialidades WHERE id = ?");
        $stmt->execute([$especialidad_id]);
        $especialidad = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($especialidad) {
            $nombreEspecialidad = strtolower(trim($especialidad['nombre']));
            $es_odontologia = (
                strpos($nombreEspecialidad, 'odonto') !== false ||
                strpos($nombreEspecialidad, 'dental') !== false ||
                strpos($nombreEspecialidad, 'dentista') !== false
            );
        }
    } catch (Exception $e) {
        // Error handling
        echo "<div class='alert alert-danger'>Error al verificar especialidad: " . $e->getMessage() . "</div>";
    }
}

// Get all available especialidades
$especialidades = [];
try {
    $stmt = $conn->query("SELECT id, nombre FROM especialidades ORDER BY nombre");
    $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error al cargar especialidades: " . $e->getMessage() . "</div>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Test Odontograma Profesional</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        #test-container {
            border: 2px dashed #007bff;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #f8f9fa;
        }
        h1 {
            color: #0056b3;
        }        h1 {
            color: #0056b3;
            margin-bottom: 20px;
        }
        .test-controls {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        .test-info {
            margin-bottom: 20px;
            padding: 10px;
            border-left: 5px solid #17a2b8;
            background-color: #e3f2fd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">Test de Odontograma Profesional</h1>
        
        <div class="test-info">
            <p><strong>Propósito:</strong> Verificar que el odontograma profesional se muestre correctamente y solamente cuando la especialidad sea Odontología.</p>
            <p><strong>Instrucciones:</strong> Seleccione diferentes especialidades del menú desplegable para verificar que el odontograma aparece únicamente cuando se selecciona una especialidad relacionada con odontología.</p>
        </div>
        
        <div class="test-controls">
            <form id="test-form" method="get" class="mb-4">
                <div class="form-group">
                    <label for="especialidad"><strong>Seleccionar Especialidad:</strong></label>
                    <select name="especialidad" id="especialidad" class="form-control">
                        <option value="0">-- Seleccione una especialidad --</option>
                        <?php foreach ($especialidades as $esp): ?>
                            <option value="<?= $esp['id'] ?>" <?= ($esp['id'] == $especialidad_id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($esp['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Aplicar Especialidad</button>
            </form>
            
            <div class="status-info p-3 mb-3 rounded">
                <h5>Estado actual:</h5>
                <div id="current-status">
                    <?php if ($especialidad_id > 0): ?>
                        <div class="alert <?= $es_odontologia ? 'alert-success' : 'alert-warning' ?>">
                            <p><strong>Especialidad seleccionada:</strong> 
                            <?= htmlspecialchars($especialidad['nombre']) ?></p>
                            <p><strong>¿Es odontología?:</strong> <?= $es_odontologia ? 'Sí' : 'No' ?></p>
                            <p><strong>Se debe mostrar el odontograma:</strong> <?= $es_odontologia ? 'Sí' : 'No' ?></p>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Ninguna especialidad seleccionada. Seleccione una especialidad para probar.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
          <hr>
        
        <!-- Simulación de la zona de campos dinámicos donde se insertaría el odontograma -->
        <h4>Formulario de Consulta (Simulado)</h4>
        <div id="campos_dinamicos">
            <!-- Aquí se insertará el odontograma si corresponde -->
        </div>
        
        <hr>
        <div class="test-section mt-4">
            <h3>Prueba Directa de Odontograma SVG Mejorado</h3>
            <p>Esta sección carga directamente el odontograma mejorado para verificar la corrección del orden de los dientes.</p>
            
            <div class="card p-3 bg-light mb-3">
                <div class="d-flex justify-content-between mb-2">
                    <h5>Cargando odontograma mejorado...</h5>
                    <div>
                        <button id="btn-cargar-svg" class="btn btn-sm btn-primary">Cargar Odontograma SVG</button>
                    </div>
                </div>
                <div id="odontograma-test-container"></div>
            </div>
        </div>
    </div>

    <!-- Scripts necesarios -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    
    <?php 
    // Incluir el archivo del odontograma tal como se hace en nueva_consulta.php
    include_once 'forzar_odontograma.php'; 
    ?>
      <script>
    $(document).ready(function() {
        // Simular que MOSTRAR_ODONTOGRAMA está definido si la especialidad es odontología
        <?php if ($es_odontologia): ?>
        window.MOSTRAR_ODONTOGRAMA = true;
        window.ESPECIALIDAD_NOMBRE = '<?= htmlspecialchars($especialidad['nombre']); ?>';
        <?php else: ?>
        window.MOSTRAR_ODONTOGRAMA = false;
        <?php endif; ?>
        
        // Mostrar información sobre la carga del odontograma
        setTimeout(function() {
            let odontogramaPresente = $('#odontograma-dinamico').length > 0;
            
            $('#current-status').append(`
                <div class="alert ${odontogramaPresente ? 'alert-success' : 'alert-secondary'}">
                    <p><strong>Odontograma cargado:</strong> ${odontogramaPresente ? 'Sí' : 'No'}</p>
                    ${odontogramaPresente ? '<p><strong>Estado:</strong> Verificar que el diseño se ve correctamente y que es funcional</p>' : ''}
                </div>
            `);
        }, 2000);
        
        // Botón para cargar directamente el odontograma SVG mejorado
        $('#btn-cargar-svg').click(function() {
            $('#odontograma-test-container').html('<div class="loading-spinner text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
            
            // Cargar el odontograma SVG mejorado directamente
            $('#odontograma-test-container').load('odontograma_svg_mejorado.php', function(response, status, xhr) {
                if (status == "error") {
                    $('#odontograma-test-container').html('<div class="alert alert-danger">Error al cargar el odontograma: ' + xhr.status + ' ' + xhr.statusText + '</div>');
                } else {
                    $('#odontograma-test-container').append('<div class="alert alert-success mt-3">Odontograma SVG mejorado cargado correctamente. Verifique que el orden de los dientes sea correcto (18-11, 21-28, 48-41, 31-38)</div>');
                }
            });
        });
    });
    </script>
</body>
</html>

