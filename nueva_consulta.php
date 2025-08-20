<?php
require_once 'session_config.php';
session_start();
require_once "permissions.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Verificar permisos para gestionar pacientes
if (!hasPermission('manage_patients')) {
    header("location: unauthorized.php");
    exit;
}

require_once "config.php";

$paciente = null;
$error = null;
$success = null;

// Verificar si se proporcionó un ID de paciente
if (isset($_GET['paciente_id']) && !empty($_GET['paciente_id'])) {
    $paciente_id = $_GET['paciente_id'];
    
    // Obtener datos del paciente
    $sql = "SELECT * FROM pacientes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$paciente_id]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$paciente) {
        $error = "Paciente no encontrado";
    }
} else {
    $error = "ID de paciente no proporcionado";
}

// Procesar el formulario de nueva consulta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'crear_consulta') {
    // Nota: Procesamiento de dientes seleccionados sin output HTML
    $transactionStarted = false;
    try {
        $conn->beginTransaction();
        $transactionStarted = true;        // Obtener ID de la especialidad configurada
        $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        $especialidad_id = $config['especialidad_id'];
        
        // Preparar el array de campos personalizados
        $campos_adicionales = [];
        
        // Campos del sistema que NO deben ir al JSON
        $campos_sistema = [
            'action', 'paciente_id', 'doctor_id', 'fecha', 'motivo_consulta', 
            'diagnostico', 'tratamiento', 'observaciones', 'dientes_seleccionados',
            'dientes_array_json', 'ultimo_diente_seleccionado', 'posiciones_dientes',
            'dientes_seleccionados_array'
        ];
        
        foreach ($_POST as $key => $value) {
            // Si el campo comienza con 'campo_' es un campo dinámico
            if (strpos($key, 'campo_') === 0) {
                $campo_nombre = substr($key, 6); // Remover el prefijo 'campo_'
                $campos_adicionales[$campo_nombre] = $value;
            }
            // También capturar otros campos que no sean del sistema (como 'observa')
            elseif (!in_array($key, $campos_sistema) && !empty(trim($value))) {
                $campos_adicionales[$key] = $value;
            }
        }
        
        // Procesar el array JSON de dientes (si existe)
        if (isset($_POST['dientes_array_json']) && !empty($_POST['dientes_array_json'])) {
            $dientes_json = $_POST['dientes_array_json'];
            $dientes_array_from_json = json_decode($dientes_json, true);
            
            if (is_array($dientes_array_from_json)) {
                // Guardar el array de dientes en el JSON
                $campos_adicionales['dientes_json'] = $dientes_array_from_json;
                
                // Crear la versión de string para compatibilidad
                $dientes_valor = implode(',', $dientes_array_from_json);
                $campos_adicionales['dientes_seleccionados'] = $dientes_valor;
                
                // echo "<p style='background:#e8f5e9; padding:5px;'><strong>✅ JSON de dientes recibido:</strong> " 
                //      . count($dientes_array_from_json) . " dientes en formato JSON estructurado</p>";
            } else {
                // Error en formato JSON - se registra para debug pero no se muestra
                $dientes_valor = '';
                $campos_adicionales['dientes_seleccionados'] = '';
            }
        } else {
            // Si no hay dientes en el JSON, establecer un valor vacío para compatibilidad
            $dientes_valor = '';
            $campos_adicionales['dientes_seleccionados'] = '';
        }
        
        // Ya no se incluye ultimo_diente_seleccionado en el JSON
        
        // Eliminado el procesamiento de posiciones_dientes (ya no se almacena en el JSON)
        
        // Convertir el array a JSON solo si tiene contenido
        $campos_adicionales_json = !empty($campos_adicionales) ? json_encode($campos_adicionales) : null;
        
        // Insertar consulta with campos adicionales y dientes seleccionados
        $sql = "INSERT INTO historial_medico (
                    paciente_id, 
                    doctor_id, 
                    fecha, 
                    motivo_consulta, 
                    diagnostico, 
                    tratamiento, 
                    observaciones,
                    campos_adicionales,
                    especialidad_id,
                    dientes_seleccionados
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(array(
            $_POST['paciente_id'],
            $_POST['doctor_id'] ?? $_SESSION['id'], // Usar ID de la sesión si no se proporciona
            $_POST['fecha'],
            $_POST['motivo_consulta'] ?? 'Consulta médica general', // Valor por defecto si no se proporciona
            $_POST['diagnostico'] ?? null,
            $_POST['tratamiento'] ?? null,
            $_POST['observaciones'] ?? null,
            $campos_adicionales_json,
            $especialidad_id,
            $dientes_valor // Guardar los dientes seleccionados desde la versión generada
        ));
        
        $consulta_id = $conn->lastInsertId();
        
        // Guardar valores de campos personalizados en tabla consulta_campos_valores
        if (!empty($_POST)) {
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'campo_') === 0) {
                    $campo_nombre = substr($key, 6); // Remover el prefijo 'campo_'
                    
                    // Obtener el ID del campo desde la tabla especialidad_campos
                    $stmt = $conn->prepare("
                        SELECT id FROM especialidad_campos 
                        WHERE especialidad_id = ? AND nombre_campo = ?
                    ");
                    $stmt->execute([$especialidad_id, $campo_nombre]);
                    $campo = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($campo) {
                        // Insertar el valor en consulta_campos_valores
                        $stmt = $conn->prepare("
                            INSERT INTO consulta_campos_valores (consulta_id, campo_id, valor)
                            VALUES (?, ?, ?)
                        ");
                        $stmt->execute([$consulta_id, $campo['id'], $value]);
                    }
                }
            }
        }
        
        $conn->commit();
        
        // Verificar que la sesión sigue activa antes de redirigir
        if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
            // Si la sesión se perdió, redirigir al login con mensaje
            header("location: login.php?error=session_lost");
            exit;
        }
        
        // Opción 1: Redirigir a ver paciente (recomendado)
        header("location: ver_paciente.php?id=" . $_POST['paciente_id'] . "&consulta_creada=1");
        exit;
        
        // Opción 2: Si hay problemas con la redirección, mostrar éxito en la misma página
        // $success = "Consulta médica creada exitosamente. ID: " . $consulta_id;
    } catch (Exception $e) {
        // Solo hacer rollback si la transacción se inició
        if ($transactionStarted && $conn->inTransaction()) {
            $conn->rollBack();
        }
        
        // Log del error para debug
        error_log("Error en nueva_consulta.php: " . $e->getMessage());
        error_log("Sesión activa: " . (isset($_SESSION["loggedin"]) ? "SI" : "NO"));
        
        $error = "Error al guardar la consulta: " . $e->getMessage();
    }
}

