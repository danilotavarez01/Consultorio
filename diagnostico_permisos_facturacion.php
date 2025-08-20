<?php
session_start();
require_once 'config.php';

echo "<h2>🔍 Diagnóstico Completo: Por qué no aparece el botón de pago</h2>";

// 1. Verificar usuario actual
echo "<h3>1. Usuario Actual</h3>";
if (isset($_SESSION['usuario_id'])) {
    echo "<p><strong>ID de Usuario:</strong> " . $_SESSION['usuario_id'] . "</p>";
    echo "<p><strong>Nombre:</strong> " . ($_SESSION['usuario_nombre'] ?? 'No definido') . "</p>";
    echo "<p><strong>Email:</strong> " . ($_SESSION['usuario_email'] ?? 'No definido') . "</p>";
} else {
    echo "<div class='alert alert-danger'>❌ <strong>PROBLEMA:</strong> No hay usuario en sesión</div>";
    exit;
}

// 2. Verificar permisos
echo "<h3>2. Verificar Permisos</h3>";
if (!function_exists('hasPermission')) {
    echo "<div class='alert alert-warning'>⚠️ Función hasPermission() no definida. Cargando...</div>";
    
    // Intentar cargar funciones de permisos
    if (file_exists('includes/functions.php')) {
        require_once 'includes/functions.php';
    } elseif (file_exists('functions.php')) {
        require_once 'functions.php';
    }
}

if (function_exists('hasPermission')) {
    $permisos_importantes = ['crear_factura', 'editar_factura', 'ver_facturas'];
    echo "<table class='table table-sm'>";
    echo "<tr><th>Permiso</th><th>Tiene Acceso</th></tr>";
    foreach ($permisos_importantes as $permiso) {
        $tiene = hasPermission($permiso) ? "✅ SÍ" : "❌ NO";
        echo "<tr><td>$permiso</td><td>$tiene</td></tr>";
    }
    echo "</table>";
} else {
    echo "<div class='alert alert-danger'>❌ No se pudo cargar la función hasPermission()</div>";
}

if (function_exists('isAdmin')) {
    $es_admin = isAdmin() ? "✅ SÍ" : "❌ NO";
    echo "<p><strong>Es Administrador:</strong> $es_admin</p>";
} else {
    echo "<p><strong>Función isAdmin():</strong> ❌ No definida</p>";
}

// 3. Verificar facturas pendientes
echo "<h3>3. Facturas Pendientes</h3>";
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
        LIMIT 10
    ");
    $stmt->execute();
    $facturas = $stmt->fetchAll();
    
    if (empty($facturas)) {
        echo "<div class='alert alert-warning'>⚠️ <strong>PROBLEMA:</strong> No hay facturas pendientes.</div>";
        echo "<p>El botón de pago solo aparece para facturas con estado 'pendiente'.</p>";
        echo "<p><a href='crear_factura_test.php' class='btn btn-success'>✅ Crear Factura de Prueba</a></p>";
    } else {
        echo "<div class='alert alert-success'>✅ Hay " . count($facturas) . " factura(s) pendiente(s).</div>";
        
        foreach ($facturas as $factura) {
            $tiene_permisos = (function_exists('hasPermission') && hasPermission('crear_factura')) || 
                             (function_exists('isAdmin') && isAdmin());
            $estado_ok = $factura['estado'] === 'pendiente';
            $condicion_completa = $estado_ok && $tiene_permisos;
            
            echo "<div class='card mb-2'>";
            echo "<div class='card-body'>";
            echo "<h6>Factura #{$factura['numero_factura']}</h6>";
            echo "<p><small>";
            echo "Estado: " . ($estado_ok ? "✅ pendiente" : "❌ {$factura['estado']}") . " | ";
            echo "Permisos: " . ($tiene_permisos ? "✅ SÍ" : "❌ NO") . " | ";
            echo "Botón debe aparecer: " . ($condicion_completa ? "✅ SÍ" : "❌ NO");
            echo "</small></p>";
            
            if ($condicion_completa) {
                echo "<div class='border p-2' style='background: #f8f9fa;'>";
                echo "<strong>Botón que debería aparecer:</strong><br>";
                echo "<button type='button' class='btn btn-outline-success btn-sm mt-1' ";
                echo "onclick='alert(\"Este botón debería funcionar\")' title='Agregar Pago'>";
                echo "<i class='fas fa-dollar-sign'></i> 💰";
                echo "</button>";
                echo "</div>";
            }
            echo "</div></div>";
        }
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ Error: " . $e->getMessage() . "</div>";
}

