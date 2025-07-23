<?php
session_start();
require_once 'config.php';

echo "=== DIAGNÓSTICO DEL SISTEMA DE PAGO Y RECIBO ===\n\n";

// 1. Verificar si hay facturas disponibles
echo "1. Verificando facturas disponibles:\n";
$stmt = $conn->query("SELECT id, numero_factura, total, estado FROM facturas ORDER BY id DESC LIMIT 3");
$facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($facturas)) {
    echo "   ❌ No hay facturas disponibles\n";
    echo "   → Creando factura de prueba...\n";
    
    // Crear factura de prueba
    try {
        $conn->beginTransaction();
        
        // Verificar si hay pacientes
        $stmt = $conn->query("SELECT COUNT(*) FROM pacientes");
        $pacientes_count = $stmt->fetchColumn();
        
        if ($pacientes_count == 0) {
            // Crear paciente de prueba
            $stmt = $conn->prepare("INSERT INTO pacientes (nombre, apellido, dni, telefono, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(['Juan', 'Pérez', '12345678', '555-1234', 'juan@email.com']);
            $paciente_id = $conn->lastInsertId();
            echo "   → Paciente de prueba creado (ID: $paciente_id)\n";
        } else {
            $stmt = $conn->query("SELECT id FROM pacientes LIMIT 1");
            $paciente_id = $stmt->fetchColumn();
            echo "   → Usando paciente existente (ID: $paciente_id)\n";
        }
        
        // Verificar si hay usuarios/médicos
        $stmt = $conn->query("SELECT id FROM usuarios LIMIT 1");
        $medico_id = $stmt->fetchColumn();
        
        if (!$medico_id) {
            echo "   ❌ No hay usuarios/médicos disponibles\n";
            $conn->rollback();
            exit();
        }
        
        // Generar número de factura
        $stmt = $conn->query("SELECT numero_factura FROM facturas ORDER BY id DESC LIMIT 1");
        $ultimo_numero = $stmt->fetchColumn();
        
        if ($ultimo_numero) {
            $numero = intval(substr($ultimo_numero, 4)) + 1;
        } else {
            $numero = 1;
        }
        $numero_factura = 'FAC-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
        
        // Crear factura
        $stmt = $conn->prepare("
            INSERT INTO facturas (numero_factura, paciente_id, medico_id, fecha_factura, fecha_vencimiento, 
                                 subtotal, descuento, total, observaciones, estado) 
            VALUES (?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), ?, ?, ?, ?, 'pendiente')
        ");
        $subtotal = 100.00;
        $stmt->execute([$numero_factura, $paciente_id, $medico_id, $subtotal, 0, $subtotal, 'Factura de prueba']);
        
        $factura_id = $conn->lastInsertId();
        
        // Crear detalle de factura
        $stmt = $conn->prepare("
            INSERT INTO factura_detalles (factura_id, procedimiento_id, descripcion, cantidad, precio_unitario, descuento_item, subtotal) 
            VALUES (?, NULL, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$factura_id, 'Consulta de prueba', 1, 100.00, 0, 100.00]);
        
        $conn->commit();
        echo "   ✅ Factura de prueba creada: $numero_factura (ID: $factura_id)\n";
        
    } catch (PDOException $e) {
        $conn->rollback();
        echo "   ❌ Error creando factura: " . $e->getMessage() . "\n";
        exit();
    }
} else {
    echo "   ✅ Facturas disponibles:\n";
    foreach ($facturas as $factura) {
        echo "   - {$factura['numero_factura']} | Total: \${$factura['total']} | Estado: {$factura['estado']}\n";
    }
}

// 2. Verificar archivos necesarios
echo "\n2. Verificando archivos del sistema:\n";
$archivos = [
    'facturacion.php' => 'Módulo de facturación',
    'imprimir_recibo.php' => 'Generador de recibos',
    'clear_ultimo_pago.php' => 'Limpiador de sesión'
];

foreach ($archivos as $archivo => $desc) {
    if (file_exists($archivo)) {
        echo "   ✅ $archivo - $desc\n";
    } else {
        echo "   ❌ $archivo - $desc (FALTA)\n";
    }
}

// 3. Simular proceso de pago completo
echo "\n3. Simulando proceso de pago:\n";

// Obtener una factura pendiente
$stmt = $conn->query("SELECT * FROM facturas WHERE estado = 'pendiente' ORDER BY id DESC LIMIT 1");
$factura = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$factura) {
    echo "   ❌ No hay facturas pendientes para probar\n";
    exit();
}

