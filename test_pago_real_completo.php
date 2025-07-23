<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';
require_once 'permissions.php';

echo "<h2>🧪 SIMULADOR DE PAGO REAL COMPLETO</h2>";
echo "<hr>";

// Verificar que el usuario esté logueado
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || !isset($_SESSION['id'])) {
    echo "<p>❌ Usuario no logueado. <a href='index.php'>Ir a login</a></p>";
    exit();
}

// Si se envió el formulario, procesar el pago
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simular_pago'])) {
    try {
        $factura_id = intval($_POST['factura_id']);
        $monto = floatval($_POST['monto']);
        $metodo_pago = $_POST['metodo_pago'];
        $numero_referencia = trim($_POST['numero_referencia'] ?? '');
        $observaciones_pago = trim($_POST['observaciones_pago'] ?? '');
        
        echo "<h3>🚀 PROCESANDO PAGO SIMULADO...</h3>";
        
        $conn->beginTransaction();
        
        // Insertar pago real
        $stmt = $conn->prepare("
            INSERT INTO pagos (factura_id, fecha_pago, monto, metodo_pago, numero_referencia, observaciones) 
            VALUES (?, CURDATE(), ?, ?, ?, ?)
        ");
        $stmt->execute([$factura_id, $monto, $metodo_pago, $numero_referencia, $observaciones_pago]);
        
        $pago_id = $conn->lastInsertId();
        echo "<p>✅ Pago registrado con ID: $pago_id</p>";
        
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
            echo "<p>✅ Factura marcada como pagada completamente</p>";
        }
        
        $conn->commit();
        
        // EXACTAMENTE IGUAL QUE EN facturacion.php - Obtener información completa de la factura para el modal
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
        
        // EXACTAMENTE IGUAL QUE EN facturacion.php - Guardar datos completos para el modal de impresión
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
        
        // EXACTAMENTE IGUAL QUE EN facturacion.php - Activar modal de impresión
        $_SESSION['show_print_modal'] = true;
        
        echo "<h3>✅ PAGO REGISTRADO EXITOSAMENTE</h3>";
        echo "<p>📦 Variables de sesión configuradas:</p>";
        echo "<pre>" . print_r($_SESSION['ultimo_pago'], true) . "</pre>";
        echo "<p><strong>show_print_modal:</strong> " . ($_SESSION['show_print_modal'] ? 'TRUE' : 'FALSE') . "</p>";
        
        echo "<div style='background: #e8f5e8; padding: 15px; border: 1px solid #4caf50; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>🎯 EL MODAL DEBE APARECER AUTOMÁTICAMENTE</h4>";
        echo "<p>Si todo funciona correctamente, el modal de pago exitoso debe aparecer en unos segundos...</p>";
        echo "</div>";
        
        // Incluir el modal EXACTAMENTE igual que en facturacion.php
        $mostrar_modal = true;
        
    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollback();
        }
        echo "<p>❌ Error al registrar pago: " . $e->getMessage() . "</p>";
    }
}

