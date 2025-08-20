<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';
require_once 'permissions.php';

echo "<h2>🔍 DIAGNÓSTICO DEL MODAL DE PAGO</h2>";
echo "<hr>";

// 1. Verificar sesión del usuario
echo "<h3>1. Verificación de Sesión</h3>";
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] && isset($_SESSION['id'])) {
    echo "<p>✅ Usuario logueado correctamente</p>";
    echo "<p>👤 ID de usuario: " . $_SESSION['id'] . "</p>";
    echo "<p>🔑 Sesión: " . ($_SESSION['loggedin'] ? 'Activa' : 'Inactiva') . "</p>";
} else {
    echo "<p>❌ Usuario NO logueado - esto es un problema</p>";
    echo "<p>Debe ir a <a href='index.php'>iniciar sesión</a> primero</p>";
    exit();
}

// 2. Verificar permisos
echo "<h3>2. Verificación de Permisos</h3>";
$permisos = [
    'ver_facturacion' => hasPermission('ver_facturacion'),
    'crear_factura' => hasPermission('crear_factura'),
    'editar_factura' => hasPermission('editar_factura'),
    'isAdmin' => isAdmin()
];

foreach ($permisos as $permiso => $tiene) {
    echo "<p>" . ($tiene ? "✅" : "❌") . " <strong>$permiso:</strong> " . ($tiene ? "SÍ" : "NO") . "</p>";
}

