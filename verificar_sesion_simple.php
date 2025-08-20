<?php
session_start();
require_once 'config.php';
require_once 'permissions.php';

echo "<h2>🔍 Estado de Sesión y Permisos</h2>";

echo "<h3>Variables de Sesión:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Verificación de Permisos:</h3>";
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
    echo "<p>✅ Usuario logueado</p>";
    echo "<p><strong>Rol:</strong> " . ($_SESSION['rol'] ?? 'No definido') . "</p>";
    
    $permisos_a_verificar = ['crear_factura', 'editar_factura', 'ver_facturacion'];
    foreach ($permisos_a_verificar as $permiso) {
        $tiene = hasPermission($permiso);
        echo "<p><strong>$permiso:</strong> " . ($tiene ? "✅ SÍ" : "❌ NO") . "</p>";
    }
    
    echo "<p><strong>Es Admin:</strong> " . (isAdmin() ? "✅ SÍ" : "❌ NO") . "</p>";
} else {
    echo "<p>❌ Usuario NO logueado</p>";
}

// Consulta directa a la BD para comparar
echo "<h3>Datos de BD (Usuario actual):</h3>";
if (isset($_SESSION['id'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['id']]);
        $usuario = $stmt->fetch();
        
        if ($usuario) {
            echo "<table border='1' cellpadding='5'>";
            foreach ($usuario as $campo => $valor) {
                echo "<tr><td>$campo</td><td>$valor</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p>❌ No se encontró el usuario en la BD</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ No hay ID de usuario en sesión</p>";
}

// Test de condición específica
echo "<h3>Test de Condición para Botón de Pago:</h3>";
$condicion1 = isset($_SESSION['loggedin']) && $_SESSION['loggedin'];
$condicion2 = hasPermission('crear_factura');
$condicion3 = isAdmin();
$condicion_final = $condicion1 && ($condicion2 || $condicion3);

echo "<p>Usuario logueado: " . ($condicion1 ? "✅" : "❌") . "</p>";
echo "<p>hasPermission('crear_factura'): " . ($condicion2 ? "✅" : "❌") . "</p>";
echo "<p>isAdmin(): " . ($condicion3 ? "✅" : "❌") . "</p>";
echo "<p><strong>Condición final (crear_factura OR admin): " . ($condicion_final ? "✅ PASS" : "❌ FAIL") . "</strong></p>";

// Si todo está bien, verificar facturas
if ($condicion_final) {
    echo "<h3>Facturas Pendientes:</h3>";
    $stmt = $conn->prepare("SELECT id, numero_factura, estado FROM facturas WHERE estado = 'pendiente' LIMIT 5");
    $stmt->execute();
    $facturas = $stmt->fetchAll();
    
    if (empty($facturas)) {
        echo "<p>❌ No hay facturas pendientes. <a href='crear_factura_test.php'>Crear una</a></p>";
    } else {
        echo "<p>✅ Hay " . count($facturas) . " factura(s) pendiente(s)</p>";
        foreach ($facturas as $factura) {
            echo "<p>- #{$factura['numero_factura']} (ID: {$factura['id']})</p>";
        }
    }
}

?>

<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<style>
    body { padding: 20px; font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
    table { border-collapse: collapse; margin: 10px 0; }
    td { padding: 5px 10px; border: 1px solid #ddd; }
</style>

<p><a href="facturacion.php">← Volver a Facturación</a></p>

