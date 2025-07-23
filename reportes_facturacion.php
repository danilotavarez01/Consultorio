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

// Verificar permisos para reportes
if (!hasPermission('ver_reportes_facturacion') && !isAdmin()) {
    echo "<div class='alert alert-danger'>No tiene permisos para acceder a esta sección.</div>";
    echo "<a href='index.php' class='btn btn-secondary'>Volver al Inicio</a>";
    exit();
}

// Obtener fechas para el filtro
$fecha_desde = $_GET['fecha_desde'] ?? date('Y-m-01'); // Primer día del mes actual
$fecha_hasta = $_GET['fecha_hasta'] ?? date('Y-m-d'); // Fecha actual

try {
    // Reporte de facturación por período
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_facturas,
            SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as facturas_pendientes,
            SUM(CASE WHEN estado = 'pagada' THEN 1 ELSE 0 END) as facturas_pagadas,
            SUM(CASE WHEN estado = 'vencida' THEN 1 ELSE 0 END) as facturas_vencidas,
            SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) as facturas_canceladas,
            SUM(total) as total_facturado,
            SUM(CASE WHEN estado = 'pagada' THEN total ELSE 0 END) as total_cobrado,
            SUM(CASE WHEN estado = 'pendiente' THEN total ELSE 0 END) as total_pendiente
        FROM facturas 
        WHERE fecha_factura BETWEEN ? AND ?
    ");
    $stmt->execute([$fecha_desde, $fecha_hasta]);
    $resumen = $stmt->fetch(PDO::FETCH_ASSOC);

    // Facturas por día
    $stmt = $conn->prepare("
        SELECT 
            DATE(fecha_factura) as fecha,
            COUNT(*) as cantidad,
            SUM(total) as total_dia
        FROM facturas 
        WHERE fecha_factura BETWEEN ? AND ?
        GROUP BY DATE(fecha_factura)
        ORDER BY fecha_factura DESC
    ");
    $stmt->execute([$fecha_desde, $fecha_hasta]);
    $facturas_por_dia = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Top procedimientos facturados
    $stmt = $conn->prepare("
        SELECT 
            fd.descripcion,
            COUNT(*) as veces_facturado,
            SUM(fd.cantidad) as cantidad_total,
            SUM(fd.subtotal) as ingresos_total,
            AVG(fd.precio_unitario) as precio_promedio
        FROM factura_detalles fd
        JOIN facturas f ON fd.factura_id = f.id
        WHERE f.fecha_factura BETWEEN ? AND ?
        GROUP BY fd.descripcion
        ORDER BY ingresos_total DESC
        LIMIT 10
    ");
    $stmt->execute([$fecha_desde, $fecha_hasta]);
    $top_procedimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Métodos de pago más utilizados
    $stmt = $conn->prepare("
        SELECT 
            p.metodo_pago,
            COUNT(*) as cantidad_pagos,
            SUM(p.monto) as total_monto
        FROM pagos p
        JOIN facturas f ON p.factura_id = f.id
        WHERE f.fecha_factura BETWEEN ? AND ?
        GROUP BY p.metodo_pago
        ORDER BY total_monto DESC
    ");
    $stmt->execute([$fecha_desde, $fecha_hasta]);
    $metodos_pago = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pacientes con más facturación
    $stmt = $conn->prepare("
        SELECT 
            CONCAT(pa.nombre, ' ', pa.apellido) as paciente,
            COUNT(f.id) as total_facturas,
            SUM(f.total) as total_facturado
        FROM facturas f
        JOIN pacientes pa ON f.paciente_id = pa.id
        WHERE f.fecha_factura BETWEEN ? AND ?
        GROUP BY f.paciente_id, pa.nombre, pa.apellido
        ORDER BY total_facturado DESC
        LIMIT 10
    ");
    $stmt->execute([$fecha_desde, $fecha_hasta]);
    $top_pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Error al generar reportes: " . $e->getMessage();
    $resumen = null;
    $facturas_por_dia = [];
    $top_procedimientos = [];
    $metodos_pago = [];
    $top_pacientes = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de Facturación - Sistema de Consultorios</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; }
        .sidebar a:hover { background-color: #454d55; text-decoration: none; }
        .content { padding: 20px; }
        .card-header {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
        }
        .stat-card {
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .precio-cell {
            text-align: right;
            font-family: monospace;
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
                    <h1><i class="fas fa-chart-bar mr-2"></i>Reportes de Facturación</h1>
                    <div>
                        <a href="facturacion.php" class="btn btn-primary mr-2">
                            <i class="fas fa-file-invoice-dollar mr-2"></i>Facturación
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Volver al Inicio
                        </a>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Filtros de Fecha -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar mr-2"></i>Período de Análisis
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="fecha_desde">Desde:</label>
                                    <input type="date" class="form-control" name="fecha_desde" 
                                           value="<?= htmlspecialchars($fecha_desde) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="fecha_hasta">Hasta:</label>
                                    <input type="date" class="form-control" name="fecha_hasta" 
                                           value="<?= htmlspecialchars($fecha_hasta) ?>">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-sync mr-2"></i>Actualizar
                                    </button>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <small class="text-muted">
                                        Período: <?= date('d/m/Y', strtotime($fecha_desde)) ?> - <?= date('d/m/Y', strtotime($fecha_hasta)) ?>
                                    </small>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($resumen): ?>
                <!-- Resumen General -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?= number_format($resumen['total_facturas']) ?></h4>
                                        <p class="mb-0">Total Facturas</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-file-invoice fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0">$<?= number_format($resumen['total_facturado'], 2) ?></h4>
                                        <p class="mb-0">Total Facturado</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-dollar-sign fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0">$<?= number_format($resumen['total_cobrado'], 2) ?></h4>
                                        <p class="mb-0">Total Cobrado</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0">$<?= number_format($resumen['total_pendiente'], 2) ?></h4>
                                        <p class="mb-0">Pendiente de Cobro</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estados de Facturas -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-pie-chart mr-2"></i>Estados de Facturas
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <span class="badge badge-warning">Pendientes</span>
                                        <h4><?= $resumen['facturas_pendientes'] ?></h4>
                                    </div>
                                    <div class="col-6">
                                        <span class="badge badge-success">Pagadas</span>
                                        <h4><?= $resumen['facturas_pagadas'] ?></h4>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-6">
                                        <span class="badge badge-danger">Vencidas</span>
                                        <h4><?= $resumen['facturas_vencidas'] ?></h4>
                                    </div>
                                    <div class="col-6">
                                        <span class="badge badge-dark">Canceladas</span>
                                        <h4><?= $resumen['facturas_canceladas'] ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-line mr-2"></i>Facturación por Día
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($facturas_por_dia)): ?>
                                    <div style="max-height: 200px; overflow-y: auto;">
                                        <?php foreach ($facturas_por_dia as $dia): ?>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <small><?= date('d/m/Y', strtotime($dia['fecha'])) ?></small>
                                                <div>
                                                    <span class="badge badge-primary"><?= $dia['cantidad'] ?> facturas</span>
                                                    <strong>$<?= number_format($dia['total_dia'], 2) ?></strong>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted text-center">No hay datos para mostrar</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Procedimientos -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-star mr-2"></i>Top Procedimientos
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($top_procedimientos)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Procedimiento</th>
                                                    <th>Cantidad</th>
                                                    <th class="text-right">Ingresos</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($top_procedimientos as $proc): ?>
                                                    <tr>
                                                        <td>
                                                            <small><?= htmlspecialchars($proc['descripcion']) ?></small>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-info"><?= $proc['cantidad_total'] ?></span>
                                                        </td>
                                                        <td class="precio-cell">
                                                            <strong>$<?= number_format($proc['ingresos_total'], 2) ?></strong>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted text-center">No hay datos para mostrar</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-users mr-2"></i>Top Pacientes
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($top_pacientes)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Paciente</th>
                                                    <th>Facturas</th>
                                                    <th class="text-right">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($top_pacientes as $paciente): ?>
                                                    <tr>
                                                        <td>
                                                            <small><?= htmlspecialchars($paciente['paciente']) ?></small>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-primary"><?= $paciente['total_facturas'] ?></span>
                                                        </td>
                                                        <td class="precio-cell">
                                                            <strong>$<?= number_format($paciente['total_facturado'], 2) ?></strong>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted text-center">No hay datos para mostrar</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Métodos de Pago -->
                <?php if (!empty($metodos_pago)): ?>
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-credit-card mr-2"></i>Métodos de Pago
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($metodos_pago as $metodo): ?>
                                        <div class="col-md-3 mb-3">
                                            <div class="card bg-light">
                                                <div class="card-body text-center">
                                                    <h5><?= ucfirst(str_replace('_', ' ', $metodo['metodo_pago'])) ?></h5>
                                                    <p class="mb-1">
                                                        <span class="badge badge-secondary"><?= $metodo['cantidad_pagos'] ?> pagos</span>
                                                    </p>
                                                    <h4 class="text-success">$<?= number_format($metodo['total_monto'], 2) ?></h4>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/theme-manager.js"></script>
</body>
</html>