// Obtener configuración del consultorio para el nombre del médico
$config = null;
try {
    $stmt = $conn->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Silenciosamente fallar si no se puede obtener la configuración
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Consulta - Consultorio Médico</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; }
        .sidebar a:hover { background-color: #454d55; text-decoration: none; }
        .content { padding: 20px; }
    </style>
</head>
<body>
    <!-- Header con modo oscuro -->
    <?php include 'includes/header.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Content -->
            <div class="col-md-10 content">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2>Nueva Consulta Médica</h2>
                    <?php if ($paciente): ?>
                    <a href="ver_paciente.php?id=<?php echo $paciente['id']; ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Paciente
                    </a>
                    <?php else: ?>
                    <a href="pacientes.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Pacientes
                    </a>
                    <?php endif; ?>
                </div>
                <hr>

                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if ($paciente): ?>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Consulta para: <?php echo htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']); ?></h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="consultaForm">
                            <input type="hidden" name="action" value="crear_consulta">
                            <input type="hidden" name="paciente_id" value="<?php echo $paciente['id']; ?>">
                            <input type="hidden" name="doctor_id" value="<?php echo $_SESSION['id']; ?>">
                            <!-- Campo oculto para almacenar los dientes seleccionados en formato array JSON 
                                 Este es ahora el único campo para almacenar la información de los dientes -->
                            <input type="hidden" name="dientes_array_json" id="dientes_array_json" value="[]">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Fecha de Consulta</label>
                                    <input type="date" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                    <!-- Letrero para mostrar fecha de parto -->
                                    <div id="letrero-fecha-parto" class="alert alert-success mt-2" style="display: none;">
                                        <i class="fas fa-baby"></i> <strong>Fecha probable de parto:</strong> 
                                        <span id="fecha-parto-calculada"></span>
                                        <br><small class="text-muted">Calculada con Fórmula de Naegele (FUR + 280 días)</small>
                                    </div>
                                </div>
                                <!-- <div class="form-group col-md-6">
                                    <label>Campo de prueba "observa"</label>
                                    <input type="text" name="observa" class="form-control" placeholder="Ej: jajaja">
                                </div> -->
                            </div>
                            
                            <br>
                            <!-- <button type="submit" name="registrar" class="btn btn-primary">REGISTRAR CONSULTA</button> -->
                            
                            <!-- Contenedor para campos dinámicos y odontograma (si aplica) -->
                            <div id="campos_dinamicos"></div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Guardar Consulta</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="js/theme-manager.js"></script>
    <!-- Usamos el nuevo script para evitar errores XML -->
    <script src="js/campos_dinamicos_nuevo.js"></script>    <!-- Incluir el script forzado del odontograma (solo se muestra si es odontología) -->    <?php include 'forzar_odontograma_corregido.php'; ?>
    <script>
        $(document).ready(function() {
            console.log('Nueva consulta cargada - Con campos dinámicos nuevo');
            console.log('Contenedor campos_dinamicos existe:', $('#campos_dinamicos').length > 0);            // Mostrar/ocultar campos dinámicos y odontograma solo si hay campos específicos
            function mostrarCamposDinamicosYOdontograma() {
                // Definir conexión a herramienta de diagnóstico
                var enlaceDiagnostico = '<div class="text-right mb-2" id="enlace-diagnostico-odontograma">'
                    + '<a href="diagnostico_odontograma.php" target="_blank" class="btn btn-sm btn-info">'
                    + '<i class="fas fa-stethoscope"></i> Diagnóstico del Odontograma</a></div>';
                  // Solo mostramos el enlace de diagnóstico si la especialidad es odontología
                if (window.MOSTRAR_ODONTOGRAMA === true) {
                    if ($('#enlace-diagnostico-odontograma').length === 0) {
                        $('#campos_dinamicos').before(enlaceDiagnostico);
                    }
                    console.log('La especialidad es odontología, mostrando odontograma');
                } else {
                    // Si no es odontología, ocultamos el enlace de diagnóstico
                    $('#enlace-diagnostico-odontograma').remove();
                    console.log('La especialidad NO es odontología, no se muestra el odontograma');
                }
                
                // NO forzamos la especialidad odontología - respetamos lo configurado
                // Mostrar el contenedor de campos dinámicos
                $('#campos_dinamicos').show();                // Verificamos si es odontología - pero NO insertamos el odontograma desde aquí
                // ya que forzar_odontograma_simple.php se encarga de eso automáticamente                if (window.MOSTRAR_ODONTOGRAMA === true) {
                    console.log("Es odontología, el odontograma será manejado por el script forzar_odontograma_corregido.php");
                    
                    // Verificar si el odontograma ya está presente para mostrar un mensaje, pero NO FORZARLO
                    // ya que forzar_odontograma_corregido.php se encarga de la carga controlada
                    if ($('#odontograma-dinamico').length === 0) {
                        console.log("El odontograma no está presente, será manejado por forzar_odontograma_corregido.php");
                    } else {
                        console.log("El odontograma ya está presente, no es necesario insertarlo de nuevo");
                    }
                } else {
                    console.log("No es odontología, no se muestra el odontograma");
                    // Remover el odontograma si existe
                    $('#odontograma-dinamico').remove();
                }
            }
            // Llamar al cargar y cuando cambie la especialidad o se carguen campos
            mostrarCamposDinamicosYOdontograma();
            $(document).on('change', 'select[name="especialidad_id"]', mostrarCamposDinamicosYOdontograma);            // Si los campos se cargan por AJAX, observar cambios pero solo para controlar el enlace de diagnóstico
            // Evitamos llamar a la función completa para evitar bucles de carga
            var observer = new MutationObserver(function(mutations) {
                // Solo actualizar el enlace de diagnóstico, no reinsertar el odontograma
                if (window.MOSTRAR_ODONTOGRAMA === true) {
                    if ($('#enlace-diagnostico-odontograma').length === 0) {
                        var enlaceDiagnostico = '<div class="text-right mb-2" id="enlace-diagnostico-odontograma">'
                            + '<a href="diagnostico_odontograma.php" target="_blank" class="btn btn-sm btn-info">'
                            + '<i class="fas fa-stethoscope"></i> Diagnóstico del Odontograma</a></div>';
                        $('#campos_dinamicos').before(enlaceDiagnostico);
                    }
                }
            });
            observer.observe(document.getElementById('campos_dinamicos'), { childList: true, subtree: false });
            
            // SOLUCIÓN MEJORADA: Interceptar el envío del formulario para sincronizar los dientes seleccionados
            $('#consultaForm').on('submit', function(e) {
                // Prevenir envío para sincronizar primero
                e.preventDefault();
                
                console.log('⚠️ SINCRONIZANDO DIENTES PARA ENVÍO DEL FORMULARIO');
                
                // PASO 1: Recolectar dientes de todas las fuentes posibles
                var todasLasFuentes = [];
                var origenUtilizado = "ninguno";
                
                // OPCIÓN 1: Verificar variable global (odontograma SVG)
                if (typeof window.seleccionados !== 'undefined') {
                    var dientesArray;
                    // Determinar el tipo de la variable global
                    if (Array.isArray(window.seleccionados)) {
                        dientesArray = window.seleccionados;
                    } else if (window.seleccionados instanceof Set) {
                        dientesArray = Array.from(window.seleccionados);
                    } else if (typeof window.seleccionados === 'string') {
                        dientesArray = window.seleccionados.split(',').filter(item => item.trim() !== '');
                    } else {
                        dientesArray = [];
                    }
                    
                    if (dientesArray.length > 0) {
                        todasLasFuentes.push({
                            origen: "variable_global",
                            dientes: dientesArray.join(','),
                            prioridad: 1 // Máxima prioridad
                        });
                    }
                }
                
                // OPCIÓN 2: Buscar campo oculto específico del odontograma
                var odontogramaDientes = $('#odontograma-dinamico #dientes_seleccionados').val();
                if (odontogramaDientes !== undefined && odontogramaDientes !== null && odontogramaDientes.trim() !== '') {
                    todasLasFuentes.push({
                        origen: "campo_odontograma",
                        dientes: odontogramaDientes.trim(),
                        prioridad: 2
                    });
                }
                
                // OPCIÓN 3: Campo manual de prueba
                var dientesManual = $('#dientes_manual').val();
                if (dientesManual && dientesManual.trim() !== '') {
                    todasLasFuentes.push({
                        origen: "campo_manual",
                        dientes: dientesManual.trim(),
                        prioridad: 3
                    });
                }
                
                // OPCIÓN 4: Extraer desde elementos visuales (para odontograma básico)
                var dientesDesdeElementos = [];
                if ($('.btn-diente.seleccionado').length > 0) {
                    $('.btn-diente.seleccionada').each(function() {
                        dientesDesdeElementos.push($(this).data('diente'));
                    });
                    
                    if (dientesDesdeElementos.length > 0) {
                        todasLasFuentes.push({
                            origen: "elementos_dom",
                            dientes: dientesDesdeElementos.join(','),
                            prioridad: 4
                        });
                    }
                }
                
                // Ordenar fuentes por prioridad
                todasLasFuentes.sort((a, b) => a.prioridad - b.prioridad);
                
                // Usar la mejor fuente disponible
                var dientesFinales = '';
                if (todasLasFuentes.length > 0) {
                    dientesFinales = todasLasFuentes[0].dientes;
                    origenUtilizado = todasLasFuentes[0].origen;
                }
                
                // Limpiar y validar los números
                if (dientesFinales) {
                    // Dividir por comas, limpiar espacios, filtrar solo números válidos
                    var dientesArray = dientesFinales.split(',').map(num => num.trim());
                    
                    var dientesLimpios = dientesArray.filter(num => {
                        var numInt = parseInt(num);
                        var esValido = !isNaN(numInt) && numInt >= 11 && numInt <= 48;
                        return esValido;
                    });
                    
                    // Eliminar duplicados
                    dientesLimpios = [...new Set(dientesLimpios)];
                    
                    // Convertir a números enteros
                    dientesLimpios = dientesLimpios.map(num => parseInt(num, 10));
                    
                    // Ordenar numéricamente
                    dientesLimpios.sort((a, b) => a - b);
                    
                    // Almacenar array de dientes como datos JSON para uso en el servidor
                    window.dientesArrayJSON = JSON.stringify(dientesLimpios);
                    
                    // Guardar en un campo oculto adicional para el backend
                    if (!$('#dientes_array_json').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            id: 'dientes_array_json',
                            name: 'dientes_array_json',
                            value: window.dientesArrayJSON
                        }).appendTo('#consultaForm');
                    } else {
                        $('#dientes_array_json').val(window.dientesArrayJSON);
                    }
                    
                    // Unir con comas para el campo tradicional
                    dientesFinales = dientesLimpios.join(',');
                } else {
                    window.dientesArrayJSON = '[]';
                    if (!$('#dientes_array_json').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            id: 'dientes_array_json',
                            name: 'dientes_array_json',
                            value: '[]'
                        }).appendTo('#consultaForm');
                    } else {
                        $('#dientes_array_json').val('[]');
                    }
                }
                
                // PASO 4: Asegurarse de que el campo JSON tiene los datos correctos
                // El campo dientes_seleccionados ha sido eliminado, ahora todo se maneja con el JSON
                if (!$('#dientes_array_json').val() || $('#dientes_array_json').val() === '') {
                    $('#dientes_array_json').val('[]');
                }
                
                // Guardar en variable global para referencia
                window.dientesArrayJSON = $('#dientes_array_json').val();
                window.dientesSeleccionadosStr = dientesFinales;
                
                // PASO 6: Mostrar confirmación visual
                var mensajeConfirmacion;
                if (dientesFinales) {
                    mensajeConfirmacion = '<div id="confirmacion-dientes" style="background:#d4edda; color:#155724; padding:10px; margin:10px 0; border-radius:5px;">' + 
                          '<strong>✅ Dientes seleccionados: </strong>' + dientesFinales + '</div>';
                } else {
                    mensajeConfirmacion = '<div id="aviso-sin-dientes" style="background:#cff4fc; color:#084298; padding:10px; margin:10px 0; border-radius:5px;">' + 
                          '<strong>ℹ️ Información: </strong>No se seleccionaron dientes. ' + 
                          'Se guardará un array vacío en el JSON.</div>';
                }
                
                // Mostrar el mensaje
                if ($('#confirmacion-dientes, #aviso-sin-dientes').length) {
                    $('#confirmacion-dientes, #aviso-sin-dientes').remove();
                }
                $('button[type="submit"]').before(mensajeConfirmacion);
                
                // PASO ADICIONAL: Mostrar alerta con los dientes seleccionados antes de enviar el formulario
                let dientesJSON;
                try {
                    dientesJSON = JSON.parse($('#dientes_array_json').val() || '[]');
                } catch (e) {
                    dientesJSON = [];
                }
                
                // Convertir a string para mostrar en alerta
                const dientesSeleccionadosStr = dientesJSON.length > 0 ? dientesJSON.join(', ') : '';
                
                // Mostrar información sobre los dientes seleccionados
                if (dientesSeleccionadosStr) {
                    alert('Diente(s) seleccionado(s): ' + dientesSeleccionadosStr);
                } else {
                    alert('No se ha seleccionado ningún diente');
                }
                
                // PASO FINAL: Continuar con el envío del formulario después de un breve retraso
                // para que el usuario vea la confirmación
                setTimeout(function() {
                    console.log('Enviando formulario con dientes (JSON):', $('#dientes_array_json').val());
                    $('#consultaForm')[0].submit();
                }, 500);
            });
            
            // Función para verificar y forzar la actualización de la lista de dientes seleccionados
            function verificarYActualizarSeleccionDientes() {
                // Verificar si el odontograma está cargado
                if ($('#odontograma').length > 0) {
                    // Verificar si la función updateSeleccionados está disponible
                    if (typeof window.updateSeleccionados === 'function') {
                        // Forzar la actualización de la lista
                        window.updateSeleccionados();
                        console.log('Lista de dientes actualizada');
                        
                        // Verificar que la lista HTML esté presente
                        const listaHTML = document.getElementById('dientes-seleccionados-lista');
                        if (listaHTML) {
                            console.log('Lista HTML encontrada:', listaHTML.innerHTML);
                        } else {
                            console.warn('Lista HTML de dientes no encontrada');
                        }
                    } else {
                        console.log('Función updateSeleccionados no disponible aún');
                    }
                }
            }
            
            // Escuchar el evento personalizado del odontograma 
            document.addEventListener('dienteClic', function(e) {
                const detalle = e.detail;
                const numero = detalle.numero;
                
                // Nota: Ya no se procesan las posiciones de los dientes
            });
            
            // Variable global para almacenar el último diente seleccionado (solo para alertas)
            window.ultimoDienteSeleccionado = null;
            
            // Monitorear clics en dientes
            $(document).on('click', '.tooth-shape', function() {
                const numeroDialente = $(this).attr('data-num');
                
                // Almacenar el último diente seleccionado en una variable global (solo para alertas)
                window.ultimoDienteSeleccionado = numeroDialente;
                
                // Mostrar alerta con el número del diente
                alert('Diente seleccionado: ' + numeroDialente);
            });
            
            // Monitorear botones de selección masiva (cuadrantes, todos, etc.)
            $(document).on('click', '[id^="btn-"]', function() {
                setTimeout(() => {
                    verificarYActualizarSeleccionDientes();
                    
                    // Actualizar campo oculto
                    if (typeof window.seleccionados !== 'undefined') {
                        const dientesArray = Array.isArray(window.seleccionados) ? 
                            window.seleccionados : 
                            (window.seleccionados instanceof Set ? 
                                Array.from(window.seleccionados) : 
                                []
                            );
                        $('#dientes_seleccionados').val(dientesArray.join(','));
                    }
                }, 300);
            });
            
            // Función para calcular fecha de parto usando Fórmula de Naegele
            function calcularFechaParto(fechaUltimaRegla) {
                if (!fechaUltimaRegla) return null;
                
                try {
                    const fecha = new Date(fechaUltimaRegla);
                    if (isNaN(fecha.getTime())) return null;
                    
                    // Fórmula de Naegele: sumar 280 días (40 semanas) a la FUR
                    const fechaParto = new Date(fecha);
                    fechaParto.setDate(fechaParto.getDate() + 280);
                    
                    return fechaParto.toISOString().split('T')[0]; // Formato YYYY-MM-DD
                } catch (e) {
                    console.error('Error calculando fecha de parto:', e);
                    return null;
                }
            }
            
            // Función para actualizar fecha de parto automáticamente
            function actualizarFechaParto() {
                const embarazadaField = $('input[name="campo_embarazada"], select[name="campo_embarazada"]');
                const fechaUltimaReglaField = $('input[name="campo_fecha_ultima_regla"]');
                const fechaPartoField = $('input[name="campo_fecha_parto"]');
                
                console.log('Verificando campos de ginecología...');
                console.log('Campo embarazada encontrado:', embarazadaField.length > 0);
                console.log('Campo fecha última regla encontrado:', fechaUltimaReglaField.length > 0);
                
                // Verificar si los campos principales existen
                if (embarazadaField.length && fechaUltimaReglaField.length) {
                    const embarazada = embarazadaField.val();
                    const fechaUltimaRegla = fechaUltimaReglaField.val();
                    
                    console.log('Valor embarazada:', embarazada);
                    console.log('Valor fecha última regla:', fechaUltimaRegla);
                    
                    // Si está embarazada (true) y hay fecha de última regla
                    if ((embarazada === 'Si' || embarazada === '1' || embarazada === 'true' || embarazada === true) && fechaUltimaRegla) {
                        const fechaParto = calcularFechaParto(fechaUltimaRegla);
                        console.log('Fecha de parto calculada:', fechaParto);
                        
                        if (fechaParto) {
                            // Actualizar el campo de fecha de parto si existe
                            if (fechaPartoField.length) {
                                fechaPartoField.val(fechaParto);
                                
                                // Mostrar mensaje informativo en el campo
                                let mensajeInfo = $('#mensaje-fecha-parto');
                                if (mensajeInfo.length === 0) {
                                    mensajeInfo = $('<div id="mensaje-fecha-parto" class="alert alert-info mt-2"></div>');
                                    fechaPartoField.closest('.form-group').append(mensajeInfo);
                                }
                                mensajeInfo.html('<i class="fas fa-info-circle"></i> Fecha calculada automáticamente usando la Fórmula de Naegele (FUR + 280 días)');
                            }
                            
                            // Mostrar letrero al lado de la fecha de consulta
                            const fechaPartoFormateada = new Date(fechaParto).toLocaleDateString('es-ES', {
                                weekday: 'long',
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            });
                            
                            $('#fecha-parto-calculada').text(fechaPartoFormateada);
                            $('#letrero-fecha-parto').slideDown();
                            
                            // Mostrar alerta con la fecha de parto
                            const alertaMensaje = `🍼 FECHA PROBABLE DE PARTO: ${fechaPartoFormateada.toUpperCase()}`;
                            alert(alertaMensaje);
                            
                            console.log('✅ Fecha de parto mostrada:', fechaPartoFormateada);
                        }
                    } else {
                        // Limpiar fecha de parto si no está embarazada o no hay fecha
                        if (fechaPartoField.length) {
                            fechaPartoField.val('');
                        }
                        $('#mensaje-fecha-parto').remove();
                        $('#letrero-fecha-parto').slideUp();
                        console.log('❌ Condiciones no cumplidas - ocultando fecha de parto');
                    }
                } else {
                    // Si no existen los campos, ocultar el letrero
                    $('#letrero-fecha-parto').slideUp();
                    console.log('⚠️ Campos de ginecología no encontrados');
                }
            }
            
            // Escuchar cambios en los campos relacionados con ginecología
            $(document).on('change', 'input[name="campo_embarazada"], select[name="campo_embarazada"]', function() {
                console.log('🔄 Campo embarazada cambió a:', $(this).val());
                setTimeout(() => actualizarFechaParto(), 100);
            });
            
            $(document).on('change blur', 'input[name="campo_fecha_ultima_regla"]', function() {
                const fechaIngresada = $(this).val();
                console.log('📅 Fecha última regla cambió a:', fechaIngresada);
                
                // Solo procesar si hay una fecha válida
                if (fechaIngresada) {
                    setTimeout(() => actualizarFechaParto(), 100);
                }
            });
            
            // Observar cuando se cargan campos dinámicos para aplicar la funcionalidad
            const observer2 = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        // Verificar si se agregaron campos de ginecología
                        setTimeout(() => {
                            actualizarFechaParto();
                        }, 500);
                    }
                });
            });
            
            if (document.getElementById('campos_dinamicos')) {
                observer2.observe(document.getElementById('campos_dinamicos'), { 
                    childList: true, 
                    subtree: true 
                });
            }
            
            // Verificación periódica inicial para asegurar que el sistema funcione
            let verificacionesRealizadas = 0;
            const verificacionInterval = setInterval(() => {
                verificacionesRealizadas++;
                
                if ($('#odontograma').length > 0 && typeof window.updateSeleccionados === 'function') {
                    console.log('Sistema de odontograma detectado y funcionando');
                    verificarYActualizarSeleccionDientes();
                    clearInterval(verificacionInterval);
                } else if (verificacionesRealizadas >= 20) {
                    // Después de 10 segundos (20 verificaciones * 500ms), detener
                    console.log('Tiempo límite alcanzado para detectar odontograma');
                    clearInterval(verificacionInterval);
                }
                
                // También verificar campos de ginecología en cada iteración
                actualizarFechaParto();
            }, 500);
        });
    </script>
</body>
</html>