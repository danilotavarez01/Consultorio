<?php
// Script para verificar la imagen del paciente
require_once "config.php";

// Verificar la imagen del paciente con ID 4
$id = 4;
$html = "<h2>Verificando imagen del paciente ID: $id</h2>";

try {
    // Consulta simple
    $sql = "SELECT id, nombre, apellido, foto FROM pacientes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($paciente) {
        $html .= "<p><strong>Paciente encontrado:</strong> " . htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']) . "</p>";
        if (!empty($paciente['foto'])) {
            $rutaFoto = 'uploads/pacientes/' . $paciente['foto'];
            $html .= "<p><strong>Nombre del archivo de foto:</strong> " . htmlspecialchars($paciente['foto']) . "</p>";
            $html .= "<p><strong>Ruta completa:</strong> " . htmlspecialchars($rutaFoto) . "</p>";
            
            if (file_exists($rutaFoto)) {
                $html .= "<p><strong>El archivo existe:</strong> Sí</p>";
                $html .= "<p><strong>Tamaño del archivo:</strong> " . filesize($rutaFoto) . " bytes</p>";
                $html .= "<p><strong>Permisos:</strong> " . substr(sprintf('%o', fileperms($rutaFoto)), -4) . "</p>";
                $html .= "<p><strong>Visualización de la imagen:</strong></p>";
                $html .= "<img src='" . htmlspecialchars($rutaFoto) . "' style='max-width: 300px;' alt='Foto del paciente'>";
            } else {
                $html .= "<p><strong>El archivo existe:</strong> No</p>";
            }
        } else {
            $html .= "<p><strong>El paciente no tiene foto registrada en la base de datos.</strong></p>";
        }
    } else {
        $html .= "<p><strong>No se encontró el paciente con ID $id</strong></p>";
    }
    
    // Listar todos los archivos en la carpeta
    $carpeta = 'uploads/pacientes/';
    $html .= "<h2>Archivos en la carpeta $carpeta:</h2>";
    
    if (file_exists($carpeta) && is_dir($carpeta)) {
        $archivos = scandir($carpeta);
        if (count($archivos) > 2) { // . y ..
            $html .= "<ul>";
            foreach ($archivos as $archivo) {
                if ($archivo != '.' && $archivo != '..') {
                    $html .= "<li>" . htmlspecialchars($archivo) . " - " . filesize($carpeta . $archivo) . " bytes</li>";
                }
            }
            $html .= "</ul>";
        } else {
            $html .= "<p>No hay archivos en la carpeta.</p>";
        }
    } else {
        $html .= "<p>La carpeta no existe.</p>";
    }
    
} catch (PDOException $e) {
    $html .= "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Verificación de Foto de Paciente</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { color: #2a5885; }
        strong { color: #333; }
        img { border: 1px solid #ddd; padding: 5px; }
    </style>
</head>
<body>
    $html
</body>
</html>";
?>
