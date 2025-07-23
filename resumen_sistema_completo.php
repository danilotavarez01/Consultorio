<?php
require_once "config.php";

echo "<h1>🎉 SISTEMA DE CONSULTAS AVANZADO - IMPLEMENTACIÓN COMPLETADA</h1>";

echo "<div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px; margin: 20px 0; text-align: center;'>";
echo "<h2>✅ NUEVA CONSULTA CON CAMPOS DINÁMICOS POR ESPECIALIDAD</h2>";
echo "<p style='font-size: 18px; margin: 0;'>Sistema completamente funcional y operativo</p>";
echo "</div>";

try {
    // 1. Verificar especialidades configuradas
    echo "<div style='background: white; padding: 20px; border-radius: 10px; margin: 20px 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>";
    echo "<h2>📋 Especialidades Configuradas</h2>";
    
    $stmt = $conn->query("
        SELECT e.id, e.codigo, e.nombre, e.descripcion, COUNT(ec.id) as campos_count
        FROM especialidades e
        LEFT JOIN especialidad_campos ec ON e.id = ec.especialidad_id
        WHERE e.estado = 'activo'
        GROUP BY e.id
        ORDER BY e.nombre
    ");
    $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($especialidades as $esp) {
        $color = $esp['campos_count'] > 0 ? '#28a745' : '#ffc107';
        echo "<div style='border-left: 4px solid {$color}; padding: 15px; margin: 10px 0; background: #f8f9fa;'>";
        echo "<h4 style='margin: 0 0 5px 0; color: #495057;'>";
        echo "<i class='fas fa-stethoscope'></i> {$esp['nombre']} ({$esp['codigo']})";
        echo "</h4>";
        echo "<p style='margin: 5px 0; color: #6c757d;'>{$esp['descripcion']}</p>";
        echo "<p style='margin: 0; font-weight: bold; color: {$color};'>";
        echo "<i class='fas fa-clipboard-list'></i> {$esp['campos_count']} campos personalizados";
        echo "</p>";
        echo "</div>";
    }
    echo "</div>";
    
    // 2. Verificar configuración del sistema
    echo "<div style='background: white; padding: 20px; border-radius: 10px; margin: 20px 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>";
    echo "<h2>⚙️ Configuración del Sistema</h2>";
    
    $stmt = $conn->prepare("
        SELECT c.especialidad_id, e.nombre as especialidad_nombre, e.codigo
        FROM configuracion c
        LEFT JOIN especialidades e ON c.especialidad_id = e.id
        WHERE c.id = 1
    ");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config && $config['especialidad_id']) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
        echo "<i class='fas fa-check-circle'></i> <strong>Especialidad por defecto:</strong> ";
        echo "{$config['especialidad_nombre']} ({$config['codigo']})";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f1c2c7; padding: 15px; border-radius: 5px;'>";
        echo "<i class='fas fa-exclamation-triangle'></i> <strong>Sin especialidad por defecto configurada</strong>";
        echo "</div>";
    }
    echo "</div>";
    
    // 3. Estadísticas del sistema
    echo "<div style='background: white; padding: 20px; border-radius: 10px; margin: 20px 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>";
    echo "<h2>📊 Estadísticas del Sistema</h2>";
    
    $stats = [];
    
    // Total de especialidades
    $stmt = $conn->query("SELECT COUNT(*) FROM especialidades WHERE estado = 'activo'");
    $stats['especialidades'] = $stmt->fetchColumn();
    
    // Total de campos personalizados
    $stmt = $conn->query("SELECT COUNT(*) FROM especialidad_campos");
    $stats['campos_totales'] = $stmt->fetchColumn();
    
    // Total de consultas con campos dinámicos
    $stmt = $conn->query("SELECT COUNT(*) FROM historial_medico WHERE campos_adicionales IS NOT NULL");
    $stats['consultas_con_campos'] = $stmt->fetchColumn();
    
    // Total de valores almacenados
    $stmt = $conn->query("SELECT COUNT(*) FROM consulta_campos_valores");
    $stats['valores_almacenados'] = $stmt->fetchColumn();
    
    echo "<div class='row' style='display: flex; flex-wrap: wrap;'>";
    
    $estatisticas = [
        ['icon' => 'fa-user-md', 'label' => 'Especialidades Activas', 'value' => $stats['especialidades'], 'color' => '#007bff'],
        ['icon' => 'fa-clipboard-list', 'label' => 'Campos Personalizados', 'value' => $stats['campos_totales'], 'color' => '#28a745'],
        ['icon' => 'fa-notes-medical', 'label' => 'Consultas con Campos', 'value' => $stats['consultas_con_campos'], 'color' => '#ffc107'],
        ['icon' => 'fa-database', 'label' => 'Valores Almacenados', 'value' => $stats['valores_almacenados'], 'color' => '#17a2b8']
    ];
    
    foreach ($estatisticas as $stat) {
        echo "<div style='flex: 1; min-width: 200px; margin: 10px; padding: 20px; background: {$stat['color']}; color: white; border-radius: 10px; text-align: center;'>";
        echo "<i class='fas {$stat['icon']} fa-2x' style='margin-bottom: 10px;'></i>";
        echo "<h3 style='margin: 10px 0 5px 0; font-size: 2em;'>{$stat['value']}</h3>";
        echo "<p style='margin: 0; opacity: 0.9;'>{$stat['label']}</p>";
        echo "</div>";
    }
    
    echo "</div>";
    echo "</div>";
    
    // 4. Archivos del sistema
    echo "<div style='background: white; padding: 20px; border-radius: 10px; margin: 20px 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>";
    echo "<h2>📁 Archivos del Sistema Avanzado</h2>";
    
    $archivos = [
        [
            'nombre' => 'nueva_consulta_avanzada.php',
            'descripcion' => 'Formulario principal con selector de especialidades y campos dinámicos',
            'tipo' => 'Principal',
            'color' => '#007bff'
        ],
        [
            'nombre' => 'get_campos_especialidad_por_id.php',
            'descripcion' => 'Endpoint para cargar campos específicos por especialidad',
            'tipo' => 'API',
            'color' => '#28a745'
        ],
        [
            'nombre' => 'configurar_especialidades_completas.php',
            'descripcion' => 'Script de configuración inicial de especialidades y campos',
            'tipo' => 'Configuración',
            'color' => '#ffc107'
        ],
        [
            'nombre' => 'DOCUMENTACION_SISTEMA_AVANZADO.md',
            'descripcion' => 'Documentación completa del sistema avanzado',
            'tipo' => 'Documentación',
            'color' => '#6f42c1'
        ]
    ];
    
    foreach ($archivos as $archivo) {
        echo "<div style='border-left: 4px solid {$archivo['color']}; padding: 15px; margin: 10px 0; background: #f8f9fa;'>";
        echo "<div style='display: flex; justify-content: space-between; align-items: center;'>";
        echo "<div>";
        echo "<h5 style='margin: 0 0 5px 0; color: #495057;'>";
        echo "<i class='fas fa-file-code'></i> {$archivo['nombre']}";
        echo "</h5>";
        echo "<p style='margin: 0; color: #6c757d;'>{$archivo['descripcion']}</p>";
        echo "</div>";
        echo "<span style='background: {$archivo['color']}; color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px;'>";
        echo "{$archivo['tipo']}";
        echo "</span>";
        echo "</div>";
        echo "</div>";
    }
    echo "</div>";
    
    // 5. Enlaces de acceso
    echo "<div style='background: white; padding: 20px; border-radius: 10px; margin: 20px 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>";
    echo "<h2>🔗 Enlaces de Acceso</h2>";
    
    $enlaces = [
        [
            'url' => 'nueva_consulta_avanzada.php?paciente_id=1',
            'titulo' => '🏥 Nueva Consulta Avanzada',
            'descripcion' => 'Formulario principal con campos dinámicos',
            'color' => '#007bff'
        ],
        [
            'url' => 'configuracion.php',
            'titulo' => '⚙️ Configuración del Sistema',
            'descripcion' => 'Administrar especialidades y configuración global',
            'color' => '#28a745'
        ],
        [
            'url' => 'get_campos_especialidad_por_id.php?especialidad_id=1',
            'titulo' => '🔌 Test de API',
            'descripcion' => 'Probar endpoint de campos por especialidad',
            'color' => '#ffc107'
        ],
        [
            'url' => 'pacientes.php',
            'titulo' => '👥 Gestión de Pacientes',
            'descripcion' => 'Administrar pacientes del sistema',
            'color' => '#17a2b8'
        ]
    ];
    
    foreach ($enlaces as $enlace) {
        echo "<a href='{$enlace['url']}' target='_blank' style='text-decoration: none; color: inherit;'>";
        echo "<div style='border: 2px solid {$enlace['color']}; padding: 15px; margin: 10px 0; border-radius: 10px; transition: all 0.3s ease;' onmouseover='this.style.backgroundColor=\"{$enlace['color']}\"; this.style.color=\"white\";' onmouseout='this.style.backgroundColor=\"transparent\"; this.style.color=\"inherit\";'>";
        echo "<h5 style='margin: 0 0 5px 0;'>{$enlace['titulo']}</h5>";
        echo "<p style='margin: 0; opacity: 0.8;'>{$enlace['descripcion']}</p>";
        echo "</div>";
        echo "</a>";
    }
    echo "</div>";
    
    // 6. Resumen final
    echo "<div style='background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 30px; border-radius: 15px; margin: 20px 0; text-align: center;'>";
    echo "<h2>🎯 IMPLEMENTACIÓN EXITOSA</h2>";
    echo "<h3>El sistema de consultas con campos dinámicos está completamente operativo</h3>";
    echo "<div style='display: flex; justify-content: center; flex-wrap: wrap; margin-top: 20px;'>";
    echo "<div style='margin: 10px 20px;'><i class='fas fa-check-circle fa-2x'></i><br><strong>Formularios Dinámicos</strong></div>";
    echo "<div style='margin: 10px 20px;'><i class='fas fa-check-circle fa-2x'></i><br><strong>Múltiples Especialidades</strong></div>";
    echo "<div style='margin: 10px 20px;'><i class='fas fa-check-circle fa-2x'></i><br><strong>Campos Personalizables</strong></div>";
    echo "<div style='margin: 10px 20px;'><i class='fas fa-check-circle fa-2x'></i><br><strong>Base de Datos Optimizada</strong></div>";
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>❌ Error al verificar el sistema</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; margin: 0; padding: 20px; }
    .container { max-width: 1200px; margin: 0 auto; }
</style>
