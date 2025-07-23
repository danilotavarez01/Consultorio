<?php
// Esta es una página simple para diagnosticar problemas
echo "Esta es una página de prueba para el sitio Consultorio Médico";
echo "<br>Fecha: " . date("Y-m-d H:i:s");
echo "<br>PHP Version: " . phpversion();

echo "<h2>Variables de sistema</h2>";
echo "<pre>";
echo "SERVER_NAME: " . $_SERVER['SERVER_NAME'] . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "</pre>";

echo "<h2>Archivos en el directorio</h2>";
echo "<pre>";
$files = scandir(".");
foreach ($files as $file) {
    if ($file != "." && $file != "..") {
        echo $file . " - " . date("Y-m-d H:i:s", filemtime($file)) . "\n";
    }
}
echo "</pre>";

echo "<h2>Enlaces de prueba</h2>";
echo "<a href='index.php'>Inicio</a><br>";
echo "<a href='turnos.php'>Turnos</a><br>";
echo "<a href='Citas.php'>Citas</a><br>";

session_start();
echo "<h2>Variables de sesión</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Verificar permisos
if (file_exists("permissions.php")) {
    require_once "permissions.php";
    echo "<h2>Verificación de permisos</h2>";
    if (function_exists("hasPermission")) {
        echo "Función hasPermission existe<br>";
        echo "manage_appointments: " . (hasPermission('manage_appointments') ? "SÍ" : "NO") . "<br>";
    } else {
        echo "La función hasPermission no existe";
    }
}
?>