// Obtener facturas disponibles para la prueba
try {
    $stmt = $conn->prepare("
        SELECT f.*, 
               CONCAT(p.nombre, ' ', p.apellido) as paciente_nombre,
               COALESCE(SUM(pg.monto), 0) as total_pagado
        FROM facturas f
        LEFT JOIN pacientes p ON f.paciente_id = p.id
        LEFT JOIN pagos pg ON f.id = pg.factura_id
        WHERE f.estado = 'pendiente'
        GROUP BY f.id
        ORDER BY f.fecha_factura DESC
        LIMIT 10
    ");
    $stmt->execute();
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $facturas = [];
    echo "<p>❌ Error al obtener facturas: " . $e->getMessage() . "</p>";
}

if (!isset($mostrar_modal) && empty($facturas)) {
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; border-radius: 5px;'>";
    echo "<h4>⚠️ No hay facturas pendientes</h4>";
    echo "<p>Necesitas crear una factura pendiente primero.</p>";
    echo "<p><a href='crear_factura_prueba.php' class='btn btn-primary'>Crear Factura de Prueba</a></p>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulador de Pago Real</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Modal de pago exitoso - EXACTAMENTE IGUAL QUE EN facturacion.php */
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
    <div class="container mt-4">
        
        <?php if (!isset($mostrar_modal)): ?>
        <h3>📋 Seleccionar Factura para Pago</h3>
        
        <?php if (!empty($facturas)): ?>
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="factura_id">Factura *</label>
                        <select class="form-control" id="factura_id" name="factura_id" required>
                            <option value="">Seleccionar factura...</option>
                            <?php foreach ($facturas as $factura): ?>
                                <option value="<?= $factura['id'] ?>">
                                    <?= htmlspecialchars($factura['numero_factura']) ?> - 
                                    <?= htmlspecialchars($factura['paciente_nombre']) ?> - 
                                    Pendiente: $<?= number_format($factura['total'] - $factura['total_pagado'], 2) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="monto">Monto del Pago *</label>
                        <input type="number" class="form-control" id="monto" name="monto" 
                               step="0.01" min="0.01" value="150.00" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="metodo_pago">Método de Pago *</label>
                        <select class="form-control" id="metodo_pago" name="metodo_pago" required>
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="tarjeta_credito">Tarjeta de Crédito</option>
                            <option value="tarjeta_debito">Tarjeta de Débito</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="numero_referencia">Número de Referencia</label>
                        <input type="text" class="form-control" id="numero_referencia" name="numero_referencia" 
                               placeholder="Opcional...">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="observaciones_pago">Observaciones</label>
                        <input type="text" class="form-control" id="observaciones_pago" name="observaciones_pago" 
                               placeholder="Pago de prueba..." value="Pago simulado para test del modal">
                    </div>
                </div>
            </div>
            
            <button type="submit" name="simular_pago" class="btn btn-success btn-lg">
                <i class="fas fa-dollar-sign mr-2"></i>🚀 REGISTRAR PAGO Y MOSTRAR MODAL
            </button>
        </form>
        <?php endif; ?>
        
        <hr>
        <p><a href="facturacion.php" class="btn btn-secondary">← Volver a Facturación</a></p>
        <?php endif; ?>
        
        <!-- Modal de Pago Exitoso - EXACTAMENTE IGUAL QUE EN facturacion.php -->
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

        <!-- JavaScript para mostrar modal - EXACTAMENTE IGUAL QUE EN facturacion.php -->
        <?php if (isset($mostrar_modal) && isset($_SESSION['show_print_modal']) && $_SESSION['show_print_modal'] === true && isset($_SESSION['ultimo_pago'])): ?>
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // Debug: Verificar que las variables están disponibles
            console.log('=== MODAL DE PAGO EXITOSO (SIMULACIÓN REAL) ===');
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
                    $('#modalPagoExitoso').modal({
                        backdrop: 'static',
                        keyboard: false,
                        show: true
                    });
                    
                    console.log('✅ Modal de pago real mostrado exitosamente');
                    console.log('🎉 ¡PAGO REGISTRADO! Modal apareciendo automáticamente...');
                }, 500);
            });
            
            function cerrarModalPago() {
                $('#modalPagoExitoso').modal('hide');
                console.log('Modal cerrado por el usuario');
            }
            
            function imprimirReciboModal() {
                window.open('imprimir_recibo_termico.php?auto_print=1', 'recibo_termico', 'width=400,height=600,scrollbars=yes');
                setTimeout(function() {
                    cerrarModalPago();
                }, 1000);
            }
        </script>
        <?php 
        // Limpiar la variable show_print_modal después de mostrar el modal
        unset($_SESSION['show_print_modal']); 
        ?>
        <?php else: ?>
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
        <?php endif; ?>
    </div>
</body>
</html>
