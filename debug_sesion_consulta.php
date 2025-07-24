<?php
// Test para verificar problemas de sesión en nueva_consulta.php
require_once 'session_config.php';
session_start();

echo "<h3>🔍 Diagnóstico de Sesión - Nueva Consulta</h3>";

// Simular una sesión activa para las pruebas
if (!isset($_SESSION['loggedin'])) {
    $_SESSION['loggedin'] = true;
    $_SESSION['username'] = 'test_user';
    $_SESSION['id'] = 1;
    $_SESSION['rol'] = 'admin';
    echo "<div style='color: orange;'>⚠️ Sesión simulada creada para testing</div>";
}

echo "<h4>📊 Estado Actual de la Sesión:</h4>";
echo "<ul>";
echo "<li><strong>Session ID:</strong> " . session_id() . "</li>";
echo "<li><strong>Session Name:</strong> " . session_name() . "</li>";
echo "<li><strong>Loggedin:</strong> " . (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? 'SÍ' : 'NO') . "</li>";
echo "<li><strong>Username:</strong> " . ($_SESSION['username'] ?? 'No definido') . "</li>";
echo "<li><strong>User ID:</strong> " . ($_SESSION['id'] ?? 'No definido') . "</li>";
echo "<li><strong>Rol:</strong> " . ($_SESSION['rol'] ?? 'No definido') . "</li>";
echo "</ul>";

echo "<h4>🧪 Pruebas de Funcionalidad:</h4>";

// Test 1: Verificar incluir config.php
echo "<div style='background: #f8f9fa; padding: 10px; margin: 10px 0; border-left: 4px solid #007bff;'>";
echo "<h5>Test 1: Conexión a Base de Datos</h5>";
try {
    require_once "config.php";
    echo "<span style='color: green;'>✅ config.php cargado correctamente</span><br>";
    echo "<span style='color: green;'>✅ Conexión a BD establecida</span><br>";
    
    // Test de consulta simple
    $stmt = $conn->query("SELECT COUNT(*) as count FROM pacientes");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<span style='color: green;'>✅ Consulta de prueba exitosa. Pacientes en BD: " . $result['count'] . "</span><br>";
} catch (Exception $e) {
    echo "<span style='color: red;'>❌ Error: " . $e->getMessage() . "</span><br>";
}
echo "</div>";

// Test 2: Simular guardado de consulta
echo "<div style='background: #f8f9fa; padding: 10px; margin: 10px 0; border-left: 4px solid #28a745;'>";
echo "<h5>Test 2: Simulación de Transacción</h5>";
try {
    $conn->beginTransaction();
    echo "<span style='color: green;'>✅ Transacción iniciada</span><br>";
    
    // Verificar configuración
    $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        echo "<span style='color: green;'>✅ Configuración obtenida. Especialidad ID: " . ($config['especialidad_id'] ?? 'NULL') . "</span><br>";
    } else {
        echo "<span style='color: orange;'>⚠️ No se encontró configuración</span><br>";
    }
    
    $conn->rollback(); // No queremos guardar datos reales
    echo "<span style='color: green;'>✅ Rollback exitoso</span><br>";
    
    // Verificar que la sesión siga activa después de la transacción
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
        echo "<span style='color: green;'>✅ Sesión sigue activa después de la transacción</span><br>";
    } else {
        echo "<span style='color: red;'>❌ La sesión se perdió después de la transacción</span><br>";
    }
    
} catch (Exception $e) {
    echo "<span style='color: red;'>❌ Error en transacción: " . $e->getMessage() . "</span><br>";
}
echo "</div>";

// Test 3: Simular redirección
echo "<div style='background: #f8f9fa; padding: 10px; margin: 10px 0; border-left: 4px solid #ffc107;'>";
echo "<h5>Test 3: Verificación de Redirección</h5>";

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
    echo "<span style='color: green;'>✅ Sesión válida para redirección</span><br>";
    echo "<span style='color: blue;'>ℹ️ La redirección a ver_paciente.php debería funcionar</span><br>";
} else {
    echo "<span style='color: red;'>❌ Sesión inválida, se redirigiría al login</span><br>";
}
echo "</div>";

// Test 4: Enlaces de prueba
echo "<div style='background: #f8f9fa; padding: 10px; margin: 10px 0; border-left: 4px solid #dc3545;'>";
echo "<h5>Test 4: Enlaces de Prueba</h5>";
echo "<a href='nueva_consulta.php?paciente_id=1' class='btn btn-primary btn-sm' style='background:#007bff; color:white; padding:5px 10px; text-decoration:none; border-radius:3px;'>Probar Nueva Consulta</a> ";
echo "<a href='ver_paciente.php?id=1' class='btn btn-secondary btn-sm' style='background:#6c757d; color:white; padding:5px 10px; text-decoration:none; border-radius:3px;'>Ver Paciente</a> ";
echo "<a href='imprimir_receta.php?id=1' class='btn btn-info btn-sm' style='background:#17a2b8; color:white; padding:5px 10px; text-decoration:none; border-radius:3px;'>Imprimir Receta</a>";
echo "</div>";

echo "<h4>💡 Recomendaciones:</h4>";
echo "<ul>";
echo "<li>Verifica que todas las páginas incluyan <code>session_config.php</code> antes de <code>session_start()</code></li>";
echo "<li>Asegúrate de que no haya <code>session_destroy()</code> o <code>session_unset()</code> no intencionados</li>";
echo "<li>Revisa que no haya conflictos en los headers o redirecciones</li>";
echo "<li>Verifica los logs de error de PHP para más detalles</li>";
echo "</ul>";

echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
echo "<h5>🎯 Próximos Pasos:</h5>";
echo "<ol>";
echo "<li>Prueba crear una consulta y observa si se mantiene la sesión</li>";
echo "<li>Revisa los logs de error de PHP si hay problemas</li>";
echo "<li>Si el problema persiste, cambia la redirección para quedarse en la misma página</li>";
echo "</ol>";
echo "</div>";
?>
