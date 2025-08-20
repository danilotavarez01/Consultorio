<?php
session_start();
require_once 'config.php';
require_once 'permissions.php';

echo "<h2>🔧 Solucionador Rápido: Botón de Pago</h2>";

$problema_encontrado = false;
$acciones_realizadas = [];

// 1. Verificar si el usuario está logueado
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "<div class='alert alert-danger'>❌ Usuario no logueado. <a href='login.php'>Iniciar sesión</a></div>";
    exit;
}

echo "<div class='alert alert-info'>✅ Usuario logueado: " . ($_SESSION['usuario_nombre'] ?? $_SESSION['email'] ?? 'ID ' . $_SESSION['id']) . "</div>";

// 2. Verificar permisos
$tiene_permiso = hasPermission('crear_factura');
$es_admin = isAdmin();

if (!$tiene_permiso && !$es_admin) {
    echo "<div class='alert alert-warning'>⚠️ Usuario sin permisos para crear facturas</div>";
    $problema_encontrado = true;
    
    // Intentar asignar permisos automáticamente
    if (isset($_GET['fix_permisos'])) {
        try {
            // Buscar el ID del permiso crear_factura
            $stmt = $conn->prepare("SELECT id FROM permisos WHERE permiso = 'crear_factura'");
            $stmt->execute();
            $permiso_id = $stmt->fetchColumn();
            
            if ($permiso_id) {
                // Asignar el permiso al usuario
                $stmt = $conn->prepare("INSERT IGNORE INTO usuario_permisos (usuario_id, permiso_id) VALUES (?, ?)");
                $stmt->execute([$_SESSION['id'], $permiso_id]);
                
                $acciones_realizadas[] = "✅ Permiso 'crear_factura' asignado al usuario";
                $tiene_permiso = true;
            } else {
                // Crear el permiso si no existe
                $stmt = $conn->prepare("INSERT INTO permisos (permiso, descripcion) VALUES ('crear_factura', 'Permiso para crear facturas')");
                $stmt->execute();
                $permiso_id = $conn->lastInsertId();
                
                // Asignar al usuario
                $stmt = $conn->prepare("INSERT INTO usuario_permisos (usuario_id, permiso_id) VALUES (?, ?)");
                $stmt->execute([$_SESSION['id'], $permiso_id]);
                
                $acciones_realizadas[] = "✅ Permiso 'crear_factura' creado y asignado";
                $tiene_permiso = true;
            }
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>❌ Error al asignar permisos: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<p><a href='?fix_permisos=1' class='btn btn-warning'>🔧 Asignar Permisos Automáticamente</a></p>";
    }
} else {
    echo "<div class='alert alert-success'>✅ Usuario tiene permisos: ";
    echo $tiene_permiso ? "crear_factura " : "";
    echo $es_admin ? "admin " : "";
    echo "</div>";
}

// 3. Verificar facturas pendientes
$stmt = $conn->prepare("
    SELECT f.id, f.numero_factura, f.total,
           COALESCE(SUM(p.monto), 0) as total_pagado,
           (f.total - COALESCE(SUM(p.monto), 0)) as monto_pendiente
    FROM facturas f 
    LEFT JOIN pagos p ON f.id = p.factura_id 
    WHERE f.estado = 'pendiente'
    GROUP BY f.id
    HAVING monto_pendiente > 0
");
$stmt->execute();
$facturas_pendientes = $stmt->fetchAll();

if (empty($facturas_pendientes)) {
    echo "<div class='alert alert-warning'>⚠️ No hay facturas pendientes con saldo</div>";
    $problema_encontrado = true;
    
    if (isset($_GET['crear_factura'])) {
        try {
            // Crear factura de prueba
            $numero_factura = 'TEST-' . date('YmdHis');
            $stmt = $conn->prepare("
                INSERT INTO facturas (numero_factura, paciente_id, fecha_factura, subtotal, descuento, total, estado, observaciones)
                VALUES (?, 1, NOW(), 100.00, 0.00, 100.00, 'pendiente', 'Factura de prueba para test de botón de pago')
            ");
            $stmt->execute([$numero_factura]);
            
            $acciones_realizadas[] = "✅ Factura de prueba creada: $numero_factura";
            
            // Refrescar la consulta
            $stmt = $conn->prepare("
                SELECT f.id, f.numero_factura, f.total,
                       COALESCE(SUM(p.monto), 0) as total_pagado,
                       (f.total - COALESCE(SUM(p.monto), 0)) as monto_pendiente
                FROM facturas f 
                LEFT JOIN pagos p ON f.id = p.factura_id 
                WHERE f.estado = 'pendiente'
                GROUP BY f.id
                HAVING monto_pendiente > 0
            ");
            $stmt->execute();
            $facturas_pendientes = $stmt->fetchAll();
            
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>❌ Error al crear factura: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<p><a href='?crear_factura=1' class='btn btn-success'>➕ Crear Factura de Prueba</a></p>";
    }
} else {
    echo "<div class='alert alert-success'>✅ Hay " . count($facturas_pendientes) . " factura(s) pendiente(s) con saldo</div>";
    
    echo "<table class='table table-sm'>";
    echo "<tr><th>Factura</th><th>Total</th><th>Pagado</th><th>Pendiente</th></tr>";
    foreach ($facturas_pendientes as $factura) {
        echo "<tr>";
        echo "<td>#{$factura['numero_factura']}</td>";
        echo "<td>$" . number_format($factura['total'], 2) . "</td>";
        echo "<td>$" . number_format($factura['total_pagado'], 2) . "</td>";
        echo "<td>$" . number_format($factura['monto_pendiente'], 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 4. Mostrar resultados
if (!empty($acciones_realizadas)) {
    echo "<div class='alert alert-info'>";
    echo "<h5>Acciones realizadas:</h5>";
    foreach ($acciones_realizadas as $accion) {
        echo "<p>$accion</p>";
    }
    echo "</div>";
}

if (!$problema_encontrado) {
    echo "<div class='alert alert-success'>";
    echo "<h4>🎉 ¡Todo configurado correctamente!</h4>";
    echo "<p>El botón de pago (💰) debería aparecer ahora en la página de facturación.</p>";
    echo "<p><a href='facturacion.php' class='btn btn-success'>🧾 Ir a Facturación</a></p>";
    echo "</div>";
} else {
    echo "<div class='alert alert-warning'>";
    echo "<h4>⚠️ Problemas detectados</h4>";
    echo "<p>Por favor, corrija los problemas mencionados arriba y luego:</p>";
    echo "<p><a href='?' class='btn btn-primary'>🔄 Volver a verificar</a></p>";
    echo "</div>";
}

// 5. Mostrar debug adicional si se solicita
if (isset($_GET['debug'])) {
    echo "<h3>🔍 Información de Debug</h3>";
    echo "<h4>Sesión:</h4>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
    
    echo "<h4>Permisos (hasPermission):</h4>";
    $permisos_test = ['crear_factura', 'editar_factura', 'ver_facturacion'];
    foreach ($permisos_test as $perm) {
        echo "<p>$perm: " . (hasPermission($perm) ? "✅ SÍ" : "❌ NO") . "</p>";
    }
}

?>

<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<style>
    body { padding: 20px; font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; }
    .table { background: white; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 12px; }
</style>

<div class="mt-4">
    <p>
        <a href="facturacion.php" class="btn btn-primary">📊 Ir a Facturación</a>
        <a href="?debug=1" class="btn btn-secondary">🔍 Mostrar Debug</a>
        <a href="verificar_sesion_simple.php" class="btn btn-info">🔍 Verificar Sesión</a>
    </p>
</div>

