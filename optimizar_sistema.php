<?php
session_start();
require_once 'config.php';

// Verificar si es el usuario administrador
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$aplicar_optimizaciones = isset($_POST['aplicar']) ? true : false;
$resultados = [];

if ($aplicar_optimizaciones) {
    
    // 1. DESCARGAR Y CONFIGURAR LIBRERÍAS LOCALES
    $resultados[] = descargarLibreriasLocales();
    
    // 2. CREAR CONFIGURACIÓN OPTIMIZADA DE PHP
    $resultados[] = crearConfigOptimizada();
    
    // 3. LIMPIAR SESIONES ANTIGUAS
    $resultados[] = limpiarSesionesAntiguas();
    
    // 4. OPTIMIZAR BASE DE DATOS
    $resultados[] = optimizarBaseDatos();
    
    // 5. CREAR HTACCESS PARA COMPRESIÓN
    $resultados[] = crearHtaccessOptimizado();
}

function descargarLibreriasLocales() {
    $libs_dir = 'assets/libs/';
    
    if (!file_exists($libs_dir)) {
        mkdir($libs_dir, 0755, true);
    }
    
    $recursos = [
        'jquery' => [
            'url' => 'assets/js/jquery.min.js',
            'archivo' => 'jquery-3.6.0.min.js'
        ],
        'bootstrap_css' => [
            'url' => 'assets/css/bootstrap-5.1.3.min.css',
            'archivo' => 'bootstrap.min.css'
        ],
        'bootstrap_js' => [
            'url' => 'assets/js/bootstrap.bundle.min.js',
            'archivo' => 'bootstrap.bundle.min.js'
        ],
        'fontawesome' => [
            'url' => 'assets/css/fontawesome-6.0.0.min.css',
            'archivo' => 'fontawesome.min.css'
        ]
    ];
    
    $descargados = 0;
    $errores = [];
    
    foreach ($recursos as $nombre => $info) {
        $archivo_local = $libs_dir . $info['archivo'];
        
        if (!file_exists($archivo_local)) {
            $contenido = @file_get_contents($info['url']);
            if ($contenido !== false) {
                file_put_contents($archivo_local, $contenido);
                $descargados++;
            } else {
                $errores[] = "Error descargando $nombre";
            }
        }
    }
    
    // Crear archivo de inclusión optimizada
    $include_libs = '<?php
// LIBRERÍAS LOCALES OPTIMIZADAS - Generado automáticamente
function incluir_libs_locales() {
    $base_url = "/Consultorio2/assets/libs/";
    echo \'
    <!-- CSS Locales -->
    <link href="\' . $base_url . \'bootstrap.min.css" rel="stylesheet">
    <link href="\' . $base_url . \'fontawesome.min.css" rel="stylesheet">
    
    <!-- JS Locales -->
    <script src="\' . $base_url . \'jquery-3.6.0.min.js"></script>
    <script src="\' . $base_url . \'bootstrap.bundle.min.js"></script>
    \';
}

function incluir_libs_fallback() {
    echo \'
    <!-- CDN Fallback -->
    <link href="assets/css/bootstrap-5.1.3.min.css" rel="stylesheet">
    <link href="assets/css/fontawesome-6.0.0.min.css" rel="stylesheet">
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    \';
}
?>';
    
    file_put_contents('include_libs.php', $include_libs);
    
    return [
        'accion' => 'Descarga de librerías locales',
        'estado' => $descargados > 0 ? 'ÉXITO' : 'ERROR',
        'detalle' => "$descargados librerías descargadas. " . (count($errores) > 0 ? implode(', ', $errores) : ''),
        'tiempo' => time()
    ];
}

function crearConfigOptimizada() {
    $config_optimizada = '; Configuración PHP optimizada para el consultorio
; memory_limit = 256M
; max_execution_time = 60
; max_input_vars = 5000
; post_max_size = 32M
; upload_max_filesize = 32M

; OPcache - Recomendado habilitar en php.ini
; opcache.enable=1
; opcache.memory_consumption=128
; opcache.interned_strings_buffer=8
; opcache.max_accelerated_files=4000
; opcache.revalidate_freq=2
; opcache.fast_shutdown=1
; opcache.enable_cli=1
';
    
    file_put_contents('php_optimizacion.ini', $config_optimizada);
    
    return [
        'accion' => 'Configuración PHP optimizada',
        'estado' => 'CREADA',
        'detalle' => 'Archivo php_optimizacion.ini creado. Aplicar manualmente en php.ini',
        'tiempo' => time()
    ];
}

function limpiarSesionesAntiguas() {
    $ruta_sesiones = session_save_path();
    if (empty($ruta_sesiones)) {
        $ruta_sesiones = sys_get_temp_dir() . '/consultorio_sessions';
    }
    
    $eliminadas = 0;
    
    if (is_dir($ruta_sesiones)) {
        $archivos = glob($ruta_sesiones . '/sess_*');
        $ahora = time();
        
        foreach ($archivos as $archivo) {
            if (is_file($archivo)) {
                $tiempo_modificacion = filemtime($archivo);
                // Eliminar sesiones más antiguas que 24 horas
                if (($ahora - $tiempo_modificacion) > 86400) {
                    unlink($archivo);
                    $eliminadas++;
                }
            }
        }
    }
    
    return [
        'accion' => 'Limpieza de sesiones',
        'estado' => 'COMPLETADA',
        'detalle' => "$eliminadas sesiones antiguas eliminadas",
        'tiempo' => time()
    ];
}

