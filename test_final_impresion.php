<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';

// Verificar login
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header("Location: index.php");
    exit();
}

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>✅ Test Final - Flujo de Impresión Completo</title>";
echo "<link href='assets/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head>";
echo "<body>";

echo "<div class='container mt-4'>";
echo "<h2>✅ Test Final - Flujo de Impresión Completo</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simular_pago_completo'])) {
    try {
        // Paso 1: Crear/obtener una factura
        $stmt = $conn->prepare("SELECT id FROM facturas WHERE estado = 'pendiente' LIMIT 1");
        $stmt->execute();
        $factura = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$factura) {
            // Crear factura de prueba
            $stmt = $conn->prepare("INSERT INTO facturas (numero_factura, paciente_id, medico_id, fecha_factura, total, estado) VALUES (?, 1, 1, NOW(), 150.00, 'pendiente')");
            $numero = 'FAC-FINAL-TEST-' . date('Ymd-His');
            $stmt->execute([$numero]);
            $factura_id = $conn->lastInsertId();
        } else {
            $factura_id = $factura['id'];
        }
        
        // Paso 2: Registrar el pago
        $stmt = $conn->prepare("INSERT INTO pagos (factura_id, monto, metodo_pago, fecha_pago, observaciones) VALUES (?, ?, ?, NOW(), ?)");
        $monto = 150.00;
        $metodo = 'efectivo';
        $observaciones = 'Pago de test final completo';
        $stmt->execute([$factura_id, $monto, $metodo, $observaciones]);
        $pago_id = $conn->lastInsertId();
        
        // Paso 3: Obtener información completa (simulando facturacion.php)
        $stmt = $conn->prepare("
            SELECT p.id as pago_id, p.monto, p.metodo_pago,
                   f.numero_factura, f.total,
                   CONCAT(pac.nombre, ' ', pac.apellido) as paciente_nombre,
                   pac.dni as paciente_cedula,
                   u.nombre as medico_nombre
            FROM pagos p
            LEFT JOIN facturas f ON p.factura_id = f.id
            LEFT JOIN pacientes pac ON f.paciente_id = pac.id
            LEFT JOIN usuarios u ON f.medico_id = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$pago_id]);
        $pago_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Paso 4: Guardar en sesión (como hace facturacion.php)
        $_SESSION['ultimo_pago'] = [
            'pago_id' => $pago_info['pago_id'],
            'factura_id' => $factura_id,
            'numero_factura' => $pago_info['numero_factura'],
            'monto' => $pago_info['monto'],
            'metodo_pago' => $pago_info['metodo_pago'],
            'paciente_nombre' => $pago_info['paciente_nombre'] ?? 'Paciente Test',
            'paciente_cedula' => $pago_info['paciente_cedula'] ?? '12345678',
            'medico_nombre' => $pago_info['medico_nombre'] ?? 'Dr. Test'
        ];
        
        $_SESSION['show_print_modal'] = true;
        
        echo "<div class='alert alert-success'>";
        echo "<h4>✅ Pago Registrado Exitosamente</h4>";
        echo "<p><strong>ID Pago:</strong> " . $pago_id . "</p>";
        echo "<p><strong>Factura:</strong> " . htmlspecialchars($pago_info['numero_factura']) . "</p>";
        echo "<p><strong>Monto:</strong> $" . number_format($pago_info['monto'], 2) . "</p>";
        echo "<p><strong>Paciente:</strong> " . htmlspecialchars($pago_info['paciente_nombre']) . "</p>";
        echo "</div>";
        
        // Modal simulado de impresión
        echo "<div class='card mt-4'>";
        echo "<div class='card-header bg-success text-white'>";
        echo "<h5>🖨️ Modal de Impresión (Simulado)</h5>";
        echo "</div>";
        echo "<div class='card-body'>";
        echo "<p>¿Desea imprimir el recibo de este pago?</p>";
        echo "<div class='table-responsive'>";
        echo "<table class='table table-sm'>";
        echo "<tr><td><strong>Factura:</strong></td><td>" . htmlspecialchars($pago_info['numero_factura']) . "</td></tr>";
        echo "<tr><td><strong>Paciente:</strong></td><td>" . htmlspecialchars($pago_info['paciente_nombre']) . "</td></tr>";
        echo "<tr><td><strong>Monto:</strong></td><td class='text-success'><strong>$" . number_format($pago_info['monto'], 2) . "</strong></td></tr>";
        echo "<tr><td><strong>Método:</strong></td><td>" . ucfirst($pago_info['metodo_pago']) . "</td></tr>";
        echo "</table>";
        echo "</div>";
        
        echo "<div class='text-center'>";
        echo "<button type='button' class='btn btn-success btn-lg' onclick='imprimirReciboTest(" . $pago_id . ")'>✅ SÍ, IMPRIMIR RECIBO</button> ";
        echo "<button type='button' class='btn btn-secondary' onclick='cerrarModal()'>❌ No, Gracias</button>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Estado actual
