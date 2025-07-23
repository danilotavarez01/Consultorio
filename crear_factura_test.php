<?php
require_once 'config.php';
require_once 'session_config.php';
session_start();

// Simular login para pruebas
$_SESSION['loggedin'] = true;
$_SESSION['username'] = 'admin';

echo "<!DOCTYPE html>";
echo "<html><head><title>Crear Factura Test</title></head><body>";
echo "<h1>🏥 Crear Factura de Prueba</h1>";

try {
    // Verificar si ya hay pacientes
    $stmt = $conn->query("SELECT COUNT(*) as total FROM pacientes");
    $total_pacientes = $stmt->fetchColumn();
    
    $paciente_id = null;
    
    if ($total_pacientes == 0) {
        echo "<h2>1. Creando Paciente de Prueba...</h2>";
        
        // Crear paciente de prueba
        $stmt = $conn->prepare("
            INSERT INTO pacientes (nombre, apellido, dni, telefono, email, fecha_nacimiento) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'Juan',
            'Pérez Test',
            '12345678-' . rand(1, 9),
            '809-555-' . rand(1000, 9999),
            'juan.test' . rand(1, 999) . '@email.com',
            '1990-01-01'
        ]);
        
        $paciente_id = $conn->lastInsertId();
        echo "✅ Paciente creado con ID: $paciente_id<br>";
    } else {
        // Usar el primer paciente disponible
        $stmt = $conn->query("SELECT id FROM pacientes LIMIT 1");
        $paciente_id = $stmt->fetchColumn();
        echo "<h2>1. Usando Paciente Existente</h2>";
        echo "✅ Paciente ID: $paciente_id<br>";
    }
    
    // Verificar si hay usuarios/médicos
    $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
    $total_usuarios = $stmt->fetchColumn();
    
    $medico_id = null;
    
    if ($total_usuarios == 0) {
        echo "<h2>2. Creando Usuario/Médico de Prueba...</h2>";
        
        // Crear usuario de prueba
        $stmt = $conn->prepare("
            INSERT INTO usuarios (username, password, nombre, email, rol) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'dr.test' . rand(1, 999),
            password_hash('123456', PASSWORD_DEFAULT),
            'Dr. Test',
            'dr.test' . rand(1, 999) . '@clinica.com',
            'medico'
        ]);
        
        $medico_id = $conn->lastInsertId();
        echo "✅ Médico creado con ID: $medico_id<br>";
    } else {
        // Usar el primer usuario disponible
        $stmt = $conn->query("SELECT id FROM usuarios LIMIT 1");
        $medico_id = $stmt->fetchColumn();
        echo "<h2>2. Usando Médico Existente</h2>";
        echo "✅ Médico ID: $medico_id<br>";
    }
    
    echo "<h2>3. Creando Factura de Prueba...</h2>";
    
    // Crear factura de prueba
    $numero_factura = 'F-TEST-' . date('Ymd-His') . '-' . rand(100, 999);
    $total = rand(50, 500) + (rand(0, 99) / 100); // Entre 50.00 y 500.99
    
    $stmt = $conn->prepare("
        INSERT INTO facturas (numero_factura, paciente_id, medico_id, fecha_factura, fecha_vencimiento, subtotal, total, estado) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $fecha_factura = date('Y-m-d');
    $fecha_vencimiento = date('Y-m-d', strtotime('+30 days'));
    
    $stmt->execute([
        $numero_factura,
        $paciente_id,
        $medico_id,
        $fecha_factura,
        $fecha_vencimiento,
        $total,
        $total,
        'pendiente'
    ]);
    
    $factura_id = $conn->lastInsertId();
    
    echo "✅ Factura creada exitosamente:<br>";
    echo "• ID: $factura_id<br>";
    echo "• Número: $numero_factura<br>";
    echo "• Total: $" . number_format($total, 2) . "<br>";
    echo "• Estado: pendiente<br>";
    
    echo "<h2>4. Verificando Funcionalidad de Pagos</h2>";
    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ <strong>¡Factura lista para recibir pagos!</strong><br>";
    echo "Ahora puede ir a la página de facturación y verá el botón de 'Agregar Pago' (💰) disponible.";
    echo "</div>";
    
    // Crear también un procedimiento de ejemplo si no existe
    echo "<h2>5. Verificando Procedimientos...</h2>";
    $stmt = $conn->query("SELECT COUNT(*) as total FROM procedimientos");
    $total_procedimientos = $stmt->fetchColumn();
    
    if ($total_procedimientos == 0) {
        echo "Creando procedimientos de ejemplo...<br>";
        
        $procedimientos_ejemplo = [
            ['CONS001', 'Consulta General', 50.00],
            ['LIMP001', 'Limpieza Dental', 75.00],
            ['RADI001', 'Radiografía Panorámica', 100.00],
            ['EXTR001', 'Extracción Simple', 150.00],
            ['ENDO001', 'Endodoncia', 300.00]
        ];
        
        foreach ($procedimientos_ejemplo as $proc) {
            $stmt = $conn->prepare("
                INSERT INTO procedimientos (codigo, nombre, precio_venta, activo) 
                VALUES (?, ?, ?, 1)
            ");
            $stmt->execute($proc);
        }
        
        echo "✅ " . count($procedimientos_ejemplo) . " procedimientos de ejemplo creados<br>";
    } else {
        echo "✅ Ya existen $total_procedimientos procedimientos<br>";
    }
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; margin: 10px 0;'>";
    echo "❌ <strong>Error:</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "<h2>6. Siguiente Paso</h2>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='facturacion.php' style='background: #007bff; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-size: 16px; margin-right: 10px;'>🧾 Ir a Facturación</a>";
echo "<a href='test_facturas_debug.php' style='background: #6c757d; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-size: 16px;'>🔍 Ver Debug</a>";
echo "</div>";

echo "<p><small>Si va a facturación ahora, debería ver la nueva factura con un botón verde (💰) para agregar pagos.</small></p>";

echo "</body></html>";
?>
