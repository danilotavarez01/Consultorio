<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';
require_once 'permissions.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || !isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}

// Verificar permisos para facturación
if (!hasPermission('ver_facturacion') && !hasPermission('crear_factura') && !isAdmin()) {
    echo "<div class='alert alert-danger'>No tiene permisos para acceder a esta sección.</div>";
    echo "<a href='index.php' class='btn btn-secondary'>Volver al Inicio</a>";
    exit();
}

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_factura') {
        $paciente_id = intval($_POST['paciente_id']);
        $medico_id = intval($_POST['medico_id'] ?? $_SESSION['id']);
        $medico_nombre = trim($_POST['medico_nombre'] ?? '');
        $turno_id = intval($_POST['turno_id'] ?? 0);
        $fecha_factura = $_POST['fecha_factura'] ?? date('Y-m-d');
        $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? date('Y-m-d', strtotime('+30 days'));
        $observaciones = trim($_POST['observaciones'] ?? '');
        $items = $_POST['items'] ?? [];
        
        if (empty($paciente_id) || empty($items)) {
            $error = "Debe seleccionar un paciente y agregar al menos un item.";
        } else {
            try {
                $conn->beginTransaction();
                
                // Generar número de factura
                $stmt = $conn->query("SELECT numero_factura FROM facturas ORDER BY id DESC LIMIT 1");
                $ultimo_numero = $stmt->fetchColumn();
                
                if ($ultimo_numero) {
                    $numero = intval(substr($ultimo_numero, 4)) + 1;
                } else {
                    $numero = 1;
                }
                $numero_factura = 'FAC-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
                
                // Calcular totales
                $subtotal = 0;
                $descuento_total = 0;
                
                foreach ($items as $item) {
                    $cantidad = floatval($item['cantidad']);
                    $precio = floatval($item['precio']);
                    $descuento = floatval($item['descuento'] ?? 0);
                    $item_subtotal = ($cantidad * $precio) - $descuento;
                    $subtotal += $item_subtotal;
                    $descuento_total += $descuento;
                }
                
                $total = $subtotal;
                
                // Crear factura
                $stmt = $conn->prepare("
                    INSERT INTO facturas (numero_factura, paciente_id, medico_id, medico_nombre, fecha_factura, fecha_vencimiento, 
                                         subtotal, descuento, total, observaciones, estado) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')
                ");
                $stmt->execute([$numero_factura, $paciente_id, $medico_id, $medico_nombre, $fecha_factura, $fecha_vencimiento, 
                               $subtotal, $descuento_total, $total, $observaciones]);
                
                $factura_id = $conn->lastInsertId();
                
                // Insertar detalles
                $stmt_detalle = $conn->prepare("
                    INSERT INTO factura_detalles (factura_id, procedimiento_id, descripcion, cantidad, precio_unitario, descuento_item, subtotal) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                foreach ($items as $item) {
                    $cantidad = floatval($item['cantidad']);
                    $precio = floatval($item['precio']);
                    $descuento = floatval($item['descuento'] ?? 0);
                    $item_subtotal = ($cantidad * $precio) - $descuento;

                    $descripcion = isset($item['descripcion']) ? $item['descripcion'] : '';
                    $stmt_detalle->execute([
                        $factura_id,
                        !empty($item['procedimiento_id']) ? intval($item['procedimiento_id']) : null,
                        $descripcion,
                        $cantidad,
                        $precio,
                        $descuento,
                        $item_subtotal
                    ]);
                }
                
                $conn->commit();

                // Cambiar estado del turno a 'atendido' si se envió turno_id
                if ($turno_id > 0) {
                    $stmt = $conn->prepare("UPDATE turnos SET estado = 'atendido' WHERE id = ?");
                    $stmt->execute([$turno_id]);
                }
                
                // Redirigir para evitar reenvío de formulario
                $_SESSION['success_message'] = "Factura $numero_factura creada exitosamente.";
                header("Location: facturacion.php");
                exit();
                
            } catch (PDOException $e) {
                if ($conn->inTransaction()) {
                    $conn->rollback();
                }
                $error = "Error al crear la factura: " . $e->getMessage();
            }
        }
    } elseif ($action === 'update_estado') {
        $factura_id = intval($_POST['factura_id']);
        $estado = $_POST['estado'];
        
        try {
            $stmt = $conn->prepare("UPDATE facturas SET estado = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$estado, $factura_id]);
            
            // Redirigir para evitar reenvío de formulario
            $_SESSION['success_message'] = "Estado de la factura actualizado.";
            header("Location: facturacion.php");
            exit();
        } catch (PDOException $e) {
            $error = "Error al actualizar estado: " . $e->getMessage();
        }
    } elseif ($action === 'add_pago') {
        $factura_id = intval($_POST['factura_id']);
        $monto = floatval($_POST['monto']);
        $metodo_pago = $_POST['metodo_pago'];
        $numero_referencia = trim($_POST['numero_referencia'] ?? '');
        $observaciones_pago = trim($_POST['observaciones_pago'] ?? '');
        
        try {
            $conn->beginTransaction();
            
            // Insertar pago
            $stmt = $conn->prepare("
                INSERT INTO pagos (factura_id, fecha_pago, monto, metodo_pago, numero_referencia, observaciones) 
                VALUES (?, CURDATE(), ?, ?, ?, ?)
            ");
            $stmt->execute([$factura_id, $monto, $metodo_pago, $numero_referencia, $observaciones_pago]);
            
            $pago_id = $conn->lastInsertId();
            
            // Verificar si la factura está completamente pagada
            $stmt = $conn->prepare("
                SELECT f.total, COALESCE(SUM(p.monto), 0) as total_pagado 
                FROM facturas f 
                LEFT JOIN pagos p ON f.id = p.factura_id 
                WHERE f.id = ? 
                GROUP BY f.id
            ");
            $stmt->execute([$factura_id]);
            $factura_info = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($factura_info && $factura_info['total_pagado'] >= $factura_info['total']) {
                $stmt = $conn->prepare("UPDATE facturas SET estado = 'pagada', updated_at = NOW() WHERE id = ?");
                $stmt->execute([$factura_id]);
            }
            
            $conn->commit();
            
            // Obtener información completa de la factura para el modal
            $stmt = $conn->prepare("
                SELECT f.numero_factura, f.total, f.id,
                       CONCAT(p.nombre, ' ', p.apellido) as paciente_nombre,
                       p.telefono as paciente_telefono,
                       p.dni as paciente_cedula,
                       u.nombre as medico_nombre
                FROM facturas f
                LEFT JOIN pacientes p ON f.paciente_id = p.id
                LEFT JOIN usuarios u ON f.medico_id = u.id
                WHERE f.id = ?
            ");
            $stmt->execute([$factura_id]);
            $factura_completa = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Guardar datos completos para el modal de impresión
            $_SESSION['ultimo_pago'] = [
                'pago_id' => $pago_id,
                'factura_id' => $factura_id,
                'numero_factura' => $factura_completa['numero_factura'] ?? 'FAC-' . $factura_id,
                'paciente_nombre' => $factura_completa['paciente_nombre'] ?? 'Paciente',
                'paciente_telefono' => $factura_completa['paciente_telefono'] ?? '',
                'paciente_cedula' => $factura_completa['paciente_cedula'] ?? '',
                'medico_nombre' => $factura_completa['medico_nombre'] ?? '',
                'monto' => $monto,
                'metodo_pago' => $metodo_pago,
                'numero_referencia' => $numero_referencia,
                'observaciones' => $observaciones_pago,
                'fecha' => date('Y-m-d H:i:s'),
                'usuario_id' => $_SESSION['id']
            ];
            
            // Activar modal de impresión
          
            $_SESSION['show_print_modal'] = true;

           
            
            // Redirigir para evitar reenvío de formulario - CON PARÁMETRO DE ÉXITO
            $_SESSION['success_message'] = "Pago registrado exitosamente.";
            header("Location: facturacion.php?pago_exitoso=1");
            exit();
            
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollback();
            }
            $error = "Error al registrar pago: " . $e->getMessage();
        }
    }
}

// Obtener filtros
$filtro_estado = $_GET['estado'] ?? '';
$filtro_paciente = $_GET['paciente'] ?? '';
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';

// Construir consulta con filtros
$where_conditions = [];
$params = [];

if (!empty($filtro_estado)) {
    $where_conditions[] = "f.estado = ?";
    $params[] = $filtro_estado;
}

if (!empty($filtro_paciente)) {
    $where_conditions[] = "(p.nombre LIKE ? OR p.apellido LIKE ?)";
    $search_paciente = "%$filtro_paciente%";
    $params[] = $search_paciente;
    $params[] = $search_paciente;
}

if (!empty($fecha_desde)) {
    $where_conditions[] = "f.fecha_factura >= ?";
    $params[] = $fecha_desde;
}

if (!empty($fecha_hasta)) {
    $where_conditions[] = "f.fecha_factura <= ?";
    $params[] = $fecha_hasta;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

try {
    $stmt = $conn->prepare("
        SELECT f.*, 
               CONCAT(p.nombre, ' ', p.apellido) as paciente_nombre,
               u.nombre as medico_nombre,
               COALESCE(SUM(pg.monto), 0) as total_pagado
        FROM facturas f
        LEFT JOIN pacientes p ON f.paciente_id = p.id
        LEFT JOIN usuarios u ON f.medico_id = u.id  
        LEFT JOIN pagos pg ON f.id = pg.factura_id
        $where_clause
        GROUP BY f.id
        ORDER BY f.fecha_factura DESC, f.id DESC
    ");
    $stmt->execute($params);
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al obtener facturas: " . $e->getMessage();
    $facturas = [];
}

// Obtener pacientes para el selector
try {
    $stmt = $conn->query("SELECT id, nombre, apellido, seguro_nombre, seguro_monto FROM pacientes ORDER BY nombre, apellido");
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $pacientes = [];
}

// Obtener procedimientos para el selector
try {
    $stmt = $conn->query("SELECT id, codigo, nombre, precio_venta FROM procedimientos WHERE activo = 1 ORDER BY nombre");
    $procedimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $procedimientos = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Facturación - Sistema de Consultorios</title>
    
    <!-- Librerías locales optimizadas -->
    <link rel="stylesheet" href="assets/libs/bootstrap.min.css">
    <link rel="stylesheet" href="assets/libs/fontawesome.local.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    
    <!-- JavaScript local optimizado -->
    <script src="assets/libs/jquery-3.6.0.min.js"></script>
    <script src="assets/libs/bootstrap.bundle.min.js"></script>
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; }
        .sidebar a:hover { background-color: #454d55; text-decoration: none; }
        .content { padding: 20px; }
        .estado-badge {
            font-size: 0.75em;
        }
        .precio-cell {
            text-align: right;
            font-family: monospace;
        }
        .card-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        .factura-item {
            border-bottom: 1px solid #dee2e6;
            padding: 10px 0;
        }
        .factura-item:last-child {
            border-bottom: none;
        }
        /* Estilos básicos para la interfaz */
        .item-row {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
        
        /* Modal de pago exitoso */
        #modalPagoExitoso .modal-content {
            border-radius: 15px;
        }
        
        #modalPagoExitoso .btn {
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        
        #modalPagoExitoso .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
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
                <div class="d-flex justify-content-between align-items-center my-4">
                    <h1><i class="fas fa-file-invoice-dollar mr-2"></i>Gestión de Facturación</h1>
                    <div>
                        <?php if (hasPermission('crear_factura') || isAdmin()): ?>
                            <button type="button" class="btn btn-primary mr-2" data-toggle="modal" data-target="#modalCrearFactura">
                                <i class="fas fa-plus mr-2"></i>Nueva Factura
                            </button>
                        <?php endif; ?>
                        
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Volver al Inicio
                        </a>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success) ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_SESSION['success_message']) ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <!-- BOTÓN DE PRUEBA TEMPORAL - ELIMINAR DESPUÉS -->
                <div class="alert alert-warning">
                    <h5><i class="fas fa-test-tube mr-2"></i>Modo de Prueba</h5>
                    <p>Usa estos botones para probar el modal de pago exitoso:</p>
                    <a href="debug_modal_pago_completo.php" class="btn btn-warning">
                        <i class="fas fa-bug mr-2"></i>Diagnóstico Completo del Modal
                    </a>
                    <button onclick="mostrarModalPrueba()" class="btn btn-success ml-2">
                        <i class="fas fa-eye mr-2"></i>Mostrar Modal de Prueba
                    </button>
                    <a href="debug_modal_pago_completo.php?simular_pago=1" class="btn btn-info ml-2">
                        <i class="fas fa-play mr-2"></i>Simular Pago Real
                    </a>
                    <a href="test_pago_completo.php" class="btn btn-primary ml-2">
                        <i class="fas fa-cogs mr-2"></i>Test Flujo Completo
                    </a>
                </div>

                <!-- Modal de Pago Exitoso - SIEMPRE PRESENTE -->
                <div class="modal fade" id="modalPagoExitoso" tabindex="-1" data-backdrop="static" data-keyboard="false">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header bg-success text-white border-0">
                                <h4 class="modal-title w-100 text-center mb-0">
                                    <i class="fas fa-check-circle fa-lg mr-2"></i>¡Pago Registrado Exitosamente!
                                </h4>
                            </div>
                            <div class="modal-body text-center py-4">
                                <div class="mb-4">
                                    <i class="fas fa-receipt text-success fa-4x mb-3"></i>
                                    <h5 class="text-success font-weight-bold">El pago se ha registrado correctamente</h5>
                                </div>
                                
                                <div class="card bg-light border-0 mx-auto" style="max-width: 350px;">
                                    <div class="card-body py-3">
                                        <table class="table table-sm table-borderless mb-0">
                                            <tr>
                                                <td class="text-right font-weight-bold">Factura:</td>
                                                <td class="text-left" id="modal-numero-factura">N/A</td>
                                            </tr>
                                            <tr>
                                                <td class="text-right font-weight-bold">Paciente:</td>
                                                <td class="text-left" id="modal-paciente-nombre">Paciente</td>
                                            </tr>
                                            <tr class="border-top">
                                                <td class="text-right font-weight-bold text-success">Monto Pagado:</td>
                                                <td class="text-left font-weight-bold text-success h5 mb-0" id="modal-monto">$0.00</td>
                                            </tr>
                                            <tr>
                                                <td class="text-right font-weight-bold">Método:</td>
                                                <td class="text-left" id="modal-metodo-pago">Efectivo</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info border-0 mt-4 mx-auto" style="max-width: 350px;">
                                    <i class="fas fa-print mr-2"></i>
                                    <strong>¿Desea imprimir el recibo del pago ahora?</strong>
                                </div>
                            </div>
                            <div class="modal-footer border-0 justify-content-center pb-4">
                                <button type="button" class="btn btn-outline-secondary btn-lg px-4 mr-3" onclick="cerrarModalPago()">
                                    <i class="fas fa-times mr-2"></i>No, Gracias
                                </button>
                                <button type="button" class="btn btn-success btn-lg px-4" onclick="imprimirReciboModal()">
                                    <i class="fas fa-print mr-2"></i>Sí, Imprimir Recibo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- JavaScript para mostrar modal condicionalmente -->
                <?php 
                $mostrar_modal_pago = (isset($_SESSION['show_print_modal']) && $_SESSION['show_print_modal'] === true && isset($_SESSION['ultimo_pago'])) || 
                                     (isset($_GET['pago_exitoso']) && $_GET['pago_exitoso'] == '1' && isset($_SESSION['ultimo_pago']));
                
                if ($mostrar_modal_pago): 
                ?>
                <script>
                    // Debug: Verificar que las variables están disponibles
                    console.log('=== MODAL DE PAGO EXITOSO (PAGO REAL) ===');
                    console.log('Parámetro GET pago_exitoso:', <?= json_encode($_GET['pago_exitoso'] ?? 'no') ?>);
                    console.log('Variables de sesión detectadas:', {
                        show_print_modal: <?= json_encode($_SESSION['show_print_modal'] ?? false) ?>,
                        ultimo_pago: <?= json_encode($_SESSION['ultimo_pago'] ?? null) ?>
                    });
                    
                    $(document).ready(function() {
                        console.log('DOM listo - Configurando modal de pago real...');
                        
                        // Datos del pago desde PHP
                        const datosUltimoPago = <?= json_encode($_SESSION['ultimo_pago']) ?>;
                        
                        // Actualizar contenido del modal con datos reales
                        $('#modal-numero-factura').text(datosUltimoPago.numero_factura || 'N/A');
                        $('#modal-paciente-nombre').text(datosUltimoPago.paciente_nombre || 'Paciente');
                        $('#modal-monto').text('$' + parseFloat(datosUltimoPago.monto || 0).toFixed(2));
                        $('#modal-metodo-pago').text(datosUltimoPago.metodo_pago ? 
                            datosUltimoPago.metodo_pago.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Efectivo');
                        
                        console.log('✅ Datos del modal actualizados con información real');
                        
                        // Mostrar el modal con un pequeño delay para asegurar que el DOM esté completamente cargado
                        setTimeout(function() {
                            console.log('🚀 Intentando mostrar modal...');
                            
                            // Verificar que el modal existe en el DOM
                            if ($('#modalPagoExitoso').length === 0) {
                                console.error('❌ ERROR: Modal #modalPagoExitoso no encontrado en el DOM');
                                alert('Error: Modal no encontrado. Revisa la consola.');
                                return;
                            }
                            
                            console.log('✅ Modal encontrado en el DOM, mostrando...');
                            
                            $('#modalPagoExitoso').modal({
                                backdrop: 'static',
                                keyboard: false,
                                show: true
                            });
                            
                            console.log('✅ Modal de pago real mostrado exitosamente');
                            console.log('🎉 ¡PAGO REGISTRADO! Modal apareciendo automáticamente...');
                            
                            // Verificar que el modal se está mostrando
                            setTimeout(function() {
                                if ($('#modalPagoExitoso').hasClass('show')) {
                                    console.log('🎯 ÉXITO: Modal está visible para el usuario');
                                } else {
                                    console.error('❌ PROBLEMA: Modal no está visible');
                                }
                            }, 200);
                            
                        }, 300);
                    });
                </script>
                <?php 
                // Limpiar la variable show_print_modal después de mostrar el modal
                unset($_SESSION['show_print_modal']); 
                ?>
                <?php endif; ?>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-filter mr-2"></i>Filtros
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET">
                            <div class="row">
                                <div class="col-md-2">
                                    <select name="estado" class="form-control" onchange="this.form.submit()">
                                        <option value="">Todos los estados</option>
                                        <option value="pendiente" <?= $filtro_estado === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="pagada" <?= $filtro_estado === 'pagada' ? 'selected' : '' ?>>Pagada</option>
                                        <option value="vencida" <?= $filtro_estado === 'vencida' ? 'selected' : '' ?>>Vencida</option>
                                        <option value="cancelada" <?= $filtro_estado === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="paciente" class="form-control" placeholder="Buscar paciente..." 
                                           value="<?= htmlspecialchars($filtro_paciente) ?>">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="fecha_desde" class="form-control" 
                                           value="<?= htmlspecialchars($fecha_desde) ?>">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="fecha_hasta" class="form-control" 
                                           value="<?= htmlspecialchars($fecha_hasta) ?>">
                                </div>
                                <div class="col-md-3">
                                    <div class="btn-group w-100">
                                        <button type="submit" class="btn btn-outline-primary">
                                            <i class="fas fa-search"></i> Buscar
                                        </button>
                                        <a href="facturacion.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Limpiar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lista de Facturas -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list mr-2"></i>Lista de Facturas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Número</th>
                                        <th>Fecha</th>
                                        <th>Paciente</th>
                                        <th>Médico</th>
                                        <th>Total</th>
                                        <th>Pagado</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($facturas)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                                <br><strong>No se encontraron facturas</strong>
                                                <br><small class="text-muted">Use el botón "Nueva Factura" para comenzar.</small>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($facturas as $factura): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($factura['numero_factura']) ?></strong>
                                                </td>
                                                <td><?= date('d/m/Y', strtotime($factura['fecha_factura'])) ?></td>
                                                <td><?= htmlspecialchars($factura['paciente_nombre']) ?></td>
                                                <td><?= htmlspecialchars($factura['medico_nombre']) ?></td>
                                                <td class="precio-cell">
                                                    <strong>$<?= number_format($factura['total'], 2) ?></strong>
                                                </td>
                                                <td class="precio-cell">
                                                    $<?= number_format($factura['total_pagado'], 2) ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $badge_class = 'secondary';
                                                    switch($factura['estado']) {
                                                        case 'pendiente': $badge_class = 'warning'; break;
                                                        case 'pagada': $badge_class = 'success'; break;
                                                        case 'vencida': $badge_class = 'danger'; break;
                                                        case 'cancelada': $badge_class = 'dark'; break;
                                                    }
                                                    ?>
                                                    <span class="badge badge-<?= $badge_class ?> estado-badge">
                                                        <?= ucfirst($factura['estado']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-info" 
                                                                onclick="verDetalleFactura(<?= $factura['id'] ?>)" title="Ver Detalle">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <?php if (($factura['estado'] === 'pendiente') && (hasPermission('crear_factura') || isAdmin())): ?>
                                                            <button type="button" class="btn btn-outline-success" 
                                                                    onclick="agregarPago(<?= $factura['id'] ?>, '<?= htmlspecialchars($factura['numero_factura']) ?>', <?= $factura['total'] - $factura['total_pagado'] ?>)" 
                                                                    title="Agregar Pago">
                                                                <i class="fas fa-dollar-sign"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <?php if (hasPermission('editar_factura') || isAdmin()): ?>
                                                            <button type="button" class="btn btn-outline-warning" 
                                                                    onclick="cambiarEstado(<?= $factura['id'] ?>, '<?= htmlspecialchars($factura['numero_factura']) ?>', '<?= $factura['estado'] ?>')" 
                                                                    title="Cambiar Estado">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Resumen -->
                        <?php if (!empty($facturas)): ?>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <?php
                                    $total_facturado = array_sum(array_column($facturas, 'total'));
                                    $total_pagado_sum = array_sum(array_column($facturas, 'total_pagado'));
                                    $pendiente_cobro = $total_facturado - $total_pagado_sum;
                                    ?>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Total: <?= count($facturas) ?> factura(s) |
                                        <span class="text-info">Facturado: $<?= number_format($total_facturado, 2) ?></span> |
                                        <span class="text-success">Pagado: $<?= number_format($total_pagado_sum, 2) ?></span> |
                                        <span class="text-warning">Pendiente: $<?= number_format($pendiente_cobro, 2) ?></span>
                                    </small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Crear Factura -->
    <?php if (hasPermission('crear_factura') || isAdmin()): ?>
    <div class="modal fade" id="modalCrearFactura" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus mr-2"></i>Nueva Factura
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="create_factura">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                <!-- Datos del Seguro del Paciente -->
                                <div class="card mb-3 shadow-sm border-success" style="background: linear-gradient(90deg,#e9f7ef 60%,#d4edda 100%);">
                                    <div class="card-body py-2 px-3">
                                        <div class="row align-items-center">
                                            <div class="col-md-7">
                                                <label for="nuevo_seguro_nombre" class="font-weight-bold text-success mb-1">
                                                    <i class="fas fa-shield-alt mr-1"></i>Seguro del Paciente
                                                </label>
                                                <input type="text" class="form-control border-success bg-white" id="nuevo_seguro_nombre" name="nuevo_seguro_nombre" readonly placeholder="Nombre del seguro">
                                            </div>
                                            <div class="col-md-5">
                                                <label for="nuevo_seguro_monto" class="font-weight-bold text-success mb-1">
                                                    <i class="fas fa-dollar-sign mr-1"></i>Monto Seguro
                                                </label>
                                                <input type="number" class="form-control border-success bg-white" id="nuevo_seguro_monto" name="nuevo_seguro_monto" readonly placeholder="$0.00">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                    <label for="paciente_id">Paciente *</label>
                                    <select class="form-control" id="paciente_id" name="paciente_id" required>
                                        <option value="">Seleccionar paciente...</option>
                                        <?php foreach ($pacientes as $paciente): ?>
                                            <option value="<?= $paciente['id'] ?>" data-seguro="<?= isset($paciente['seguro_nombre']) ? htmlspecialchars($paciente['seguro_nombre']) : '' ?>" data-seguro-monto="<?= isset($paciente['seguro_monto']) ? htmlspecialchars($paciente['seguro_monto']) : '' ?>">
                                                <?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_factura">Fecha Factura</label>
                                    <input type="date" class="form-control" id="fecha_factura" name="fecha_factura" 
                                           value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_vencimiento">Fecha Vencimiento</label>
                                    <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" 
                                           value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Items de la Factura *</label>
                            <div id="items-container">
                                <div class="item-row" data-index="0">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <select class="form-control procedimiento-select" name="items[0][procedimiento_id]" 
                                                    onchange="cargarPrecioProcedimiento(this, 0)">
                                                <option value="">Seleccionar procedimiento...</option>
                                                <?php foreach ($procedimientos as $proc): ?>
                                                    <option value="<?= $proc['id'] ?>" data-precio="<?= $proc['precio_venta'] ?>">
                                                        <?= htmlspecialchars(($proc['codigo'] ? $proc['codigo'] . ' - ' : '') . $proc['nombre']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" name="items[0][descripcion]" 
                                                   placeholder="Descripción personalizada..." required>
                                        </div>
                                        <div class="col-md-1">
                                            <input type="number" class="form-control cantidad-input" name="items[0][cantidad]" 
                                                   placeholder="Cant." min="1" value="1" onchange="calcularSubtotal(0)" required>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" class="form-control precio-input" name="items[0][precio]" 
                                                   placeholder="Precio" step="0.01" min="0" onchange="calcularSubtotal(0)" required>
                                        </div>
                                        <div class="col-md-1">
                                            <input type="number" class="form-control descuento-input" name="items[0][descuento]" 
                                                   placeholder="Desc." step="0.01" min="0" value="0" onchange="calcularSubtotal(0)">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-danger btn-sm" onclick="eliminarItem(0)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            <small class="text-muted">Subtotal: $<span id="subtotal-0">0.00</span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="agregarItem()">
                                <i class="fas fa-plus mr-1"></i>Agregar Item
                            </button>
                        </div>

                        <div class="form-group">
                            <label for="observaciones">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6"></div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6"><strong>Subtotal:</strong></div>
                                            <div class="col-6 text-right">$<span id="total-subtotal">0.00</span></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6"><strong>Descuento:</strong></div>
                                            <div class="col-6 text-right">$<span id="total-descuento">0.00</span></div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-6"><strong>Total:</strong></div>
                                            <div class="col-6 text-right"><strong>$<span id="total-final">0.00</span></strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Crear Factura
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal Agregar Pago -->
    <div class="modal fade" id="modalAgregarPago" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-dollar-sign mr-2"></i>Agregar Pago
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST" onsubmit="console.log('🚀 Enviando formulario de pago...', this);">
                    <input type="hidden" name="action" value="add_pago">
                    <input type="hidden" name="factura_id" id="pago_factura_id">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Factura:</strong> <span id="pago_numero_factura"></span><br>
                            <strong>Monto pendiente:</strong> $<span id="pago_monto_pendiente"></span>
                        </div>

                        <div class="form-group">
                            <label for="seguro_nombre">Nombre del Seguro del Paciente</label>
                            <input type="text" class="form-control" id="seguro_nombre" name="seguro_nombre" placeholder="Nombre del seguro">
                        </div>

                        <div class="form-group">
                            <label for="seguro_monto">Monto Seguro</label>
                            <input type="number" class="form-control" id="seguro_monto" name="seguro_monto" step="0.01" min="0" placeholder="$0.00">
                        </div>

                        <div class="form-group">
                            <label for="monto">Monto del Pago *</label>
                            <input type="number" class="form-control" id="monto" name="monto" 
                                   step="0.01" min="0.01" required>
                        </div>

                        <div class="form-group">
                            <label for="metodo_pago">Método de Pago *</label>
                            <select class="form-control" id="metodo_pago" name="metodo_pago" required>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="tarjeta_credito">Tarjeta de Crédito</option>
                                <option value="tarjeta_debito">Tarjeta de Débito</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="numero_referencia">Número de Referencia</label>
                            <input type="text" class="form-control" id="numero_referencia" name="numero_referencia" 
                                   placeholder="Número de transacción, cheque, etc.">
                        </div>

                        <div class="form-group">
                            <label for="observaciones_pago">Observaciones</label>
                            <textarea class="form-control" id="observaciones_pago" name="observaciones_pago" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check mr-2"></i>Registrar Pago
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Cambiar Estado -->
    <div class="modal fade" id="modalCambiarEstado" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit mr-2"></i>Cambiar Estado
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update_estado">
                    <input type="hidden" name="factura_id" id="estado_factura_id">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Factura:</strong> <span id="estado_numero_factura"></span>
                        </div>
                        
                        <div class="form-group">
                            <label for="estado">Nuevo Estado *</label>
                            <select class="form-control" id="estado" name="estado" required>
                                <option value="pendiente">Pendiente</option>
                                <option value="pagada">Pagada</option>
                                <option value="vencida">Vencida</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save mr-2"></i>Cambiar Estado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts adicionales (jQuery ya está cargado en el head) -->
    <script src="js/theme-manager.js"></script>
    <script>
        let itemIndex = 1;

        // Solución para mostrar el modal de Nueva Factura si data-toggle/data-target no funciona
        document.addEventListener('DOMContentLoaded', function() {
            const btnNuevaFactura = document.querySelector('button[data-target="#modalCrearFactura"]');
            if (btnNuevaFactura) {
                btnNuevaFactura.addEventListener('click', function(e) {
                    e.preventDefault();
                    $('#modalCrearFactura').modal('show');
                });
            }

            // Evento para traer datos del seguro al seleccionar paciente
            const pacienteSelect = document.getElementById('paciente_id');
            if (pacienteSelect) {
                pacienteSelect.addEventListener('change', function() {
                    const selected = pacienteSelect.options[pacienteSelect.selectedIndex];
                    const seguroNombre = selected.getAttribute('data-seguro') || '';
                    const seguroMonto = selected.getAttribute('data-seguro-monto') || '';
                    document.getElementById('nuevo_seguro_nombre').value = seguroNombre;
                    document.getElementById('nuevo_seguro_monto').value = seguroMonto;
                });
            }
        });

        function agregarItem() {
            const container = document.getElementById('items-container');
            const newItem = document.createElement('div');
            newItem.className = 'item-row';
            newItem.setAttribute('data-index', itemIndex);
            
            newItem.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <select class="form-control procedimiento-select" name="items[${itemIndex}][procedimiento_id]" 
                                onchange="cargarPrecioProcedimiento(this, ${itemIndex})">
                            <option value="">Seleccionar procedimiento...</option>
                            <?php foreach ($procedimientos as $proc): ?>
                                <option value="<?= $proc['id'] ?>" data-precio="<?= $proc['precio_venta'] ?>">
                                    <?= htmlspecialchars(($proc['codigo'] ? $proc['codigo'] . ' - ' : '') . $proc['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="items[${itemIndex}][descripcion]" 
                               placeholder="Descripción personalizada..." required>
                    </div>
                    <div class="col-md-1">
                        <input type="number" class="form-control cantidad-input" name="items[${itemIndex}][cantidad]" 
                               placeholder="Cant." min="1" value="1" onchange="calcularSubtotal(${itemIndex})" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control precio-input" name="items[${itemIndex}][precio]" 
                               placeholder="Precio" step="0.01" min="0" onchange="calcularSubtotal(${itemIndex})" required>
                    </div>
                    <div class="col-md-1">
                        <input type="number" class="form-control descuento-input" name="items[${itemIndex}][descuento]" 
                               placeholder="Desc." step="0.01" min="0" value="0" onchange="calcularSubtotal(${itemIndex})">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm" onclick="eliminarItem(${itemIndex})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <small class="text-muted">Subtotal: $<span id="subtotal-${itemIndex}">0.00</span></small>
                    </div>
                </div>
            `;
            
            container.appendChild(newItem);
            itemIndex++;
        }

        function eliminarItem(index) {
            const item = document.querySelector(`[data-index="${index}"]`);
            if (item) {
                item.remove();
                calcularTotales();
            }
        }

        function cargarPrecioProcedimiento(select, index) {
            const option = select.options[select.selectedIndex];
            const precio = option.getAttribute('data-precio');
            const descripcionInput = document.querySelector(`input[name="items[${index}][descripcion]"]`);
            const precioInput = document.querySelector(`input[name="items[${index}][precio]"]`);
            
            if (precio && precioInput) {
                precioInput.value = precio;
                if (option.text && descripcionInput) {
                    descripcionInput.value = option.text.replace(/^[A-Z]{3,4}\d{3} - /, '');
                }
                calcularSubtotal(index);
            }
        }

        function calcularSubtotal(index) {
            const cantidadInput = document.querySelector(`input[name="items[${index}][cantidad]"]`);
            const precioInput = document.querySelector(`input[name="items[${index}][precio]"]`);
            const descuentoInput = document.querySelector(`input[name="items[${index}][descuento]"]`);
            const subtotalSpan = document.getElementById(`subtotal-${index}`);
            
            if (cantidadInput && precioInput && subtotalSpan) {
                const cantidad = parseFloat(cantidadInput.value) || 0;
                const precio = parseFloat(precioInput.value) || 0;
                const descuento = parseFloat(descuentoInput.value) || 0;
                const subtotal = (cantidad * precio) - descuento;
                
                subtotalSpan.textContent = subtotal.toFixed(2);
                calcularTotales();
            }
        }

        function calcularTotales() {
            let totalSubtotal = 0;
            let totalDescuento = 0;
            
            document.querySelectorAll('.item-row').forEach(function(item) {
                const index = item.getAttribute('data-index');
                const subtotalSpan = document.getElementById(`subtotal-${index}`);
                const descuentoInput = document.querySelector(`input[name="items[${index}][descuento]"]`);
                
                if (subtotalSpan) {
                    const subtotal = parseFloat(subtotalSpan.textContent) || 0;
                    const descuento = parseFloat(descuentoInput.value) || 0;
                    
                    totalSubtotal += subtotal + descuento; // Subtotal antes del descuento
                    totalDescuento += descuento;
                }
            });
            
            const totalFinal = totalSubtotal - totalDescuento;
            
            document.getElementById('total-subtotal').textContent = totalSubtotal.toFixed(2);
            document.getElementById('total-descuento').textContent = totalDescuento.toFixed(2);
            document.getElementById('total-final').textContent = totalFinal.toFixed(2);
        }

        function agregarPago(facturaId, numeroFactura, montoPendiente) {
            document.getElementById('pago_factura_id').value = facturaId;
            document.getElementById('pago_numero_factura').textContent = numeroFactura;
            document.getElementById('pago_monto_pendiente').textContent = montoPendiente.toFixed(2);
            document.getElementById('monto').value = montoPendiente.toFixed(2);

            // Buscar el nombre del seguro del paciente en la lista de facturas
            let seguroNombre = '';
            try {
                // Buscar la fila de la factura en la tabla
                const filas = document.querySelectorAll('table.table-striped tbody tr');
                for (let fila of filas) {
                    // Buscar el botón de agregar pago en la fila
                    const btn = fila.querySelector('button.btn-outline-success');
                    if (btn && btn.getAttribute('onclick') && btn.getAttribute('onclick').includes(facturaId)) {
                        // Buscar el nombre del seguro en un atributo data-seguro o en una celda oculta
                        // Si tienes el nombre del seguro en la estructura de la factura, puedes agregarlo como data-seguro
                        if (btn.hasAttribute('data-seguro')) {
                            seguroNombre = btn.getAttribute('data-seguro');
                        } else {
                            // Alternativamente, buscar en la fila una celda con clase 'seguro-nombre'
                            const celdaSeguro = fila.querySelector('.seguro-nombre');
                            if (celdaSeguro) {
                                seguroNombre = celdaSeguro.textContent.trim();
                            }
                        }
                        break;
                    }
                }
            } catch (e) {
                console.warn('No se pudo obtener el nombre del seguro:', e);
            }
            document.getElementById('seguro_nombre').value = seguroNombre;

            $('#modalAgregarPago').modal('show');
        }

        function cambiarEstado(facturaId, numeroFactura, estadoActual) {
            document.getElementById('estado_factura_id').value = facturaId;
            document.getElementById('estado_numero_factura').textContent = numeroFactura;
            document.getElementById('estado').value = estadoActual;
            $('#modalCambiarEstado').modal('show');
        }

        function verDetalleFactura(facturaId) {
            // TODO: Implementar modal de detalle de factura
            alert('Funcionalidad de detalle en desarrollo. ID: ' + facturaId);
        }

        // Calcular subtotal inicial
        document.addEventListener('DOMContentLoaded', function() {
            calcularSubtotal(0);
        });

        function imprimirRecibo() {
            // Función para imprimir recibo en impresora térmica 80mm
            window.open('imprimir_recibo_termico.php', 'recibo_termico', 'width=400,height=600,scrollbars=yes');
        }

        function imprimirReciboModal() {
            // Función para imprimir desde el modal y cerrarlo - Versión térmica
            window.open('imprimir_recibo_termico.php?auto_print=1', 'recibo_termico', 'width=400,height=600,scrollbars=yes');
            
            // Cerrar el modal después de un breve delay
            setTimeout(function() {
                cerrarModalPago();
            }, 1000);
        }

        function cerrarModalPago() {
            // Cerrar el modal de pago exitoso
            $('#modalPagoExitoso').modal('hide');
            
            // Limpiar los datos del último pago de la sesión
            fetch('clear_ultimo_pago.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=clear_all'
            })
            .then(response => response.json())
            .then(data => {
                console.log('Datos de pago limpiados:', data);
            })
            .catch(error => {
                console.warn('Advertencia al limpiar datos:', error);
            });
        }

        // FUNCIÓN DE PRUEBA - ELIMINAR DESPUÉS
        function mostrarModalPrueba() {
            console.log('🧪 Iniciando modal de prueba...');
            
            // Verificar si el modal del sistema existe
            if ($('#modalPagoExitoso').length > 0) {
                console.log('✅ Modal del sistema encontrado, usando modal real...');
                
                // Configurar datos de prueba en el modal real
                $('#modal-numero-factura').text('FAC-PRUEBA-001');
                $('#modal-paciente-nombre').text('Paciente de Prueba');
                $('#modal-monto').text('$150.00');
                $('#modal-metodo-pago').text('Efectivo');
                
                // Mostrar modal
                $('#modalPagoExitoso').modal('show');
                return;
            }
            
            console.log('⚠️ Modal del sistema no encontrado');
            alert('❌ Modal no encontrado en el DOM. Revisa la consola para más detalles.');
        }
        
        function cerrarModalPrueba() {
            $('#modalPagoPrueba').modal('hide');
            setTimeout(() => {
                $('#modalPagoPrueba').remove();
                alert('✅ Modal de prueba funcionando correctamente!\n\nSi ves este modal, significa que el código JavaScript funciona.');
            }, 300);
        }
        
        function imprimirPrueba() {
            // Simular datos de pago para la prueba de impresión térmica
            const datosSimulados = {
                factura_id: 999,
                numero_factura: 'FAC-TEST-' + Date.now(),
                paciente_nombre: 'Paciente de Prueba',
                monto: 150.00,
                metodo_pago: 'efectivo',
                fecha: new Date().toISOString()
            };
            
            // Crear sesión temporal para la prueba (usando fetch)
            fetch('crear_pago_prueba.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datosSimulados)
            })
            .then(() => {
                // Abrir ventana de impresión térmica
                window.open('imprimir_recibo_termico.php', 'prueba_termica', 'width=400,height=600,scrollbars=yes');
            })
            .catch(error => {
                console.error('Error en prueba:', error);
                alert('🖨️ Función de impresión térmica activada!\n\nEn el sistema real, esto abriría la ventana de impresión optimizada para impresoras térmicas de 80mm.');
            });
            
            cerrarModalPrueba();
        }
    </script>
</body>
</html>
