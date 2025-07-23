<?php
echo "<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico Final</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .ok { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block; }
    </style>
</head>
<body>
    <h1>🔧 Diagnóstico de Procedimientos</h1>";

echo "<div class='info'>";
echo "<h3>1. Verificación de Archivos</h3>";
echo file_exists('procedimientos.php') ? "<span class='ok'>✅ procedimientos.php existe</span><br>" : "<span class='error'>❌ procedimientos.php NO existe</span><br>";
echo file_exists('sidebar.php') ? "<span class='ok'>✅ sidebar.php existe</span><br>" : "<span class='error'>❌ sidebar.php NO existe</span><br>";
echo file_exists('config.php') ? "<span class='ok'>✅ config.php existe</span><br>" : "<span class='error'>❌ config.php NO existe</span><br>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>2. Verificación de Base de Datos</h3>";
try {
    require_once 'config.php';
    echo "<span class='ok'>✅ Conexión a BD exitosa</span><br>";
    
    $stmt = $conn->query("SHOW TABLES LIKE 'procedimientos'");
    if ($stmt->rowCount() > 0) {
        echo "<span class='ok'>✅ Tabla 'procedimientos' existe</span><br>";
        
        $count = $conn->query("SELECT COUNT(*) FROM procedimientos")->fetchColumn();
        echo "<span class='ok'>✅ Datos en tabla: $count registros</span><br>";
    } else {
        echo "<span class='error'>❌ Tabla 'procedimientos' NO existe</span><br>";
    }
} catch (Exception $e) {
    echo "<span class='error'>❌ Error BD: " . $e->getMessage() . "</span><br>";
}
echo "</div>";

echo "<div class='info'>";
echo "<h3>3. Verificación de Sesión</h3>";
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
    echo "<span class='ok'>✅ Sesión activa</span><br>";
    echo "Usuario: " . ($_SESSION['username'] ?? 'N/A') . "<br>";
    echo "Rol: " . ($_SESSION['rol'] ?? 'N/A') . "<br>";
} else {
    echo "<span class='error'>❌ No hay sesión activa</span><br>";
    echo "<p><strong>PROBLEMA IDENTIFICADO:</strong> Necesitas iniciar sesión primero</p>";
}
echo "</div>";

echo "<h3>4. Pruebas de Acceso</h3>";
echo "<a href='index.php' class='btn'>🏠 Ir al Inicio (y login)</a>";
echo "<a href='test_sesion_procedimientos.php' class='btn'>🔧 Test con Sesión Simulada</a>";
echo "<a href='procedimientos.php' class='btn'>📋 Intentar Acceso Directo</a>";

echo "<div class='info'>";
echo "<h3>5. Instrucciones</h3>";
echo "<ol>";
echo "<li>Si no hay sesión activa, ve al <strong>Inicio</strong> y haz login</li>";
echo "<li>Una vez logueado como admin, busca 'Procedimientos' en el menú lateral</li>";
echo "<li>Si aún tienes problemas, usa el <strong>Test con Sesión Simulada</strong></li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>