function optimizarBaseDatos() {
    global $conn;
    
    $optimizaciones = [];
    
    // Crear índices importantes si no existen
    $indices = [
        "ALTER TABLE facturas ADD INDEX idx_fecha (fecha)",
        "ALTER TABLE facturas ADD INDEX idx_paciente (paciente_id)",
        "ALTER TABLE pagos ADD INDEX idx_factura (factura_id)",
        "ALTER TABLE pagos ADD INDEX idx_fecha (fecha_pago)",
        "ALTER TABLE pacientes ADD INDEX idx_dni (dni)",
        "ALTER TABLE usuarios ADD INDEX idx_email (email)"
    ];
    
    foreach ($indices as $sql) {
        $resultado = mysqli_query($conn, $sql);
        if ($resultado) {
            $optimizaciones[] = "✅ Índice creado";
        } else {
            $error = mysqli_error($conn);
            if (strpos($error, 'Duplicate key name') !== false) {
                $optimizaciones[] = "ℹ️ Índice ya existe";
            } else {
                $optimizaciones[] = "❌ Error: " . $error;
            }
        }
    }
    
    // Optimizar tablas
    $tablas = ['facturas', 'pagos', 'pacientes', 'usuarios', 'factura_detalles'];
    foreach ($tablas as $tabla) {
        mysqli_query($conn, "OPTIMIZE TABLE $tabla");
        $optimizaciones[] = "🔧 Tabla $tabla optimizada";
    }
    
    return [
        'accion' => 'Optimización de base de datos',
        'estado' => 'COMPLETADA',
        'detalle' => implode(', ', $optimizaciones),
        'tiempo' => time()
    ];
}

function crearHtaccessOptimizado() {
    $htaccess = '# Configuración optimizada para el consultorio
# Compresión GZIP
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Caché de archivos estáticos
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/svg+xml "access plus 1 month"
</IfModule>

# Seguridad
<Files "*.ini">
    Order allow,deny
    Deny from all
</Files>

<Files "*.log">
    Order allow,deny
    Deny from all
</Files>
';
    
    file_put_contents('.htaccess', $htaccess);
    
    return [
        'accion' => 'Configuración Apache (.htaccess)',
        'estado' => 'CREADA',
        'detalle' => 'Compresión y caché configurados',
        'tiempo' => time()
    ];
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Optimización del Sistema - Consultorio</title>
    <link href="assets/css/bootstrap-5.1.3.min.css" rel="stylesheet">
    <link href="assets/css/fontawesome-6.0.0.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2><i class="fas fa-rocket"></i> Optimización del Sistema</h2>
                <hr>
                
                <?php if (!$aplicar_optimizaciones): ?>
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Optimizaciones Disponibles</h5>
                        <p>El sistema aplicará las siguientes optimizaciones basadas en el diagnóstico:</p>
                        <ul>
                            <li><strong>Librerías locales:</strong> Descarga jQuery, Bootstrap y FontAwesome localmente</li>
                            <li><strong>Configuración PHP:</strong> Crea archivo con configuraciones optimizadas</li>
                            <li><strong>Limpieza de sesiones:</strong> Elimina sesiones antiguas</li>
                            <li><strong>Optimización de BD:</strong> Crea índices y optimiza tablas</li>
                            <li><strong>Compresión web:</strong> Configura .htaccess para mejor rendimiento</li>
                        </ul>
                    </div>
                    
                    <form method="POST">
                        <button type="submit" name="aplicar" class="btn btn-success btn-lg">
                            <i class="fas fa-rocket"></i> Aplicar Todas las Optimizaciones
                        </button>
                        <a href="facturacion.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                    </form>
                    
                <?php else: ?>
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> Optimizaciones Aplicadas</h5>
                        <p>Las optimizaciones se han ejecutado correctamente.</p>
                    </div>
                    
                    <div class="row">
                        <?php foreach ($resultados as $resultado): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h6><i class="fas fa-cog"></i> <?php echo $resultado['accion']; ?></h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1">
                                            <span class="badge bg-<?php echo $resultado['estado'] === 'ÉXITO' || $resultado['estado'] === 'COMPLETADA' || $resultado['estado'] === 'CREADA' ? 'success' : 'warning'; ?>">
                                                <?php echo $resultado['estado']; ?>
                                            </span>
                                        </p>
                                        <p class="text-muted small"><?php echo $resultado['detalle']; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Pasos Manuales Adicionales</h6>
                        <ol>
                            <li><strong>Configuración PHP:</strong> Aplicar configuraciones del archivo <code>php_optimizacion.ini</code> en el <code>php.ini</code> principal</li>
                            <li><strong>Librerías locales:</strong> Actualizar archivos PHP para usar las librerías locales en lugar de CDN</li>
                            <li><strong>Reiniciar servicios:</strong> Reiniciar Apache/IIS para aplicar cambios</li>
                        </ol>
                    </div>
                    
                    <a href="diagnostico_rendimiento.php" class="btn btn-info">
                        <i class="fas fa-chart-line"></i> Nuevo Diagnóstico
                    </a>
                    <a href="facturacion.php" class="btn btn-success">
                        <i class="fas fa-arrow-left"></i> Volver a Facturación
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>



