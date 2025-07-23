<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header("Location: index.php");
    exit();
}

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>Simular Pago y Test Recibo</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head>";
echo "<body>";

echo "<div class='container mt-4'>";
echo "<h2>🧪 Simulador de Pago para Test de Recibo</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['simular_pago'])) {
        // Simular un pago real
        try {
            // Obtener una factura existente o crear una básica para el test
            $stmt = $conn->prepare("SELECT id FROM facturas WHERE estado = 'pendiente' LIMIT 1");
            $stmt->execute();
            $factura = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$factura) {
                // Crear una factura básica para el test
                $stmt = $conn->prepare("INSERT INTO facturas (numero_factura, paciente_id, medico_id, fecha_factura, total, estado) VALUES (?, 1, 1, NOW(), 100.00, 'pendiente')");
                $numero_test = 'FAC-TEST-' . date('Ymd-His');
                $stmt->execute([$numero_test]);
                $factura_id = $conn->lastInsertId();
            } else {
                $factura_id = $factura['id'];
            }
            
            // Registrar el pago
            $stmt = $conn->prepare("INSERT INTO pagos (factura_id, monto, metodo_pago, fecha_pago, observaciones) VALUES (?, ?, ?, NOW(), ?)");
            $monto = 100.00;
            $metodo = 'efectivo';
            $observaciones = 'Pago de prueba para test de recibo';
            $stmt->execute([$factura_id, $monto, $metodo, $observaciones]);
            $pago_id = $conn->lastInsertId();
            
            // Obtener información completa para el recibo
            $stmt = $conn->prepare("
                SELECT p.id as pago_id, p.monto, p.metodo_pago,
                       f.numero_factura, f.total,
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
            $pago_detalle = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($pago_detalle) {
                // Guardar en sesión
                $_SESSION['ultimo_pago'] = [
                    'pago_id' => $pago_detalle['pago_id'],
                    'factura_id' => $factura_id,
                    'numero_factura' => $pago_detalle['numero_factura'],
                    'monto' => $pago_detalle['monto'],
                    'metodo_pago' => $pago_detalle['metodo_pago'],
                    'paciente_nombre' => $pago_detalle['paciente_nombre'] ?? 'Paciente Test',
                    'paciente_cedula' => $pago_detalle['paciente_cedula'] ?? '12345678',
                    'medico_nombre' => $pago_detalle['medico_nombre'] ?? 'Dr. Test',
                    'fecha_pago_formato' => $pago_detalle['fecha_pago_formato']
                ];
                
                $_SESSION['show_print_modal'] = true;
                
                echo "<div class='alert alert-success'>";
                echo "<h4>✅ Pago simulado exitosamente</h4>";
                echo "<p><strong>ID del Pago:</strong> " . $pago_id . "</p>";
                echo "<p><strong>Factura:</strong> " . htmlspecialchars($pago_detalle['numero_factura']) . "</p>";
                echo "<p><strong>Monto:</strong> $" . number_format($pago_detalle['monto'], 2) . "</p>";
                echo "<p><strong>Paciente:</strong> " . htmlspecialchars($pago_detalle['paciente_nombre']) . "</p>";
                echo "</div>";
                
                echo "<div class='mt-3'>";
                echo "<button class='btn btn-primary btn-lg' onclick='abrirRecibo()'>🖨️ Abrir Recibo Ahora</button> ";
                echo "<button class='btn btn-info' onclick='verDebug()'>🔍 Ver Debug de Sesión</button>";
                echo "</div>";
                
            } else {
                echo "<div class='alert alert-danger'>Error: No se pudo obtener información del pago</div>";
            }
            
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error en base de datos: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        
    } elseif (isset($_POST['limpiar'])) {
        unset($_SESSION['ultimo_pago']);
        unset($_SESSION['show_print_modal']);
        echo "<div class='alert alert-warning'>Variables de sesión limpiadas</div>";
    }
}

// Mostrar estado actual
echo "<div class='row mt-4'>";
echo "<div class='col-md-6'>";
echo "<div class='card'>";
echo "<div class='card-header'><h5>Estado Actual de la Sesión</h5></div>";
echo "<div class='card-body'>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Usuario:</strong> " . htmlspecialchars($_SESSION['username'] ?? 'N/A') . "</p>";
echo "<p><strong>ultimo_pago:</strong> " . (isset($_SESSION['ultimo_pago']) ? '✅ Existe' : '❌ No existe') . "</p>";
echo "<p><strong>show_print_modal:</strong> " . (isset($_SESSION['show_print_modal']) ? '✅ Existe' : '❌ No existe') . "</p>";

if (isset($_SESSION['ultimo_pago'])) {
    echo "<hr><h6>Datos del último pago:</h6>";
    echo "<pre style='font-size: 11px; max-height: 200px; overflow-y: auto;'>";
    print_r($_SESSION['ultimo_pago']);
    echo "</pre>";
}
echo "</div>";
echo "</div>";
echo "</div>";

echo "<div class='col-md-6'>";
echo "<div class='card'>";
echo "<div class='card-header'><h5>Acciones</h5></div>";
echo "<div class='card-body'>";
echo "<form method='POST' class='mb-3'>";
echo "<button type='submit' name='simular_pago' class='btn btn-success btn-block'>💰 Simular Nuevo Pago</button>";
echo "</form>";

echo "<form method='POST' class='mb-3'>";
echo "<button type='submit' name='limpiar' class='btn btn-warning btn-block'>🧹 Limpiar Sesión</button>";
echo "</form>";

if (isset($_SESSION['ultimo_pago'])) {
    echo "<button class='btn btn-primary btn-block' onclick='abrirRecibo()'>🖨️ Abrir Recibo</button>";
}

echo "<button class='btn btn-info btn-block' onclick='verDebug()'>🔍 Debug de Sesión</button>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "<div class='mt-4'>";
echo "<a href='facturacion.php' class='btn btn-secondary'>← Volver a Facturación</a>";
echo "</div>";

echo "</div>"; // container

echo "<script>";
echo "function abrirRecibo() {";
echo "    console.log('Abriendo recibo...');";
echo "    let url = 'imprimir_recibo.php';";
if (isset($_SESSION['ultimo_pago']['pago_id'])) {
    echo "    url += '?pago_id=" . $_SESSION['ultimo_pago']['pago_id'] . "';";
}
echo "    console.log('URL del recibo:', url);";
echo "    const ventanaRecibo = window.open(url, 'recibo', 'width=800,height=600,scrollbars=yes,resizable=yes');";
echo "    if (!ventanaRecibo) {";
echo "        alert('El navegador bloqueó la ventana emergente. Por favor permita ventanas emergentes para este sitio.');";
echo "    }";
echo "}";

echo "function verDebug() {";
echo "    window.open('debug_session.php', 'debug', 'width=800,height=600,scrollbars=yes');";
echo "}";
echo "</script>";

echo "</body>";
echo "</html>";
?>
