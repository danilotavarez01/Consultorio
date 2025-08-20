<?php
/**
 * Solución Completa para el Problema de Impresión de Recibos
 * Este script corrige el flujo de datos entre el registro de pagos y la impresión
 */
require_once 'session_config.php';
session_start();
require_once 'config.php';

// Verificar autenticación
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit();
}

// Función para obtener datos completos de un pago
function obtenerDatosPago($conn, $pago_id) {
    try {
        $stmt = $conn->prepare("
            SELECT p.id as pago_id, p.monto, p.metodo_pago, p.observaciones,
                   p.fecha_pago, p.numero_referencia,
                   f.id as factura_id, f.numero_factura, f.total as total_factura,
                   f.fecha_factura,
                   CONCAT(pac.nombre, ' ', pac.apellido) as paciente_nombre,
                   pac.dni as paciente_cedula, pac.telefono as paciente_telefono,
                   u.nombre as medico_nombre,
                   DATE_FORMAT(p.fecha_pago, '%d/%m/%Y %H:%i') as fecha_pago_formato
            FROM pagos p
            LEFT JOIN facturas f ON p.factura_id = f.id
            LEFT JOIN pacientes pac ON f.paciente_id = pac.id
            LEFT JOIN usuarios u ON f.medico_id = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$pago_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return false;
    }
}

// Manejar diferentes acciones
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'preparar_impresion':
        // Preparar datos para impresión basado en pago_id
        $pago_id = $_POST['pago_id'] ?? $_GET['pago_id'] ?? '';
        
        if (empty($pago_id)) {
            echo json_encode(['success' => false, 'message' => 'ID de pago no proporcionado']);
            break;
        }
        
        $datos_pago = obtenerDatosPago($conn, $pago_id);
        
        if ($datos_pago) {
            // Guardar en sesión de forma robusta
            $_SESSION['ultimo_pago'] = $datos_pago;
            $_SESSION['ultimo_pago_timestamp'] = time();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Datos preparados para impresión',
                'pago_id' => $pago_id,
                'datos' => $datos_pago
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el pago especificado']);
        }
        break;
        
    case 'verificar_datos':
        // Verificar si hay datos de impresión disponibles
        if (isset($_SESSION['ultimo_pago'])) {
            echo json_encode([
                'success' => true,
                'tiene_datos' => true,
                'pago_id' => $_SESSION['ultimo_pago']['pago_id'] ?? null,
                'timestamp' => $_SESSION['ultimo_pago_timestamp'] ?? 0,
                'datos' => $_SESSION['ultimo_pago']
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'tiene_datos' => false,
                'message' => 'No hay datos de impresión en sesión'
            ]);
        }
        break;
        
    case 'obtener_ultimo_pago':
        // Obtener el último pago de la base de datos
        try {
            $stmt = $conn->query("SELECT id FROM pagos ORDER BY id DESC LIMIT 1");
            $ultimo_pago = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($ultimo_pago) {
                $datos_pago = obtenerDatosPago($conn, $ultimo_pago['id']);
                
                if ($datos_pago) {
                    $_SESSION['ultimo_pago'] = $datos_pago;
                    $_SESSION['ultimo_pago_timestamp'] = time();
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Último pago cargado exitosamente',
                        'pago_id' => $ultimo_pago['id'],
                        'datos' => $datos_pago
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al obtener datos del último pago']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'No hay pagos en la base de datos']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
        }
        break;
        
    case 'limpiar_datos':
        // Limpiar datos de impresión
        unset($_SESSION['ultimo_pago']);
        unset($_SESSION['ultimo_pago_timestamp']);
        unset($_SESSION['show_print_modal']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Datos de impresión limpiados'
        ]);
        break;
        
    default:
        // Mostrar página de gestión
        mostrarPaginaGestion($conn);
        break;
}

function mostrarPaginaGestion($conn) {
    // Obtener últimos pagos para referencia
    try {
        $stmt = $conn->query("
            SELECT p.id, p.monto, p.fecha_pago, f.numero_factura, 
                   CONCAT(pac.nombre, ' ', pac.apellido) as paciente
            FROM pagos p
            LEFT JOIN facturas f ON p.factura_id = f.id
            LEFT JOIN pacientes pac ON f.paciente_id = pac.id
            ORDER BY p.id DESC
            LIMIT 10
        ");
        $ultimos_pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $ultimos_pagos = [];
    }
    
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Gestión de Impresión de Recibos</title>
        <link rel="stylesheet" href="assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="assets/css/fontawesome.min.css">
        <style>
            .status-good { color: #28a745; }
            .status-warning { color: #ffc107; }
            .status-error { color: #dc3545; }
        </style>
    </head>
    <body>
        <div class="container mt-4">
            <h2><i class="fas fa-print"></i> Gestión de Impresión de Recibos</h2>
            <hr>
            
            <!-- Estado actual -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle"></i> Estado Actual</h5>
                </div>
                <div class="card-body">
                    <div id="estado-actual">
                        <i class="fas fa-spinner fa-spin"></i> Verificando estado...
                    </div>
                </div>
            </div>
            
            <!-- Acciones -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-cogs"></i> Acciones Disponibles</h5>
                </div>
                <div class="card-body">
                    <div class="btn-group-vertical w-100">
                        <button onclick="cargarUltimoPago()" class="btn btn-primary mb-2">
                            <i class="fas fa-download"></i> Cargar Último Pago para Impresión
                        </button>
                        <button onclick="verificarDatos()" class="btn btn-info mb-2">
                            <i class="fas fa-search"></i> Verificar Datos de Impresión
                        </button>
                        <button onclick="limpiarDatos()" class="btn btn-warning mb-2">
                            <i class="fas fa-broom"></i> Limpiar Datos de Sesión
                        </button>
                        <a href="facturacion.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver a Facturación
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Últimos pagos -->
            <?php if (!empty($ultimos_pagos)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> Últimos Pagos Registrados</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Factura</th>
                                        <th>Paciente</th>
                                        <th>Monto</th>
                                        <th>Fecha</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimos_pagos as $pago): ?>
                                        <tr>
                                            <td><?= $pago['id'] ?></td>
                                            <td><?= htmlspecialchars($pago['numero_factura'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($pago['paciente'] ?? 'N/A') ?></td>
                                            <td>$<?= number_format($pago['monto'], 2) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($pago['fecha_pago'])) ?></td>
                                            <td>
                                                <button onclick="prepararImpresion(<?= $pago['id'] ?>)" class="btn btn-sm btn-success">
                                                    <i class="fas fa-print"></i> Imprimir
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <script>
        // Verificar estado al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            verificarDatos();
        });
        
        function verificarDatos() {
            fetch('?action=verificar_datos', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    const estado = document.getElementById('estado-actual');
                    
                    if (data.tiene_datos) {
                        estado.innerHTML = `
                            <div class="alert alert-success">
                                <h6><i class="fas fa-check-circle status-good"></i> Datos de impresión disponibles</h6>
                                <ul class="mb-0">
                                    <li><strong>Pago ID:</strong> ${data.pago_id}</li>
                                    <li><strong>Paciente:</strong> ${data.datos.paciente_nombre || 'N/A'}</li>
                                    <li><strong>Monto:</strong> $${parseFloat(data.datos.monto || 0).toFixed(2)}</li>
                                    <li><strong>Factura:</strong> ${data.datos.numero_factura || 'N/A'}</li>
                                </ul>
                                <div class="mt-3">
                                    <button onclick="imprimirRecibo()" class="btn btn-success">
                                        <i class="fas fa-print"></i> Imprimir Recibo Ahora
                                    </button>
                                </div>
                            </div>
                        `;
                    } else {
                        estado.innerHTML = `
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle status-warning"></i> No hay datos de impresión</h6>
                                <p class="mb-0">No hay información de pago preparada para impresión.</p>
                                <div class="mt-3">
                                    <button onclick="cargarUltimoPago()" class="btn btn-primary">
                                        <i class="fas fa-download"></i> Cargar Último Pago
                                    </button>
                                </div>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('estado-actual').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle status-error"></i> Error al verificar estado
                        </div>
                    `;
                });
        }
        
        function cargarUltimoPago() {
            fetch('?action=obtener_ultimo_pago', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Último pago cargado exitosamente.\nPago ID: ' + data.pago_id);
                        verificarDatos();
                    } else {
                        alert('❌ Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de comunicación con el servidor');
                });
        }
        
        function prepararImpresion(pagoId) {
            fetch('?action=preparar_impresion', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'pago_id=' + pagoId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Abrir ventana de impresión inmediatamente
                    const url = 'imprimir_recibo.php?pago_id=' + pagoId;
                    const ventana = window.open(url, 'recibo_' + pagoId, 
                        'width=450,height=700,scrollbars=yes,resizable=yes');
                    
                    if (!ventana) {
                        alert('No se pudo abrir la ventana de impresión. Verifique las ventanas emergentes.');
                    } else {
                        console.log('Ventana de impresión abierta para pago:', pagoId);
                    }
                } else {
                    alert('Error al preparar impresión: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de comunicación');
            });
        }
        
        function imprimirRecibo() {
            // Usar los datos que ya están en sesión
            window.open('imprimir_recibo.php', 'recibo_actual', 
                'width=450,height=700,scrollbars=yes,resizable=yes');
        }
        
        function limpiarDatos() {
            if (confirm('¿Está seguro de que desea limpiar los datos de impresión?')) {
                fetch('?action=limpiar_datos', { method: 'POST' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('✅ Datos limpiados exitosamente');
                            verificarDatos();
                        } else {
                            alert('❌ Error al limpiar datos');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error de comunicación');
                    });
            }
        }
        </script>
    </body>
    </html>
    <?php
}
?>

