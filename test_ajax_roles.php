<?php
// Script de prueba para verificar ajax_gestionar_roles.php

// Simular sesión de usuario administrador autenticado
session_start();
$_SESSION['loggedin'] = true;
$_SESSION['id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['nombre'] = 'Administrador';
$_SESSION['rol'] = 'admin';

// Simular datos POST
$_POST['action'] = 'crear';
$_POST['nombre'] = 'test_rol_' . time();
$_POST['descripcion'] = 'Rol de prueba creado automáticamente';
$_POST['permisos'] = 'ver_pacientes,agregar_pacientes';

echo "=== PRUEBA DE AJAX ROLES ===\n";
echo "Sesión simulada:\n";
echo "- loggedin: " . ($_SESSION['loggedin'] ? 'true' : 'false') . "\n";
echo "- username: " . $_SESSION['username'] . "\n";
echo "- rol: " . $_SESSION['rol'] . "\n";
echo "\nDatos enviados:\n";
print_r($_POST);
echo "\n=== RESPUESTA ===\n";

// Limpiar buffer para capturar solo la salida del AJAX
ob_clean();

// Incluir el archivo AJAX
include 'ajax_gestionar_roles.php';
?>
