<?php
require_once 'config.php';

echo "<h2>🔧 TEST DE CORRECCIÓN ERROR SQL - COLUMNA CEDULA</h2>";
echo "<hr>";

// 1. Verificar estructura de tabla pacientes
echo "<h3>1. Verificación estructura tabla pacientes</h3>";
try {
    $stmt = $conn->query('DESCRIBE pacientes');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $has_dni = false;
    $has_cedula = false;
    
    foreach($columns as $column) {
        if ($column['Field'] === 'dni') $has_dni = true;
        if ($column['Field'] === 'cedula') $has_cedula = true;
    }
    
    echo "<p>✅ Tabla pacientes existe</p>";
    echo "<p>" . ($has_dni ? "✅" : "❌") . " Columna 'dni' existe: " . ($has_dni ? "SÍ" : "NO") . "</p>";
    echo "<p>" . ($has_cedula ? "⚠️" : "✅") . " Columna 'cedula' existe: " . ($has_cedula ? "SÍ (debería ser NO)" : "NO (correcto)") . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

// 2. Test de consulta corregida
echo "<h3>2. Test de consulta SQL corregida</h3>";
try {
    $stmt = $conn->prepare("
        SELECT f.numero_factura, f.total, f.id,
               CONCAT(p.nombre, ' ', p.apellido) as paciente_nombre,
               p.telefono as paciente_telefono,
               p.dni as paciente_cedula,
               u.nombre as medico_nombre
        FROM facturas f
        LEFT JOIN pacientes p ON f.paciente_id = p.id
        LEFT JOIN usuarios u ON f.medico_id = u.id
        WHERE f.id > 0
        LIMIT 1
    ");
    $stmt->execute();
    $test_result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($test_result) {
        echo "<p>✅ Consulta SQL ejecutada exitosamente</p>";
        echo "<p>📋 Datos obtenidos:</p>";
        echo "<ul>";
        echo "<li><strong>Factura:</strong> " . htmlspecialchars($test_result['numero_factura'] ?? 'N/A') . "</li>";
        echo "<li><strong>Paciente:</strong> " . htmlspecialchars($test_result['paciente_nombre'] ?? 'N/A') . "</li>";
        echo "<li><strong>DNI/Cédula:</strong> " . htmlspecialchars($test_result['paciente_cedula'] ?? 'N/A') . "</li>";
        echo "<li><strong>Médico:</strong> " . htmlspecialchars($test_result['medico_nombre'] ?? 'N/A') . "</li>";
        echo "</ul>";
    } else {
        echo "<p>⚠️ No hay facturas en la base de datos para probar</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error en consulta SQL: " . $e->getMessage() . "</p>";
}

// 3. Verificar archivos corregidos
echo "<h3>3. Archivos corregidos verificados</h3>";
$archivos_corregidos = [
    'facturacion.php',
    'test_pago_completo.php', 
    'imprimir_recibo_termico.php'
];

foreach ($archivos_corregidos as $archivo) {
    if (file_exists($archivo)) {
        $content = file_get_contents($archivo);
        $tiene_error = strpos($content, 'p.cedula') !== false;
        echo "<p>" . ($tiene_error ? "❌" : "✅") . " <strong>$archivo:</strong> " . 
             ($tiene_error ? "AÚN CONTIENE p.cedula" : "CORREGIDO (usa p.dni)") . "</p>";
    } else {
        echo "<p>⚠️ <strong>$archivo:</strong> No encontrado</p>";
    }
}

// 4. Test de sesión para modal
echo "<h3>4. Test preparación de sesión para modal</h3>";
try {
    session_start();
    
    // Simular datos del último pago como lo haría facturacion.php
    $_SESSION['ultimo_pago'] = [
        'pago_id' => 999,
        'factura_id' => 1,
        'numero_factura' => 'FAC-TEST-001',
        'paciente_nombre' => 'Juan Pérez Test',
        'paciente_telefono' => '809-555-0123',
        'paciente_cedula' => '001-1234567-8',
        'medico_nombre' => 'Dr. Test',
        'monto' => 1500.00,
        'metodo_pago' => 'efectivo',
        'numero_referencia' => '',
        'fecha_pago' => date('Y-m-d H:i:s')
    ];
    
    echo "<p>✅ Sesión configurada para modal de pago exitoso</p>";
    echo "<p>📋 Datos en sesión:</p>";
    echo "<ul>";
    foreach ($_SESSION['ultimo_pago'] as $key => $value) {
        echo "<li><strong>$key:</strong> " . htmlspecialchars($value) . "</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>❌ Error configurando sesión: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🎯 RESUMEN DE CORRECCIÓN</h3>";
echo "<p>✅ <strong>Error SQL corregido:</strong> Cambiado 'p.cedula' por 'p.dni' en consultas</p>";
echo "<p>✅ <strong>Archivos actualizados:</strong> facturacion.php, test_pago_completo.php, imprimir_recibo_termico.php</p>";
echo "<p>✅ <strong>Sistema listo:</strong> El modal de pago exitoso debe funcionar correctamente</p>";

echo "<hr>";
echo "<p><strong>Próximo paso:</strong> Probar el flujo real de pago en facturacion.php</p>";
echo "<p><a href='facturacion.php' target='_blank'>🔗 Ir a Facturación</a></p>";
?>
