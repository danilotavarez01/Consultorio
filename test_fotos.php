<?php
require_once "config.php";

// Obtener un paciente con foto para hacer pruebas
$sql = "SELECT id, nombre, apellido, foto FROM pacientes WHERE foto IS NOT NULL AND foto != '' LIMIT 1";
$stmt = $conn->query($sql);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if ($paciente) {
    echo "<h2>🧪 Prueba de Visualización de Fotos</h2>";
    echo "<div style='padding: 20px; font-family: Arial;'>";
    
    echo "<h3>Paciente de prueba:</h3>";
    echo "<p><strong>Nombre:</strong> " . htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']) . "</p>";
    echo "<p><strong>Archivo de foto:</strong> " . htmlspecialchars($paciente['foto']) . "</p>";
    
    // Probar diferentes rutas
    $rutasWeb = [
        'uploads/pacientes/' . $paciente['foto'],
        './uploads/pacientes/' . $paciente['foto'],
        '/uploads/pacientes/' . $paciente['foto'],
        'http://192.168.6.168/uploads/pacientes/' . $paciente['foto']
    ];
    
    echo "<h3>Pruebas de visualización:</h3>";
    
    foreach ($rutasWeb as $index => $ruta) {
        echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px; display: inline-block;'>";
        echo "<h4>Prueba " . ($index + 1) . "</h4>";
        echo "<p><strong>Ruta:</strong> <code>" . htmlspecialchars($ruta) . "</code></p>";
        echo "<img src='$ruta' style='width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd;' ";
        echo "onerror=\"this.style.border='2px solid red'; this.alt='ERROR: No se pudo cargar';\" ";
        echo "onload=\"this.style.border='2px solid green';\" ";
        echo "alt='Foto de prueba'>";
        echo "</div>";
    }
    
    echo "<div style='clear: both; margin-top: 20px;'>";
    echo "<h3>Información técnica:</h3>";
    echo "<p><strong>Directorio actual:</strong> " . __DIR__ . "</p>";
    echo "<p><strong>Archivo existe (ruta relativa):</strong> " . (file_exists('uploads/pacientes/' . $paciente['foto']) ? '✅ SÍ' : '❌ NO') . "</p>";
    echo "<p><strong>Ruta completa del archivo:</strong> " . __DIR__ . '/uploads/pacientes/' . $paciente['foto'] . "</p>";
    echo "<p><strong>Archivo existe (ruta completa):</strong> " . (file_exists(__DIR__ . '/uploads/pacientes/' . $paciente['foto']) ? '✅ SÍ' : '❌ NO') . "</p>";
    
    if (file_exists(__DIR__ . '/uploads/pacientes/' . $paciente['foto'])) {
        $tamano = filesize(__DIR__ . '/uploads/pacientes/' . $paciente['foto']);
        echo "<p><strong>Tamaño del archivo:</strong> " . number_format($tamano) . " bytes</p>";
        
        $info = getimagesize(__DIR__ . '/uploads/pacientes/' . $paciente['foto']);
        if ($info) {
            echo "<p><strong>Dimensiones:</strong> " . $info[0] . " x " . $info[1] . " píxeles</p>";
            echo "<p><strong>Tipo MIME:</strong> " . $info['mime'] . "</p>";
        }
    }
    echo "</div>";
    
    echo "</div>";
} else {
    echo "<h2>❌ No se encontraron pacientes con foto para hacer pruebas</h2>";
    echo "<p>Crear un paciente con foto primero.</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
code { background: #f0f0f0; padding: 2px 5px; border-radius: 3px; }
</style>
