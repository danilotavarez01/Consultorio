<?php
// Versión corregida y simplificada de imprimir_recibo.php
require_once 'session_config.php';
session_start();
require_once 'config.php';

// Debug: Log para troubleshooting
error_log("=== IMPRIMIR RECIBO DEBUG ===");
error_log("Session ID: " . session_id());
error_log("GET params: " . print_r($_GET, true));
error_log("Session loggedin: " . (isset($_SESSION['loggedin']) ? $_SESSION['loggedin'] : 'NO SET'));
error_log("ultimo_pago existe: " . (isset($_SESSION['ultimo_pago']) ? 'SI' : 'NO'));

// HTML inicial
echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Recibo de Pago</title>";
echo "<style>";
echo "body { font-family: 'Courier New', monospace; font-size: 12px; margin: 0; padding: 20px; background: white; }";
echo ".recibo { max-width: 300px; margin: 0 auto; border: 1px solid #000; padding: 10px; }";
echo ".header { text-align: center; border-bottom: 1px dashed #000; margin-bottom: 10px; padding-bottom: 10px; }";
echo ".linea { margin: 5px 0; }";
echo ".total { font-weight: bold; border-top: 1px dashed #000; margin-top: 10px; padding-top: 10px; }";
echo ".error { color: red; text-align: center; padding: 20px; }";
echo ".debug { background: #f0f0f0; padding: 10px; margin: 10px 0; font-size: 10px; }";
echo "@media print { .no-print { display: none; } }";
echo "</style>";
echo "</head>";
echo "<body>";

// Verificar login
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "<div class='error'>";
    echo "<h3>❌ Error de Sesión</h3>";
    echo "<p>Su sesión ha expirado o no está logueado.</p>";
    echo "<p>Session ID: " . session_id() . "</p>";
    echo "<button onclick='window.close()' class='no-print'>Cerrar</button>";
    echo "</div>";
    echo "</body></html>";
    exit();
}

// Obtener datos del pago
$pago = null;

// Método 1: Por parámetro GET (más confiable)
if (isset($_GET['pago_id']) && is_numeric($_GET['pago_id'])) {
    $pago_id = $_GET['pago_id'];
    
    try {
        $stmt = $conn->prepare("
            SELECT p.id as pago_id, p.monto, p.metodo_pago, p.observaciones,
                   f.numero_factura, f.total as total_factura,
                   CONCAT(pac.nombre, ' ', pac.apellido) as paciente_nombre,
                   pac.dni as paciente_cedula,
                   u.nombre as medico_nombre,
                   DATE_FORMAT(p.fecha_pago, '%d/%m/%Y %H:%i') as fecha_pago_formato
            FROM pagos p
            LEFT JOIN facturas f ON p.factura_id = f.id
            LEFT JOIN pacientes pac ON f.paciente_id = pac.id
            LEFT JOIN usuarios u ON f.medico_id = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$pago_id]);
        $pago_bd = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($pago_bd) {
            $pago = $pago_bd;
            error_log("Pago obtenido de BD correctamente: ID " . $pago_id);
        }
    } catch (PDOException $e) {
        error_log("Error al obtener pago de BD: " . $e->getMessage());
    }
}

// Método 2: Por sesión (fallback)
if (!$pago && isset($_SESSION['ultimo_pago'])) {
    $pago = $_SESSION['ultimo_pago'];
    error_log("Usando pago de sesión como fallback");
}

// Si no hay datos, mostrar error
if (!$pago) {
    echo "<div class='error'>";
    echo "<h3>❌ Sin Datos de Pago</h3>";
    echo "<p>No hay información de pago para imprimir.</p>";
    echo "<p>Por favor registre un pago primero.</p>";
    
    echo "<div class='debug no-print'>";
    echo "<strong>Debug Info:</strong><br>";
    echo "Session ID: " . session_id() . "<br>";
    echo "Usuario: " . htmlspecialchars($_SESSION['username'] ?? 'N/A') . "<br>";
    echo "GET pago_id: " . htmlspecialchars($_GET['pago_id'] ?? 'NO') . "<br>";
    echo "Session ultimo_pago: " . (isset($_SESSION['ultimo_pago']) ? 'EXISTS' : 'NO') . "<br>";
    if (isset($_SESSION['ultimo_pago'])) {
        echo "Contenido:<br><pre>" . print_r($_SESSION['ultimo_pago'], true) . "</pre>";
    }
    echo "</div>";
    
    echo "<button onclick='window.close()' class='no-print'>Cerrar</button>";
    echo "</div>";
    echo "</body></html>";
    exit();
}