echo "<div class='row mt-4'>";
echo "<div class='col-md-6'>";
echo "<div class='card'>";
echo "<div class='card-header'><h5>📊 Estado Actual</h5></div>";
echo "<div class='card-body'>";
echo "<p><strong>Usuario:</strong> " . htmlspecialchars($_SESSION['username']) . "</p>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>ultimo_pago:</strong> " . (isset($_SESSION['ultimo_pago']) ? '✅ Existe' : '❌ No existe') . "</p>";
echo "<p><strong>show_print_modal:</strong> " . (isset($_SESSION['show_print_modal']) ? '✅ Existe' : '❌ No existe') . "</p>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "<div class='col-md-6'>";
echo "<div class='card'>";
echo "<div class='card-header'><h5>🚀 Acciones</h5></div>";
echo "<div class='card-body'>";

if (!isset($_SESSION['ultimo_pago'])) {
    echo "<form method='POST'>";
    echo "<button type='submit' name='simular_pago_completo' class='btn btn-primary btn-block'>💰 Simular Pago Completo</button>";
    echo "</form>";
} else {
    $pago_id = $_SESSION['ultimo_pago']['pago_id'] ?? null;
    echo "<button class='btn btn-success btn-block' onclick='imprimirReciboTest(" . $pago_id . ")'>🖨️ Imprimir Recibo</button>";
    echo "<hr>";
    echo "<form method='POST'>";
    echo "<button type='submit' name='simular_pago_completo' class='btn btn-warning btn-block'>🔄 Nuevo Pago</button>";
    echo "</form>";
}

echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";

// Test directo
echo "<div class='mt-4'>";
echo "<h4>🔧 Tests Directos</h4>";
echo "<a href='imprimir_recibo.php' target='_blank' class='btn btn-info btn-sm'>📄 Test Sesión</a> ";
echo "<a href='imprimir_recibo.php?pago_id=68' target='_blank' class='btn btn-success btn-sm'>📄 Test BD (ID 68)</a> ";
echo "<a href='test_simple_sesion.php' class='btn btn-secondary btn-sm'>🧪 Test Simple</a>";
echo "</div>";

echo "<div class='mt-4'>";
echo "<a href='facturacion.php' class='btn btn-outline-primary'>← Volver a Facturación</a>";
echo "</div>";

echo "</div>"; // container

echo "<script>";
echo "function imprimirReciboTest(pagoId) {";
echo "    console.log('Abriendo recibo para pago ID:', pagoId);";
echo "    let url = 'imprimir_recibo.php';";
echo "    if (pagoId && pagoId !== 'null') {";
echo "        url += '?pago_id=' + pagoId;";
echo "    }";
echo "    console.log('URL:', url);";
echo "    const ventana = window.open(url, 'recibo_test', 'width=400,height=600,scrollbars=yes,resizable=yes');";
echo "    if (!ventana) {";
echo "        alert('Ventana bloqueada. Por favor permita ventanas emergentes.');";
echo "    } else {";
echo "        console.log('Ventana abierta exitosamente');";
echo "    }";
echo "}";

echo "function cerrarModal() {";
echo "    console.log('Modal cerrado sin imprimir');";
echo "}";
echo "</script>";

echo "</body>";
echo "</html>";
?>

