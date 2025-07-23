<?php
// Script para verificar la ruta de la imagen del paciente
session_start();
require_once "config.php";
require_once "permissions.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    echo "<p>Error: Necesitas iniciar sesión para usar esta herramienta.</p>";
    echo "<p><a href='login.php'>Iniciar sesión</a></p>";
    exit;
}

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Verificación de Rutas de Imágenes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .path { background: #f5f5f5; padding: 5px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>Verificación de Rutas de Imágenes</h1>";

// Función para verificar una ruta
function checkPath($path, $description) {
    echo "<div style='margin-bottom: 10px;'>";
    echo "<p><strong>$description:</strong> <span class='path'>$path</span></p>";
    
    if (file_exists($path)) {
        echo "<p class='success'>✓ El archivo/directorio existe</p>";
        
        if (is_dir($path)) {
            echo "<p class='info'>Es un directorio</p>";
            
            // Listar contenido del directorio
            $files = scandir($path);
            echo "<p>Contenido (" . (count($files) - 2) . " archivos/directorios):</p>";
            echo "<ul>";
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $fullPath = $path . '/' . $file;
                    $type = is_dir($fullPath) ? "directorio" : "archivo";
                    $size = is_file($fullPath) ? " - " . filesize($fullPath) . " bytes" : "";
                    echo "<li>$file ($type$size)</li>";
                }
            }
            echo "</ul>";
        } else {
            echo "<p class='info'>Es un archivo</p>";
            echo "<p>Tamaño: " . filesize($path) . " bytes</p>";
            
            // Si es una imagen, intentar mostrarla
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                echo "<p>Vista previa:</p>";
                echo "<img src='$path' style='max-width: 300px; border: 1px solid #ccc;' alt='Vista previa'>";
            }
        }
    } else {
        echo "<p class='error'>✗ El archivo/directorio NO existe</p>";
        
        // Verificar si el directorio padre existe
        $parent = dirname($path);
        if (file_exists($parent)) {
            echo "<p class='info'>El directorio padre ($parent) existe</p>";
        } else {
            echo "<p class='error'>El directorio padre ($parent) NO existe</p>";
        }
    }
    
    echo "</div>";
    echo "<hr>";
}

// Verificar la ruta de uploads/pacientes
$uploadDir = "uploads/pacientes/";
checkPath($uploadDir, "Directorio de uploads de pacientes (relativo)");

// Verificar la ruta absoluta
$absUploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Consultorio2/' . $uploadDir;
checkPath($absUploadDir, "Directorio de uploads de pacientes (absoluto)");

// Obtener un paciente con foto
try {
    $sql = "SELECT id, nombre, apellido, foto FROM pacientes WHERE foto IS NOT NULL LIMIT 1";
    $stmt = $conn->query($sql);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($paciente) {
        echo "<h2>Verificando foto para el paciente: " . htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']) . "</h2>";
        
        $fotoRelativa = $uploadDir . $paciente['foto'];
        $fotoAbsoluta = $absUploadDir . $paciente['foto'];
        
        checkPath($fotoRelativa, "Ruta de la imagen (relativa)");
        checkPath($fotoAbsoluta, "Ruta de la imagen (absoluta)");
        
        // Añadir prueba de imagen con tag HTML
        echo "<h3>Probando etiqueta img HTML:</h3>";
        echo "<p>Con ruta relativa:</p>";
        echo "<img src='$fotoRelativa' style='max-width: 300px; border: 1px solid #ccc;' alt='Foto con ruta relativa'>";
        
        echo "<p>Con ruta desde la raíz del sitio:</p>";
        echo "<img src='/$fotoRelativa' style='max-width: 300px; border: 1px solid #ccc;' alt='Foto con ruta desde raíz'>";
        
        // Añadir un código JS para verificar carga de imagen
        echo "<script>
        function checkImageLoaded(imgElement, description) {
            imgElement.onload = function() {
                console.log('Imagen cargada exitosamente: ' + description);
                document.getElementById('jsResult').innerHTML += '<p class=\"success\">✓ Imagen cargada: ' + description + '</p>';
            };
            imgElement.onerror = function() {
                console.error('Error al cargar imagen: ' + description);
                document.getElementById('jsResult').innerHTML += '<p class=\"error\">✗ Error al cargar imagen: ' + description + '</p>';
            };
        }
        
        window.onload = function() {
            var img1 = document.createElement('img');
            img1.src = '$fotoRelativa';
            checkImageLoaded(img1, 'Ruta relativa');
            
            var img2 = document.createElement('img');
            img2.src = '/$fotoRelativa';
            checkImageLoaded(img2, 'Ruta desde raíz');
        };
        </script>";
        
        echo "<div id='jsResult'><h3>Resultados de carga de imagen (JavaScript):</h3></div>";
    } else {
        echo "<p class='error'>No se encontraron pacientes con foto.</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
?>
