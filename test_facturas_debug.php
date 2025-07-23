<?php
require_once 'config.php';
require_once 'session_config.php';
session_start();

// Simular login para pruebas
$_SESSION['loggedin'] = true;
$_SESSION['username'] = 'admin';

echo "<!DOCTYPE html>";
echo "<html><head><title>Test Facturas</title></head><body>";
echo "<h1>🧪 Test de Facturas y Pagos</h1>";

try {
    // Test 1: Verificar conexión a BD
    echo "<h2>1. Conexión a Base de Datos</h2>";
    if ($conn) {
        echo "✅ Conexión exitosa<br>";
    } else {
        echo "❌ Sin conexión<br>";
    }
    
    // Test 2: Contar facturas
    echo "<h2>2. Facturas en la Base de Datos</h2>";
    $stmt = $conn->query("SELECT COUNT(*) as total FROM facturas");
    $total_facturas = $stmt->fetchColumn();
    echo "Total de facturas: <strong>$total_facturas</strong><br>";
    
    if ($total_facturas == 0) {
        echo "<div style='background: #fff3cd; padding: 10px; border: 1px solid #ffeaa7; margin: 10px 0;'>";
        echo "⚠️ <strong>No hay facturas en la base de datos.</strong><br>";
        echo "Esto explica por qué no aparecen botones para agregar pagos.<br>";
        echo "<a href='crear_factura_test.php' style='background: #007bff; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;'>Crear Factura de Prueba</a>";
        echo "</div>";
    }
    
    // Test 3: Mostrar facturas existentes
    if ($total_facturas > 0) {
        echo "<h2>3. Últimas Facturas</h2>";
        $stmt = $conn->query("
            SELECT f.*, 
                   CONCAT(p.nombre, ' ', p.apellido) as paciente_nombre,
                   u.nombre as medico_nombre,
                   COALESCE(SUM(pg.monto), 0) as total_pagado
            FROM facturas f
            LEFT JOIN pacientes p ON f.paciente_id = p.id
            LEFT JOIN usuarios u ON f.medico_id = u.id  
            LEFT JOIN pagos pg ON f.id = pg.factura_id
            GROUP BY f.id
            ORDER BY f.fecha_factura DESC, f.id DESC
            LIMIT 5
        ");
        $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th>ID</th><th>Número</th><th>Paciente</th><th>Total</th><th>Pagado</th><th>Estado</th><th>Acciones</th>";
        echo "</tr>";
        
        foreach ($facturas as $factura) {
            $pendiente = $factura['total'] - $factura['total_pagado'];
            $puede_pagar = ($factura['estado'] === 'pendiente' && $pendiente > 0);
            
            echo "<tr>";
            echo "<td>{$factura['id']}</td>";
            echo "<td>{$factura['numero_factura']}</td>";
            echo "<td>{$factura['paciente_nombre']}</td>";
            echo "<td>\${$factura['total']}</td>";
            echo "<td>\${$factura['total_pagado']}</td>";
            echo "<td>{$factura['estado']}</td>";
            echo "<td>";
            
            if ($puede_pagar) {
                echo "<span style='color: green;'>✅ Puede recibir pagos</span>";
            } else {
                echo "<span style='color: red;'>❌ No puede recibir pagos</span>";
                if ($factura['estado'] !== 'pendiente') {
                    echo " (Estado: {$factura['estado']})";
                }
                if ($pendiente <= 0) {
                    echo " (Ya pagada completamente)";
                }
            }
            
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test 4: Verificar pacientes
    echo "<h2>4. Pacientes Disponibles</h2>";
    $stmt = $conn->query("SELECT COUNT(*) as total FROM pacientes");
    $total_pacientes = $stmt->fetchColumn();
    echo "Total de pacientes: <strong>$total_pacientes</strong><br>";
    
    if ($total_pacientes == 0) {
        echo "<div style='background: #fff3cd; padding: 10px; border: 1px solid #ffeaa7; margin: 10px 0;'>";
        echo "⚠️ <strong>No hay pacientes registrados.</strong><br>";
        echo "Necesita pacientes para poder crear facturas.<br>";
        echo "<a href='pacientes.php' style='background: #28a745; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;'>Ir a Pacientes</a>";
        echo "</div>";
    }
    
    // Test 5: Verificar permisos
    echo "<h2>5. Permisos de Usuario</h2>";
    
    // Simular funciones de permisos
    function hasPermission($perm) {
        return true; // Simular que tiene todos los permisos
    }
    
    function isAdmin() {
        return true; // Simular que es admin
    }
    
    $puede_crear_factura = hasPermission('crear_factura') || isAdmin();
    $puede_editar_factura = hasPermission('editar_factura') || isAdmin();
    
    echo "Puede crear facturas: " . ($puede_crear_factura ? "✅ SÍ" : "❌ NO") . "<br>";
    echo "Puede editar facturas: " . ($puede_editar_factura ? "✅ SÍ" : "❌ NO") . "<br>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; margin: 10px 0;'>";
    echo "❌ <strong>Error de Base de Datos:</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "<h2>6. Acciones Disponibles</h2>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='facturacion.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Ir a Facturación</a>";
echo "<a href='crear_factura_test.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Crear Factura Test</a>";
echo "<a href='index.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Volver al Inicio</a>";
echo "</div>";

echo "</body></html>";
?>
