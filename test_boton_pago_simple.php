<?php
session_start();
require_once 'config.php';

// Crear una factura de prueba si no existe
$stmt = $conn->prepare("SELECT COUNT(*) FROM facturas WHERE estado = 'pendiente'");
$stmt->execute();
$tiene_facturas = $stmt->fetchColumn() > 0;

if (!$tiene_facturas) {
    // Crear factura de prueba
    $stmt = $conn->prepare("
        INSERT INTO facturas (numero_factura, paciente_id, fecha_factura, subtotal, descuento, total, estado, observaciones)
        VALUES ('TEST-001', 1, NOW(), 100.00, 0.00, 100.00, 'pendiente', 'Factura de prueba para diagnosticar botón de pago')
    ");
    $stmt->execute();
    $factura_id = $conn->lastInsertId();
    echo "<div class='alert alert-info'>Se creó una factura de prueba: TEST-001</div>";
} else {
    // Obtener una factura existente
    $stmt = $conn->prepare("
        SELECT f.id, f.numero_factura, f.total,
               COALESCE(SUM(p.monto), 0) as total_pagado
        FROM facturas f 
        LEFT JOIN pagos p ON f.id = p.factura_id 
        WHERE f.estado = 'pendiente'
        GROUP BY f.id
        HAVING (f.total - COALESCE(SUM(p.monto), 0)) > 0
        LIMIT 1
    ");
    $stmt->execute();
    $factura = $stmt->fetch();
    $factura_id = $factura['id'];
    $numero_factura = $factura['numero_factura'];
    $monto_pendiente = $factura['total'] - $factura['total_pagado'];
}

if (!isset($numero_factura)) {
    $numero_factura = 'TEST-001';
    $monto_pendiente = 100.00;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Botón de Pago Simple</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>🧪 Test Botón de Pago Simple</h2>
        
        <div class="alert alert-info">
            <h5>Estado del Sistema</h5>
            <ul>
                <li><strong>Factura ID:</strong> <?= $factura_id ?></li>
                <li><strong>Número:</strong> <?= $numero_factura ?></li>
                <li><strong>Monto Pendiente:</strong> $<?= number_format($monto_pendiente, 2) ?></li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h4>Botón Original (copia exacta de facturacion.php)</h4>
                <button type="button" class="btn btn-outline-success btn-lg" 
                        onclick="agregarPago(<?= $factura_id ?>, '<?= htmlspecialchars($numero_factura) ?>', <?= $monto_pendiente ?>)" 
                        title="Agregar Pago">
                    <i class="fas fa-dollar-sign"></i> 💰 Botón Original
                </button>
            </div>
            
            <div class="col-md-6">
                <h4>Botón Alternativo (sin emoji)</h4>
                <button type="button" class="btn btn-success btn-lg" 
                        onclick="agregarPagoAlternativo(<?= $factura_id ?>, '<?= htmlspecialchars($numero_factura) ?>', <?= $monto_pendiente ?>)" 
                        title="Agregar Pago Alternativo">
                    <i class="fas fa-dollar-sign"></i> PAGAR
                </button>
            </div>
        </div>

        <div class="mt-4">
            <h4>Resultados de Prueba:</h4>
            <div id="resultado" class="alert alert-secondary">Haga clic en cualquiera de los botones arriba...</div>
        </div>

        <div class="mt-4">
            <h4>Estado de Dependencias:</h4>
            <ul id="dependencias" class="list-group">
                <li class="list-group-item">Cargando...</li>
            </ul>
        </div>
    </div>

    <!-- Modal de Pago (copiado de facturacion.php) -->
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

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <script>
        // Función original copiada de facturacion.php
        function agregarPago(facturaId, numeroFactura, montoPendiente) {
            console.log('agregarPago() llamada con:', {facturaId, numeroFactura, montoPendiente});
            
            try {
                document.getElementById('pago_factura_id').value = facturaId;
                document.getElementById('pago_numero_factura').textContent = numeroFactura;
                document.getElementById('pago_monto_pendiente').textContent = montoPendiente.toFixed(2);
                document.getElementById('monto').value = montoPendiente.toFixed(2);
                $('#modalAgregarPago').modal('show');
                
                actualizarResultado('✅ ÉXITO: Función agregarPago() funcionó correctamente', 'success');
            } catch (error) {
                console.error('Error en agregarPago:', error);
                actualizarResultado('❌ ERROR en agregarPago(): ' + error.message, 'danger');
            }
        }

        // Función alternativa para pruebas
        function agregarPagoAlternativo(facturaId, numeroFactura, montoPendiente) {
            console.log('agregarPagoAlternativo() llamada');
            
            try {
                // Llenar campos del modal
                $('#pago_factura_id').val(facturaId);
                $('#pago_numero_factura').text(numeroFactura);
                $('#pago_monto_pendiente').text(montoPendiente.toFixed(2));
                $('#monto').val(montoPendiente.toFixed(2));
                
                // Mostrar modal usando jQuery
                $('#modalAgregarPago').modal('show');
                
                actualizarResultado('✅ ÉXITO: Función alternativa funcionó correctamente', 'success');
            } catch (error) {
                console.error('Error en agregarPagoAlternativo:', error);
                actualizarResultado('❌ ERROR en función alternativa: ' + error.message, 'danger');
            }
        }

        function actualizarResultado(mensaje, tipo) {
            const div = document.getElementById('resultado');
            div.textContent = mensaje;
            div.className = 'alert alert-' + tipo;
        }

        // Verificar dependencias cuando la página esté lista
        $(document).ready(function() {
            console.log('Página lista, verificando dependencias...');
            
            const dependencias = [
                { name: 'jQuery', check: typeof $ !== 'undefined', version: $ ? $.fn.jquery : 'N/A' },
                { name: 'Bootstrap Modal', check: typeof $.fn.modal !== 'undefined', version: 'Bootstrap 4.5.2' },
                { name: 'FontAwesome', check: document.querySelector('.fas') !== null, version: '5.15.4' }
            ];
            
            const lista = document.getElementById('dependencias');
            lista.innerHTML = '';
            
            dependencias.forEach(dep => {
                const item = document.createElement('li');
                item.className = 'list-group-item ' + (dep.check ? 'list-group-item-success' : 'list-group-item-danger');
                item.innerHTML = `
                    <strong>${dep.name}:</strong> 
                    ${dep.check ? '✅ Cargado' : '❌ NO cargado'} 
                    <small class="text-muted">(${dep.version})</small>
                `;
                lista.appendChild(item);
            });
        });

        // Detectar errores de JavaScript
        window.addEventListener('error', function(e) {
            console.error('Error detectado:', e.error);
            actualizarResultado('❌ ERROR de JavaScript: ' + e.error.message, 'danger');
        });
    </script>
</body>
</html>


