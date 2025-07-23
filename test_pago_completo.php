<?php
// test_pago_completo.php - Script para probar el flujo completo de pago
require_once 'session_config.php';
session_start();
require_once 'config.php';
require_once 'permissions.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || !isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}

echo "<html><head><title>Test Pago Completo</title>";
echo "<link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>";
echo "<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css'>";
echo "</head><body class='p-4'>";

echo "<div class='container'>";
echo "<h2><i class='fas fa-credit-card mr-2'></i>Test del Flujo Completo de Pago</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_pago'])) {
    try {
        // Simular un pago completo igual que el sistema real
        $factura_id = 1; // Usar factura ID existente o crear una de prueba
        $monto = 250.00;
        $metodo_pago = 'efectivo';
        $numero_referencia = 'TEST-' . time();
        $observaciones_pago = 'Pago de prueba para testing del modal';
        
        $conn->beginTransaction();
        
        // Insertar pago de prueba
        $stmt = $conn->prepare("
            INSERT INTO pagos (factura_id, fecha_pago, monto, metodo_pago, numero_referencia, observaciones) 
            VALUES (?, CURDATE(), ?, ?, ?, ?)
        ");
        $stmt->execute([$factura_id, $monto, $metodo_pago, $numero_referencia, $observaciones_pago]);
        
        $pago_id = $conn->lastInsertId();
        
        $conn->commit();
        
        // Obtener información completa de la factura para el modal (igual que en facturacion.php)
        $stmt = $conn->prepare("
            SELECT f.numero_factura, f.total, f.id,
                   CONCAT(p.nombre, ' ', p.apellido) as paciente_nombre,
                   p.telefono as paciente_telefono,
                   p.dni as paciente_cedula,
                   u.nombre as medico_nombre
            FROM facturas f
            LEFT JOIN pacientes p ON f.paciente_id = p.id
            LEFT JOIN usuarios u ON f.medico_id = u.id
            WHERE f.id = ?
        ");
        $stmt->execute([$factura_id]);
        $factura_completa = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Guardar datos completos para el modal de impresión (igual que en facturacion.php)
        $_SESSION['ultimo_pago'] = [
            'pago_id' => $pago_id,
            'factura_id' => $factura_id,
            'numero_factura' => $factura_completa['numero_factura'] ?? 'FAC-TEST-' . $factura_id,
            'paciente_nombre' => $factura_completa['paciente_nombre'] ?? 'Paciente de Prueba',
            'paciente_telefono' => $factura_completa['paciente_telefono'] ?? '',
            'paciente_cedula' => $factura_completa['paciente_cedula'] ?? '',
            'medico_nombre' => $factura_completa['medico_nombre'] ?? '',
            'monto' => $monto,
            'metodo_pago' => $metodo_pago,
            'numero_referencia' => $numero_referencia,
            'observaciones' => $observaciones_pago,
            'fecha' => date('Y-m-d H:i:s'),
            'usuario_id' => $_SESSION['id']
        ];
        
        // Activar modal de impresión
        $_SESSION['show_print_modal'] = true;
        
        echo "<div class='alert alert-success'>";
        echo "<h4><i class='fas fa-check mr-2'></i>¡Pago de prueba registrado exitosamente!</h4>";
        echo "<p>Se ha creado un pago de prueba y se han establecido las variables de sesión.</p>";
        echo "<p><strong>Pago ID:</strong> $pago_id</p>";
        echo "<p><strong>Factura ID:</strong> $factura_id</p>";
        echo "<p><strong>Monto:</strong> $" . number_format($monto, 2) . "</p>";
        echo "</div>";
        
        echo "<div class='alert alert-info'>";
        echo "<h5>Variables de sesión establecidas:</h5>";
        echo "<pre>" . print_r($_SESSION['ultimo_pago'], true) . "</pre>";
        echo "<p><strong>show_print_modal:</strong> " . ($_SESSION['show_print_modal'] ? 'TRUE' : 'FALSE') . "</p>";
        echo "</div>";
        
        echo "<div class='mt-4'>";
        echo "<a href='facturacion.php' class='btn btn-success btn-lg'>";
        echo "<i class='fas fa-arrow-right mr-2'></i>Ir a Facturación (Debería mostrar modal automáticamente)";
        echo "</a>";
        echo "</div>";
        
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollback();
        }
        echo "<div class='alert alert-danger'>";
        echo "<h4><i class='fas fa-exclamation-triangle mr-2'></i>Error en la prueba</h4>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        echo "</div>";
    }
} else {
    echo "<div class='card'>";
    echo "<div class='card-body'>";
    echo "<h5>¿Qué hace esta prueba?</h5>";
    echo "<ul>";
    echo "<li>Simula el registro de un pago real en el sistema</li>";
    echo "<li>Establece las mismas variables de sesión que el sistema real</li>";
    echo "<li>Te redirige a facturación donde debería aparecer el modal automáticamente</li>";
    echo "</ul>";
    
    echo "<form method='POST'>";
    echo "<button type='submit' name='test_pago' class='btn btn-primary btn-lg'>";
    echo "<i class='fas fa-play mr-2'></i>Ejecutar Prueba de Pago Completo";
    echo "</button>";
    echo "</form>";
    echo "</div>";
    echo "</div>";
}

echo "<div class='mt-4'>";
echo "<a href='facturacion.php' class='btn btn-secondary'>";
echo "<i class='fas fa-arrow-left mr-2'></i>Volver a Facturación";
echo "</a>";
echo "</div>";

echo "</div>";
echo "</body></html>";
?>
