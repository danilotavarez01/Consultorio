<?php
// Herramienta de diagnóstico para campos dinámicos
// Este script ayuda a diagnosticar problemas con los endpoints de campos dinámicos
// ACTUALIZADO: 2025-06-20 - Versión 2.0 con detección y reparación automática

// Cabecera para que se vea mejor en el navegador
header('Content-Type: text/html; charset=utf-8');

// Configuración para la herramienta
$config = [
    'version' => '2.0',
    'fecha_actualizacion' => '2025-06-20',
    'modo_debug' => true
];

// Manejo de acciones automáticas si se solicitan
$accion = isset($_GET['action']) ? $_GET['action'] : '';
$mensaje_accion = '';

if ($accion == 'autorepair') {
    $mensaje_accion = realizarReparacionAutomatica();
}

// Función para realizar reparación automática
function realizarReparacionAutomatica() {
    $acciones_realizadas = [];
    
    // 1. Verificar y crear el archivo de emergencia si no existe
    if (!file_exists(__DIR__ . '/get_campos_emergencia.php')) {
        $contenido = <<<'PHP'
<?php
// Endpoint de emergencia para campos dinámicos (Auto-generado)
// Este archivo siempre devuelve campos predefinidos sin acceder a la base de datos
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Declaramos los campos fijos que siempre se mostrarán
$respuesta = [
    'success' => true,
    'campos' => [
        'temperatura' => [
            'label' => 'Temperatura (°C)',
            'tipo' => 'number',
            'requerido' => true
        ],
        'presion_arterial' => [
            'label' => 'Presión Arterial',
            'tipo' => 'text',
            'requerido' => true
        ],
        'frecuencia_cardiaca' => [
            'label' => 'Frecuencia Cardíaca (lpm)',
            'tipo' => 'number',
            'requerido' => false
        ],
        'peso' => [
            'label' => 'Peso (kg)',
            'tipo' => 'number',
            'requerido' => false
        ],
        'diagnostico' => [
            'label' => 'Diagnóstico',
            'tipo' => 'textarea',
            'requerido' => true
        ],
        'observaciones' => [
            'label' => 'Observaciones',
            'tipo' => 'textarea',
            'requerido' => false
        ]
    ],
    'mensaje' => 'Campos de emergencia cargados correctamente (auto-reparación)',
    'origen' => 'get_campos_emergencia.php (auto-generado)'
];

// Devolver la respuesta
echo json_encode($respuesta);
?>
PHP;
        if (file_put_contents(__DIR__ . '/get_campos_emergencia.php', $contenido)) {
            $acciones_realizadas[] = 'Se creó el archivo get_campos_emergencia.php';
        }
    }
    
    // 2. Verificar permiso de lectura/escritura en archivos críticos
    $archivos_criticos = [
        'get_campos_emergencia.php',
        'get_campos_simple.php',
        'get_campos_especialidad_nuevo.php'
    ];
    
    foreach ($archivos_criticos as $archivo) {
        $ruta = __DIR__ . '/' . $archivo;
        if (file_exists($ruta) && !is_readable($ruta)) {
            if (@chmod($ruta, 0644)) {
                $acciones_realizadas[] = "Se corrigieron los permisos de {$archivo}";
            }
        }
    }
    
    // 3. Generar un reporte de diagnóstico
    $log = "==== Diagnóstico Automático ====\n";
    $log .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
    $log .= "Acciones realizadas:\n";
    
    if (count($acciones_realizadas) > 0) {
        foreach ($acciones_realizadas as $accion) {
            $log .= "- {$accion}\n";
        }
    } else {
        $log .= "- No se requirieron reparaciones automáticas\n";
    }
    
    $log .= "\n==== Fin del diagnóstico ====\n";
    
    // Guardar el log
    @file_put_contents(__DIR__ . '/consulta_test_log.txt', $log, FILE_APPEND);
    
    return 'Se realizaron ' . count($acciones_realizadas) . ' reparaciones automáticas';
}

// Función para verificar si un archivo existe y es accesible
function verificarArchivo($ruta) {
    $resultado = [
        'existe' => file_exists($ruta),
        'legible' => is_readable($ruta),
        'tamaño' => file_exists($ruta) ? filesize($ruta) : 0,
        'ruta' => $ruta,
        'permisos' => file_exists($ruta) ? substr(sprintf('%o', fileperms($ruta)), -4) : 'N/A',
        'ultima_modificacion' => file_exists($ruta) ? date('Y-m-d H:i:s', filemtime($ruta)) : 'N/A'
    ];
    
    return $resultado;
}

