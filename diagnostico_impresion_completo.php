<?php
/**
 * Diagnóstico Rápido del Sistema de Impresión de Recibos
 */
require_once 'session_config.php';
session_start();
require_once 'config.php';

// Verificar autenticación
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "❌ Usuario no autenticado. <a href='index.php'>Ir al login</a>";
    exit();
}

echo "<h2>🔍 Diagnóstico del Sistema de Impresión</h2>";
echo "<hr>";

// 1. Verificar estado de sesión
echo "<h3>📋 Estado de la Sesión</h3>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Usuario logueado: " . ($_SESSION['loggedin'] ? 'SÍ' : 'NO') . "\n";
echo "ID de usuario: " . ($_SESSION['id'] ?? 'No definido') . "\n";
echo "Username: " . ($_SESSION['username'] ?? 'No definido') . "\n";
echo "</pre>";

// 2. Verificar datos de último pago
echo "<h3>💰 Datos de Último Pago en Sesión</h3>";
if (isset($_SESSION['ultimo_pago'])) {
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
    echo "<strong>✅ Datos de pago disponibles:</strong><br>";
    echo "<pre>" . print_r($_SESSION['ultimo_pago'], true) . "</pre>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>❌ No hay datos de último pago en la sesión</strong>";
    echo "</div>";
}

// 3. Verificar último pago en base de datos
echo "<h3>🗄️ Último Pago en Base de Datos</h3>";
try {
    $stmt = $conn->prepare("
        SELECT p.id, p.monto, p.metodo_pago, p.fecha_pago,
               f.numero_factura, f.total,
               CONCAT(pac.nombre, ' ', pac.apellido) as paciente_nombre,
               pac.dni as paciente_cedula,
               u.nombre as medico_nombre
        FROM pagos p
        LEFT JOIN facturas f ON p.factura_id = f.id
        LEFT JOIN pacientes pac ON f.paciente_id = pac.id
        LEFT JOIN usuarios u ON f.medico_id = u.id
        ORDER BY p.id DESC
        LIMIT 1
    ");
    $stmt->execute();
    $ultimo_pago_bd = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($ultimo_pago_bd) {
        echo "<div style='background: #d1ecf1; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Último pago encontrado en BD:</strong><br>";
        echo "<pre>" . print_r($ultimo_pago_bd, true) . "</pre>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ No hay pagos en la base de datos</strong>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>❌ Error al consultar BD:</strong> " . $e->getMessage();
    echo "</div>";
}

// 4. Probar URL de impresión
echo "<h3>🔗 Pruebas de URL de Impresión</h3>";

if (isset($_SESSION['ultimo_pago'])) {
    $pago_id = $_SESSION['ultimo_pago']['pago_id'];
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
    echo "<strong>✅ Usando datos de sesión:</strong><br>";
    echo "Pago ID: $pago_id<br>";
    echo "URL: <a href='imprimir_recibo.php?pago_id=$pago_id' target='_blank'>imprimir_recibo.php?pago_id=$pago_id</a><br>";
    echo "<button onclick=\"window.open('imprimir_recibo.php?pago_id=$pago_id', '_blank')\">🖨️ Probar Impresión</button>";
    echo "</div>";
} elseif (isset($ultimo_pago_bd)) {
    $pago_id = $ultimo_pago_bd['id'];
    echo "<div style='background: #d1ecf1; padding: 10px; border-radius: 5px;'>";
    echo "<strong>ℹ️ Usando último pago de BD:</strong><br>";
    echo "Pago ID: $pago_id<br>";
    echo "URL: <a href='imprimir_recibo.php?pago_id=$pago_id' target='_blank'>imprimir_recibo.php?pago_id=$pago_id</a><br>";
    echo "<button onclick=\"window.open('imprimir_recibo.php?pago_id=$pago_id', '_blank')\">🖨️ Probar Impresión</button>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>❌ No hay datos para generar URL de impresión</strong>";
    echo "</div>";
}

// 5. Verificar archivos de impresión
echo "<h3>📄 Verificación de Archivos</h3>";
$archivos_impresion = [
    'imprimir_recibo.php',
    'imprimir_recibo_backup.php',
    'imprimir_recibo_mejorado.php',
    'clear_ultimo_pago.php',
    'get_ultimo_pago.php'
];

foreach ($archivos_impresion as $archivo) {
    if (file_exists($archivo)) {
        echo "✅ $archivo - EXISTE<br>";
    } else {
        echo "❌ $archivo - NO EXISTE<br>";
    }
}

// 6. Acciones de reparación
echo "<h3>🔧 Acciones de Reparación</h3>";
echo "<div style='padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";

if (!isset($_SESSION['ultimo_pago']) && isset($ultimo_pago_bd)) {
    echo "<button onclick='restaurarDatos()' style='background: #007bff; color: white; padding: 10px; border: none; border-radius: 5px; margin: 5px;'>";
    echo "🔄 Restaurar Datos de Último Pago";
    echo "</button>";
}

echo "<button onclick='crearPagoPrueba()' style='background: #28a745; color: white; padding: 10px; border: none; border-radius: 5px; margin: 5px;'>";
echo "➕ Crear Pago de Prueba";
echo "</button>";

echo "<button onclick='limpiarSesion()' style='background: #ffc107; color: black; padding: 10px; border: none; border-radius: 5px; margin: 5px;'>";
echo "🧹 Limpiar Sesión";
echo "</button>";

echo "<a href='facturacion.php' style='background: #6c757d; color: white; padding: 10px; border: none; border-radius: 5px; margin: 5px; text-decoration: none; display: inline-block;'>";
echo "🏠 Volver a Facturación";
echo "</a>";

echo "</div>";

// 7. Información del sistema
echo "<h3>💻 Información del Sistema</h3>";
echo "<pre>";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Session Path: " . session_save_path() . "\n";
echo "Session Name: " . session_name() . "\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "</pre>";

?>

<script>
function restaurarDatos() {
    if (confirm('¿Restaurar los datos del último pago para impresión?')) {
        fetch('get_ultimo_pago.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Datos restaurados exitosamente. Pago ID: ' + data.pago_id);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error de comunicación: ' + error);
            });
    }
}

function crearPagoPrueba() {
    if (confirm('¿Crear un pago de prueba para testing?')) {
        window.location.href = 'test_impresion_recibo.php';
    }
}

function limpiarSesion() {
    if (confirm('¿Limpiar todas las variables de sesión relacionadas con impresión?')) {
        fetch('clear_ultimo_pago.php', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                alert('Sesión limpiada: ' + data.message);
                location.reload();
            })
            .catch(error => {
                alert('Error: ' + error);
            });
    }
}
</script>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
button { cursor: pointer; }
button:hover { opacity: 0.8; }
</style>
