<?php
// Análisis de rendimiento específico para index.php
$start_time = microtime(true);

// Incluir archivos necesarios para análisis
require_once 'session_config.php';
session_start();
require_once "permissions.php";
require_once "config.php";

echo "<h2>🔍 ANÁLISIS DE RENDIMIENTO - INDEX.PHP</h2>";
echo "<hr>";

// 1. Análisis de consultas SQL
echo "<h3>1. 📊 Análisis de Consultas SQL</h3>";
echo "<table border='1' style='width:100%; margin:10px 0; border-collapse: collapse;'>";
echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Consulta</th><th style='padding: 8px;'>Tiempo (ms)</th><th style='padding: 8px;'>Registros</th><th style='padding: 8px;'>Estado</th></tr>";

// Test 1: Configuración del consultorio
$test_start = microtime(true);
try {
    $stmt = $conn->query("SELECT nombre_consultorio FROM configuracion WHERE id = 1");
    $config_consultorio = $stmt->fetch(PDO::FETCH_ASSOC);
    $test_end = microtime(true);
    $tiempo = round(($test_end - $test_start) * 1000, 2);
    $estado = $tiempo < 5 ? "✅ Rápida" : ($tiempo < 20 ? "⚠️ Aceptable" : "❌ Lenta");
    echo "<tr><td>Configuración consultorio</td><td>{$tiempo} ms</td><td>1</td><td>{$estado}</td></tr>";
} catch (Exception $e) {
    echo "<tr><td>Configuración consultorio</td><td>ERROR</td><td>0</td><td>❌ {$e->getMessage()}</td></tr>";
}

// Test 2: Turnos de hoy
$test_start = microtime(true);
try {
    $sql = "SELECT COUNT(*) as total FROM turnos WHERE fecha_turno = CURDATE()";
    $stmt = $conn->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $test_end = microtime(true);
    $tiempo = round(($test_end - $test_start) * 1000, 2);
    $estado = $tiempo < 10 ? "✅ Rápida" : ($tiempo < 50 ? "⚠️ Aceptable" : "❌ Lenta");
    echo "<tr><td>Turnos de hoy (COUNT)</td><td>{$tiempo} ms</td><td>{$row['total']}</td><td>{$estado}</td></tr>";
} catch (Exception $e) {
    echo "<tr><td>Turnos de hoy</td><td>ERROR</td><td>0</td><td>❌ {$e->getMessage()}</td></tr>";
}

// Test 3: Total pacientes
$test_start = microtime(true);
try {
    $sql = "SELECT COUNT(*) as total FROM pacientes";
    $stmt = $conn->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $test_end = microtime(true);
    $tiempo = round(($test_end - $test_start) * 1000, 2);
    $estado = $tiempo < 10 ? "✅ Rápida" : ($tiempo < 50 ? "⚠️ Aceptable" : "❌ Lenta");
    echo "<tr><td>Total pacientes (COUNT)</td><td>{$tiempo} ms</td><td>{$row['total']}</td><td>{$estado}</td></tr>";
} catch (Exception $e) {
    echo "<tr><td>Total pacientes</td><td>ERROR</td><td>0</td><td>❌ {$e->getMessage()}</td></tr>";
}

// Test 4: Citas de hoy
$test_start = microtime(true);
try {
    $sql = "SELECT COUNT(*) as total FROM citas WHERE fecha = CURDATE()";
    $stmt = $conn->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $test_end = microtime(true);
    $tiempo = round(($test_end - $test_start) * 1000, 2);
    $estado = $tiempo < 10 ? "✅ Rápida" : ($tiempo < 50 ? "⚠️ Aceptable" : "❌ Lenta");
    echo "<tr><td>Citas de hoy (COUNT)</td><td>{$tiempo} ms</td><td>{$row['total']}</td><td>{$estado}</td></tr>";
} catch (Exception $e) {
    echo "<tr><td>Citas de hoy</td><td>ERROR</td><td>0</td><td>❌ {$e->getMessage()}</td></tr>";
}

