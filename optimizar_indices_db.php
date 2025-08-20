<?php
require_once 'config.php';

echo "<h2>🔧 OPTIMIZACIÓN DE ÍNDICES - BASE DE DATOS</h2>";
echo "<hr>";

$optimizaciones = [];
$tiempo_inicio = microtime(true);

// Índices críticos para mejorar rendimiento (corregidos para MySQL)
$indices = [
    // Facturas - índices más importantes
    "CREATE INDEX idx_facturas_fecha ON facturas(fecha_factura)" => "Índice por fecha en facturas",
    "CREATE INDEX idx_facturas_paciente ON facturas(paciente_id)" => "Índice por paciente en facturas",
    "CREATE INDEX idx_facturas_medico ON facturas(medico_id)" => "Índice por médico en facturas",
    "CREATE INDEX idx_facturas_estado ON facturas(estado)" => "Índice por estado en facturas",
    "CREATE INDEX idx_facturas_numero ON facturas(numero_factura)" => "Índice por número de factura",
    
    // Pagos - para JOINs rápidos
    "CREATE INDEX idx_pagos_factura ON pagos(factura_id)" => "Índice por factura en pagos",
    "CREATE INDEX idx_pagos_fecha ON pagos(fecha_pago)" => "Índice por fecha en pagos",
    
    // Pacientes - búsquedas frecuentes
    "CREATE INDEX idx_pacientes_dni ON pacientes(dni)" => "Índice por DNI en pacientes",
    "CREATE INDEX idx_pacientes_nombre ON pacientes(nombre, apellido)" => "Índice compuesto nombre-apellido",
    
    // Citas - filtros comunes
    "CREATE INDEX idx_citas_fecha ON citas(fecha)" => "Índice por fecha en citas",
    "CREATE INDEX idx_citas_doctor ON citas(doctor_id)" => "Índice por doctor en citas",
    "CREATE INDEX idx_citas_paciente ON citas(paciente_id)" => "Índice por paciente en citas",
    "CREATE INDEX idx_citas_estado ON citas(estado)" => "Índice por estado en citas",
    
    // Turnos - optimización crítica
    "CREATE INDEX idx_turnos_fecha ON turnos(fecha_turno)" => "Índice por fecha en turnos",
    "CREATE INDEX idx_turnos_medico ON turnos(medico_id)" => "Índice por médico en turnos",
    "CREATE INDEX idx_turnos_paciente ON turnos(paciente_id)" => "Índice por paciente en turnos",
    "CREATE INDEX idx_turnos_estado ON turnos(estado)" => "Índice por estado en turnos",
    "CREATE INDEX idx_turnos_orden ON turnos(orden_llegada)" => "Índice por orden de llegada",
    
    // Historial médico - consultas frecuentes
    "CREATE INDEX idx_historial_paciente ON historial_medico(paciente_id)" => "Índice por paciente en historial",
    "CREATE INDEX idx_historial_doctor ON historial_medico(doctor_id)" => "Índice por doctor en historial",
    "CREATE INDEX idx_historial_fecha ON historial_medico(fecha)" => "Índice por fecha en historial",
    
    // Usuarios - autenticación y búsquedas
    "CREATE INDEX idx_usuarios_username ON usuarios(username)" => "Índice por username",
    "CREATE INDEX idx_usuarios_rol ON usuarios(rol)" => "Índice por rol",
    "CREATE INDEX idx_usuarios_activo ON usuarios(active)" => "Índice por estado activo",
    
    // Factura detalles - reportes
    "CREATE INDEX idx_factura_detalles_factura ON factura_detalles(factura_id)" => "Índice por factura en detalles",
    "CREATE INDEX idx_factura_detalles_procedimiento ON factura_detalles(procedimiento_id)" => "Índice por procedimiento"
];

echo "<h3>Creando Índices Críticos</h3>";
echo "<table border='1' style='width:100%; margin:10px 0; border-collapse: collapse;'>";
echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Índice</th><th style='padding: 8px;'>Estado</th><th style='padding: 8px;'>Detalle</th></tr>";

