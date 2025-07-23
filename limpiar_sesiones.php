<?php
// Script de limpieza de sesiones antiguas
// Ejecutar regularmente para mantener rendimiento

$tiempo_inicio = microtime(true);

echo "<h2>🧹 LIMPIEZA DE SESIONES ANTIGUAS</h2><hr>";

// Obtener ruta de sesiones
$ruta_sesiones = session_save_path();
if (empty($ruta_sesiones)) {
    $ruta_sesiones = sys_get_temp_dir() . '/consultorio_sessions';
}

echo "<p><strong>Ruta de sesiones:</strong> $ruta_sesiones</p>";

$eliminadas = 0;
$errores = 0;
$tamaño_liberado = 0;

if (is_dir($ruta_sesiones)) {
    $archivos = glob($ruta_sesiones . '/sess_*');
    $total_archivos = count($archivos);
    $ahora = time();
    
    echo "<p><strong>Archivos de sesión encontrados:</strong> $total_archivos</p>";
    
    echo "<h3>Procesando archivos...</h3>";
    echo "<table border='1' style='width:100%; margin:10px 0;'>";
    echo "<tr><th>Archivo</th><th>Antigüedad</th><th>Tamaño</th><th>Acción</th></tr>";
    
    foreach ($archivos as $archivo) {
        if (is_file($archivo)) {
            $tiempo_modificacion = filemtime($archivo);
            $antigüedad_horas = round(($ahora - $tiempo_modificacion) / 3600, 1);
            $tamaño_archivo = filesize($archivo);
            $nombre_archivo = basename($archivo);
            
            // Eliminar sesiones más antiguas que 24 horas
            if (($ahora - $tiempo_modificacion) > 86400) {
                if (unlink($archivo)) {
                    $eliminadas++;
                    $tamaño_liberado += $tamaño_archivo;
                    $accion = "✅ ELIMINADO";
                } else {
                    $errores++;
                    $accion = "❌ ERROR";
                }
            } else {
                $accion = "⏳ CONSERVADO";
            }
            
            echo "<tr>";
            echo "<td>$nombre_archivo</td>";
            echo "<td>{$antigüedad_horas}h</td>";
            echo "<td>" . number_format($tamaño_archivo) . " bytes</td>";
            echo "<td>$accion</td>";
            echo "</tr>";
        }
    }
    
    echo "</table>";
} else {
    echo "<p class='alert alert-warning'>⚠️ Directorio de sesiones no encontrado: $ruta_sesiones</p>";
}

// Limpiar también archivos temporales del sistema
$temp_dir = sys_get_temp_dir();
$archivos_temp = glob($temp_dir . '/php*');
$temp_eliminados = 0;

if (!empty($archivos_temp)) {
    echo "<h3>Limpieza de Archivos Temporales PHP</h3>";
    
    foreach ($archivos_temp as $archivo_temp) {
        if (is_file($archivo_temp)) {
            $tiempo_modificacion = filemtime($archivo_temp);
            $antigüedad = $ahora - $tiempo_modificacion;
            
            // Eliminar archivos temporales más antiguos que 1 hora
            if ($antigüedad > 3600) {
                if (@unlink($archivo_temp)) {
                    $temp_eliminados++;
                    $tamaño_liberado += filesize($archivo_temp);
                }
            }
        }
    }
    
    echo "<p>✅ <strong>Archivos temporales eliminados:</strong> $temp_eliminados</p>";
}

$tiempo_fin = microtime(true);
$tiempo_total = round(($tiempo_fin - $tiempo_inicio) * 1000, 2);
$tamaño_liberado_kb = round($tamaño_liberado / 1024, 2);

echo "<hr>";
echo "<div style='background: #eeffee; padding: 15px; border: 1px solid #00aa00; border-radius: 5px;'>";
echo "<h4>✅ LIMPIEZA COMPLETADA</h4>";
echo "<ul>";
echo "<li><strong>Sesiones eliminadas:</strong> $eliminadas</li>";
echo "<li><strong>Archivos temporales eliminados:</strong> $temp_eliminados</li>";
echo "<li><strong>Errores:</strong> $errores</li>";
echo "<li><strong>Espacio liberado:</strong> {$tamaño_liberado_kb} KB</li>";
echo "<li><strong>Tiempo de ejecución:</strong> {$tiempo_total} ms</li>";
echo "</ul>";
echo "</div>";

// Recomendaciones
echo "<hr>";
echo "<h3>📋 Recomendaciones</h3>";
echo "<ul>";
echo "<li><strong>Frecuencia recomendada:</strong> Ejecutar este script semanalmente</li>";
echo "<li><strong>Automatización:</strong> Configurar como tarea programada</li>";
echo "<li><strong>Monitoreo:</strong> Si se eliminan muchas sesiones, revisar configuración de timeouts</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='diagnostico_rendimiento.php' class='btn btn-info'>🔍 Diagnóstico de Rendimiento</a> ";
echo "<a href='facturacion.php' class='btn btn-success'>← Volver a Facturación</a></p>";
?>
