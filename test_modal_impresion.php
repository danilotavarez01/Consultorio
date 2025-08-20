<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';

// Verificar login
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header("Location: index.php");
    exit();
}

// Simular un pago para testing
if (isset($_GET['crear_pago_test'])) {
    try {
        // Obtener o crear una factura
        $stmt = $conn->prepare("SELECT id FROM facturas WHERE estado = 'pendiente' LIMIT 1");
        $stmt->execute();
        $factura = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$factura) {
            $stmt = $conn->prepare("INSERT INTO facturas (numero_factura, paciente_id, medico_id, fecha_factura, total, estado) VALUES (?, 1, 1, NOW(), 100.00, 'pendiente')");
            $numero = 'FAC-TEST-PRINT-' . date('Ymd-His');
            $stmt->execute([$numero]);
            $factura_id = $conn->lastInsertId();
        } else {
            $factura_id = $factura['id'];
        }
        
        // Crear pago
        $stmt = $conn->prepare("INSERT INTO pagos (factura_id, monto, metodo_pago, fecha_pago, observaciones) VALUES (?, ?, ?, NOW(), ?)");
        $stmt->execute([$factura_id, 100.00, 'efectivo', 'Test de impresión']);
        $pago_id = $conn->lastInsertId();
        
        // Obtener info completa
        $stmt = $conn->prepare("
            SELECT p.id as pago_id, p.monto, p.metodo_pago,
                   f.numero_factura, f.total,
                   CONCAT(pac.nombre, ' ', pac.apellido) as paciente_nombre,
                   pac.dni as paciente_cedula,
                   u.nombre as medico_nombre
            FROM pagos p
            LEFT JOIN facturas f ON p.factura_id = f.id
            LEFT JOIN pacientes pac ON f.paciente_id = pac.id
            LEFT JOIN usuarios u ON f.medico_id = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$pago_id]);
        $pago_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Guardar en sesión como hace facturación
        $_SESSION['ultimo_pago'] = [
            'pago_id' => $pago_info['pago_id'],
            'factura_id' => $factura_id,
            'numero_factura' => $pago_info['numero_factura'],
            'monto' => $pago_info['monto'],
            'metodo_pago' => $pago_info['metodo_pago'],
            'paciente_nombre' => $pago_info['paciente_nombre'] ?? 'Paciente Test',
            'paciente_cedula' => $pago_info['paciente_cedula'] ?? '12345678',
            'medico_nombre' => $pago_info['medico_nombre'] ?? 'Dr. Test'
        ];
        
        $_SESSION['show_print_modal'] = true;
        
        // Redirigir para simular el flujo real
        header("Location: test_modal_impresion.php");
        exit();
        
    } catch (Exception $e) {
        $error = "Error al crear pago: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🧪 Test Modal de Impresión</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/fontawesome.min.css" rel="stylesheet">
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h2>🧪 Test Modal de Impresión</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>📊 Estado Actual</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Usuario:</strong> <?= htmlspecialchars($_SESSION['username']) ?></p>
                        <p><strong>último_pago:</strong> <?= isset($_SESSION['ultimo_pago']) ? '✅ Existe' : '❌ No existe' ?></p>
                        <p><strong>show_print_modal:</strong> <?= isset($_SESSION['show_print_modal']) ? '✅ Existe' : '❌ No existe' ?></p>
                        
                        <?php if (isset($_SESSION['ultimo_pago'])): ?>
                            <hr>
                            <h6>Datos del pago:</h6>
                            <small>
                                <strong>ID:</strong> <?= htmlspecialchars($_SESSION['ultimo_pago']['pago_id'] ?? 'N/A') ?><br>
                                <strong>Factura:</strong> <?= htmlspecialchars($_SESSION['ultimo_pago']['numero_factura'] ?? 'N/A') ?><br>
                                <strong>Monto:</strong> $<?= number_format(floatval($_SESSION['ultimo_pago']['monto'] ?? 0), 2) ?><br>
                                <strong>Paciente:</strong> <?= htmlspecialchars($_SESSION['ultimo_pago']['paciente_nombre'] ?? 'N/A') ?>
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>🚀 Acciones</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!isset($_SESSION['ultimo_pago'])): ?>
                            <a href="?crear_pago_test=1" class="btn btn-primary btn-block">💰 Crear Pago de Prueba</a>
                        <?php else: ?>
                            <button class="btn btn-success btn-block" onclick="mostrarModalTest()">🖨️ Mostrar Modal</button>
                            <button class="btn btn-info btn-block" onclick="imprimirDirecto()">📄 Imprimir Directo</button>
                            <hr>
                            <a href="?crear_pago_test=1" class="btn btn-warning btn-block">🔄 Nuevo Pago</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="facturacion.php" class="btn btn-secondary">← Volver a Facturación</a>
            <a href="diagnostico_impresion.php" class="btn btn-info">🔧 Diagnóstico</a>
        </div>
    </div>

    <!-- Modal de prueba (copia del modal de facturación) -->
    <?php if (isset($_SESSION['show_print_modal']) && $_SESSION['show_print_modal'] === true && isset($_SESSION['ultimo_pago'])): ?>
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
                                    <td class="text-right"><strong>Factura:</strong></td>
                                    <td class="text-left"><?= htmlspecialchars($_SESSION['ultimo_pago']['numero_factura'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <td class="text-right"><strong>Paciente:</strong></td>
                                    <td class="text-left"><?= htmlspecialchars($_SESSION['ultimo_pago']['paciente_nombre'] ?? 'Paciente') ?></td>
                                </tr>
                                <tr>
                                    <td class="text-right"><strong>Monto:</strong></td>
                                    <td class="text-left font-weight-bold text-success h5 mb-0">$<?= number_format(floatval($_SESSION['ultimo_pago']['monto'] ?? 0), 2) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-right"><strong>Método:</strong></td>
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
                    <button type="button" class="btn btn-outline-secondary btn-lg px-4 mr-3" onclick="cerrarModalImpresion()">
                        <i class="fas fa-times mr-2"></i>No, Gracias
                    </button>
                    <button type="button" class="btn btn-success btn-lg px-4" onclick="imprimirRecibo()">
                        <i class="fas fa-print mr-2"></i>Sí, Imprimir Recibo
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    
    <script>
        console.log('=== MODAL DE PAGO MOSTRADO ===');
        console.log('Datos del pago:', <?= json_encode($_SESSION['ultimo_pago']) ?>);
        
        // Limpiar variables después de mostrar el modal
        setTimeout(function() {
            <?php 
            unset($_SESSION['show_print_modal']); 
            unset($_SESSION['ultimo_pago']);
            ?>
            console.log('Variables de sesión limpiadas');
        }, 1000);
    </script>
    <?php endif; ?>

    <script>
        function mostrarModalTest() {
            console.log('Intentando mostrar modal...');
            // Recargar la página para mostrar el modal
            window.location.href = 'test_modal_impresion.php';
        }

        function imprimirDirecto() {
            console.log('Impresión directa...');
            <?php if (isset($_SESSION['ultimo_pago']['pago_id'])): ?>
                const pagoId = '<?= $_SESSION['ultimo_pago']['pago_id'] ?>';
                const url = 'imprimir_recibo.php?pago_id=' + pagoId;
            <?php else: ?>
                const url = 'imprimir_recibo.php';
            <?php endif; ?>
            
            console.log('URL:', url);
            const ventana = window.open(url, 'recibo_test', 'width=400,height=600,scrollbars=yes,resizable=yes');
            
            if (!ventana) {
                alert('Ventana bloqueada por el navegador');
            } else {
                console.log('Ventana abierta exitosamente');
            }
        }

        function imprimirRecibo() {
            console.log('=== FUNCIÓN IMPRIMIR RECIBO EJECUTADA ===');
            
            // Obtener ID del pago si está disponible
            let pagoId = '';
            <?php if (isset($_SESSION['ultimo_pago']['pago_id'])): ?>
                pagoId = '<?= $_SESSION['ultimo_pago']['pago_id'] ?>';
            <?php endif; ?>
            
            // Construir URL con parámetros
            let url = 'imprimir_recibo.php';
            if (pagoId && pagoId !== '') {
                url += '?pago_id=' + encodeURIComponent(pagoId);
            }
            
            console.log('URL del recibo:', url);
            console.log('Pago ID:', pagoId);
            
            // Abrir ventana de impresión
            const ventanaImpresion = window.open(url, '_blank', 'width=400,height=600,scrollbars=yes,resizable=yes');
            
            if (!ventanaImpresion) {
                alert('No se pudo abrir la ventana de impresión.\n\nPor favor:\n1. Verifique que no esté bloqueada por el navegador\n2. Permita ventanas emergentes para este sitio\n3. Intente nuevamente');
                console.error('Error: Ventana bloqueada');
                return;
            }
            
            console.log('Ventana de recibo abierta exitosamente');
            
            // Cerrar el modal
            cerrarModalImpresion();
        }

        function cerrarModalImpresion() {
            console.log('Cerrando modal...');
            const modal = $('#modalImprimirRecibo');
            if (modal.length > 0) {
                modal.hide();
                modal.removeClass('show fade');
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
            }
        }
    </script>
</body>
</html>


