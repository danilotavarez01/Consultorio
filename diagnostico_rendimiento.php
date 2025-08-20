<?php
require_once 'config.php';

echo "<h2>🔍 DIAGNÓSTICO DE RENDIMIENTO DEL SISTEMA</h2>";
echo "<hr>";

$start_time = microtime(true);

// 1. INFORMACIÓN DEL SERVIDOR PHP
echo "<h3>1. Configuración del Servidor PHP</h3>";
echo "<table border='1' style='width:100%; margin:10px 0;'>";
echo "<tr><th>Configuración</th><th>Valor Actual</th><th>Recomendado</th><th>Estado</th></tr>";

$php_configs = [
    'memory_limit' => ['actual' => ini_get('memory_limit'), 'recomendado' => '256M+'],
    'max_execution_time' => ['actual' => ini_get('max_execution_time'), 'recomendado' => '60+'],
    'max_input_vars' => ['actual' => ini_get('max_input_vars'), 'recomendado' => '3000+'],
    'post_max_size' => ['actual' => ini_get('post_max_size'), 'recomendado' => '32M+'],
    'upload_max_filesize' => ['actual' => ini_get('upload_max_filesize'), 'recomendado' => '32M+'],
    'opcache.enable' => ['actual' => ini_get('opcache.enable') ? 'ON' : 'OFF', 'recomendado' => 'ON']
];

foreach ($php_configs as $config => $values) {
    $status = "⚠️";
    if ($config === 'opcache.enable') {
        $status = $values['actual'] === 'ON' ? "✅" : "❌";
    }
    echo "<tr>";
    echo "<td><strong>$config</strong></td>";
    echo "<td>{$values['actual']}</td>";
    echo "<td>{$values['recomendado']}</td>";
    echo "<td>$status</td>";
    echo "</tr>";
}
echo "</table>";

