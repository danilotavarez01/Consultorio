<?php
// Script para probar la visualización de la foto del paciente en la página de detalles
session_start();
require_once "config.php";

// ARCHIVO DE TEST DESACTIVADO PARA EVITAR AUTO-LOGIN
// Para usar este test, descomente las siguientes líneas manualmente:
/*
$_SESSION["loggedin"] = true;
$_SESSION["id"] = 1;
$_SESSION["username"] = "admin";
$_SESSION["role"] = "admin";
*/

require_once "permissions.php";

// Verificación de sesión habilitada
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px;'>";
    echo "<h3>❌ Error: Usuario no autenticado</h3>";
    echo "<p>Este es un archivo de test que requiere autenticación.</p>";
    echo "<a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;'>Ir al Login</a>";
    echo "</div>";
    exit;
}

echo "<h2>Prueba de visualización de foto de paciente</h2>";

try {
    // Buscar un paciente que tenga foto
    $sql = "SELECT id, nombre, apellido, foto FROM pacientes WHERE foto IS NOT NULL LIMIT 1";
    $stmt = $conn->query($sql);
    $pacienteConFoto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($pacienteConFoto) {
        echo "<p>Paciente con foto encontrado: {$pacienteConFoto['nombre']} {$pacienteConFoto['apellido']}</p>";
        echo "<p>ID del paciente: {$pacienteConFoto['id']}</p>";
        echo "<p>Foto actual: {$pacienteConFoto['foto']}</p>";
        
        // Mostrar la imagen
        echo "<img src='uploads/pacientes/{$pacienteConFoto['foto']}' style='max-width: 150px;' alt='Foto del paciente'>";
        
        // Enlace para ver los detalles
        echo "<p><a href='ver_paciente.php?id={$pacienteConFoto['id']}' target='_blank'>Ver detalles de este paciente</a></p>";
    } else {
        echo "<p>No se encontraron pacientes con foto.</p>";
        
        // Buscar cualquier paciente
        $sql = "SELECT id, nombre, apellido FROM pacientes LIMIT 1";
        $stmt = $conn->query($sql);
        $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($paciente) {
            echo "<p>Paciente sin foto encontrado: {$paciente['nombre']} {$paciente['apellido']}</p>";
            echo "<p>ID del paciente: {$paciente['id']}</p>";
            
            // Enlace para ver los detalles
            echo "<p><a href='ver_paciente.php?id={$paciente['id']}' target='_blank'>Ver detalles de este paciente (sin foto)</a></p>";
            
            // Enlace para editar
            echo "<p><a href='editar_paciente.php?id={$paciente['id']}' target='_blank'>Editar este paciente (agregar foto)</a></p>";
        } else {
            echo "<p>No se encontraron pacientes en la base de datos.</p>";
        }
    }
    
    // Verificar carpeta de fotos
    if (file_exists('uploads/pacientes/')) {
        echo "<p>Directorio de fotos de pacientes encontrado.</p>";
        
        $fotos = glob('uploads/pacientes/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        if (count($fotos) > 0) {
            echo "<p>Número de fotos encontradas: " . count($fotos) . "</p>";
        } else {
            echo "<p>No hay fotos en el directorio.</p>";
        }
    } else {
        echo "<p>El directorio de fotos de pacientes no existe.</p>";
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
