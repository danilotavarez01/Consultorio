<?php
// Verificar especialidades en la base de datos
require_once "config.php";

echo "<h2>🔍 Especialidades disponibles en el sistema</h2>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;}</style>";

try {
    $stmt = $conn->query("SELECT id, codigo, nombre FROM especialidades ORDER BY nombre");
    $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($especialidades) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Código</th><th>Nombre</th></tr>";
        foreach ($especialidades as $esp) {
            $highlight = (strtolower($esp['nombre']) === 'odontologia' || strtolower($esp['codigo']) === 'odon') ? ' style="background:#e8f5e8;"' : '';
            echo "<tr$highlight>";
            echo "<td>" . $esp['id'] . "</td>";
            echo "<td>" . htmlspecialchars($esp['codigo']) . "</td>";
            echo "<td>" . htmlspecialchars($esp['nombre']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Buscar si existe odontología
        $odontologia = array_filter($especialidades, function($esp) {
            return stripos($esp['nombre'], 'odont') !== false || stripos($esp['codigo'], 'odon') !== false;
        });
        
        if ($odontologia) {
            $odon = array_values($odontologia)[0];
            echo "<div style='background:#e8f5e8;padding:15px;margin:20px 0;border-radius:5px;'>";
            echo "<h3>✅ Odontología encontrada</h3>";
            echo "<p><strong>ID:</strong> {$odon['id']}</p>";
            echo "<p><strong>Código:</strong> {$odon['codigo']}</p>";
            echo "<p><strong>Nombre:</strong> {$odon['nombre']}</p>";
            echo "</div>";
        } else {
            echo "<div style='background:#fff3cd;padding:15px;margin:20px 0;border-radius:5px;'>";
            echo "<h3>⚠️ Odontología no encontrada</h3>";
            echo "<p>No se encontró una especialidad de odontología. Asegúrate de crear una con nombre 'Odontología' o código 'ODON'.</p>";
            echo "</div>";
        }
    } else {
        echo "<p>No hay especialidades registradas</p>";
    }
    
    // Verificar configuración actual
    echo "<h3>📋 Configuración actual del consultorio</h3>";
    $stmt = $conn->query("SELECT especialidad_id, medico_nombre FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        echo "<p><strong>Especialidad ID configurada:</strong> " . ($config['especialidad_id'] ?? 'No configurada') . "</p>";
        echo "<p><strong>Médico:</strong> " . ($config['medico_nombre'] ?? 'No configurado') . "</p>";
        
        if ($config['especialidad_id']) {
            $stmt = $conn->prepare("SELECT codigo, nombre FROM especialidades WHERE id = ?");
            $stmt->execute([$config['especialidad_id']]);
            $esp_actual = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($esp_actual) {
                echo "<p><strong>Especialidad actual:</strong> {$esp_actual['nombre']} ({$esp_actual['codigo']})</p>";
                
                $es_odontologia = stripos($esp_actual['nombre'], 'odont') !== false || stripos($esp_actual['codigo'], 'odon') !== false;
                if ($es_odontologia) {
                    echo "<div style='background:#d4edda;padding:10px;border-radius:5px;'>";
                    echo "✅ <strong>La especialidad actual ES odontología - El odontograma se mostrará</strong>";
                    echo "</div>";
                } else {
                    echo "<div style='background:#f8d7da;padding:10px;border-radius:5px;'>";
                    echo "❌ <strong>La especialidad actual NO es odontología - El odontograma NO se mostrará</strong>";
                    echo "</div>";
                }
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='configuracion.php'>⚙️ Ir a configuración</a></p>";
echo "<p><a href='ver_consulta.php?id=31'>👁️ Probar ver consulta</a></p>";
?>
