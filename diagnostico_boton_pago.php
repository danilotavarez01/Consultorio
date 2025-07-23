<?php
session_start();
require_once 'config.php';

echo "<h2>🔍 Diagnóstico: Botón de Pago (💰) No Funciona</h2>";

// 1. Verificar si hay facturas pendientes
echo "<h3>1. Verificar Facturas Pendientes</h3>";
try {
    $stmt = $conn->prepare("
        SELECT f.id, f.numero_factura, f.estado, f.total,
               COALESCE(SUM(p.monto), 0) as total_pagado,
               (f.total - COALESCE(SUM(p.monto), 0)) as monto_pendiente,
               pa.nombre as paciente_nombre
        FROM facturas f 
        LEFT JOIN pagos p ON f.id = p.factura_id 
        LEFT JOIN pacientes pa ON f.paciente_id = pa.id
        WHERE f.estado = 'pendiente'
        GROUP BY f.id
        ORDER BY f.fecha_factura DESC
        LIMIT 5
    ");
    $stmt->execute();
    $facturas_pendientes = $stmt->fetchAll();
    
    if (empty($facturas_pendientes)) {
        echo "<div class='alert alert-warning'>⚠️ <strong>PROBLEMA ENCONTRADO:</strong> No hay facturas pendientes.</div>";
        echo "<p>El botón de pago solo aparece para facturas con estado 'pendiente'.</p>";
        echo "<p><a href='crear_factura_test.php' class='btn btn-success'>✅ Crear Factura de Prueba</a></p>";
    } else {
        echo "<div class='alert alert-success'>✅ Hay " . count($facturas_pendientes) . " factura(s) pendiente(s):</div>";
        echo "<table class='table table-sm'>";
        echo "<tr><th>Factura</th><th>Paciente</th><th>Total</th><th>Pagado</th><th>Pendiente</th><th>Botón Debe Aparecer</th></tr>";
        foreach ($facturas_pendientes as $factura) {
            $debe_aparecer = $factura['monto_pendiente'] > 0 ? "SÍ" : "NO";
            echo "<tr>";
            echo "<td>#{$factura['numero_factura']}</td>";
            echo "<td>{$factura['paciente_nombre']}</td>";
            echo "<td>$" . number_format($factura['total'], 2) . "</td>";
            echo "<td>$" . number_format($factura['total_pagado'], 2) . "</td>";
            echo "<td>$" . number_format($factura['monto_pendiente'], 2) . "</td>";
            echo "<td><strong>$debe_aparecer</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ Error al consultar facturas: " . $e->getMessage() . "</div>";
}

// 2. Verificar permisos
echo "<h3>2. Verificar Permisos de Usuario</h3>";
if (!function_exists('hasPermission')) {
    echo "<div class='alert alert-warning'>⚠️ Función hasPermission() no está definida.</div>";
} else {
    $puede_crear = hasPermission('crear_factura') ? "SÍ" : "NO";
    $es_admin = function_exists('isAdmin') && isAdmin() ? "SÍ" : "NO";
    echo "<p><strong>Puede crear facturas:</strong> $puede_crear</p>";
    echo "<p><strong>Es administrador:</strong> $es_admin</p>";
    
    if (!hasPermission('crear_factura') && !isAdmin()) {
        echo "<div class='alert alert-warning'>⚠️ <strong>POSIBLE PROBLEMA:</strong> El usuario no tiene permisos para crear facturas.</div>";
    }
}

// 3. Generar botón de prueba
echo "<h3>3. Probar Botón de Pago Directamente</h3>";
if (!empty($facturas_pendientes)) {
    $factura_test = $facturas_pendientes[0];
    $monto_pendiente = $factura_test['total'] - $factura_test['total_pagado'];
    
    echo "<div class='border p-3 mb-3'>";
    echo "<p><strong>Factura de Prueba:</strong> #{$factura_test['numero_factura']} - Pendiente: $" . number_format($monto_pendiente, 2) . "</p>";
    echo "<button type='button' class='btn btn-outline-success' onclick='testAgregarPago({$factura_test['id']}, \"{$factura_test['numero_factura']}\", $monto_pendiente)' title='Agregar Pago'>";
    echo "<i class='fas fa-dollar-sign'></i> 💰 PROBAR BOTÓN";
    echo "</button>";
    echo "</div>";
} else {
    echo "<p>❌ No hay facturas pendientes para probar el botón.</p>";
}

// 4. Script de prueba de JavaScript
echo "<h3>4. Script de Prueba</h3>";
echo "<div id='test-result' class='alert alert-info'>Haga clic en el botón de arriba para probar...</div>";

?>

<!-- Cargar dependencias necesarias -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
    body { padding: 20px; font-family: Arial, sans-serif; }
    .container { max-width: 800px; margin: 0 auto; }
    .alert { margin: 10px 0; }
    .table { background: white; }
</style>

<!-- Modal de prueba (copiado de facturacion.php) -->
<div class="modal fade" id="modalAgregarPago" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-dollar-sign mr-2"></i>Agregar Pago
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="facturacion.php">
                    <input type="hidden" name="action" value="add_pago">
                    <input type="hidden" id="pago_factura_id" name="factura_id">
                    
                    <div class="form-group">
                        <label>Factura:</label>
                        <div class="form-control-plaintext">
                            #<span id="pago_numero_factura"></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Monto Pendiente:</label>
                        <div class="form-control-plaintext">
                            $<span id="pago_monto_pendiente"></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="monto">Monto a Pagar *</label>
                        <input type="number" class="form-control" id="monto" name="monto" step="0.01" min="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="metodo_pago">Método de Pago *</label>
                        <select class="form-control" id="metodo_pago" name="metodo_pago" required>
                            <option value="">Seleccionar...</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="numero_referencia">Número de Referencia</label>
                        <input type="text" class="form-control" id="numero_referencia" name="numero_referencia">
                    </div>
                    
                    <div class="form-group">
                        <label for="observaciones_pago">Observaciones</label>
                        <textarea class="form-control" id="observaciones_pago" name="observaciones_pago" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i>Registrar Pago
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function testAgregarPago(facturaId, numeroFactura, montoPendiente) {
    console.log('testAgregarPago llamada con:', {facturaId, numeroFactura, montoPendiente});
    
    try {
        // Verificar si jQuery está cargado
        if (typeof $ === 'undefined') {
            document.getElementById('test-result').innerHTML = '<strong>❌ ERROR:</strong> jQuery no está cargado.';
            document.getElementById('test-result').className = 'alert alert-danger';
            return;
        }
        
        // Verificar si Bootstrap está cargado
        if (typeof $.fn.modal === 'undefined') {
            document.getElementById('test-result').innerHTML = '<strong>❌ ERROR:</strong> Bootstrap modal no está disponible.';
            document.getElementById('test-result').className = 'alert alert-danger';
            return;
        }
        
        // Llenar los campos del modal
        document.getElementById('pago_factura_id').value = facturaId;
        document.getElementById('pago_numero_factura').textContent = numeroFactura;
        document.getElementById('pago_monto_pendiente').textContent = montoPendiente.toFixed(2);
        document.getElementById('monto').value = montoPendiente.toFixed(2);
        
        // Mostrar el modal
        $('#modalAgregarPago').modal('show');
        
        document.getElementById('test-result').innerHTML = '<strong>✅ ÉXITO:</strong> Modal abierto correctamente. jQuery y Bootstrap funcionando.';
        document.getElementById('test-result').className = 'alert alert-success';
        
    } catch (error) {
        console.error('Error en testAgregarPago:', error);
        document.getElementById('test-result').innerHTML = '<strong>❌ ERROR:</strong> ' + error.message;
        document.getElementById('test-result').className = 'alert alert-danger';
    }
}

// Verificar cuando la página esté lista
$(document).ready(function() {
    console.log('jQuery cargado:', typeof $ !== 'undefined');
    console.log('Bootstrap modal disponible:', typeof $.fn.modal !== 'undefined');
    
    // Verificar si hay errores de consola previos
    window.addEventListener('error', function(e) {
        console.error('Error de JavaScript detectado:', e.error);
    });
});
</script>

<p><a href="facturacion.php" class="btn btn-primary">← Volver a Facturación</a></p>