// 4. Simular condiciones de facturacion.php
echo "<h3>4. Simulación de Condiciones de facturacion.php</h3>";
if (!empty($facturas)) {
    $factura_test = $facturas[0];
    $estado_pendiente = $factura_test['estado'] === 'pendiente';
    $tiene_permisos = (function_exists('hasPermission') && hasPermission('crear_factura')) || 
                     (function_exists('isAdmin') && isAdmin());
    
    echo "<div class='border p-3'>";
    echo "<h6>Condición PHP para mostrar botón:</h6>";
    echo "<code>\$factura['estado'] === 'pendiente' && (hasPermission('crear_factura') || isAdmin())</code>";
    echo "<br><br>";
    echo "<strong>Evaluación:</strong><br>";
    echo "- Estado es 'pendiente': " . ($estado_pendiente ? "✅ true" : "❌ false") . "<br>";
    echo "- hasPermission('crear_factura'): " . ((function_exists('hasPermission') && hasPermission('crear_factura')) ? "✅ true" : "❌ false") . "<br>";
    echo "- isAdmin(): " . ((function_exists('isAdmin') && isAdmin()) ? "✅ true" : "❌ false") . "<br>";
    echo "- <strong>Resultado final: " . ($estado_pendiente && $tiene_permisos ? "✅ MOSTRAR BOTÓN" : "❌ NO MOSTRAR") . "</strong>";
    echo "</div>";
}

// 5. Consulta directa a permisos
echo "<h3>5. Consulta Directa a Base de Datos</h3>";
try {
    $stmt = $conn->prepare("
        SELECT u.id, u.nombre, u.email, u.is_admin,
               GROUP_CONCAT(p.permiso) as permisos
        FROM usuarios u 
        LEFT JOIN usuario_permisos up ON u.id = up.usuario_id 
        LEFT JOIN permisos p ON up.permiso_id = p.id 
        WHERE u.id = ?
        GROUP BY u.id
    ");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario_data = $stmt->fetch();
    
    if ($usuario_data) {
        echo "<table class='table table-sm'>";
        echo "<tr><th>Campo</th><th>Valor</th></tr>";
        echo "<tr><td>ID</td><td>{$usuario_data['id']}</td></tr>";
        echo "<tr><td>Nombre</td><td>{$usuario_data['nombre']}</td></tr>";
        echo "<tr><td>Email</td><td>{$usuario_data['email']}</td></tr>";
        echo "<tr><td>Is Admin</td><td>" . ($usuario_data['is_admin'] ? "✅ SÍ" : "❌ NO") . "</td></tr>";
        echo "<tr><td>Permisos</td><td>" . ($usuario_data['permisos'] ?: 'Ninguno') . "</td></tr>";
        echo "</table>";
        
        $permisos_array = $usuario_data['permisos'] ? explode(',', $usuario_data['permisos']) : [];
        $tiene_crear_factura = in_array('crear_factura', $permisos_array);
        echo "<p><strong>Tiene permiso 'crear_factura':</strong> " . ($tiene_crear_factura ? "✅ SÍ" : "❌ NO") . "</p>";
    } else {
        echo "<div class='alert alert-danger'>❌ No se encontraron datos del usuario</div>";
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ Error en consulta: " . $e->getMessage() . "</div>";
}

?>

<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/fontawesome.min.css">
<style>
    body { padding: 20px; font-family: Arial, sans-serif; }
    .container { max-width: 900px; margin: 0 auto; }
    code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
</style>

<div class="mt-4">
    <h3>🔧 Acciones Recomendadas</h3>
    <div class="btn-group">
        <a href="facturacion.php" class="btn btn-primary">🔍 Ver Facturación</a>
        <a href="crear_factura_test.php" class="btn btn-success">➕ Crear Factura</a>
        <a href="configurar_permisos.php" class="btn btn-warning">🔑 Configurar Permisos</a>
    </div>
</div>