// Test 5: Recetas del mes
$test_start = microtime(true);
try {
    $sql = "SELECT COUNT(*) as total FROM recetas WHERE MONTH(fecha_receta) = MONTH(CURRENT_DATE())";
    $stmt = $conn->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $test_end = microtime(true);
    $tiempo = round(($test_end - $test_start) * 1000, 2);
    $estado = $tiempo < 10 ? "✅ Rápida" : ($tiempo < 50 ? "⚠️ Aceptable" : "❌ Lenta");
    echo "<tr><td>Recetas del mes (COUNT)</td><td>{$tiempo} ms</td><td>{$row['total']}</td><td>{$estado}</td></tr>";
} catch (Exception $e) {
    echo "<tr><td>Recetas del mes</td><td>ERROR</td><td>0</td><td>❌ {$e->getMessage()}</td></tr>";
}

// Test 6: Actividad de hoy (consulta más compleja)
$test_start = microtime(true);
try {
    $sql = "SELECT 
        h.id,
        h.fecha, 
        CONCAT(p.nombre, ' ', p.apellido) as paciente,
        p.id as paciente_id,
        h.tipo_consulta as tipo,
        h.motivo_consulta as detalle
        FROM historial_medico h
        JOIN pacientes p ON h.paciente_id = p.id
        WHERE DATE(h.fecha) = CURDATE()
        ORDER BY h.fecha DESC";
    $stmt = $conn->query($sql);
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $test_end = microtime(true);
    $tiempo = round(($test_end - $test_start) * 1000, 2);
    $estado = $tiempo < 20 ? "✅ Rápida" : ($tiempo < 100 ? "⚠️ Aceptable" : "❌ Lenta");
    echo "<tr><td>Actividad de hoy (JOIN)</td><td>{$tiempo} ms</td><td>" . count($registros) . "</td><td>{$estado}</td></tr>";
} catch (Exception $e) {
    echo "<tr><td>Actividad de hoy</td><td>ERROR</td><td>0</td><td>❌ {$e->getMessage()}</td></tr>";
}

echo "</table>";

// 2. Análisis de archivos incluidos
echo "<h3>2. 📁 Análisis de Archivos Incluidos</h3>";
echo "<table border='1' style='width:100%; margin:10px 0; border-collapse: collapse;'>";
echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Archivo</th><th style='padding: 8px;'>Tamaño</th><th style='padding: 8px;'>Estado</th></tr>";

$archivos_incluidos = [
    'session_config.php',
    'permissions.php', 
    'config.php',
    'includes/header.php',
    'sidebar.php'
];

foreach ($archivos_incluidos as $archivo) {
    if (file_exists($archivo)) {
        $tamaño = round(filesize($archivo) / 1024, 2);
        $estado = $tamaño < 10 ? "✅ Pequeño" : ($tamaño < 50 ? "⚠️ Medio" : "❌ Grande");
        echo "<tr><td>{$archivo}</td><td>{$tamaño} KB</td><td>{$estado}</td></tr>";
    } else {
        echo "<tr><td>{$archivo}</td><td>N/A</td><td>❌ No encontrado</td></tr>";
    }
}

echo "</table>";

// 3. Análisis de recursos estáticos
echo "<h3>3. 🎨 Análisis de Recursos Estáticos</h3>";
echo "<table border='1' style='width:100%; margin:10px 0; border-collapse: collapse;'>";
echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Recurso</th><th style='padding: 8px;'>Tamaño</th><th style='padding: 8px;'>Estado</th></tr>";

$recursos_estaticos = [
    'assets/libs/bootstrap.min.css',
    'assets/libs/fontawesome.local.min.css',
    'css/dark-mode.css',
    'assets/css/form-style.css',
    'assets/libs/jquery-3.6.0.min.js',
    'assets/libs/bootstrap.bundle.min.js',
    'js/theme-manager.js'
];

foreach ($recursos_estaticos as $recurso) {
    if (file_exists($recurso)) {
        $tamaño = round(filesize($recurso) / 1024, 2);
        $estado = $tamaño < 100 ? "✅ Optimizado" : ($tamaño < 500 ? "⚠️ Medio" : "❌ Grande");
        echo "<tr><td>{$recurso}</td><td>{$tamaño} KB</td><td>{$estado}</td></tr>";
    } else {
        echo "<tr><td>{$recurso}</td><td>N/A</td><td>❌ No encontrado</td></tr>";
    }
}

echo "</table>";

