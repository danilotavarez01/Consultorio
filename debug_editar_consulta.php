<?php
require_once 'session_config.php';
session_start();
require_once "permissions.php";

echo "<h2>🔍 Debug - Acceso a Editar Consulta</h2>";
echo "<div style='padding: 20px; font-family: Arial;'>";

// Verificar estado de sesión
echo "<h3>📊 Estado de Sesión Actual</h3>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Sesión iniciada:</strong> " . (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true ? '✅ SÍ' : '❌ NO') . "</p>";

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    echo "<p><strong>Usuario:</strong> " . ($_SESSION["username"] ?? 'No definido') . "</p>";
    echo "<p><strong>ID Usuario:</strong> " . ($_SESSION["id"] ?? 'No definido') . "</p>";
    echo "<p><strong>Rol:</strong> " . ($_SESSION["rol"] ?? 'No definido') . "</p>";
    
    // Verificar permisos
    echo "<h3>🔐 Verificación de Permisos</h3>";
    $hasPermission = hasPermission('manage_patients');
    echo "<p><strong>Permiso 'manage_patients':</strong> " . ($hasPermission ? '✅ SÍ' : '❌ NO') . "</p>";
    
    if ($hasPermission) {
        echo "<p style='color: green;'>✅ El usuario tiene permisos para editar consultas</p>";
        
        // Verificar si se puede acceder a editar_consulta.php
        echo "<h3>🧪 Test de Acceso a Editar Consulta</h3>";
        
        // Obtener una consulta de prueba
        require_once "config.php";
        try {
            $stmt = $conn->query("SELECT id, DATE(fecha) as fecha FROM historial_medico ORDER BY id DESC LIMIT 1");
            $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($consulta) {
                echo "<p><strong>Consulta de prueba encontrada:</strong> ID " . $consulta['id'] . " (Fecha: " . $consulta['fecha'] . ")</p>";
                echo "<p><a href='editar_consulta.php?id=" . $consulta['id'] . "' target='_blank' class='btn btn-warning'>🔗 Probar Acceso a Editar Consulta</a></p>";
                echo "<p><small><strong>Instrucciones:</strong> Haz clic en el enlace. Si te desloguea, el problema está en editar_consulta.php</small></p>";
            } else {
                echo "<p>❌ No hay consultas disponibles para probar</p>";
            }
        } catch (Exception $e) {
            echo "<p>❌ Error al obtener consulta de prueba: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ El usuario NO tiene permisos para editar consultas</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ No hay sesión activa</p>";
}

echo "<h3>🔄 Enlaces de Navegación</h3>";
echo "<a href='login.php'>🔑 Ir a Login</a> | ";
echo "<a href='ver_paciente.php?id=1'>👤 Ver Paciente (ID: 1)</a> | ";
echo "<a href='pacientes.php'>📋 Lista Pacientes</a>";

echo "</div>";

// CSS básico
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.btn { 
    display: inline-block; 
    padding: 8px 16px; 
    background: #007bff; 
    color: white; 
    text-decoration: none; 
    border-radius: 4px; 
    margin: 5px;
}
.btn-warning { background: #ffc107; color: #212529; }
h2, h3 { color: #333; }
p { margin: 8px 0; }
</style>";
?>
