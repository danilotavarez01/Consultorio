<?php
require_once "config.php";

echo "<h2>🔍 Debug de Fotos de Pacientes</h2>";

// Obtener todos los pacientes con foto
$sql = "SELECT id, nombre, apellido, foto FROM pacientes WHERE foto IS NOT NULL AND foto != '' ORDER BY id DESC LIMIT 10";
$stmt = $conn->query($sql);
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<div style='padding: 20px; font-family: Arial;'>";

foreach ($pacientes as $paciente) {
    echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 15px; background: #f9f9f9;'>";
    echo "<h3>Paciente: " . htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']) . " (ID: " . $paciente['id'] . ")</h3>";
    
    $nombreFoto = $paciente['foto'];
    echo "<p><strong>Nombre del archivo:</strong> " . htmlspecialchars($nombreFoto) . "</p>";
    
    // Verificar diferentes rutas posibles
    $rutas = [
        'uploads/pacientes/' . $nombreFoto,
        './uploads/pacientes/' . $nombreFoto,
        __DIR__ . '/uploads/pacientes/' . $nombreFoto,
        $_SERVER['DOCUMENT_ROOT'] . '/uploads/pacientes/' . $nombreFoto,
        'c:\inetpub\wwwroot\Consultorio2\uploads\pacientes\\' . $nombreFoto
    ];
    
    echo "<h4>Verificación de rutas:</h4>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Ruta</th><th>Existe</th><th>Tamaño</th></tr>";
    
    foreach ($rutas as $ruta) {
        $existe = file_exists($ruta);
        $tamano = $existe ? filesize($ruta) : 'N/A';
        $color = $existe ? 'green' : 'red';
        
        echo "<tr>";
        echo "<td style='font-family: monospace;'>" . htmlspecialchars($ruta) . "</td>";
        echo "<td style='color: $color; font-weight: bold;'>" . ($existe ? 'SÍ' : 'NO') . "</td>";
        echo "<td>" . ($existe ? number_format($tamano) . ' bytes' : 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Mostrar la foto si encontramos una ruta válida
    $rutaValida = null;
    foreach ($rutas as $ruta) {
        if (file_exists($ruta)) {
            // Convertir ruta física a ruta web
            if (strpos($ruta, __DIR__) === 0) {
                $rutaValida = str_replace(__DIR__ . '/', '', $ruta);
                $rutaValida = str_replace('\\', '/', $rutaValida);
                break;
            } elseif (strpos($ruta, 'uploads/') === 0) {
                $rutaValida = $ruta;
                break;
            }
        }
    }
    
    if ($rutaValida) {
        echo "<h4>Vista previa de la foto:</h4>";
        echo "<img src='$rutaValida' style='max-width: 200px; max-height: 200px; border: 1px solid #ccc;' alt='Foto del paciente'>";
        echo "<p style='color: green;'><strong>✅ Ruta web válida:</strong> $rutaValida</p>";
    } else {
        echo "<p style='color: red;'><strong>❌ No se encontró una ruta válida para mostrar la foto</strong></p>";
    }
    
    echo "</div>";
}

echo "</div>";

// Información del sistema
echo "<div style='margin-top: 30px; padding: 15px; background: #e9e9e9;'>";
echo "<h3>📋 Información del Sistema</h3>";
echo "<p><strong>DOCUMENT_ROOT:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>__DIR__:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Script actual:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p><strong>Directorio uploads:</strong> " . (is_dir('uploads/pacientes') ? '✅ Existe' : '❌ No existe') . "</p>";
echo "</div>";
?>
