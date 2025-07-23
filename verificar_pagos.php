<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';

// Verificar autenticación
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "❌ Usuario no autenticado. <a href='index.php'>Ir al login</a>";
    exit();
}

echo "<h2>🔍 Verificación del Estado de Pagos</h2>";
echo "<hr>";

// 1. Verificar datos de sesión
echo "<h3>📋 Estado de la Sesión</h3>";
echo "<pre>";
echo "Usuario logueado: " . ($_SESSION['loggedin'] ? 'SÍ' : 'NO') . "\n";
echo "ID de usuario: " . ($_SESSION['id'] ?? 'No definido') . "\n";
echo "Último pago en sesión: " . (isset($_SESSION['ultimo_pago']) ? 'SÍ' : 'NO') . "\n";

if (isset($_SESSION['ultimo_pago'])) {
    echo "Datos del último pago:\n";
    print_r($_SESSION['ultimo_pago']);
}

echo "Modal de impresión: " . (isset($_SESSION['show_print_modal']) ? 'SÍ' : 'NO') . "\n";
echo "</pre>";

// 2. Verificar estructura de la tabla pagos
echo "<h3>🗄️ Estructura de la Tabla Pagos</h3>";
try {
    $stmt = $conn->query("DESCRIBE pagos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "❌ Error al obtener estructura: " . $e->getMessage();
}

// 3. Verificar cantidad de pagos
echo "<h3>📊 Estadísticas de Pagos</h3>";
try {
    $stmt = $conn->query("SELECT COUNT(*) as total FROM pagos");
    $total = $stmt->fetchColumn();
    echo "<p><strong>Total de pagos en la base de datos:</strong> $total</p>";
    
    if ($total > 0) {
        // Mostrar los últimos 5 pagos
        echo "<h4>🕐 Últimos 5 Pagos</h4>";
        $stmt = $conn->query("
            SELECT p.id, p.monto, p.fecha_pago, p.paciente_id, 
                   CONCAT(pac.nombre, ' ', pac.apellido) as paciente_nombre
            FROM pagos p 
            LEFT JOIN pacientes pac ON p.paciente_id = pac.id 
            ORDER BY p.id DESC 
            LIMIT 5
        ");
        $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Monto</th><th>Fecha</th><th>Paciente</th><th>Acciones</th></tr>";
        foreach ($pagos as $pago) {
            echo "<tr>";
            echo "<td>{$pago['id']}</td>";
            echo "<td>$" . number_format($pago['monto'], 2) . "</td>";
            echo "<td>{$pago['fecha_pago']}</td>";
            echo "<td>{$pago['paciente_nombre']}</td>";
            echo "<td>";
            echo "<a href='imprimir_recibo.php?pago_id={$pago['id']}' target='_blank'>Ver Recibo</a> | ";
            echo "<a href='imprimir_recibo_mejorado.php?pago_id={$pago['id']}' target='_blank'>Ver Recibo Mejorado</a>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div style='background: #fff3cd; padding: 10px; border: 1px solid #ffeaa7; border-radius: 5px;'>";
        echo "⚠️ <strong>No hay pagos en la base de datos.</strong><br>";
        echo "Para probar la impresión, puede:";
        echo "<ul>";
        echo "<li><a href='crear_pago_prueba.php'>Crear un pago de prueba</a></li>";
        echo "<li><a href='facturacion.php'>Ir a facturación para registrar un pago real</a></li>";
        echo "</ul>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "❌ Error al consultar pagos: " . $e->getMessage();
}

// 4. Verificar estructura de la tabla pacientes
echo "<h3>👥 Verificar Tabla Pacientes</h3>";
try {
    $stmt = $conn->query("SELECT COUNT(*) as total FROM pacientes");
    $total_pacientes = $stmt->fetchColumn();
    echo "<p><strong>Total de pacientes:</strong> $total_pacientes</p>";
} catch (Exception $e) {
    echo "❌ Error al consultar pacientes: " . $e->getMessage();
}

// 5. Links útiles
echo "<h3>🔗 Enlaces Útiles</h3>";
echo "<ul>";
echo "<li><a href='facturacion.php'>🧾 Ir a Facturación</a></li>";
echo "<li><a href='crear_pago_prueba.php'>💰 Crear Pago de Prueba</a></li>";
echo "<li><a href='test_impresion_automatica.php'>🖨️ Test de Impresión</a></li>";
echo "<li><a href='clear_all_sessions.php'>🧹 Limpiar Sesiones</a></li>";
echo "<li><a href='index.php'>🏠 Volver al Inicio</a></li>";
echo "</ul>";

?>
