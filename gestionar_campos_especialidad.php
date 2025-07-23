<?php
session_start();
require_once "permissions.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Verificar permisos para configurar el sistema (solo admin)
if ($_SESSION['rol'] !== 'admin') {
    header("location: unauthorized.php");
    exit;
}

require_once "config.php";

$exito = null;
$error = null;

// Obtener lista de especialidades
$especialidades = [];
try {
    $stmt = $conn->query("SELECT id, nombre FROM especialidades ORDER BY nombre");
    $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Error al cargar especialidades: " . $e->getMessage();
}

// Procesar formulario si se envió
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $conn->beginTransaction();
        
        $especialidad_id = $_POST['especialidad_id'];
        $action = $_POST['action'];
        
        // Validar que existe la especialidad
        $stmt = $conn->prepare("SELECT id FROM especialidades WHERE id = ?");
        $stmt->execute([$especialidad_id]);
        if (!$stmt->fetch()) {
            throw new Exception("La especialidad seleccionada no existe");
        }
        
        // Eliminar campo
        if ($action == 'delete' && isset($_POST['campo_id'])) {
            $campo_id = $_POST['campo_id'];
            
            // Verificar que el campo pertenece a esta especialidad
            $stmt = $conn->prepare("SELECT id FROM especialidad_campos WHERE id = ? AND especialidad_id = ?");
            $stmt->execute([$campo_id, $especialidad_id]);
            if (!$stmt->fetch()) {
                throw new Exception("El campo no pertenece a esta especialidad");
            }
            
            $stmt = $conn->prepare("DELETE FROM especialidad_campos WHERE id = ?");
            $stmt->execute([$campo_id]);
            
            $exito = "Campo eliminado correctamente";
        }
          // Crear nuevo campo
        elseif ($action == 'create' && isset($_POST['nombre_campo'])) {
            $nombre_campo = trim(strtolower($_POST['nombre_campo']));
            $etiqueta = trim($_POST['etiqueta']);
            $tipo_campo = $_POST['tipo_campo'];
            $opciones = isset($_POST['opciones']) ? trim($_POST['opciones']) : '';
            $requerido = isset($_POST['requerido']) ? 1 : 0;
            
            // Validaciones
            if (empty($nombre_campo)) {
                throw new Exception("El nombre del campo no puede estar vacío");
            }
            
            if (!preg_match('/^[a-z][a-z0-9_]*$/', $nombre_campo)) {
                throw new Exception("El nombre del campo debe comenzar con una letra minúscula y solo puede contener letras minúsculas, números y guiones bajos");
            }
            
            if (strlen($nombre_campo) < 3 || strlen($nombre_campo) > 50) {
                throw new Exception("El nombre del campo debe tener entre 3 y 50 caracteres");
            }
            
            // Verificar palabras reservadas
            $palabras_reservadas = ['id', 'fecha', 'paciente_id', 'doctor_id', 'consulta_id'];
            if (in_array($nombre_campo, $palabras_reservadas)) {
                throw new Exception("El nombre \"{$nombre_campo}\" está reservado y no puede ser utilizado");
            }
            
            if (empty($etiqueta)) {
                throw new Exception("La etiqueta del campo no puede estar vacía");
            }
            
            // Verificar que el nombre no existe para esta especialidad
            $stmt = $conn->prepare("SELECT id FROM especialidad_campos WHERE nombre_campo = ? AND especialidad_id = ?");
            $stmt->execute([$nombre_campo, $especialidad_id]);
            if ($stmt->fetch()) {
                throw new Exception("Ya existe un campo con ese nombre en esta especialidad");
            }
            
            // Obtener el siguiente orden
            $stmt = $conn->prepare("SELECT COALESCE(MAX(orden), 0) + 1 AS siguiente_orden FROM especialidad_campos WHERE especialidad_id = ?");
            $stmt->execute([$especialidad_id]);
            $orden = $stmt->fetch(PDO::FETCH_ASSOC)['siguiente_orden'];
            
            // Insertar nuevo campo
            $stmt = $conn->prepare("
                INSERT INTO especialidad_campos (
                    especialidad_id, nombre_campo, etiqueta, 
                    tipo_campo, opciones, requerido, orden
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $especialidad_id, $nombre_campo, $etiqueta, 
                $tipo_campo, $opciones, $requerido, $orden
            ]);
            
            $exito = "Campo creado correctamente";
        }
        
        // Actualizar orden
        elseif ($action == 'reorder' && isset($_POST['campos_orden'])) {
            $campos_orden = json_decode($_POST['campos_orden'], true);
            
            if (is_array($campos_orden)) {
                foreach ($campos_orden as $index => $campo_id) {
                    $stmt = $conn->prepare("UPDATE especialidad_campos SET orden = ? WHERE id = ? AND especialidad_id = ?");
                    $stmt->execute([$index + 1, $campo_id, $especialidad_id]);
                }
                
                $exito = "Orden actualizado correctamente";
            }
        }
          // Actualizar campo
        elseif ($action == 'update' && isset($_POST['campo_id'])) {
            $campo_id = $_POST['campo_id'];
            $etiqueta = trim($_POST['etiqueta']);
            $tipo_campo = $_POST['tipo_campo'];
            $opciones = isset($_POST['opciones']) ? trim($_POST['opciones']) : '';
            $requerido = isset($_POST['requerido']) ? 1 : 0;
            
            // Validaciones
            if (empty($etiqueta)) {
                throw new Exception("La etiqueta del campo no puede estar vacía");
            }
            
            // Actualizar campo
            $stmt = $conn->prepare("
                UPDATE especialidad_campos 
                SET etiqueta = ?, tipo_campo = ?, opciones = ?, requerido = ? 
                WHERE id = ? AND especialidad_id = ?
            ");
            $stmt->execute([$etiqueta, $tipo_campo, $opciones, $requerido, $campo_id, $especialidad_id]);
            
            $exito = "Campo actualizado correctamente";
        }
        
        // Duplicar campo existente
        elseif ($action == 'duplicate' && isset($_POST['campo_id'])) {
            $campo_id = $_POST['campo_id'];
            
            // Obtener datos del campo a duplicar
            $stmt = $conn->prepare("
                SELECT nombre_campo, etiqueta, tipo_campo, opciones, requerido 
                FROM especialidad_campos 
                WHERE id = ? AND especialidad_id = ?
            ");
            $stmt->execute([$campo_id, $especialidad_id]);
            $campo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$campo) {
                throw new Exception("El campo a duplicar no existe");
            }
            
            // Generar un nuevo nombre único
            $base_nombre = $campo['nombre_campo'];
            $contador = 1;
            $nuevo_nombre = $base_nombre . '_copia';
            
            // Verificar que el nuevo nombre no exista
            while (true) {
                $stmt = $conn->prepare("
                    SELECT id FROM especialidad_campos 
                    WHERE especialidad_id = ? AND nombre_campo = ?
                ");
                $stmt->execute([$especialidad_id, $nuevo_nombre]);
                
                if (!$stmt->fetch()) {
                    break; // Nombre disponible
                }
                
                $contador++;
                $nuevo_nombre = $base_nombre . '_copia' . $contador;
            }
            
            // Obtener el siguiente orden
            $stmt = $conn->prepare("SELECT COALESCE(MAX(orden), 0) + 1 AS siguiente_orden FROM especialidad_campos WHERE especialidad_id = ?");
            $stmt->execute([$especialidad_id]);
            $orden = $stmt->fetch(PDO::FETCH_ASSOC)['siguiente_orden'];
            
            // Insertar el campo duplicado
            $stmt = $conn->prepare("
                INSERT INTO especialidad_campos (
                    especialidad_id, nombre_campo, etiqueta, 
                    tipo_campo, opciones, requerido, orden
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $nueva_etiqueta = $campo['etiqueta'] . ' (Copia)';
            $stmt->execute([
                $especialidad_id, 
                $nuevo_nombre, 
                $nueva_etiqueta, 
                $campo['tipo_campo'], 
                $campo['opciones'], 
                $campo['requerido'], 
                $orden
            ]);
            
            $exito = "Campo duplicado correctamente";
        }
        
        $conn->commit();
        
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

// Obtener campos de la especialidad seleccionada
$campos_especialidad = [];
$especialidad_seleccionada = null;
$especialidad_id = $_GET['id'] ?? null;

if ($especialidad_id) {
    try {
        // Obtener información de la especialidad
        $stmt = $conn->prepare("SELECT id, nombre FROM especialidades WHERE id = ?");
        $stmt->execute([$especialidad_id]);
        $especialidad_seleccionada = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($especialidad_seleccionada) {
            // Obtener campos de la especialidad
            $stmt = $conn->prepare("
                SELECT id, nombre_campo, etiqueta, tipo_campo, opciones, requerido, orden
                FROM especialidad_campos 
                WHERE especialidad_id = ? 
                ORDER BY orden ASC
            ");
            $stmt->execute([$especialidad_id]);
            $campos_especialidad = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error = "Especialidad no encontrada";
        }
    } catch (Exception $e) {
        $error = "Error al cargar datos: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configurar Campos de Especialidad - Consultorio Médico</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- jQuery UI CSS para drag & drop -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; }
        .sidebar a:hover { background-color: #454d55; text-decoration: none; }
        .content { padding: 20px; }
        .campo-sortable { cursor: grab; padding: 10px; margin-bottom: 5px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; }
        .campo-sortable:hover { background: #e9ecef; }
        .campo-sortable .handle { cursor: grab; margin-right: 10px; color: #6c757d; }
        .campo-sortable .badge-required { font-weight: normal; font-size: 80%; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Content -->
            <div class="col-md-10 content">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2>Configurar Campos de Especialidad</h2>
                    <a href="configuracion.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Configuración
                    </a>
                </div>
                <hr>
                
                <?php if ($exito): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($exito); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <!-- Selector de Especialidad -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Seleccionar Especialidad</h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="get">
                            <div class="form-group">
                                <label for="id">Especialidad</label>
                                <select name="id" id="id" class="form-control" onchange="this.form.submit()">
                                    <option value="">Seleccione una especialidad</option>
                                    <?php foreach ($especialidades as $especialidad): ?>
                                        <option value="<?php echo $especialidad['id']; ?>" <?php echo ($especialidad_id == $especialidad['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($especialidad['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php if ($especialidad_seleccionada): ?>
                <div class="row">
                    <!-- Lista de campos existentes -->
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    Campos de <?php echo htmlspecialchars($especialidad_seleccionada['nombre']); ?>
                                    <small class="ml-2 text-white-50">(Arrastre para reordenar)</small>
                                </h5>
                            </div>                            <div class="card-body">
                                <?php if (empty($campos_especialidad)): ?>
                                    <p class="text-muted">No hay campos configurados para esta especialidad.</p>
                                <?php else: ?>
                                    <div class="form-group mb-3">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            </div>
                                            <input type="text" id="buscarCampo" class="form-control" placeholder="Buscar campo...">
                                        </div>
                                        <small class="form-text text-muted">Busca por nombre o etiqueta</small>
                                    </div>
                                    
                                    <form id="formOrden" action="" method="post">
                                        <input type="hidden" name="action" value="reorder">
                                        <input type="hidden" name="especialidad_id" value="<?php echo $especialidad_id; ?>">
                                        <input type="hidden" name="campos_orden" id="campos_orden" value="">
                                        
                                        <ul class="list-unstyled" id="listaCampos">
                                            <?php foreach ($campos_especialidad as $campo): ?>
                                            <li class="campo-sortable" data-id="<?php echo $campo['id']; ?>">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="fas fa-grip-vertical handle"></i>
                                                        <strong><?php echo htmlspecialchars($campo['etiqueta']); ?></strong>
                                                        <span class="badge badge-secondary ml-2"><?php echo htmlspecialchars($campo['nombre_campo']); ?></span>
                                                        <?php if ($campo['requerido']): ?>
                                                        <span class="badge badge-danger badge-required">Requerido</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>                                                        <button type="button" class="btn btn-sm btn-outline-primary btn-edit"
                                                                data-id="<?php echo $campo['id']; ?>"
                                                                data-etiqueta="<?php echo htmlspecialchars($campo['etiqueta']); ?>"
                                                                data-tipo="<?php echo htmlspecialchars($campo['tipo_campo']); ?>"
                                                                data-opciones="<?php echo htmlspecialchars($campo['opciones']); ?>"
                                                                data-requerido="<?php echo $campo['requerido']; ?>">
                                                            <i class="fas fa-edit"></i> Editar
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-info btn-duplicate"
                                                                data-id="<?php echo $campo['id']; ?>"
                                                                data-etiqueta="<?php echo htmlspecialchars($campo['etiqueta']); ?>">
                                                            <i class="fas fa-copy"></i> Duplicar
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                                                data-id="<?php echo $campo['id']; ?>"
                                                                data-etiqueta="<?php echo htmlspecialchars($campo['etiqueta']); ?>">
                                                            <i class="fas fa-trash"></i> Eliminar
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="mt-2 small text-muted">
                                                    Tipo: 
                                                    <strong>
                                                        <?php 
                                                        switch ($campo['tipo_campo']) {
                                                            case 'texto': echo 'Texto'; break;
                                                            case 'numero': echo 'Número'; break;
                                                            case 'fecha': echo 'Fecha'; break;
                                                            case 'textarea': echo 'Texto multilínea'; break;
                                                            case 'seleccion': echo 'Selección'; break;
                                                            case 'checkbox': echo 'Casilla de verificación'; break;
                                                            default: echo $campo['tipo_campo']; break;
                                                        }
                                                        ?>
                                                    </strong>
                                                    <?php if ($campo['tipo_campo'] === 'seleccion' && !empty($campo['opciones'])): ?>
                                                        <span class="ml-2">
                                                            Opciones: <?php echo htmlspecialchars($campo['opciones']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        
                                        <div class="mt-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Guardar Orden
                                            </button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Formulario para crear nuevo campo -->
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Crear Nuevo Campo</h5>
                            </div>
                            <div class="card-body">
                                <form id="formNuevoCampo" action="" method="post">
                                    <input type="hidden" name="action" value="create">
                                    <input type="hidden" name="especialidad_id" value="<?php echo $especialidad_id; ?>">
                                    
                                    <div class="form-group">
                                        <label for="nombre_campo">Nombre Interno*</label>
                                        <input type="text" class="form-control" id="nombre_campo" name="nombre_campo" 
                                               placeholder="Ej: temperatura" pattern="[a-z0-9_]+" required>
                                        <small class="form-text text-muted">
                                            Solo letras minúsculas, números y guiones bajos. Sin espacios.
                                        </small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="etiqueta">Etiqueta (visible para el usuario)*</label>
                                        <input type="text" class="form-control" id="etiqueta" name="etiqueta" 
                                               placeholder="Ej: Temperatura (°C)" required>
                                    </div>
                                      <div class="form-group">
                                        <label for="tipo_campo">Tipo de Campo*</label>
                                        <select class="form-control" id="tipo_campo" name="tipo_campo" required>
                                            <option value="texto" title="Para datos cortos como nombres, códigos, etc.">Texto</option>
                                            <option value="numero" title="Para valores numéricos como peso, temperatura, etc.">Número</option>
                                            <option value="fecha" title="Para fechas como vacunas, cirugías previas, etc.">Fecha</option>
                                            <option value="textarea" title="Para textos largos como observaciones, síntomas, etc.">Texto multilínea</option>
                                            <option value="seleccion" title="Para opciones predefinidas como diagnósticos comunes, estados, etc.">Selección</option>
                                            <option value="checkbox" title="Para valores booleanos como alergias, vacunas, etc.">Casilla de verificación</option>
                                        </select>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> Recomendaciones:
                                            <ul class="mt-1 small">
                                                <li><strong>Texto</strong>: Alergias específicas, medicamentos</li>
                                                <li><strong>Número</strong>: Temperatura, presión arterial, peso, etc.</li>
                                                <li><strong>Fecha</strong>: Última vacuna, próximo control</li>
                                                <li><strong>Texto multilínea</strong>: Observaciones, antecedentes</li>
                                                <li><strong>Selección</strong>: Estado del paciente, tipo de consulta</li>
                                                <li><strong>Casilla</strong>: Requiere seguimiento, tiene alergia</li>
                                            </ul>
                                        </small>
                                    </div>
                                    
                                    <div class="form-group" id="grupo_opciones" style="display: none;">
                                        <label for="opciones">Opciones (separadas por comas)</label>
                                        <input type="text" class="form-control" id="opciones" name="opciones" 
                                               placeholder="Ej: Normal, Elevada, Muy elevada">
                                        <small class="form-text text-muted">
                                            Sólo aplica para campos de tipo selección.
                                        </small>
                                    </div>
                                      <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="requerido" name="requerido">
                                        <label class="form-check-label" for="requerido">Campo requerido</label>
                                    </div>
                                    
                                    <div class="form-group mt-4">
                                        <label>Vista previa del campo:</label>
                                        <div id="previewContainer" class="p-3 border rounded bg-light">
                                            <div class="form-group">
                                                <label id="previewLabel">Etiqueta de ejemplo</label>
                                                <div id="previewField">
                                                    <!-- El campo se insertará aquí mediante JavaScript -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-success mt-3">
                                        <i class="fas fa-plus-circle"></i> Crear Campo
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal para editar campo -->
    <div class="modal fade" id="editarCampoModal" tabindex="-1" role="dialog" aria-labelledby="editarCampoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editarCampoModalLabel">Editar Campo</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formEditarCampo" action="" method="post">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="especialidad_id" value="<?php echo $especialidad_id; ?>">
                        <input type="hidden" name="campo_id" id="editar_campo_id" value="">
                        
                        <div class="form-group">
                            <label for="editar_etiqueta">Etiqueta (visible para el usuario)*</label>
                            <input type="text" class="form-control" id="editar_etiqueta" name="etiqueta" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="editar_tipo_campo">Tipo de Campo*</label>
                            <select class="form-control" id="editar_tipo_campo" name="tipo_campo" required>
                                <option value="texto">Texto</option>
                                <option value="numero">Número</option>
                                <option value="fecha">Fecha</option>
                                <option value="textarea">Texto multilínea</option>
                                <option value="seleccion">Selección</option>
                                <option value="checkbox">Casilla de verificación</option>
                            </select>
                        </div>
                        
                        <div class="form-group" id="editar_grupo_opciones" style="display: none;">
                            <label for="editar_opciones">Opciones (separadas por comas)</label>
                            <input type="text" class="form-control" id="editar_opciones" name="opciones" 
                                   placeholder="Ej: Normal, Elevada, Muy elevada">
                            <small class="form-text text-muted">
                                Sólo aplica para campos de tipo selección.
                            </small>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="editar_requerido" name="requerido">
                            <label class="form-check-label" for="editar_requerido">Campo requerido</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formEditarCampo" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>
      <!-- Modal para confirmar eliminación -->
    <div class="modal fade" id="eliminarCampoModal" tabindex="-1" role="dialog" aria-labelledby="eliminarCampoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="eliminarCampoModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar el campo <strong id="eliminar_campo_etiqueta"></strong>?</p>
                    <p class="text-danger">Esta acción no se puede deshacer.</p>
                    
                    <form id="formEliminarCampo" action="" method="post">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="especialidad_id" value="<?php echo $especialidad_id; ?>">
                        <input type="hidden" name="campo_id" id="eliminar_campo_id" value="">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formEliminarCampo" class="btn btn-danger">Eliminar</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para confirmar duplicación -->
    <div class="modal fade" id="duplicarCampoModal" tabindex="-1" role="dialog" aria-labelledby="duplicarCampoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="duplicarCampoModalLabel">Confirmar Duplicación</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Desea duplicar el campo <strong id="duplicar_campo_etiqueta"></strong>?</p>
                    <p>Se creará una copia del campo con todos sus atributos.</p>
                    
                    <form id="formDuplicarCampo" action="" method="post">
                        <input type="hidden" name="action" value="duplicate">
                        <input type="hidden" name="especialidad_id" value="<?php echo $especialidad_id; ?>">
                        <input type="hidden" name="campo_id" id="duplicar_campo_id" value="">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formDuplicarCampo" class="btn btn-info">Duplicar</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Mostrar opciones solo para campos de tipo selección
            function toggleOpcionesVisibility(selectElement, opcionesDiv) {
                if ($(selectElement).val() === 'seleccion') {
                    $(opcionesDiv).show();
                } else {
                    $(opcionesDiv).hide();
                }
            }
            
            $('#tipo_campo').on('change', function() {
                toggleOpcionesVisibility(this, '#grupo_opciones');
            });
            
            $('#editar_tipo_campo').on('change', function() {
                toggleOpcionesVisibility(this, '#editar_grupo_opciones');
            });
            
            // Inicializar estado
            toggleOpcionesVisibility('#tipo_campo', '#grupo_opciones');
            
            // Sortable para reordenar campos
            $("#listaCampos").sortable({
                handle: ".handle",
                update: function(event, ui) {
                    actualizarOrden();
                }
            });
            
            function actualizarOrden() {
                var ids = [];
                $("#listaCampos li").each(function() {
                    ids.push($(this).data('id'));
                });
                $("#campos_orden").val(JSON.stringify(ids));
            }
              // Inicializar orden
            actualizarOrden();
            
            // Filtrar campos
            $('#buscarCampo').on('keyup', function() {
                var valor = $(this).val().toLowerCase();
                $("#listaCampos li").filter(function() {
                    var texto = $(this).text().toLowerCase();
                    $(this).toggle(texto.indexOf(valor) > -1);
                });
            });
            
            // Manejo de edición
            $('.btn-edit').on('click', function() {
                var $btn = $(this);
                var campoId = $btn.data('id');
                var etiqueta = $btn.data('etiqueta');
                var tipo = $btn.data('tipo');
                var opciones = $btn.data('opciones');
                var requerido = $btn.data('requerido') === 1;
                
                $('#editar_campo_id').val(campoId);
                $('#editar_etiqueta').val(etiqueta);
                $('#editar_tipo_campo').val(tipo);
                $('#editar_opciones').val(opciones);
                $('#editar_requerido').prop('checked', requerido);
                
                toggleOpcionesVisibility('#editar_tipo_campo', '#editar_grupo_opciones');
                
                $('#editarCampoModal').modal('show');
            });
              // Manejo de eliminación
            $('.btn-delete').on('click', function() {
                var $btn = $(this);
                var campoId = $btn.data('id');
                var etiqueta = $btn.data('etiqueta');
                
                $('#eliminar_campo_id').val(campoId);
                $('#eliminar_campo_etiqueta').text(etiqueta);
                
                $('#eliminarCampoModal').modal('show');
            });
            
            // Manejo de duplicación
            $('.btn-duplicate').on('click', function() {
                var $btn = $(this);
                var campoId = $btn.data('id');
                var etiqueta = $btn.data('etiqueta');
                
                $('#duplicar_campo_id').val(campoId);
                $('#duplicar_campo_etiqueta').text(etiqueta);
                
                $('#duplicarCampoModal').modal('show');
            });
              // Validación formulario
            $('#formNuevoCampo').on('submit', function(e) {
                var nombreCampo = $('#nombre_campo').val();
                
                // Validar formato del nombre
                if (!/^[a-z][a-z0-9_]*$/.test(nombreCampo)) {
                    e.preventDefault();
                    alert('El nombre del campo debe comenzar con una letra minúscula y solo puede contener letras minúsculas, números y guiones bajos.');
                    return false;
                }
                
                // Validar longitud del nombre
                if (nombreCampo.length < 3 || nombreCampo.length > 50) {
                    e.preventDefault();
                    alert('El nombre del campo debe tener entre 3 y 50 caracteres.');
                    return false;
                }
                
                // Verificar palabras reservadas
                var palabrasReservadas = ['id', 'fecha', 'paciente_id', 'doctor_id', 'consulta_id'];
                if (palabrasReservadas.includes(nombreCampo)) {
                    e.preventDefault();
                    alert('El nombre "' + nombreCampo + '" está reservado y no puede ser utilizado.');
                    return false;
                }
                
                // Validar opciones para tipo selección
                if ($('#tipo_campo').val() === 'seleccion' && $('#opciones').val().trim() === '') {
                    e.preventDefault();
                    alert('Debe especificar al menos una opción para el campo de tipo selección.');
                    return false;
                }
            });
            
            // Validación edición
            $('#formEditarCampo').on('submit', function(e) {
                // Validar opciones para tipo selección
                if ($('#editar_tipo_campo').val() === 'seleccion' && $('#editar_opciones').val().trim() === '') {
                    e.preventDefault();
                    alert('Debe especificar al menos una opción para el campo de tipo selección.');
                    return false;
                }
            });
        });
    </script>
</body>
</html>