// Comprobar archivos críticos
$archivos = [
    'get_campos_especialidad_nuevo.php',
    'get_campos_simple.php',
    'get_campos_emergencia.php',
    'js/consulta_dinamica_simple.js',
    'js/debug_consulta.js',
    'diagnostico_campos_dinamicos.php'
];

$resultados = [];

foreach ($archivos as $archivo) {
    $resultados[$archivo] = verificarArchivo(__DIR__ . '/' . $archivo);
}

// Verificar estructura de base de datos
$dbOK = false;
$especialidadConfigurada = false;
$camposConfigurados = false;
$errorDB = '';

try {
    require_once "config.php";
    
    // Verificar conexión
    $dbOK = true;
    
    // Verificar especialidad configurada
    $stmt = $conn->query("SELECT COUNT(*) FROM configuracion WHERE especialidad_id IS NOT NULL AND especialidad_id > 0");
    $especialidadConfigurada = ($stmt->fetchColumn() > 0);
    
    // Verificar campos configurados
    $stmt = $conn->query("SELECT COUNT(*) FROM especialidad_campos");
    $camposConfigurados = ($stmt->fetchColumn() > 0);
    
} catch (Exception $e) {
    $errorDB = $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Campos Dinámicos</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Diagnóstico de Campos Dinámicos</h1>
        
        <div class="alert alert-info">
            <p>Esta herramienta diagnostica problemas con la carga de campos dinámicos en consultas.</p>
        </div>
        
        <h2>1. Verificación de archivos</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Archivo</th>
                    <th>Existe</th>
                    <th>Legible</th>
                    <th>Tamaño</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados as $nombre => $info): ?>
                <tr>
                    <td><?php echo htmlspecialchars($nombre); ?></td>
                    <td class="<?php echo $info['existe'] ? 'success' : 'error'; ?>">
                        <?php echo $info['existe'] ? '✓' : '✗'; ?>
                    </td>
                    <td class="<?php echo $info['legible'] ? 'success' : 'error'; ?>">
                        <?php echo $info['legible'] ? '✓' : '✗'; ?>
                    </td>
                    <td><?php echo $info['tamaño']; ?> bytes</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h2>2. Verificación de base de datos</h2>
        <table class="table table-bordered">
            <tr>
                <th>Conexión a base de datos</th>
                <td class="<?php echo $dbOK ? 'success' : 'error'; ?>">
                    <?php echo $dbOK ? '✓ OK' : '✗ Error: ' . htmlspecialchars($errorDB); ?>
                </td>
            </tr>
            <tr>
                <th>Especialidad configurada</th>
                <td class="<?php echo $especialidadConfigurada ? 'success' : 'warning'; ?>">
                    <?php echo $especialidadConfigurada ? '✓ OK' : '⚠ No hay especialidad configurada'; ?>
                </td>
            </tr>
            <tr>
                <th>Campos configurados</th>
                <td class="<?php echo $camposConfigurados ? 'success' : 'warning'; ?>">
                    <?php echo $camposConfigurados ? '✓ OK' : '⚠ No hay campos configurados'; ?>
                </td>
            </tr>
        </table>
        
        <h2>3. Accesibilidad de endpoints</h2>
        <p>Haga clic en los enlaces para probar si los endpoints son accesibles:</p>
        <ul class="list-group">
            <li class="list-group-item">
                <a href="get_campos_especialidad_nuevo.php" target="_blank">get_campos_especialidad_nuevo.php</a>
            </li>
            <li class="list-group-item">
                <a href="get_campos_simple.php" target="_blank">get_campos_simple.php</a>
            </li>
            <li class="list-group-item">
                <a href="get_campos_emergencia.php" target="_blank">get_campos_emergencia.php</a>
            </li>
        </ul>
        
        <h2>4. Acciones correctivas</h2>
        <div class="card mt-3">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Reparación automática</h3>
            </div>
            <div class="card-body">
                <p>Si los archivos no existen o hay problemas con la especialidad configurada, puede intentar la reparación automática:</p>
                <form action="fix_campos_dinamicos.php" method="post">
                    <input type="hidden" name="action" value="repair">
                    <button type="submit" class="btn btn-danger">Intentar reparación automática</button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>

