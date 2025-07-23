<?php
session_start();
require_once 'config.php';

echo "=== DIAGNÓSTICO DEL SISTEMA DE RECIBOS ===\n\n";

try {
    // 1. Verificar si hay datos de último pago en sesión
    echo "1. Verificando datos de sesión:\n";
    if (isset($_SESSION['ultimo_pago'])) {
        echo "   ✓ Datos de último pago encontrados en sesión\n";
        foreach ($_SESSION['ultimo_pago'] as $key => $value) {
            echo "   - $key: " . (is_string($value) ? $value : var_export($value, true)) . "\n";
        }
    } else {
        echo "   ✗ No hay datos de último pago en sesión\n";
    }
    
    // 2. Verificar que exista una factura para probar
    echo "\n2. Verificando facturas disponibles:\n";
    $stmt = $conn->query("SELECT id, numero_factura, total, estado FROM facturas ORDER BY id DESC LIMIT 3");
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($facturas)) {
        echo "   ✗ No hay facturas en el sistema\n";
    } else {
        echo "   ✓ Facturas disponibles:\n";
        foreach ($facturas as $factura) {
            echo "   - ID: {$factura['id']}, Número: {$factura['numero_factura']}, Total: \${$factura['total']}, Estado: {$factura['estado']}\n";
        }
    }
    
    // 3. Verificar archivos necesarios
    echo "\n3. Verificando archivos del sistema:\n";
    $archivos_necesarios = [
        'facturacion.php' => 'Módulo principal de facturación',
        'imprimir_recibo.php' => 'Generador de recibos',
        'clear_ultimo_pago.php' => 'Limpiador de sesión'
    ];
    
    foreach ($archivos_necesarios as $archivo => $descripcion) {
        if (file_exists($archivo)) {
            echo "   ✓ $archivo - $descripcion\n";
        } else {
            echo "   ✗ $archivo - $descripcion (FALTA)\n";
        }
    }
    
    // 4. Verificar tabla de pagos
    echo "\n4. Verificando tabla de pagos:\n";
    $stmt = $conn->query("SELECT COUNT(*) as total FROM pagos");
    $total_pagos = $stmt->fetchColumn();
    echo "   - Total de pagos registrados: $total_pagos\n";
    
    if ($total_pagos > 0) {
        $stmt = $conn->query("SELECT id, factura_id, monto, metodo_pago, fecha_pago FROM pagos ORDER BY id DESC LIMIT 3");
        $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "   - Últimos pagos:\n";
        foreach ($pagos as $pago) {
            echo "     * ID: {$pago['id']}, Factura: {$pago['factura_id']}, Monto: \${$pago['monto']}, Método: {$pago['metodo_pago']}, Fecha: {$pago['fecha_pago']}\n";
        }
    }
    
    // 5. Simular un pago para probar el proceso
    echo "\n5. Simulando proceso de pago:\n";
    if (!empty($facturas)) {
        $factura_test = $facturas[0];
        echo "   - Usando factura: {$factura_test['numero_factura']} (ID: {$factura_test['id']})\n";
        
        // Simular datos de pago
        $_SESSION['ultimo_pago'] = [
            'pago_id' => 999,
            'factura_id' => $factura_test['id'],
            'numero_factura' => $factura_test['numero_factura'],
            'monto' => 50.00,
            'metodo_pago' => 'efectivo',
            'paciente_nombre' => 'Paciente de Prueba',
            'paciente_cedula' => '123456789',
            'medico_nombre' => 'Dr. Prueba',
            'fecha_factura' => date('Y-m-d'),
            'total_factura' => $factura_test['total']
        ];
        
        echo "   ✓ Datos de pago simulados creados\n";
        echo "   - Número de factura: {$_SESSION['ultimo_pago']['numero_factura']}\n";
        echo "   - Monto: \${$_SESSION['ultimo_pago']['monto']}\n";
        echo "   - Paciente: {$_SESSION['ultimo_pago']['paciente_nombre']}\n";
        
        echo "\n6. Enlaces de prueba:\n";
        echo "   🔗 Facturación: http://localhost/Consultorio2/facturacion.php\n";
        echo "   🔗 Recibo directo: http://localhost/Consultorio2/imprimir_recibo.php\n";
        echo "   📋 El modal de impresión debería aparecer automáticamente en facturación\n";
    }
    
    echo "\n=== DIAGNÓSTICO COMPLETADO ===\n";

} catch (Exception $e) {
    echo "Error durante el diagnóstico: " . $e->getMessage() . "\n";
}
?>
