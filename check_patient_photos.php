<?php
// Script para verificar fotos de pacientes en la base de datos
require_once "config.php";

try {
    // Verificar cuántos pacientes tienen foto
    $sql = "SELECT id, nombre, apellido, foto FROM pacientes WHERE foto IS NOT NULL";
    $stmt = $conn->query($sql);
    $pacientesConFoto = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Pacientes con foto: " . count($pacientesConFoto) . "\n";
    
    foreach ($pacientesConFoto as $paciente) {
        echo "ID: " . $paciente['id'] . ", Nombre: " . $paciente['nombre'] . " " . $paciente['apellido'] . ", Foto: " . $paciente['foto'] . "\n";
    }
    
    // Verificar carpeta de fotos
    $uploadDir = 'uploads/pacientes/';
    if (file_exists($uploadDir)) {
        echo "Directorio " . $uploadDir . " existe.\n";
        $fotos = glob($uploadDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        echo "Fotos encontradas: " . count($fotos) . "\n";
        
        foreach ($fotos as $foto) {
            echo basename($foto) . "\n";
        }
    } else {
        echo "El directorio " . $uploadDir . " no existe.\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