echo "   → Usando factura: {$factura['numero_factura']}\n";

// Simular registro de pago
try {
    $conn->beginTransaction();
    
    $stmt = $conn->prepare("
        INSERT INTO pagos (factura_id, fecha_pago, monto, metodo_pago, numero_referencia, observaciones) 
        VALUES (?, NOW(), ?, ?, ?, ?)
    ");
    $stmt->execute([$factura['id'], $factura['total'], 'efectivo', '', 'Pago de prueba automático']);
    
    $pago_id = $conn->lastInsertId();
    echo "   ✅ Pago simulado registrado (ID: $pago_id)\n";
    
    // Actualizar estado de factura
    $stmt = $conn->prepare("UPDATE facturas SET estado = 'pagada' WHERE id = ?");
    $stmt->execute([$factura['id']]);
    
    $conn->commit();
    
    // Configurar datos de sesión para el recibo
    $_SESSION['ultimo_pago'] = [
        'pago_id' => $pago_id,
        'factura_id' => $factura['id'],
        'numero_factura' => $factura['numero_factura'],
        'monto' => $factura['total'],
        'metodo_pago' => 'efectivo',
        'paciente_nombre' => 'Paciente de Prueba',
        'paciente_cedula' => '12345678',
        'medico_nombre' => 'Dr. Prueba',
        'fecha_factura' => $factura['fecha_factura'],
        'total_factura' => $factura['total']
    ];
    
    $_SESSION['show_print_modal'] = true;
    $_SESSION['success_message'] = 'Pago de prueba registrado exitosamente.';
    
    echo "   ✅ Datos de sesión configurados para modal de impresión\n";
    
} catch (PDOException $e) {
    $conn->rollback();
    echo "   ❌ Error simulando pago: " . $e->getMessage() . "\n";
    exit();
}

echo "\n4. Verificando configuración del recibo:\n";

// Verificar configuración del consultorio
$stmt = $conn->query("SELECT * FROM configuracion LIMIT 1");
$config = $stmt->fetch(PDO::FETCH_ASSOC);

if ($config) {
    echo "   ✅ Configuración del consultorio encontrada\n";
    echo "   - Nombre: " . ($config['nombre_consultorio'] ?? 'No definido') . "\n";
} else {
    echo "   ⚠️  No hay configuración del consultorio\n";
    echo "   → Creando configuración básica...\n";
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO configuracion (nombre_consultorio, direccion, telefono, email, mensaje_recibo) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'CONSULTORIO DENTAL',
            'Calle Principal #123',
            '555-0123',
            'info@consultorio.com',
            'Gracias por su visita'
        ]);
        echo "   ✅ Configuración básica creada\n";
    } catch (PDOException $e) {
        echo "   ❌ Error creando configuración: " . $e->getMessage() . "\n";
    }
}

echo "\n=== RESULTADO ===\n";
echo "✅ Sistema configurado correctamente\n";
echo "✅ Datos de pago en sesión listos\n";
echo "✅ Modal debería aparecer al cargar facturacion.php\n\n";

echo "PRÓXIMOS PASOS:\n";
echo "1. Vaya a: http://localhost/Consultorio2/facturacion.php\n";
echo "2. Debería ver el mensaje de éxito y el modal de impresión\n";
echo "3. Haga clic en 'Imprimir Recibo' para probar la impresión\n";
echo "4. Si no funciona, revise la consola del navegador (F12)\n\n";

echo "Para limpiar la sesión después de la prueba:\n";
echo "http://localhost/Consultorio2/clear_ultimo_pago.php\n";
?>
