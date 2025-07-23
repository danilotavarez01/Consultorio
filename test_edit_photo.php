<?php
// Script para probar la funcionalidad de edición de fotos de pacientes
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
    echo "<p>Para usarlo, debe:</p>";
    echo "<ol>";
    echo "<li>Loguearse normalmente en el sistema</li>";
    echo "<li>O descomentar las líneas de bypass temporal en el código</li>";
    echo "</ol>";
    echo "<a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;'>Ir al Login</a>";
    echo "</div>";
    exit;
}

if (!hasPermission('manage_patients')) {
    echo "Error: No tiene permisos para gestionar pacientes";
    exit;
}

// Probar la búsqueda de un paciente con foto
echo "<h2>Probando funcionalidad de edición de fotos de pacientes</h2>";

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
        
        // Enlace para editar
        echo "<p><a href='editar_paciente.php?id={$pacienteConFoto['id']}' target='_blank'>Editar este paciente</a></p>";
    } else {
        echo "<p>No se encontraron pacientes con foto.</p>";
        
        // Buscar cualquier paciente para edición
        $sql = "SELECT id, nombre, apellido FROM pacientes LIMIT 1";
        $stmt = $conn->query($sql);
        $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($paciente) {
            echo "<p>Paciente sin foto encontrado: {$paciente['nombre']} {$paciente['apellido']}</p>";
            echo "<p>ID del paciente: {$paciente['id']}</p>";
            echo "<p>Este paciente no tiene foto. Puede agregar una en la página de edición.</p>";
            
            // Enlace para editar
            echo "<p><a href='editar_paciente.php?id={$paciente['id']}' target='_blank'>Editar este paciente</a></p>";
        } else {
            echo "<p>No se encontraron pacientes en la base de datos.</p>";
        }
    }
    
    // Comprobar estructura del directorio de fotos
    $directorioPacientes = 'uploads/pacientes/';
    if (file_exists($directorioPacientes)) {
        echo "<p>El directorio para fotos de pacientes existe: $directorioPacientes</p>";
        
        // Listar algunas fotos si existen
        $fotos = glob($directorioPacientes . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        echo "<p>Número de fotos encontradas: " . count($fotos) . "</p>";
        
        if (count($fotos) > 0) {
            echo "<p>Últimas 5 fotos:</p>";
            $fotos = array_slice($fotos, 0, 5);
            foreach ($fotos as $foto) {
                $nombreFoto = basename($foto);
                echo "<p>$nombreFoto</p>";
            }
        }
    } else {
        echo "<p>Advertencia: El directorio para fotos de pacientes no existe: $directorioPacientes</p>";
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