// Procesar datos para el recibo
$numero_factura = $pago['numero_factura'] ?? 'N/A';
$monto = floatval($pago['monto'] ?? 0);
$metodo_pago = $pago['metodo_pago'] ?? 'efectivo';
$fecha = $pago['fecha_pago_formato'] ?? date('d/m/Y H:i');
$paciente_nombre = $pago['paciente_nombre'] ?? 'Paciente';
$paciente_cedula = $pago['paciente_cedula'] ?? '';
$medico_nombre = $pago['medico_nombre'] ?? 'Médico';

// Obtener configuración del consultorio
try {
    $stmt = $conn->query("SELECT nombre_consultorio, direccion, telefono, ruc FROM configuracion LIMIT 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $config = null;
}

$nombre_consultorio = $config['nombre_consultorio'] ?? 'CONSULTORIO ODONTOLÓGICO';
$direccion = $config['direccion'] ?? 'Dirección del Consultorio';
$telefono = $config['telefono'] ?? '(555) 123-4567';
$ruc = $config['ruc'] ?? 'RUC: 12345678901';

// Generar el recibo
echo "<div class='recibo'>";

// Header
echo "<div class='header'>";
echo "<strong>" . htmlspecialchars($nombre_consultorio) . "</strong><br>";
echo htmlspecialchars($direccion) . "<br>";
echo "Tel: " . htmlspecialchars($telefono) . "<br>";
echo htmlspecialchars($ruc) . "<br>";
echo "<br>";
echo "<strong>RECIBO DE PAGO</strong><br>";
echo "Fecha: " . htmlspecialchars($fecha);
echo "</div>";

// Datos del pago
echo "<div class='linea'><strong>Factura N°:</strong> " . htmlspecialchars($numero_factura) . "</div>";
echo "<div class='linea'><strong>Paciente:</strong> " . htmlspecialchars($paciente_nombre) . "</div>";
if ($paciente_cedula) {
    echo "<div class='linea'><strong>Cédula:</strong> " . htmlspecialchars($paciente_cedula) . "</div>";
}
echo "<div class='linea'><strong>Atendido por:</strong> " . htmlspecialchars($medico_nombre) . "</div>";
echo "<div class='linea'><strong>Método de pago:</strong> " . ucfirst(str_replace('_', ' ', htmlspecialchars($metodo_pago))) . "</div>";

echo "<div class='total'>";
echo "<div class='linea'><strong>TOTAL PAGADO: $" . number_format($monto, 2) . "</strong></div>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 15px; font-size: 10px;'>";
echo "¡Gracias por su confianza!<br>";
echo "Recibo generado el " . date('d/m/Y H:i');
echo "</div>";

echo "</div>";

// Botones de control (no se imprimen)
echo "<div class='no-print' style='text-align: center; margin-top: 20px;'>";
echo "<button onclick='window.print()' style='padding: 10px 20px; margin: 5px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;'>🖨️ Imprimir</button>";
echo "<button onclick='window.close()' style='padding: 10px 20px; margin: 5px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;'>❌ Cerrar</button>";
echo "</div>";

echo "<script>";
echo "console.log('Recibo cargado exitosamente');";
echo "console.log('Datos del pago:', " . json_encode($pago) . ");";

// Limpiar datos de sesión después de mostrar el recibo (solo si no es de prueba)
if (isset($pago['pago_id']) && $pago['pago_id'] != 999) {
    echo "
    // Limpiar datos de sesión después de un breve delay
    setTimeout(function() {
        fetch('clear_ultimo_pago.php').then(function() {
            console.log('Datos de sesión limpiados');
        }).catch(function(error) {
            console.log('Error al limpiar sesión:', error);
        });
    }, 2000);
    ";
}

echo "</script>";
echo "</body></html>";

error_log("Recibo generado exitosamente para pago: " . ($pago['pago_id'] ?? 'SESION'));
?>