// 3. Verificar facturas disponibles
echo "<h3>3. Verificación de Facturas</h3>";
try {
    $stmt = $conn->prepare("
        SELECT f.*, 
               CONCAT(p.nombre, ' ', p.apellido) as paciente_nombre,
               u.nombre as medico_nombre,
               COALESCE(SUM(pg.monto), 0) as total_pagado
        FROM facturas f
        LEFT JOIN pacientes p ON f.paciente_id = p.id
        LEFT JOIN usuarios u ON f.medico_id = u.id  
        LEFT JOIN pagos pg ON f.id = pg.factura_id
        GROUP BY f.id
        ORDER BY f.fecha_factura DESC, f.id DESC
        LIMIT 5
    ");
    $stmt->execute();
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($facturas)) {
        echo "<p>⚠️ <strong>NO HAY FACTURAS EN EL SISTEMA</strong></p>";
        echo "<p>Debe crear una factura primero para poder agregar pagos.</p>";
        echo "<p><a href='facturacion.php' class='btn btn-primary'>Ir a crear factura</a></p>";
    } else {
        echo "<p>✅ Facturas encontradas: " . count($facturas) . "</p>";
        echo "<table border='1' style='width:100%; margin:10px 0;'>";
        echo "<tr><th>ID</th><th>Número</th><th>Paciente</th><th>Estado</th><th>Total</th><th>Pagado</th><th>Pendiente</th><th>¿Puede Pagar?</th></tr>";
        
        foreach ($facturas as $factura) {
            $pendiente = $factura['total'] - $factura['total_pagado'];
            $puede_pagar = ($factura['estado'] === 'pendiente') && ($pendiente > 0) && (hasPermission('crear_factura') || isAdmin());
            
            echo "<tr>";
            echo "<td>" . $factura['id'] . "</td>";
            echo "<td>" . htmlspecialchars($factura['numero_factura']) . "</td>";
            echo "<td>" . htmlspecialchars($factura['paciente_nombre']) . "</td>";
            echo "<td><strong>" . ucfirst($factura['estado']) . "</strong></td>";
            echo "<td>$" . number_format($factura['total'], 2) . "</td>";
            echo "<td>$" . number_format($factura['total_pagado'], 2) . "</td>";
            echo "<td>$" . number_format($pendiente, 2) . "</td>";
            echo "<td>" . ($puede_pagar ? "✅ SÍ" : "❌ NO") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Error al obtener facturas: " . $e->getMessage() . "</p>";
}

// 4. Verificar estado de variables de sesión para modal
echo "<h3>4. Variables de Sesión para Modal</h3>";
echo "<p><strong>show_print_modal:</strong> " . (isset($_SESSION['show_print_modal']) ? ($_SESSION['show_print_modal'] ? 'TRUE' : 'FALSE') : 'NO DEFINIDA') . "</p>";
echo "<p><strong>ultimo_pago existe:</strong> " . (isset($_SESSION['ultimo_pago']) ? 'SÍ' : 'NO') . "</p>";

if (isset($_SESSION['ultimo_pago'])) {
    echo "<h4>Datos del último pago:</h4>";
    echo "<pre>" . print_r($_SESSION['ultimo_pago'], true) . "</pre>";
}

// 5. Test rápido de JavaScript
echo "<h3>5. Test de JavaScript</h3>";
echo "<p>Probando si jQuery está cargado y funcionando:</p>";
echo "<div id='test-js'><em>Esperando JavaScript...</em></div>";

// 6. Crear factura de prueba si no hay facturas pendientes
echo "<h3>6. Acción Recomendada</h3>";
$stmt = $conn->query("SELECT COUNT(*) FROM facturas WHERE estado = 'pendiente'");
$facturas_pendientes = $stmt->fetchColumn();

if ($facturas_pendientes == 0) {
    echo "<div style='background: #ffeeee; padding: 15px; border: 1px solid #ff0000; border-radius: 5px;'>";
    echo "<h4>🚨 PROBLEMA DETECTADO</h4>";
    echo "<p><strong>No hay facturas con estado 'pendiente'</strong></p>";
    echo "<p>Para probar el modal de pago necesitas:</p>";
    echo "<ol>";
    echo "<li>Crear una factura nueva (botón 'Nueva Factura')</li>";
    echo "<li>La factura debe quedar en estado 'pendiente'</li>";
    echo "<li>Entonces aparecerá el botón de 'Agregar Pago' (💲)</li>";
    echo "</ol>";
    echo "<p><a href='facturacion.php' class='btn btn-primary'>🔗 Ir a Facturación</a></p>";
    echo "</div>";
} else {
    echo "<div style='background: #eeffee; padding: 15px; border: 1px solid #00aa00; border-radius: 5px;'>";
    echo "<h4>✅ TODO LISTO</h4>";
    echo "<p>Hay $facturas_pendientes factura(s) pendiente(s)</p>";
    echo "<p>Ve a <a href='facturacion.php'>Facturación</a> y busca el botón 💲 para agregar pago</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h3>📋 INSTRUCCIONES PASO A PASO</h3>";
echo "<ol>";
echo "<li><strong>Ve a facturación:</strong> <a href='facturacion.php'>facturacion.php</a></li>";
echo "<li><strong>Si no hay facturas:</strong> Haz clic en 'Nueva Factura' y crea una</li>";
echo "<li><strong>Busca facturas pendientes:</strong> En la tabla, busca facturas con estado 'Pendiente'</li>";
echo "<li><strong>Haz clic en el botón 💲:</strong> En la columna 'Acciones', haz clic en el ícono del dólar</li>";
echo "<li><strong>Se abrirá el modal:</strong> 'Agregar Pago'</li>";
echo "<li><strong>Completa los datos:</strong> Monto, método de pago, etc.</li>";
echo "<li><strong>Envía:</strong> Al hacer clic en 'Registrar Pago', se debe mostrar el modal de éxito</li>";
echo "</ol>";
?>

<script src="assets/js/jquery.min.js"></script>
<script>
$(document).ready(function() {
    // Test de jQuery
    $('#test-js').html('<span style="color: green;">✅ jQuery funciona correctamente</span>');
    
    // Test de modal de Bootstrap
    if (typeof $.fn.modal !== 'undefined') {
        $('#test-js').append('<br><span style="color: green;">✅ Bootstrap Modal disponible</span>');
    } else {
        $('#test-js').append('<br><span style="color: red;">❌ Bootstrap Modal NO disponible</span>');
    }
    
    console.log('🔍 Diagnóstico completado - jQuery y Bootstrap cargados');
});
</script>

