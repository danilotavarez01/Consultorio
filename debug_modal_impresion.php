<?php
// Debug del modal de impresión - No requiere autenticación para pruebas
session_start();

// Simular datos de pago para testing
$_SESSION['ultimo_pago'] = [
    'pago_id' => '999',
    'numero_factura' => 'F-TEST-001',
    'paciente_nombre' => 'Paciente Test',
    'monto' => '150.00',
    'metodo_pago' => 'efectivo'
];

$_SESSION['show_print_modal'] = true;

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug - Modal de Impresión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background-color: #f8f9fa; }
        .debug-card { margin-bottom: 20px; }
        .variable-display { background: #f1f3f4; padding: 10px; border-radius: 5px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">🐛 Debug - Modal de Impresión de Recibo</h1>
        
        <div class="card debug-card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">📊 Estado de Variables de Sesión</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>$_SESSION['show_print_modal']:</h6>
                        <div class="variable-display">
                            <?= isset($_SESSION['show_print_modal']) ? ($_SESSION['show_print_modal'] ? 'true' : 'false') : 'NO DEFINIDA' ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>$_SESSION['ultimo_pago']:</h6>
                        <div class="variable-display">
                            <?php if (isset($_SESSION['ultimo_pago'])): ?>
                                <pre><?= print_r($_SESSION['ultimo_pago'], true) ?></pre>
                            <?php else: ?>
                                NO DEFINIDA
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card debug-card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">🔧 Condiciones del Modal</h5>
            </div>
            <div class="card-body">
                <?php 
                $show_modal_set = isset($_SESSION['show_print_modal']);
                $show_modal_true = $show_modal_set && $_SESSION['show_print_modal'] === true;
                $ultimo_pago_set = isset($_SESSION['ultimo_pago']);
                $should_show = $show_modal_true && $ultimo_pago_set;
                ?>
                
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        ¿Existe $_SESSION['show_print_modal']?
                        <span class="badge badge-<?= $show_modal_set ? 'success' : 'danger' ?> badge-pill">
                            <?= $show_modal_set ? 'SÍ' : 'NO' ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        ¿$_SESSION['show_print_modal'] === true?
                        <span class="badge badge-<?= $show_modal_true ? 'success' : 'danger' ?> badge-pill">
                            <?= $show_modal_true ? 'SÍ' : 'NO' ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        ¿Existe $_SESSION['ultimo_pago']?
                        <span class="badge badge-<?= $ultimo_pago_set ? 'success' : 'danger' ?> badge-pill">
                            <?= $ultimo_pago_set ? 'SÍ' : 'NO' ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <strong>¿Debería mostrar el modal?</strong>
                        <span class="badge badge-<?= $should_show ? 'success' : 'danger' ?> badge-pill">
                            <?= $should_show ? 'SÍ' : 'NO' ?>
                        </span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card debug-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">🧪 Prueba del Modal</h5>
            </div>
            <div class="card-body">
                <button class="btn btn-primary" onclick="simularModal()">
                    <i class="fas fa-play mr-2"></i>Simular Modal Manualmente
                </button>
                <button class="btn btn-info ml-2" onclick="testJavaScript()">
                    <i class="fas fa-code mr-2"></i>Test JavaScript
                </button>
                <button class="btn btn-success ml-2" onclick="abrirVentanaImpresion()">
                    <i class="fas fa-print mr-2"></i>Test Ventana Impresión
                </button>
            </div>
        </div>

        <!-- Simulación del Modal -->
        <?php if (isset($_SESSION['show_print_modal']) && $_SESSION['show_print_modal'] === true && isset($_SESSION['ultimo_pago'])): ?>
            <div class="alert alert-success">
                <h4>✅ Condiciones cumplidas - El modal debería aparecer automáticamente</h4>
            </div>
            
            <!-- MODAL IDÉNTICO AL DE FACTURACIÓN -->
            <div class="modal fade show" id="modalImprimirRecibo" tabindex="-1" data-backdrop="static" data-keyboard="false" style="display: block;">
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
                                            <td class="text-left"><?= htmlspecialchars($_SESSION['ultimo_pago']['numero_factura'] ?? 'N/A') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-right font-weight-bold">Paciente:</td>
                                            <td class="text-left"><?= htmlspecialchars($_SESSION['ultimo_pago']['paciente_nombre'] ?? 'Paciente') ?></td>
                                        </tr>
                                        <tr class="border-top">
                                            <td class="text-right font-weight-bold text-success">Monto Pagado:</td>
                                            <td class="text-left font-weight-bold text-success h5 mb-0">$<?= number_format(floatval($_SESSION['ultimo_pago']['monto'] ?? 0), 2) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-right font-weight-bold">Método:</td>
                                            <td class="text-left"><?= ucfirst(str_replace('_', ' ', $_SESSION['ultimo_pago']['metodo_pago'] ?? 'efectivo')) ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="alert alert-info border-0 mt-4 mx-auto" style="max-width: 350px;">
                                <i class="fas fa-print mr-2"></i>
                                <strong>¿Desea imprimir el recibo térmico ahora?</strong>
                            </div>
                        </div>
                        <div class="modal-footer border-0 justify-content-center pb-4">
                            <button type="button" class="btn btn-outline-secondary btn-lg px-4 mr-3" onclick="cerrarModalDebug()">
                                <i class="fas fa-times mr-2"></i>No, Gracias
                            </button>
                            <button type="button" class="btn btn-success btn-lg px-4" onclick="imprimirReciboDebug()">
                                <i class="fas fa-print mr-2"></i>Sí, Imprimir Recibo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-backdrop fade show"></div>
            
            <script>
                console.log('🐛 DEBUG: Modal automático mostrado');
                console.log('Datos del pago:', <?= json_encode($_SESSION['ultimo_pago']) ?>);
            </script>
        <?php else: ?>
            <div class="alert alert-danger">
                <h4>❌ Condiciones NO cumplidas - El modal NO debería aparecer</h4>
                <p>Revise las condiciones arriba para ver qué está faltando.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function simularModal() {
            console.log('🧪 Simulando modal manualmente...');
            $('#modalImprimirRecibo').modal('show');
        }

        function testJavaScript() {
            console.log('🧪 Ejecutando test de JavaScript...');
            
            // Test básico de jQuery
            console.log('jQuery disponible:', typeof $ !== 'undefined');
            console.log('Bootstrap disponible:', typeof $.fn.modal !== 'undefined');
            
            // Test de elementos del DOM
            const modal = document.getElementById('modalImprimirRecibo');
            console.log('Modal encontrado en DOM:', modal !== null);
            
            if (modal) {
                console.log('Clases del modal:', modal.className);
                console.log('Estilo display:', modal.style.display);
            }
            
            alert('Test completado - Revise la consola del navegador (F12)');
        }

        function abrirVentanaImpresion() {
            console.log('🧪 Probando apertura de ventana de impresión...');
            
            const url = 'imprimir_recibo.php?pago_id=999';
            const windowFeatures = 'width=450,height=700,scrollbars=yes,resizable=yes';
            
            try {
                const ventana = window.open(url, 'test_recibo', windowFeatures);
                
                if (ventana) {
                    console.log('✅ Ventana abierta exitosamente');
                    alert('✅ Ventana de impresión abierta correctamente');
                } else {
                    console.error('❌ Error al abrir ventana');
                    alert('❌ Error: La ventana fue bloqueada por el navegador');
                }
            } catch (error) {
                console.error('❌ Error en window.open:', error);
                alert('❌ Error: ' + error.message);
            }
        }

        function cerrarModalDebug() {
            console.log('🐛 Cerrando modal de debug...');
            $('#modalImprimirRecibo').modal('hide');
            $('.modal-backdrop').remove();
        }

        function imprimirReciboDebug() {
            console.log('🐛 Ejecutando impresión desde debug...');
            alert('🖨️ En el sistema real, esto abriría la ventana de impresión');
            abrirVentanaImpresion();
        }

        // Auto-ejecutar test inicial
        $(document).ready(function() {
            console.log('🐛 DEBUG: Página cargada completamente');
            
            // Verificar si el modal debería estar visible
            const shouldShow = <?= ($should_show ?? false) ? 'true' : 'false' ?>;
            console.log('¿Debería mostrar modal?', shouldShow);
            
            if (shouldShow) {
                console.log('✅ Modal debería ser visible automáticamente');
            } else {
                console.log('❌ Modal NO debería ser visible');
            }
        });
    </script>

    <div class="mt-4">
        <div class="card">
            <div class="card-body">
                <h5>🔗 Acciones de Debug</h5>
                <a href="facturacion.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left mr-2"></i>Volver a Facturación
                </a>
                <button onclick="location.reload()" class="btn btn-outline-secondary ml-2">
                    <i class="fas fa-sync mr-2"></i>Recargar Test
                </button>
                <a href="clear_ultimo_pago.php" class="btn btn-outline-warning ml-2" target="_blank">
                    <i class="fas fa-trash mr-2"></i>Limpiar Sesión
                </a>
            </div>
        </div>
    </div>

    <?php
    // Limpiar la variable para que no siga apareciendo
    unset($_SESSION['show_print_modal']); 
    ?>
</body>
</html>
