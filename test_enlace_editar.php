<?php
require_once 'session_config.php';
session_start();
require_once "permissions.php";

echo "<h2>🧪 Simulador de Enlace Editar Consulta</h2>";
echo "<div style='padding: 20px; font-family: Arial;'>";

// Simular exactamente lo que hace el enlace desde ver_paciente.php
echo "<h3>🔗 Simulando enlace desde ver_paciente.php</h3>";

// Verificar estado de sesión ANTES de intentar acceder
echo "<h4>Estado ANTES de acceder a editar_consulta.php:</h4>";
echo "<p><strong>Sesión activa:</strong> " . (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true ? '✅ SÍ' : '❌ NO') . "</p>";

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    echo "<p><strong>Usuario:</strong> " . ($_SESSION["username"] ?? 'No definido') . "</p>";
    
    // Verificar permisos
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        echo "<p style='color: red;'>❌ FALLO: Verificación de sesión falló</p>";
    } else {
        echo "<p style='color: green;'>✅ Verificación de sesión pasó</p>";
    }

    // Verificar permisos para gestionar pacientes
    if (!hasPermission('manage_patients')) {
        echo "<p style='color: red;'>❌ FALLO: Sin permisos para manage_patients</p>";
    } else {
        echo "<p style='color: green;'>✅ Verificación de permisos pasó</p>";
    }
    
    // Obtener una consulta de prueba
    require_once "config.php";
    try {
        $stmt = $conn->query("SELECT h.id, DATE(h.fecha) as fecha, CONCAT(p.nombre, ' ', p.apellido) as paciente 
                             FROM historial_medico h 
                             JOIN pacientes p ON h.paciente_id = p.id 
                             ORDER BY h.id DESC LIMIT 1");
        $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($consulta) {
            echo "<h4>📋 Consulta de prueba encontrada:</h4>";
            echo "<p><strong>ID:</strong> " . $consulta['id'] . "</p>";
            echo "<p><strong>Fecha:</strong> " . $consulta['fecha'] . "</p>";
            echo "<p><strong>Paciente:</strong> " . $consulta['paciente'] . "</p>";
            
            // Generar el enlace exacto que aparece en ver_paciente.php
            $enlace = "editar_consulta.php?id=" . $consulta['id'];
            echo "<h4>🔗 Enlaces de Prueba:</h4>";
            echo "<p><a href='$enlace' target='_blank' style='background: #ffc107; color: #212529; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>📝 Editar Consulta (nueva ventana)</a></p>";
            echo "<p><a href='$enlace' style='background: #ffc107; color: #212529; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>📝 Editar Consulta (misma ventana)</a></p>";
            
            echo "<div style='margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffc107;'>";
            echo "<h4>⚠️ Instrucciones de Prueba:</h4>";
            echo "<ol>";
            echo "<li>Haz clic en cualquiera de los enlaces de arriba</li>";
            echo "<li>Si te redirige al login, el problema está en editar_consulta.php</li>";
            echo "<li>Si funciona correctamente, el problema podría estar en otro lado</li>";
            echo "</ol>";
            echo "</div>";
            
        } else {
            echo "<p style='color: red;'>❌ No hay consultas disponibles para probar</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error al obtener consulta: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ No hay sesión activa - necesitas loguearte primero</p>";
    echo "<p><a href='login.php'>🔑 Ir a Login</a></p>";
}

echo "<h3>🔄 Enlaces de Navegación</h3>";
echo "<a href='debug_editar_consulta.php'>🔍 Debug Completo</a> | ";
echo "<a href='ver_paciente.php?id=1'>👤 Ver Paciente</a> | ";
echo "<a href='pacientes.php'>📋 Lista Pacientes</a>";

echo "</div>";
?>