// 4. Análisis de rendimiento de permisos
echo "<h3>4. 🔐 Análisis de Rendimiento de Permisos</h3>";
$permisos_test = [
    'manage_appointments',
    'manage_patients', 
    'view_appointments',
    'manage_prescriptions',
    'view_prescriptions',
    'view_medical_history'
];

echo "<table border='1' style='width:100%; margin:10px 0; border-collapse: collapse;'>";
echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Permiso</th><th style='padding: 8px;'>Tiempo (ms)</th><th style='padding: 8px;'>Resultado</th></tr>";

foreach ($permisos_test as $permiso) {
    $test_start = microtime(true);
    $tiene_permiso = hasPermission($permiso);
    $test_end = microtime(true);
    $tiempo = round(($test_end - $test_start) * 1000, 2);
    $resultado = $tiene_permiso ? "✅ Sí" : "❌ No";
    echo "<tr><td>{$permiso}</td><td>{$tiempo} ms</td><td>{$resultado}</td></tr>";
}

echo "</table>";

// 5. Análisis de memoria
echo "<h3>5. 💾 Análisis de Uso de Memoria</h3>";
$memoria_actual = memory_get_usage();
$memoria_pico = memory_get_peak_usage();

echo "<table border='1' style='width:100%; margin:10px 0; border-collapse: collapse;'>";
echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Métrica</th><th style='padding: 8px;'>Valor</th><th style='padding: 8px;'>Estado</th></tr>";

$memoria_actual_mb = round($memoria_actual / 1024 / 1024, 2);
$memoria_pico_mb = round($memoria_pico / 1024 / 1024, 2);

$estado_actual = $memoria_actual_mb < 10 ? "✅ Excelente" : ($memoria_actual_mb < 25 ? "⚠️ Aceptable" : "❌ Alto");
$estado_pico = $memoria_pico_mb < 15 ? "✅ Excelente" : ($memoria_pico_mb < 50 ? "⚠️ Aceptable" : "❌ Alto");

echo "<tr><td>Memoria actual</td><td>{$memoria_actual_mb} MB</td><td>{$estado_actual}</td></tr>";
echo "<tr><td>Pico de memoria</td><td>{$memoria_pico_mb} MB</td><td>{$estado_pico}</td></tr>";

echo "</table>";

// 6. Tiempo total de ejecución
$total_time = microtime(true) - $start_time;
$tiempo_total_ms = round($total_time * 1000, 2);

echo "<h3>6. ⏱️ Resumen de Rendimiento</h3>";
echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0;'>";

$estado_general = "✅ EXCELENTE";
if ($tiempo_total_ms > 200) $estado_general = "⚠️ MEJORABLE";
if ($tiempo_total_ms > 500) $estado_general = "❌ LENTO";

echo "<h4>{$estado_general}: Análisis de index.php completado</h4>";
echo "<p><strong>Tiempo total de análisis:</strong> {$tiempo_total_ms} ms</p>";

// Recomendaciones
echo "<h4>📋 Recomendaciones:</h4>";
echo "<ul>";

if ($tiempo_total_ms > 100) {
    echo "<li>⚠️ Considerar implementar caché para las consultas COUNT()</li>";
}

if ($tiempo_total_ms > 200) {
    echo "<li>❌ Optimizar consultas con LIMIT para reducir carga</li>";
}

echo "<li>✅ Usar índices en fechas para consultas CURDATE()</li>";
echo "<li>✅ Implementar lazy loading para secciones no críticas</li>";
echo "<li>✅ Considerar caché de permisos de usuario</li>";

echo "</ul>";
echo "</div>";

echo "<div style='margin-top: 20px;'>";
echo "<a href='index.php' class='btn btn-primary' style='margin-right: 10px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>🏠 Volver al Dashboard</a>";
echo "<a href='test_opcache.php' class='btn btn-success' style='margin-right: 10px; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>🚀 Test OPcache</a>";
echo "<a href='verificar_optimizaciones.php' class='btn btn-info' style='padding: 10px 20px; background: #17a2b8; color: white; text-decoration: none; border-radius: 5px;'>📊 Verificar Optimizaciones</a>";
echo "</div>";

echo "<style>
table { font-family: Arial, sans-serif; font-size: 14px; }
.btn { display: inline-block; margin: 5px; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; text-align: center; }
</style>";
?>
