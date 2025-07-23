<?php
// Test directo de procedimientos.php
session_start();

// Simular una sesión válida de admin para test
$_SESSION['loggedin'] = true;
$_SESSION['id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['rol'] = 'admin';

echo "<!DOCTYPE html><html><head><title>Test Procedimientos</title></head><body>";
echo "<h2>Test de Acceso Directo a Procedimientos</h2>";
echo "<p><strong>Sesión simulada:</strong></p>";
echo "<ul>";
echo "<li>loggedin: " . ($_SESSION['loggedin'] ? 'true' : 'false') . "</li>";
echo "<li>id: " . $_SESSION['id'] . "</li>";
echo "<li>username: " . $_SESSION['username'] . "</li>";
echo "<li>rol: " . $_SESSION['rol'] . "</li>";
echo "</ul>";

echo "<p><a href='procedimientos.php' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Acceder a Procedimientos</a></p>";

echo "<p><strong>Nota:</strong> Este script establece una sesión válida temporalmente para probar el acceso.</p>";
echo "<p><a href='index.php'>Volver al inicio</a></p>";
echo "</body></html>";
?>
