<?php
// Test para verificar el comportamiento del odontograma según la especialidad
require_once "config.php";

echo "<h1>🧪 Test: Odontograma por Especialidad</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;} .info{background:#e8f4f8;padding:10px;border-radius:5px;margin:10px 0;}</style>";

// Obtener especialidades disponibles
$stmt = $conn->query("SELECT id, codigo, nombre FROM especialidades ORDER BY nombre");
$especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener configuración actual
$stmt = $conn->query("SELECT especialidad_id, medico_nombre FROM configuracion WHERE id = 1");
$config_actual = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<div class='info'>";
echo "<h3>📋 Configuración actual</h3>";
if ($config_actual && $config_actual['especialidad_id']) {
    $stmt = $conn->prepare("SELECT codigo, nombre FROM especialidades WHERE id = ?");
    $stmt->execute([$config_actual['especialidad_id']]);
    $esp_actual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($esp_actual) {
        echo "<p><strong>Especialidad:</strong> {$esp_actual['nombre']} ({$esp_actual['codigo']})</p>";
        $es_odontologia_actual = stripos($esp_actual['nombre'], 'odont') !== false || stripos($esp_actual['codigo'], 'odon') !== false;
        echo "<p><strong>¿Es odontología?:</strong> " . ($es_odontologia_actual ? 'SÍ' : 'NO') . "</p>";
    }
} else {
    echo "<p class='error'>⚠️ No hay especialidad configurada</p>";
}
echo "</div>";

// Buscar consulta con dientes para probar
$stmt = $conn->query("SELECT id, dientes_seleccionados FROM historial_medico WHERE dientes_seleccionados IS NOT NULL AND dientes_seleccionados != '' ORDER BY id DESC LIMIT 1");
$consulta_test = $stmt->fetch(PDO::FETCH_ASSOC);

if ($consulta_test) {
    echo "<div class='info'>";
    echo "<h3>🔬 Consulta de prueba</h3>";
    echo "<p><strong>ID:</strong> {$consulta_test['id']}</p>";
    echo "<p><strong>Dientes:</strong> {$consulta_test['dientes_seleccionados']}</p>";
    echo "</div>";
    
    echo "<h3>🔄 Simular cambio de especialidades</h3>";
    echo "<p>Cambia temporalmente la especialidad para ver cómo afecta la visualización del odontograma:</p>";
    
    foreach ($especialidades as $esp) {
        $es_odontologia = stripos($esp['nombre'], 'odont') !== false || stripos($esp['codigo'], 'odon') !== false;
        $color = $es_odontologia ? '#d4edda' : '#f8d7da';
        $icon = $es_odontologia ? '✅' : '❌';
        
        echo "<div style='background:$color; padding:10px; margin:5px 0; border-radius:5px;'>";
        echo "<strong>$icon {$esp['nombre']} ({$esp['codigo']})</strong>";
        echo "<form method='POST' style='display:inline; margin-left:10px;'>";
        echo "<input type='hidden' name='cambiar_especialidad' value='{$esp['id']}'>";
        echo "<button type='submit' style='padding:5px 10px; background:#007bff; color:white; border:none; border-radius:3px;'>Probar</button>";
        echo "</form>";
        echo "<br><small>Odontograma: " . ($es_odontologia ? "SE MOSTRARÁ" : "NO se mostrará") . "</small>";
        echo "</div>";
    }
    
    // Procesar cambio de especialidad temporal
    if (isset($_POST['cambiar_especialidad'])) {
        $nueva_especialidad = $_POST['cambiar_especialidad'];
        $stmt = $conn->prepare("UPDATE configuracion SET especialidad_id = ? WHERE id = 1");
        if ($stmt->execute([$nueva_especialidad])) {
            echo "<div style='background:#d1ecf1; padding:15px; border-radius:5px; margin:20px 0;'>";
            echo "✅ <strong>Especialidad cambiada temporalmente</strong><br>";
            
            $stmt = $conn->prepare("SELECT codigo, nombre FROM especialidades WHERE id = ?");
            $stmt->execute([$nueva_especialidad]);
            $nueva_esp = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($nueva_esp) {
                echo "Nueva especialidad: {$nueva_esp['nombre']} ({$nueva_esp['codigo']})<br>";
                $es_nueva_odon = stripos($nueva_esp['nombre'], 'odont') !== false || stripos($nueva_esp['codigo'], 'odon') !== false;
                echo "Odontograma: " . ($es_nueva_odon ? "SE MOSTRARÁ" : "NO se mostrará") . "<br>";
                echo "<a href='ver_consulta.php?id={$consulta_test['id']}' target='_blank' style='padding:8px 15px; background:#28a745; color:white; text-decoration:none; border-radius:5px; margin-top:10px; display:inline-block;'>🔍 Ver consulta con nueva especialidad</a>";
            }
            echo "</div>";
        }
    }
    
    echo "<hr>";
    echo "<h3>🔗 Enlaces de prueba</h3>";
    echo "<ul>";
    echo "<li><a href='ver_consulta.php?id={$consulta_test['id']}' target='_blank'>Ver consulta ID {$consulta_test['id']}</a></li>";
    echo "<li><a href='configuracion.php' target='_blank'>Ir a configuración</a></li>";
    echo "<li><a href='verificar_especialidades.php' target='_blank'>Ver todas las especialidades</a></li>";
    echo "</ul>";
    
    // Botón para restaurar especialidad original
    if ($config_actual && $config_actual['especialidad_id']) {
        echo "<form method='POST' style='margin-top:20px;'>";
        echo "<input type='hidden' name='cambiar_especialidad' value='{$config_actual['especialidad_id']}'>";
        echo "<button type='submit' style='padding:10px 20px; background:#6c757d; color:white; border:none; border-radius:5px;'>🔄 Restaurar especialidad original</button>";
        echo "</form>";
    }
    
} else {
    echo "<div class='info'>";
    echo "<h3>⚠️ Sin consultas para probar</h3>";
    echo "<p>No hay consultas con dientes seleccionados para probar el comportamiento.</p>";
    echo "<a href='nueva_consulta.php'>Crear nueva consulta con dientes</a>";
    echo "</div>";
}
?>
