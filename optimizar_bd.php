<?php
session_start();
require_once 'config.php';

echo "<h2>🔧 OPTIMIZACIÓN DE BASE DE DATOS</h2><hr>";

$optimizaciones = [];
$tiempo_inicio = microtime(true);

// Crear índices importantes si no existen
$indices = [
    "ALTER TABLE facturas ADD INDEX idx_fecha (fecha)" => "Índice por fecha en facturas",
    "ALTER TABLE facturas ADD INDEX idx_paciente (paciente_id)" => "Índice por paciente en facturas",
    "ALTER TABLE pagos ADD INDEX idx_factura (factura_id)" => "Índice por factura en pagos",
    "ALTER TABLE pagos ADD INDEX idx_fecha (fecha_pago)" => "Índice por fecha en pagos",
    "ALTER TABLE pacientes ADD INDEX idx_dni (dni)" => "Índice por DNI en pacientes",
    "ALTER TABLE usuarios ADD INDEX idx_email (email)" => "Índice por email en usuarios"
];

echo "<h3>Creando Índices</h3>";
echo "<table border='1' style='width:100%; margin:10px 0;'>";
echo "<tr><th>Índice</th><th>Estado</th><th>Detalle</th></tr>";

foreach ($indices as $sql => $descripcion) {
    try {
        $resultado = $pdo->exec($sql);
        $estado = "✅ CREADO";
        $detalle = "Índice creado exitosamente";
        $optimizaciones[] = "✅ " . $descripcion;
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
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
    echo "<td><strong>$descripcion</strong></td>";
    echo "<td>$estado</td>";
    echo "<td>$detalle</td>";
    echo "</tr>";
}

echo "</table>";

// Optimizar tablas
echo "<h3>Optimizando Tablas</h3>";
echo "<table border='1' style='width:100%; margin:10px 0;'>";
echo "<tr><th>Tabla</th><th>Estado</th><th>Tamaño Antes</th><th>Tamaño Después</th></tr>";

$tablas = ['facturas', 'pagos', 'pacientes', 'usuarios', 'factura_detalles'];
foreach ($tablas as $tabla) {
    // Obtener tamaño antes
    $query_size_antes = "SELECT 
        ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
        FROM information_schema.TABLES 
        WHERE table_schema = '" . DB_NAME . "' 
        AND table_name = '$tabla'";
    
    try {
        $stmt_antes = $pdo->query($query_size_antes);
        $size_antes = $stmt_antes ? $stmt_antes->fetchColumn() : 0;
        
        // Optimizar tabla
        $optimize_result = $pdo->exec("OPTIMIZE TABLE $tabla");
        
        // Obtener tamaño después
        $stmt_despues = $pdo->query($query_size_antes);
        $size_despues = $stmt_despues ? $stmt_despues->fetchColumn() : 0;
        
        $estado = "✅ OPTIMIZADA";
        $optimizaciones[] = "🔧 Tabla $tabla optimizada";
    } catch (PDOException $e) {
        $estado = "❌ ERROR";
        $size_antes = $size_despues = 0;
        $optimizaciones[] = "❌ Error optimizando $tabla: " . $e->getMessage();
    }
    
    echo "<tr>";
    echo "<td><strong>$tabla</strong></td>";
    echo "<td>$estado</td>";
    echo "<td>{$size_antes} MB</td>";
    echo "<td>{$size_despues} MB</td>";
    echo "</tr>";
}

echo "</table>";

// Analizar estadísticas de uso
echo "<h3>Estadísticas de Uso</h3>";

$stats_queries = [
    'Facturas totales' => "SELECT COUNT(*) as total FROM facturas",
    'Pagos este mes' => "SELECT COUNT(*) as total FROM pagos WHERE MONTH(fecha_pago) = MONTH(CURDATE())",
    'Pacientes activos' => "SELECT COUNT(*) as total FROM pacientes WHERE activo = 1",
    'Usuarios activos' => "SELECT COUNT(*) as total FROM usuarios WHERE activo = 1"
];

echo "<table border='1' style='width:100%; margin:10px 0;'>";
echo "<tr><th>Métrica</th><th>Valor</th></tr>";

foreach ($stats_queries as $nombre => $query) {
    try {
        $stmt = $pdo->query($query);
        $valor = $stmt ? $stmt->fetchColumn() : 0;
    } catch (PDOException $e) {
        $valor = "Error: " . $e->getMessage();
    }
    
    echo "<tr>";
    echo "<td><strong>$nombre</strong></td>";
    echo "<td>$valor</td>";
    echo "</tr>";
}

echo "</table>";

$tiempo_fin = microtime(true);
$tiempo_total = round(($tiempo_fin - $tiempo_inicio) * 1000, 2);

echo "<hr>";
echo "<div style='background: #eeffee; padding: 15px; border: 1px solid #00aa00; border-radius: 5px;'>";
echo "<h4>✅ OPTIMIZACIÓN COMPLETADA</h4>";
echo "<p><strong>Tiempo total:</strong> {$tiempo_total} ms</p>";
echo "<p><strong>Optimizaciones aplicadas:</strong></p>";
echo "<ul>";
foreach ($optimizaciones as $opt) {
    echo "<li>$opt</li>";
}
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><a href='diagnostico_rendimiento.php' class='btn btn-info'>🔍 Nuevo Diagnóstico</a> ";
echo "<a href='facturacion.php' class='btn btn-success'>← Volver a Facturación</a></p>";
?>