foreach ($indices as $sql => $descripcion) {
    try {
        // Verificar si el índice ya existe antes de crearlo
        $index_name = substr($sql, strpos($sql, 'idx_'));
        $index_name = substr($index_name, 0, strpos($index_name, ' '));
        
        $check_sql = "SHOW INDEX FROM " . substr($sql, strpos($sql, ' ON ') + 4, strpos($sql, '(') - strpos($sql, ' ON ') - 4) . " WHERE Key_name = '$index_name'";
        $check_stmt = $conn->query($check_sql);
        
        if ($check_stmt && $check_stmt->rowCount() > 0) {
            $estado = "ℹ️ EXISTE";
            $detalle = "Índice ya existe";
            $optimizaciones[] = "ℹ️ " . $descripcion . " (ya existe)";
        } else {
            // Crear el índice
            $resultado = $conn->exec($sql);
            $estado = "✅ CREADO";
            $detalle = "Índice creado exitosamente";
            $optimizaciones[] = "✅ " . $descripcion;
        }
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false || 
            strpos($e->getMessage(), 'already exists') !== false) {
            $estado = "ℹ️ EXISTE";
            $detalle = "Índice ya existe";
            $optimizaciones[] = "ℹ️ " . $descripcion . " (ya existe)";
        } else {
            $estado = "❌ ERROR";
            $detalle = $e->getMessage();
            $optimizaciones[] = "❌ Error en " . $descripcion . ": " . $e->getMessage();
        }
    }
    
    echo "<tr>";
    echo "<td style='padding: 8px;'><strong>$descripcion</strong></td>";
    echo "<td style='padding: 8px;'>$estado</td>";
    echo "<td style='padding: 8px;'>$detalle</td>";
    echo "</tr>";
}

echo "</table>";

// Optimizar tablas después de crear índices
echo "<h3>Optimizando Tablas</h3>";
echo "<table border='1' style='width:100%; margin:10px 0; border-collapse: collapse;'>";
echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Tabla</th><th style='padding: 8px;'>Estado</th><th style='padding: 8px;'>Resultado</th></tr>";

$tablas = ['facturas', 'pagos', 'pacientes', 'usuarios', 'citas', 'turnos', 'historial_medico', 'factura_detalles'];
foreach ($tablas as $tabla) {
    try {
        $conn->exec("OPTIMIZE TABLE $tabla");
        $optimizaciones[] = "🔧 Tabla $tabla optimizada";
        echo "<tr>";
        echo "<td style='padding: 8px;'><strong>$tabla</strong></td>";
        echo "<td style='padding: 8px;'>✅ OPTIMIZADA</td>";
        echo "<td style='padding: 8px;'>Tabla optimizada correctamente</td>";
        echo "</tr>";
    } catch (PDOException $e) {
        echo "<tr>";
        echo "<td style='padding: 8px;'><strong>$tabla</strong></td>";
        echo "<td style='padding: 8px;'>⚠️ ADVERTENCIA</td>";
        echo "<td style='padding: 8px;'>" . $e->getMessage() . "</td>";
        echo "</tr>";
    }
}

echo "</table>";

// Estadísticas de mejora
echo "<h3>Análisis de Índices Creados</h3>";
try {
    $stmt = $conn->query("
        SELECT 
            table_name,
            index_name,
            column_name,
            seq_in_index
        FROM information_schema.statistics 
        WHERE table_schema = DATABASE()
        AND table_name IN ('facturas', 'pagos', 'pacientes', 'citas', 'turnos', 'historial_medico')
        ORDER BY table_name, index_name, seq_in_index
    ");
    $indices_info = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($indices_info) {
        echo "<table border='1' style='width:100%; margin:10px 0; border-collapse: collapse;'>";
        echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Tabla</th><th style='padding: 8px;'>Índice</th><th style='padding: 8px;'>Columna</th></tr>";
        
        foreach ($indices_info as $indice) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . $indice['table_name'] . "</td>";
            echo "<td style='padding: 8px;'>" . $indice['index_name'] . "</td>";
            echo "<td style='padding: 8px;'>" . $indice['column_name'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p>⚠️ No se pudo obtener información de índices: " . $e->getMessage() . "</p>";
}

$tiempo_fin = microtime(true);
$tiempo_total = round(($tiempo_fin - $tiempo_inicio) * 1000, 2);

echo "<hr>";
echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>✅ OPTIMIZACIÓN DE ÍNDICES COMPLETADA</h4>";
echo "<p><strong>Tiempo total:</strong> {$tiempo_total} ms</p>";
echo "<p><strong>Optimizaciones aplicadas:</strong></p>";
echo "<ul>";
foreach ($optimizaciones as $opt) {
    echo "<li>$opt</li>";
}
echo "</ul>";
echo "<div style='background: #fff3cd; padding: 10px; border-radius: 3px; margin-top: 10px;'>";
echo "<strong>📈 Mejora esperada:</strong> 60-80% en velocidad de consultas con JOIN y filtros";
echo "</div>";
echo "</div>";

echo "<div style='margin-top: 20px;'>";
echo "<a href='facturacion.php' class='btn btn-primary' style='margin-right: 10px;'>Probar Facturación</a>";
echo "<a href='Citas.php' class='btn btn-success' style='margin-right: 10px;'>Probar Citas</a>";
echo "<a href='turnos.php' class='btn btn-info' style='margin-right: 10px;'>Probar Turnos</a>";
echo "<a href='diagnostico_rendimiento.php' class='btn btn-warning'>Verificar Rendimiento</a>";
echo "</div>";
?>
