<?php
// Debug específico para problema de odontograma no se muestra
require_once "config.php";

$id = $_GET['id'] ?? 31; // ID por defecto

echo "<h1>🔬 Debug Específico: ¿Por qué no se muestra el odontograma?</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .debug{background:#fff3cd;padding:15px;border-radius:5px;margin:10px 0;} .error{background:#f8d7da;} .success{background:#d4edda;}</style>";

echo "<p><strong>Analizando consulta ID:</strong> $id</p>";

try {
    // 1. Obtener datos de la consulta
    $sql = "SELECT h.*, p.nombre, p.apellido FROM historial_medico h JOIN pacientes p ON h.paciente_id = p.id WHERE h.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$consulta) {
        echo "<div class='debug error'><h3>❌ Consulta no encontrada</h3></div>";
        exit;
    }
    
    echo "<div class='debug'>";
    echo "<h3>📋 1. Datos de la consulta</h3>";
    echo "<p><strong>Paciente:</strong> {$consulta['nombre']} {$consulta['apellido']}</p>";
    echo "<p><strong>Fecha:</strong> {$consulta['fecha']}</p>";
    echo "<p><strong>Campo dientes_seleccionados:</strong> '" . ($consulta['dientes_seleccionados'] ?? 'NULL') . "'</p>";
    echo "<p><strong>Longitud:</strong> " . strlen($consulta['dientes_seleccionados'] ?? '') . " caracteres</p>";
    echo "</div>";
    
    // 2. Verificar dientes
    $tiene_dientes = !empty($consulta['dientes_seleccionados']) && trim($consulta['dientes_seleccionados']) !== '';
    echo "<div class='debug " . ($tiene_dientes ? "success" : "error") . "'>";
    echo "<h3>🦷 2. Verificación de dientes</h3>";
    echo "<p><strong>empty():</strong> " . (empty($consulta['dientes_seleccionados']) ? 'TRUE (vacío)' : 'FALSE (no vacío)') . "</p>";
    echo "<p><strong>trim() !== '':</strong> " . ((trim($consulta['dientes_seleccionados'] ?? '') !== '') ? 'TRUE (tiene contenido)' : 'FALSE (solo espacios)') . "</p>";
    echo "<p><strong>Resultado \$tiene_dientes:</strong> " . ($tiene_dientes ? 'TRUE ✅' : 'FALSE ❌') . "</p>";
    echo "</div>";
    
    // 3. Obtener configuración
    $stmt = $conn->query("SELECT medico_nombre, especialidad_id FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<div class='debug'>";
    echo "<h3>⚙️ 3. Configuración del consultorio</h3>";
    echo "<p><strong>Especialidad ID:</strong> " . ($config['especialidad_id'] ?? 'NULL') . "</p>";
    echo "</div>";
    
    // 4. Obtener información de especialidad
    $especialidad_info = null;
    $es_odontologia = false;
    
    if ($config && $config['especialidad_id']) {
        $stmt = $conn->prepare("SELECT codigo, nombre FROM especialidades WHERE id = ?");
        $stmt->execute([$config['especialidad_id']]);
        $especialidad_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($especialidad_info) {
            $es_odontologia = stripos($especialidad_info['nombre'], 'odont') !== false || 
                             stripos($especialidad_info['codigo'], 'odon') !== false;
        }
    }
    
    echo "<div class='debug " . ($es_odontologia ? "success" : "error") . "'>";
    echo "<h3>🏥 4. Información de especialidad</h3>";
    if ($especialidad_info) {
        echo "<p><strong>Nombre:</strong> {$especialidad_info['nombre']}</p>";
        echo "<p><strong>Código:</strong> {$especialidad_info['codigo']}</p>";
        echo "<p><strong>Contiene 'odont':</strong> " . (stripos($especialidad_info['nombre'], 'odont') !== false ? 'SÍ' : 'NO') . "</p>";
        echo "<p><strong>Contiene 'odon':</strong> " . (stripos($especialidad_info['codigo'], 'odon') !== false ? 'SÍ' : 'NO') . "</p>";
        echo "<p><strong>Es odontología:</strong> " . ($es_odontologia ? 'SÍ ✅' : 'NO ❌') . "</p>";
    } else {
        echo "<p><strong>⚠️ No se encontró información de especialidad</strong></p>";
    }
    echo "</div>";
    
    // 5. Resultado final
    $mostrar_odontograma = $tiene_dientes && $es_odontologia;
    echo "<div class='debug " . ($mostrar_odontograma ? "success" : "error") . "'>";
    echo "<h3>🎯 5. Resultado final</h3>";
    echo "<p><strong>Condición: \$tiene_dientes && \$es_odontologia</strong></p>";
    echo "<p><strong>$tiene_dientes && $es_odontologia = </strong>" . ($mostrar_odontograma ? 'TRUE ✅' : 'FALSE ❌') . "</p>";
    echo "<p><strong>¿Se debe mostrar el odontograma?</strong> " . ($mostrar_odontograma ? 'SÍ' : 'NO') . "</p>";
    echo "</div>";
    
    // 6. Acciones recomendadas
    echo "<div class='debug'>";
    echo "<h3>💡 6. Diagnóstico y soluciones</h3>";
    
    if (!$tiene_dientes) {
        echo "<p>❌ <strong>Problema:</strong> No se detectan dientes seleccionados</p>";
        if (empty($consulta['dientes_seleccionados'])) {
            echo "<p>🔧 <strong>Causa:</strong> El campo dientes_seleccionados está vacío</p>";
            echo "<p>📝 <strong>Solución:</strong> Esta consulta no tiene dientes seleccionados. Ve a editarla y selecciona dientes en el odontograma.</p>";
        } else {
            echo "<p>🔧 <strong>Causa:</strong> El campo contiene solo espacios en blanco</p>";
            echo "<p>📝 <strong>Solución:</strong> Limpiar el campo y volver a seleccionar dientes.</p>";
        }
    }
    
    if (!$es_odontologia) {
        echo "<p>❌ <strong>Problema:</strong> La especialidad no es odontología</p>";
        echo "<p>🔧 <strong>Causa:</strong> La especialidad configurada no contiene 'odont' ni 'odon'</p>";
        echo "<p>📝 <strong>Solución:</strong> Ve a configuración y cambia la especialidad a 'Odontología' o similar.</p>";
    }
    
    if ($mostrar_odontograma) {
        echo "<p>✅ <strong>Todo correcto:</strong> El odontograma DEBERÍA mostrarse</p>";
        echo "<p>🔧 <strong>Si no se muestra:</strong> Revisar errores JavaScript en la consola del navegador (F12)</p>";
    }
    echo "</div>";
    
    echo "<hr>";
    echo "<h3>🔗 Enlaces de prueba</h3>";
    echo "<p><a href='ver_consulta.php?id=$id' target='_blank' style='padding:10px 15px; background:#007bff; color:white; text-decoration:none; border-radius:5px;'>Ver consulta original</a></p>";
    echo "<p><a href='configuracion.php' target='_blank' style='padding:10px 15px; background:#28a745; color:white; text-decoration:none; border-radius:5px;'>Ir a configuración</a></p>";
    echo "<p><a href='test_especialidad_odontograma.php' target='_blank' style='padding:10px 15px; background:#6c757d; color:white; text-decoration:none; border-radius:5px;'>Cambiar especialidad temporalmente</a></p>";
    
} catch (Exception $e) {
    echo "<div class='debug error'><h3>❌ Error</h3><p>" . $e->getMessage() . "</p></div>";
}
?>
