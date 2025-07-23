<?php
/**
 * Script para probar el sistema de impresión de recibos
 * Simula un pago y abre la ventana de impresión
 */
require_once 'session_config.php';
session_start();
require_once 'config.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "<!DOCTYPE html><html><body>";
    echo "<h2>❌ Error: Usuario no autenticado</h2>";
    echo "<p>Debe <a href='login.php'>iniciar sesión</a> para usar este test.</p>";
    echo "</body></html>";
    exit;
}

// Verificar si hay un pago de prueba
if (isset($_POST['crear_pago_prueba'])) {
    try {
        // Buscar o crear un paciente de prueba
        $stmt = $conn->prepare("SELECT id FROM pacientes WHERE nombre = 'Test' AND apellido = 'Paciente' LIMIT 1");
        $stmt->execute();
        $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$paciente) {
            // Crear paciente de prueba
            $stmt = $conn->prepare("INSERT INTO pacientes (nombre, apellido, dni, telefono, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(['Test', 'Paciente', '000-0000000-0', '809-555-0000', 'test@ejemplo.com']);
            $paciente_id = $conn->lastInsertId();
        } else {
            $paciente_id = $paciente['id'];
        }
        
        // Crear factura de prueba
        $numero_factura = 'TEST-' . date('His');
        $stmt = $conn->prepare("
            INSERT INTO facturas (numero_factura, paciente_id, medico_id, fecha_factura, fecha_vencimiento, subtotal, total, estado) 
            VALUES (?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 1000, 1000, 'pendiente')
        ");
        $stmt->execute([$numero_factura, $paciente_id, $_SESSION['id']]);
        $factura_id = $conn->lastInsertId();
        
        // Crear pago de prueba
        $monto_pago = 500.00;
        $stmt = $conn->prepare("
            INSERT INTO pagos (factura_id, fecha_pago, monto, metodo_pago, observaciones) 
            VALUES (?, NOW(), ?, 'efectivo', 'Pago de prueba para test de impresión')
        ");
        $stmt->execute([$factura_id, $monto_pago]);
        $pago_id = $conn->lastInsertId();
        
        // Configurar datos en sesión para impresión
        $_SESSION['ultimo_pago'] = [
            'pago_id' => $pago_id,
            'factura_id' => $factura_id,
            'numero_factura' => $numero_factura,
            'monto' => $monto_pago,
            'metodo_pago' => 'efectivo',
            'paciente_nombre' => 'Test Paciente',
            'paciente_cedula' => '000-0000000-0',
            'medico_nombre' => $_SESSION['nombre'] ?? 'Dr. Test',
            'fecha_factura' => date('Y-m-d'),
            'total_factura' => 1000.00
        ];
        
        $_SESSION['show_print_modal'] = true;
        $_SESSION['success_message'] = "Pago de prueba creado exitosamente. ID: $pago_id";
        
        header("Location: test_impresion_recibo.php?success=1");
        exit;
        
    } catch (Exception $e) {
        $error = "Error al crear pago de prueba: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test de Impresión de Recibos - Consultorio</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include "sidebar.php"; ?>
        
        <div class="col-md-10 main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-print"></i> Test de Impresión de Recibos</h2>
                <a href="facturacion.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a Facturación
                </a>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <h5><i class="fas fa-check-circle"></i> Pago de Prueba Creado</h5>
                    <p>Se ha creado exitosamente un pago de prueba. Ahora puede probar la impresión del recibo.</p>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-triangle"></i> Error</h5>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <!-- Estado actual -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle"></i> Estado del Sistema de Impresión</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Sesión de Usuario:</h6>
                            <ul class="list-unstyled">
                                <li><strong>Usuario:</strong> <?= htmlspecialchars($_SESSION['username'] ?? 'N/A') ?></li>
                                <li><strong>Nombre:</strong> <?= htmlspecialchars($_SESSION['nombre'] ?? 'N/A') ?></li>
                                <li><strong>ID de Sesión:</strong> <code><?= session_id() ?></code></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Datos de Impresión:</h6>
                            <?php if (isset($_SESSION['ultimo_pago'])): ?>
                                <div class="alert alert-success">
                                    <strong><i class="fas fa-check"></i> Datos de pago disponibles</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>ID del Pago: <?= $_SESSION['ultimo_pago']['pago_id'] ?></li>
                                        <li>Factura: <?= htmlspecialchars($_SESSION['ultimo_pago']['numero_factura'] ?? 'N/A') ?></li>
                                        <li>Monto: $<?= number_format($_SESSION['ultimo_pago']['monto'] ?? 0, 2) ?></li>
                                        <li>Paciente: <?= htmlspecialchars($_SESSION['ultimo_pago']['paciente_nombre'] ?? 'N/A') ?></li>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <strong><i class="fas fa-exclamation-triangle"></i> No hay datos de pago</strong>
                                    <p class="mb-0">No hay información de pago guardada en la sesión.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones disponibles -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-cogs"></i> Acciones de Prueba</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>1. Crear Pago de Prueba</h6>
                            <p>Crear un pago ficticio para probar el sistema de impresión.</p>
                            <form method="POST">
                                <button type="submit" name="crear_pago_prueba" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Crear Pago de Prueba
                                </button>
                            </form>
                        </div>
                        
                        <div class="col-md-6">
                            <h6>2. Probar Impresión</h6>
                            <p>Abrir directamente la ventana de impresión de recibos.</p>
                            
                            <?php if (isset($_SESSION['ultimo_pago'])): ?>
                                <button onclick="imprimirRecibo()" class="btn btn-success">
                                    <i class="fas fa-print"></i> Imprimir Recibo
                                </button>
                                <button onclick="abrirReciboDirecto()" class="btn btn-info ml-2">
                                    <i class="fas fa-external-link-alt"></i> Abrir en Nueva Ventana
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled>
                                    <i class="fas fa-print"></i> Primero Cree un Pago
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>3. Limpiar Datos</h6>
                            <p>Limpiar los datos de pago de la sesión.</p>
                            <button onclick="limpiarDatos()" class="btn btn-warning">
                                <i class="fas fa-broom"></i> Limpiar Datos de Sesión
                            </button>
                        </div>
                        
                        <div class="col-md-6">
                            <h6>4. Herramientas Adicionales</h6>
                            <p>Acceder a herramientas de diagnóstico y configuración.</p>
                            <a href="configuracion_impresora_80mm.php" class="btn btn-outline-primary">
                                <i class="fas fa-cogs"></i> Config. Impresora
                            </a>
                            <a href="diagnostico_rapido.php" class="btn btn-outline-info ml-2">
                                <i class="fas fa-stethoscope"></i> Diagnóstico
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Última actividad -->
            <?php
            try {
                $stmt = $conn->query("SELECT p.id, p.monto, p.fecha_pago, f.numero_factura, CONCAT(pac.nombre, ' ', pac.apellido) as paciente 
                                     FROM pagos p 
                                     JOIN facturas f ON p.factura_id = f.id 
                                     JOIN pacientes pac ON f.paciente_id = pac.id 
                                     ORDER BY p.id DESC LIMIT 5");
                $ultimos_pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $ultimos_pagos = [];
            }
            ?>

            <?php if (!empty($ultimos_pagos)): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-history"></i> Últimos Pagos Registrados</h5>
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
                                            <td><?= htmlspecialchars($pago['numero_factura']) ?></td>
                                            <td><?= htmlspecialchars($pago['paciente']) ?></td>
                                            <td>$<?= number_format($pago['monto'], 2) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($pago['fecha_pago'])) ?></td>
                                            <td>
                                                <button onclick="imprimirPagoEspecifico(<?= $pago['id'] ?>)" class="btn btn-sm btn-outline-primary">
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
    </div>
</div>

<script>
function imprimirRecibo() {
    console.log('Iniciando impresión de recibo...');
    
    <?php if (isset($_SESSION['ultimo_pago'])): ?>
        const pagoId = '<?= $_SESSION['ultimo_pago']['pago_id'] ?>';
        const url = 'imprimir_recibo.php?pago_id=' + pagoId;
        
        const ventana = window.open(url, 'recibo_' + Date.now(), 
            'width=450,height=700,scrollbars=yes,resizable=yes,menubar=no,toolbar=no');
        
        if (!ventana) {
            alert('No se pudo abrir la ventana de impresión. Verifique que no esté bloqueando ventanas emergentes.');
            return;
        }
        
        console.log('Ventana de impresión abierta:', url);
    <?php else: ?>
        alert('No hay datos de pago disponibles para imprimir.');
    <?php endif; ?>
}

function abrirReciboDirecto() {
    <?php if (isset($_SESSION['ultimo_pago'])): ?>
        const pagoId = '<?= $_SESSION['ultimo_pago']['pago_id'] ?>';
        const url = 'imprimir_recibo.php?pago_id=' + pagoId;
        window.open(url, '_blank');
    <?php endif; ?>
}

function imprimirPagoEspecifico(pagoId) {
    const url = 'imprimir_recibo.php?pago_id=' + pagoId;
    const ventana = window.open(url, 'recibo_' + pagoId, 
        'width=450,height=700,scrollbars=yes,resizable=yes');
    
    if (!ventana) {
        alert('No se pudo abrir la ventana de impresión.');
    }
}

function limpiarDatos() {
    if (confirm('¿Está seguro de que desea limpiar los datos de pago de la sesión?')) {
        fetch('clear_ultimo_pago.php', { 
            method: 'POST' 
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Datos limpiados exitosamente.');
                location.reload();
            } else {
                alert('Error al limpiar datos: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al comunicarse con el servidor.');
        });
    }
}
</script>

<script src="js/theme-manager.js"></script>
</body>
</html>
