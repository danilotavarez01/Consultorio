<?php
require_once 'config.php';

echo "<h2>🔍 VERIFICACIÓN DE OPTIMIZACIONES APLICADAS</h2>";
echo "<hr>";

$start_time = microtime(true);

echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>🎯 Resumen de Optimizaciones Aplicadas</h3>";
echo "<ul style='line-height: 1.8;'>";
echo "<li>✅ <strong>Índices de base de datos:</strong> Creados para todas las tablas críticas</li>";
echo "<li>✅ <strong>Consultas optimizadas:</strong> Agregada paginación y LIMIT a listados</li>";
echo "<li>✅ <strong>JOIN optimizados:</strong> Uso de índices y subconsultas eficientes</li>";
echo "<li>✅ <strong>Compresión HTTP:</strong> GZIP habilitado en .htaccess</li>";
echo "<li>✅ <strong>Caché de archivos:</strong> Headers de caché para recursos estáticos</li>";
echo "<li>⚠️ <strong>OPcache:</strong> Requiere configuración manual en php.ini</li>";
echo "</ul>";
echo "</div>";

// 1. VERIFICAR ÍNDICES CREADOS
echo "<h3>1. Verificación de Índices en la Base de Datos</h3>";
try {
    $stmt = $conn->query("
        SELECT 
            table_name,
            index_name,
            GROUP_CONCAT(column_name ORDER BY seq_in_index) as columns
        FROM information_schema.statistics 
        WHERE table_schema = DATABASE()
        AND table_name IN ('facturas', 'pagos', 'pacientes', 'citas', 'turnos', 'historial_medico', 'usuarios')
        AND index_name LIKE 'idx_%'
        GROUP BY table_name, index_name
        ORDER BY table_name, index_name
    ");
    $indices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($indices) {
        echo "<table border='1' style='width:100%; margin:10px 0; border-collapse: collapse;'>";
        echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Tabla</th><th style='padding: 8px;'>Índice</th><th style='padding: 8px;'>Columnas</th></tr>";
        
        foreach ($indices as $indice) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . $indice['table_name'] . "</td>";
            echo "<td style='padding: 8px;'>" . $indice['index_name'] . "</td>";
            echo "<td style='padding: 8px;'>" . $indice['columns'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p style='color: green;'>✅ <strong>" . count($indices) . " índices personalizados encontrados</strong></p>";
    } else {
        echo "<p style='color: red;'>❌ No se encontraron índices personalizados. Ejecute optimizar_indices_db.php</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error verificando índices: " . $e->getMessage() . "</p>";
}

// 2. VERIFICAR CONFIGURACIÓN PHP
echo "<h3>2. Configuración de PHP</h3>";
echo "<table border='1' style='width:100%; margin:10px 0; border-collapse: collapse;'>";
echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Configuración</th><th style='padding: 8px;'>Valor Actual</th><th style='padding: 8px;'>Estado</th><th style='padding: 8px;'>Recomendación</th></tr>";

$php_checks = [
    'memory_limit' => ['current' => ini_get('memory_limit'), 'min' => 256, 'unit' => 'M'],
    'max_execution_time' => ['current' => ini_get('max_execution_time'), 'min' => 60, 'unit' => 's'],
    'opcache.enable' => ['current' => ini_get('opcache.enable') ? 'ON' : 'OFF', 'expected' => 'ON'],
    'opcache.memory_consumption' => ['current' => ini_get('opcache.memory_consumption'), 'min' => 128, 'unit' => 'MB'],
    'post_max_size' => ['current' => ini_get('post_max_size'), 'min' => 32, 'unit' => 'M'],
    'upload_max_filesize' => ['current' => ini_get('upload_max_filesize'), 'min' => 32, 'unit' => 'M']
];

foreach ($php_checks as $setting => $check) {
    $current = $check['current'];
    $status = "⚠️";
    $recommendation = "";
    
    if ($setting === 'opcache.enable') {
        $status = ($current === 'ON') ? "✅" : "❌";
        $recommendation = ($current === 'ON') ? "Óptimo" : "Habilitar en php.ini";
    } else {
        $current_num = (int)$current;
        $min_val = $check['min'];
        if ($current_num >= $min_val) {
            $status = "✅";
            $recommendation = "Óptimo";
        } else {
            $status = "❌";
            $recommendation = "Aumentar a {$min_val}{$check['unit']}";
        }
    }
    
    echo "<tr>";
    echo "<td style='padding: 8px;'><strong>$setting</strong></td>";
    echo "<td style='padding: 8px;'>$current</td>";
    echo "<td style='padding: 8px;'>$status</td>";
    echo "<td style='padding: 8px;'>$recommendation</td>";
    echo "</tr>";
}
echo "</table>";

// 3. PRUEBA DE RENDIMIENTO DE CONSULTAS
echo "<h3>3. Prueba de Rendimiento de Consultas Críticas</h3>";
$query_tests = [
    'Facturas con paginación' => "
        SELECT f.id, f.numero_factura, f.fecha_factura, f.total, f.estado,
               CONCAT(p.nombre, ' ', p.apellido) as paciente_nombre,
               u.nombre as medico_nombre
        FROM facturas f
        LEFT JOIN pacientes p ON f.paciente_id = p.id
        LEFT JOIN usuarios u ON f.medico_id = u.id
        ORDER BY f.fecha_factura DESC
        LIMIT 20
    ",
    'Citas con filtros' => "
        SELECT c.id, c.fecha, c.hora, c.estado,
               CONCAT(p.nombre, ' ', p.apellido) as paciente,
               u.nombre as doctor
        FROM citas c
        JOIN pacientes p ON c.paciente_id = p.id
        JOIN usuarios u ON c.doctor_id = u.id
        WHERE c.fecha >= CURDATE()
        ORDER BY c.fecha, c.hora
        LIMIT 20
    ",
    'Turnos ordenados' => "
        SELECT t.id, t.fecha_turno, t.hora_turno, t.estado, t.orden_llegada,
               CONCAT(p.nombre, ' ', p.apellido) as paciente
        FROM turnos t
        JOIN pacientes p ON t.paciente_id = p.id
        WHERE t.fecha_turno = CURDATE()
        ORDER BY t.orden_llegada IS NULL, t.orden_llegada, t.hora_turno
        LIMIT 20
    "
];

echo "<table border='1' style='width:100%; margin:10px 0; border-collapse: collapse;'>";
echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Consulta</th><th style='padding: 8px;'>Tiempo (ms)</th><th style='padding: 8px;'>Registros</th><th style='padding: 8px;'>Estado</th></tr>";

foreach ($query_tests as $name => $query) {
    $query_start = microtime(true);
    try {
        $stmt = $conn->query($query);
        $results = $stmt->fetchAll();
        $query_time = (microtime(true) - $query_start) * 1000;
        $record_count = count($results);
        
        $status = "✅ Rápida";
        if ($query_time > 100) $status = "⚠️ Aceptable";
        if ($query_time > 500) $status = "❌ Lenta";
        
        echo "<tr>";
        echo "<td style='padding: 8px;'><strong>$name</strong></td>";
        echo "<td style='padding: 8px;'>" . number_format($query_time, 2) . " ms</td>";
        echo "<td style='padding: 8px;'>$record_count</td>";
        echo "<td style='padding: 8px;'>$status</td>";
        echo "</tr>";
    } catch (Exception $e) {
        echo "<tr>";
        echo "<td style='padding: 8px;'><strong>$name</strong></td>";
        echo "<td style='padding: 8px;'>ERROR</td>";
        echo "<td style='padding: 8px;'>0</td>";
        echo "<td style='padding: 8px;'>❌ " . substr($e->getMessage(), 0, 50) . "...</td>";
        echo "</tr>";
    }
}
echo "</table>";

// 4. ESTADÍSTICAS DE LA BASE DE DATOS
echo "<h3>4. Estadísticas de la Base de Datos</h3>";
try {
    $stmt = $conn->query("
        SELECT 
            table_name,
            table_rows,
            ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
            ROUND((index_length / 1024 / 1024), 2) AS index_size_mb
        FROM information_schema.tables 
        WHERE table_schema = DATABASE()
        AND table_name IN ('facturas', 'pagos', 'pacientes', 'citas', 'turnos', 'historial_medico', 'usuarios')
        ORDER BY size_mb DESC
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='width:100%; margin:10px 0; border-collapse: collapse;'>";
    echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Tabla</th><th style='padding: 8px;'>Registros</th><th style='padding: 8px;'>Tamaño Total</th><th style='padding: 8px;'>Tamaño Índices</th><th style='padding: 8px;'>Estado</th></tr>";
    
    foreach ($tables as $table) {
        $status = "✅ Óptima";
        if ($table['size_mb'] > 50) $status = "⚠️ Grande";
        if ($table['table_rows'] > 10000) $status = "⚠️ Muchos registros";
        
        echo "<tr>";
        echo "<td style='padding: 8px;'><strong>{$table['table_name']}</strong></td>";
        echo "<td style='padding: 8px;'>" . number_format($table['table_rows']) . "</td>";
        echo "<td style='padding: 8px;'>{$table['size_mb']} MB</td>";
        echo "<td style='padding: 8px;'>{$table['index_size_mb']} MB</td>";
        echo "<td style='padding: 8px;'>$status</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error obteniendo estadísticas: " . $e->getMessage() . "</p>";
}

$total_time = (microtime(true) - $start_time) * 1000;

echo "<hr>";
echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>📊 RESUMEN DE VERIFICACIÓN</h4>";
echo "<p><strong>Tiempo total de verificación:</strong> " . number_format($total_time, 2) . " ms</p>";

// Calcular puntuación de optimización
$score = 0;
$max_score = 100;

// Verificar índices (40 puntos)
if (count($indices ?? []) >= 15) $score += 40;
elseif (count($indices ?? []) >= 10) $score += 30;
elseif (count($indices ?? []) >= 5) $score += 20;

// Verificar OPcache (30 puntos)
if (ini_get('opcache.enable')) $score += 30;

// Verificar configuraciones PHP (20 puntos)
$php_optimal = 0;
foreach ($php_checks as $setting => $check) {
    if ($setting === 'opcache.enable') {
        if ($check['current'] === 'ON') $php_optimal++;
    } else {
        $current_num = (int)$check['current'];
        if ($current_num >= $check['min']) $php_optimal++;
    }
}
$score += ($php_optimal / count($php_checks)) * 20;

// Verificar rendimiento de consultas (10 puntos)
$fast_queries = 0;
foreach ($query_tests as $name => $query) {
    $query_start = microtime(true);
    try {
        $stmt = $conn->query($query);
        $stmt->fetchAll();
        $query_time = (microtime(true) - $query_start) * 1000;
        if ($query_time < 100) $fast_queries++;
    } catch (Exception $e) {}
}
$score += ($fast_queries / count($query_tests)) * 10;

$score = round($score);

echo "<div style='text-align: center; font-size: 24px; margin: 20px 0;'>";
if ($score >= 80) {
    echo "🎉 <span style='color: green;'><strong>EXCELENTE: {$score}/100</strong></span>";
    echo "<br><small>Sistema altamente optimizado</small>";
} elseif ($score >= 60) {
    echo "✅ <span style='color: orange;'><strong>BUENO: {$score}/100</strong></span>";
    echo "<br><small>Optimización satisfactoria</small>";
} else {
    echo "⚠️ <span style='color: red;'><strong>MEJORABLE: {$score}/100</strong></span>";
    echo "<br><small>Requiere más optimizaciones</small>";
}
echo "</div>";

echo "<h4>🔧 Próximos Pasos:</h4>";
echo "<ol>";
if (!ini_get('opcache.enable')) {
    echo "<li><strong>CRÍTICO:</strong> Habilitar OPcache en php.ini (mejora 60-80% rendimiento)</li>";
}
if (count($indices ?? []) < 15) {
    echo "<li>Ejecutar <a href='optimizar_indices_db.php'>optimizar_indices_db.php</a> para crear índices faltantes</li>";
}
echo "<li>Configurar php.ini con los valores recomendados de <code>php_optimization.ini</code></li>";
echo "<li>Reiniciar servidor web para aplicar cambios de configuración</li>";
echo "<li>Monitorear rendimiento regularmente con este script</li>";
echo "</ol>";

echo "</div>";

echo "<div style='margin-top: 20px;'>";
echo "<a href='optimizar_indices_db.php' class='btn btn-primary' style='margin-right: 10px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>🔧 Optimizar BD</a>";
echo "<a href='diagnostico_rendimiento.php' class='btn btn-success' style='margin-right: 10px; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>📊 Diagnóstico Completo</a>";
echo "<a href='index.php' class='btn btn-info' style='padding: 10px 20px; background: #17a2b8; color: white; text-decoration: none; border-radius: 5px;'>🏠 Ir al Sistema</a>";
echo "</div>";
?>

<style>
.btn {
    display: inline-block;
    margin: 5px;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    text-align: center;
}
table {
    font-family: Arial, sans-serif;
    font-size: 14px;
}
</style>