// 2. INFORMACIÓN DE LA BASE DE DATOS
echo "<h3>2. Estado de la Base de Datos</h3>";
try {
    $db_start = microtime(true);
    
    // Test de conexión
    $stmt = $conn->query("SELECT 1");
    $connection_time = (microtime(true) - $db_start) * 1000;
    
    echo "<p>✅ <strong>Conexión a BD:</strong> " . number_format($connection_time, 2) . " ms</p>";
    
    // Información de la BD
    $stmt = $conn->query("SELECT VERSION() as version");
    $db_version = $stmt->fetchColumn();
    echo "<p><strong>Versión MySQL:</strong> $db_version</p>";
    
    // Tamaño de las tablas principales
    $stmt = $conn->query("
        SELECT 
            table_name,
            table_rows,
            ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
        FROM information_schema.tables 
        WHERE table_schema = DATABASE()
        AND table_name IN ('facturas', 'pacientes', 'usuarios', 'pagos', 'factura_detalles')
        ORDER BY size_mb DESC
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Tamaño de Tablas Principales:</h4>";
    echo "<table border='1' style='width:100%; margin:10px 0;'>";
    echo "<tr><th>Tabla</th><th>Registros</th><th>Tamaño (MB)</th><th>Estado</th></tr>";
    
    foreach ($tables as $table) {
        $status = "✅";
        if ($table['size_mb'] > 100) $status = "⚠️ Grande";
        if ($table['table_rows'] > 10000) $status = "⚠️ Muchos registros";
        
        echo "<tr>";
        echo "<td><strong>{$table['table_name']}</strong></td>";
        echo "<td>" . number_format($table['table_rows']) . "</td>";
        echo "<td>{$table['size_mb']} MB</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p>❌ Error conectando a la BD: " . $e->getMessage() . "</p>";
}

// 3. CONSULTAS LENTAS POTENCIALES
echo "<h3>3. Análisis de Consultas Críticas</h3>";
try {
    $queries_to_test = [
        'Facturas con filtros' => "
            SELECT f.*, 
                   CONCAT(p.nombre, ' ', p.apellido) as paciente_nombre,
                   u.nombre as medico_nombre,
                   COALESCE(SUM(pg.monto), 0) as total_pagado
            FROM facturas f
            LEFT JOIN pacientes p ON f.paciente_id = p.id
            LEFT JOIN usuarios u ON f.medico_id = u.id  
            LEFT JOIN pagos pg ON f.id = pg.factura_id
            GROUP BY f.id
            ORDER BY f.fecha_factura DESC
            LIMIT 10
        ",
        'Búsqueda de pacientes' => "
            SELECT id, nombre, apellido, dni 
            FROM pacientes 
            ORDER BY nombre, apellido 
            LIMIT 50
        ",
        'Procedimientos activos' => "
            SELECT id, codigo, nombre, precio_venta 
            FROM procedimientos 
            WHERE activo = 1 
            ORDER BY nombre 
            LIMIT 50
        "
    ];
    
    echo "<table border='1' style='width:100%; margin:10px 0;'>";
    echo "<tr><th>Consulta</th><th>Tiempo (ms)</th><th>Estado</th></tr>";
    
    foreach ($queries_to_test as $name => $query) {
        $query_start = microtime(true);
        try {
            $stmt = $conn->query($query);
            $stmt->fetchAll();
            $query_time = (microtime(true) - $query_start) * 1000;
            
            $status = "✅ Rápida";
            if ($query_time > 100) $status = "⚠️ Lenta";
            if ($query_time > 500) $status = "❌ Muy lenta";
            
            echo "<tr>";
            echo "<td><strong>$name</strong></td>";
            echo "<td>" . number_format($query_time, 2) . " ms</td>";
            echo "<td>$status</td>";
            echo "</tr>";
        } catch (Exception $e) {
            echo "<tr>";
            echo "<td><strong>$name</strong></td>";
            echo "<td>ERROR</td>";
            echo "<td>❌ " . $e->getMessage() . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p>❌ Error en análisis de consultas: " . $e->getMessage() . "</p>";
}

// 4. ARCHIVOS Y CARGA DE RECURSOS
echo "<h3>4. Análisis de Archivos y Recursos</h3>";

// Contar archivos PHP
$php_files = glob("*.php");
$js_files = glob("js/*.js");
$css_files = glob("css/*.css");

echo "<p><strong>Archivos PHP:</strong> " . count($php_files) . " archivos</p>";
echo "<p><strong>Archivos JS:</strong> " . count($js_files) . " archivos</p>";
echo "<p><strong>Archivos CSS:</strong> " . count($css_files) . " archivos</p>";

// Verificar archivos grandes
echo "<h4>Archivos Grandes (>100KB):</h4>";
$large_files = [];
foreach ($php_files as $file) {
    $size = filesize($file);
    if ($size > 102400) { // >100KB
        $large_files[] = ['file' => $file, 'size' => round($size/1024, 2)];
    }
}

if (empty($large_files)) {
    echo "<p>✅ No hay archivos PHP excesivamente grandes</p>";
} else {
    echo "<ul>";
    foreach ($large_files as $file) {
        echo "<li><strong>{$file['file']}</strong>: {$file['size']} KB</li>";
    }
    echo "</ul>";
}

// 5. RECURSOS EXTERNOS
echo "<h3>5. Recursos Externos (CDN)</h3>";
$external_resources = [
    'jQuery' => 'assets/js/jquery.min.js',
    'Bootstrap CSS' => 'assets/css/bootstrap.min.css',
    'Bootstrap JS' => 'assets/js/bootstrap.bundle.min.js',
    'Font Awesome' => 'assets/css/fontawesome.min.css'
];

echo "<table border='1' style='width:100%; margin:10px 0;'>";
echo "<tr><th>Recurso</th><th>Estado</th><th>Recomendación</th></tr>";

foreach ($external_resources as $name => $url) {
    echo "<tr>";
    echo "<td><strong>$name</strong></td>";
    echo "<td>⚠️ Externo (CDN)</td>";
    echo "<td>Considerar versión local para mejor rendimiento</td>";
    echo "</tr>";
}
echo "</table>";

// 6. SESIONES Y ARCHIVOS TEMPORALES
echo "<h3>6. Sesiones y Archivos Temporales</h3>";
$session_path = session_save_path();
echo "<p><strong>Ruta de sesiones:</strong> $session_path</p>";

if (is_dir($session_path)) {
    $session_files = glob($session_path . "/sess_*");
    echo "<p><strong>Archivos de sesión:</strong> " . count($session_files) . " archivos</p>";
    
    if (count($session_files) > 100) {
        echo "<p>⚠️ <strong>Muchos archivos de sesión.</strong> Considera limpiar sesiones antiguas.</p>";
    } else {
        echo "<p>✅ Número normal de archivos de sesión</p>";
    }
}

// 7. TIEMPO TOTAL DEL DIAGNÓSTICO
$total_time = (microtime(true) - $start_time) * 1000;

echo "<hr>";
echo "<h3>📊 RESUMEN DEL DIAGNÓSTICO</h3>";
echo "<p><strong>Tiempo total del diagnóstico:</strong> " . number_format($total_time, 2) . " ms</p>";

if ($total_time > 2000) {
    echo "<div style='background: #ffeeee; padding: 15px; border: 1px solid #ff0000; border-radius: 5px;'>";
    echo "<h4>🚨 PROBLEMA DE RENDIMIENTO DETECTADO</h4>";
    echo "<p>El diagnóstico tomó más de 2 segundos, lo que indica problemas de rendimiento.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #eeffee; padding: 15px; border: 1px solid #00aa00; border-radius: 5px;'>";
    echo "<h4>✅ RENDIMIENTO ACEPTABLE</h4>";
    echo "<p>El diagnóstico se completó en tiempo razonable.</p>";
    echo "</div>";
}

// 8. RECOMENDACIONES
echo "<h3>💡 RECOMENDACIONES PARA MEJORAR RENDIMIENTO</h3>";
echo "<ol>";
echo "<li><strong>Habilitar OPcache:</strong> Mejora significativamente el rendimiento de PHP</li>";
echo "<li><strong>Optimizar consultas:</strong> Agregar índices a tablas grandes</li>";
echo "<li><strong>Recursos locales:</strong> Descargar librerías externas (jQuery, Bootstrap) localmente</li>";
echo "<li><strong>Compresión:</strong> Habilitar gzip en el servidor web</li>";
echo "<li><strong>Caché de sesiones:</strong> Limpiar sesiones antiguas regularmente</li>";
echo "<li><strong>Monitoreo:</strong> Implementar logs de rendimiento</li>";
echo "</ol>";

echo "<hr>";
echo "<p><a href='facturacion.php' class='btn btn-primary'>← Volver a Facturación</a></p>";
echo "<p><a href='optimizar_sistema.php' class='btn btn-success'>🚀 Aplicar Optimizaciones</a></p>";
?>

