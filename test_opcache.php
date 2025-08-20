<?php
// Verificación específica de OPcache desde el servidor web
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación OPcache - Estado en Vivo</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        .status-card { 
            margin: 20px 0; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .status-success { background-color: #d4edda; border-left: 5px solid #28a745; }
        .status-warning { background-color: #fff3cd; border-left: 5px solid #ffc107; }
        .status-danger { background-color: #f8d7da; border-left: 5px solid #dc3545; }
        .metric { margin: 10px 0; font-family: monospace; }
        .score { font-size: 48px; font-weight: bold; text-align: center; margin: 20px 0; }
        .score-excellent { color: #28a745; }
        .score-good { color: #ffc107; }
        .score-poor { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center mb-4">🚀 Verificación OPcache en Vivo</h1>
        
        <?php
        $score = 0;
        $maxScore = 100;
        
        // Verificar OPcache
        $opcacheEnabled = extension_loaded('opcache') && ini_get('opcache.enable');
        $opcacheStatus = $opcacheEnabled ? opcache_get_status() : false;
        ?>
        
        <!-- Estado de OPcache -->
        <div class="status-card <?php echo $opcacheEnabled ? 'status-success' : 'status-danger'; ?>">
            <h3>📊 Estado de OPcache</h3>
            <?php if ($opcacheEnabled && $opcacheStatus): ?>
                <?php $score += 50; ?>
                <div class="metric"><strong>✅ Estado:</strong> HABILITADO Y FUNCIONANDO</div>
                <div class="metric"><strong>🎯 Hit Rate:</strong> <?php echo round($opcacheStatus['opcache_statistics']['opcache_hit_rate'], 2); ?>%</div>
                <div class="metric"><strong>💾 Memoria Usada:</strong> <?php echo round($opcacheStatus['memory_usage']['used_memory'] / 1024 / 1024, 2); ?> MB</div>
                <div class="metric"><strong>📁 Archivos Cached:</strong> <?php echo $opcacheStatus['opcache_statistics']['num_cached_scripts']; ?></div>
                <div class="metric"><strong>🔥 Cache Hits:</strong> <?php echo number_format($opcacheStatus['opcache_statistics']['hits']); ?></div>
                <div class="metric"><strong>❌ Cache Misses:</strong> <?php echo number_format($opcacheStatus['opcache_statistics']['misses']); ?></div>
            <?php elseif (extension_loaded('opcache')): ?>
                <div class="metric"><strong>⚠️ Estado:</strong> EXTENSIÓN CARGADA PERO DESHABILITADA</div>
                <div class="metric"><strong>📝 Configuración:</strong> opcache.enable = <?php echo ini_get('opcache.enable') ? 'ON' : 'OFF'; ?></div>
            <?php else: ?>
                <div class="metric"><strong>❌ Estado:</strong> EXTENSIÓN NO DISPONIBLE</div>
                <div class="metric"><strong>📝 Acción:</strong> Instalar extensión OPcache de PHP</div>
            <?php endif; ?>
        </div>
        
        <!-- Configuración de PHP -->
        <div class="status-card status-warning">
            <h3>⚙️ Configuración de PHP</h3>
            <?php
            $configs = [
                'memory_limit' => ['current' => ini_get('memory_limit'), 'optimal' => '512M', 'points' => 10],
                'max_execution_time' => ['current' => ini_get('max_execution_time'), 'optimal' => '60', 'points' => 5],
                'post_max_size' => ['current' => ini_get('post_max_size'), 'optimal' => '32M', 'points' => 5],
                'upload_max_filesize' => ['current' => ini_get('upload_max_filesize'), 'optimal' => '32M', 'points' => 5]
            ];
            
            foreach ($configs as $config => $details):
                $isOptimal = false;
                if ($config === 'memory_limit' && (int)$details['current'] >= 512) $isOptimal = true;
                elseif ($config === 'max_execution_time' && (int)$details['current'] >= 60) $isOptimal = true;
                elseif (($config === 'post_max_size' || $config === 'upload_max_filesize') && (int)$details['current'] >= 32) $isOptimal = true;
                
                if ($isOptimal) $score += $details['points'];
            ?>
                <div class="metric">
                    <strong><?php echo $isOptimal ? '✅' : '⚠️'; ?> <?php echo $config; ?>:</strong> 
                    <?php echo $details['current']; ?> 
                    <?php if (!$isOptimal): ?>
                        (recomendado: <?php echo $details['optimal']; ?>)
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Test de rendimiento -->
        <div class="status-card status-success">
            <h3>⚡ Test de Rendimiento</h3>
            <?php
            // Test simple de rendimiento
            $startTime = microtime(true);
            
            // Simulación de operaciones PHP
            for ($i = 0; $i < 10000; $i++) {
                $test = md5($i . 'test_performance');
            }
            
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000; // en milisegundos
            
            if ($executionTime < 50) {
                $score += 20;
                $performanceStatus = "✅ EXCELENTE";
                $performanceClass = "text-success";
            } elseif ($executionTime < 100) {
                $score += 15;
                $performanceStatus = "✅ BUENO";
                $performanceClass = "text-warning";
            } else {
                $score += 5;
                $performanceStatus = "⚠️ MEJORABLE";
                $performanceClass = "text-danger";
            }
            ?>
            <div class="metric">
                <strong>🕐 Tiempo de ejecución:</strong> 
                <span class="<?php echo $performanceClass; ?>">
                    <?php echo round($executionTime, 2); ?> ms (<?php echo $performanceStatus; ?>)
                </span>
            </div>
        </div>
        
        <!-- Puntuación final -->
        <?php
        $scoreClass = $score >= 80 ? 'score-excellent' : ($score >= 60 ? 'score-good' : 'score-poor');
        $scoreText = $score >= 80 ? 'EXCELENTE' : ($score >= 60 ? 'BUENO' : 'NECESITA MEJORAS');
        ?>
        
        <div class="status-card text-center">
            <h3>🎯 Puntuación de Rendimiento</h3>
            <div class="score <?php echo $scoreClass; ?>">
                <?php echo $score; ?>/<?php echo $maxScore; ?>
            </div>
            <h4 class="<?php echo $scoreClass === 'score-excellent' ? 'text-success' : ($scoreClass === 'score-good' ? 'text-warning' : 'text-danger'); ?>">
                <?php echo $scoreText; ?>
            </h4>
            
            <?php if ($score < 80): ?>
                <div class="mt-3">
                    <h5>🔧 Recomendaciones:</h5>
                    <ul class="text-left">
                        <?php if (!$opcacheEnabled): ?>
                            <li>Habilitar OPcache (+50 puntos)</li>
                        <?php endif; ?>
                        <?php if ((int)ini_get('max_execution_time') < 60): ?>
                            <li>Aumentar max_execution_time a 60s (+5 puntos)</li>
                        <?php endif; ?>
                        <?php if ((int)ini_get('post_max_size') < 32): ?>
                            <li>Aumentar post_max_size a 32M (+5 puntos)</li>
                        <?php endif; ?>
                        <?php if ((int)ini_get('upload_max_filesize') < 32): ?>
                            <li>Aumentar upload_max_filesize a 32M (+5 puntos)</li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Información del sistema -->
        <div class="status-card status-warning">
            <h3>💻 Información del Sistema</h3>
            <div class="metric"><strong>🐘 Versión PHP:</strong> <?php echo PHP_VERSION; ?></div>
            <div class="metric"><strong>💾 Memoria actual:</strong> <?php echo round(memory_get_usage() / 1024 / 1024, 2); ?> MB</div>
            <div class="metric"><strong>📊 Pico de memoria:</strong> <?php echo round(memory_get_peak_usage() / 1024 / 1024, 2); ?> MB</div>
            <div class="metric"><strong>🕐 Tiempo de generación:</strong> <?php echo round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2); ?> ms</div>
        </div>
        
        <div class="text-center mt-4">
            <a href="facturacion.php" class="btn btn-primary">🧾 Probar Facturación</a>
            <a href="Citas.php" class="btn btn-success">📅 Probar Citas</a>
            <a href="usuarios.php" class="btn btn-info">👥 Probar Usuarios</a>
            <a href="index.php" class="btn btn-secondary">🏠 Volver al Inicio</a>
        </div>
    </div>
</body>
</html>
