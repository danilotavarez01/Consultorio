<?php
// Script para probar la obtención de la foto de un paciente específico
require_once "config.php";

try {
    // Obtener datos del paciente con ID 4 (Danilo Tavarez)
    $id = 4;
    $sql = "SELECT id, nombre, apellido, foto FROM pacientes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($paciente) {
        echo "Paciente encontrado:\n";
        echo "ID: " . $paciente['id'] . "\n";
        echo "Nombre: " . $paciente['nombre'] . " " . $paciente['apellido'] . "\n";
        echo "Foto: " . ($paciente['foto'] ? $paciente['foto'] : "No tiene foto") . "\n";
        
        if ($paciente['foto']) {
            $rutaFoto = 'uploads/pacientes/' . $paciente['foto'];
            echo "Ruta completa: " . $rutaFoto . "\n";
            echo "¿El archivo existe? " . (file_exists($rutaFoto) ? "Sí" : "No") . "\n";
        }
    } else {
        echo "No se encontró el paciente con ID: " . $id . "\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// También probar la consulta con GROUP_CONCAT
try {
    echo "\n--- Prueba con GROUP_CONCAT ---\n";
    $id = 4;
    $sql = "SELECT p.*, GROUP_CONCAT(e.nombre SEPARATOR ', ') as enfermedades 
            FROM pacientes p 
            LEFT JOIN paciente_enfermedades pe ON p.id = pe.paciente_id 
            LEFT JOIN enfermedades e ON pe.enfermedad_id = e.id 
            WHERE p.id = ?
            GROUP BY p.id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($paciente) {
        echo "Paciente encontrado (con GROUP_CONCAT):\n";
        echo "ID: " . $paciente['id'] . "\n";
        echo "Nombre: " . $paciente['nombre'] . " " . $paciente['apellido'] . "\n";
        echo "Foto: " . (isset($paciente['foto']) ? $paciente['foto'] : "Campo 'foto' no presente") . "\n";
        var_dump($paciente);
    } else {
        echo "No se encontró el paciente con ID: " . $id . " usando GROUP_CONCAT\n";
    }
} catch (PDOException $e) {
    echo "Error en la consulta GROUP_CONCAT: " . $e->getMessage() . "\n";
}
?>
