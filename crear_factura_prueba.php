<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';
require_once 'permissions.php';

// Crear una factura de prueba para asegurar que hay algo para pagar
try {
    // Verificar si ya existe una factura de prueba
    $stmt = $conn->query("SELECT COUNT(*) FROM facturas WHERE numero_factura LIKE 'FAC-TEST-%'");
    $facturas_test = $stmt->fetchColumn();
    
    if ($facturas_test == 0) {
        echo "<h3>Creando factura de prueba...</h3>";
        
        // Obtener un paciente (crear uno si no existe)
        $stmt = $conn->query("SELECT id FROM pacientes LIMIT 1");
        $paciente_id = $stmt->fetchColumn();
        
        if (!$paciente_id) {
            // Crear paciente de prueba
            $stmt = $conn->prepare("INSERT INTO pacientes (nombre, apellido, dni, telefono, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(['Juan', 'Pérez Test', '001-1234567-8', '809-555-0123', 'juan.test@email.com']);
            $paciente_id = $conn->lastInsertId();
            echo "<p>✅ Paciente de prueba creado (ID: $paciente_id)</p>";
        }
        
        // Crear factura de prueba
        $numero_factura = 'FAC-TEST-' . date('Ymd-His');
        $stmt = $conn->prepare("
            INSERT INTO facturas (numero_factura, paciente_id, medico_id, fecha_factura, fecha_vencimiento, 
                                 subtotal, descuento, total, observaciones, estado) 
            VALUES (?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), ?, 0, ?, ?, 'pendiente')
        ");
        
        $total = 1500.00;
        $stmt->execute([$numero_factura, $paciente_id, $_SESSION['id'], $total, $total, 'Factura de prueba para modal de pago']);
        $factura_id = $conn->lastInsertId();
        
        // Agregar detalle
        $stmt = $conn->prepare("
            INSERT INTO factura_detalles (factura_id, descripcion, cantidad, precio_unitario, descuento_item, subtotal) 
            VALUES (?, ?, 1, ?, 0, ?)
        ");
        $stmt->execute([$factura_id, 'Consulta de prueba para modal', $total, $total]);
        
        echo "<p>✅ Factura de prueba creada: <strong>$numero_factura</strong> (ID: $factura_id)</p>";
        echo "<p>💰 Monto: $" . number_format($total, 2) . "</p>";
        echo "<p>📊 Estado: Pendiente</p>";
    } else {
        echo "<p>✅ Ya existe factura(s) de prueba en el sistema</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error creando factura de prueba: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🎯 Instrucciones para Probar el Modal</h3>";
echo "<ol>";
echo "<li><strong>Ve a:</strong> <a href='facturacion.php' target='_blank'>facturacion.php</a></li>";
echo "<li><strong>Busca</strong> la factura con número que empiece por 'FAC-TEST-'</li>";
echo "<li><strong>En la columna 'Acciones'</strong>, haz clic en el botón verde con ícono 💲</li>";
echo "<li><strong>Se abrirá</strong> el modal 'Agregar Pago'</li>";
echo "<li><strong>Completa los datos:</strong>";
echo "<ul>";
echo "<li>Monto: Cualquier cantidad (ej: 500.00)</li>";
echo "<li>Método: Efectivo</li>";
echo "<li>Observaciones: 'Pago de prueba'</li>";
echo "</ul>";
echo "</li>";
echo "<li><strong>Haz clic en 'Registrar Pago'</strong></li>";
echo "<li><strong>Debe aparecer automáticamente</strong> el modal de pago exitoso 🎉</li>";
echo "</ol>";

echo "<hr>";
echo "<div style='background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3;'>";
echo "<h4>💡 Si el modal no aparece, revisa:</h4>";
echo "<ul>";
echo "<li>Consola del navegador (F12) para errores de JavaScript</li>";
echo "<li>Que tengas permisos para crear facturas</li>";
echo "<li>Que la factura esté en estado 'Pendiente'</li>";
echo "<li>Que no hayas deshabilitado JavaScript en el navegador</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><strong>🔧 Scripts de Diagnóstico Adicionales:</strong></p>";
echo "<ul>";
echo "<li><a href='diagnostico_modal.php'>📋 Diagnóstico Completo del Sistema</a></li>";
echo "<li><a href='test_correccion_sql.php'>🔍 Verificar Corrección SQL</a></li>";
echo "<li><a href='debug_modal_pago_completo.php'>🧪 Debug del Modal</a></li>";
echo "</ul>";
?>
