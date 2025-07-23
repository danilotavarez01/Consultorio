<?php
require_once "config.php";

// Obtener algunos pacientes para prueba
$sql = "SELECT id, nombre, apellido, foto FROM pacientes ORDER BY id DESC LIMIT 5";
$stmt = $conn->query($sql);
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>📋 Lista de Pacientes para Prueba</h2>";
echo "<div style='padding: 20px; font-family: Arial;'>";

if ($pacientes) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Foto</th><th>Acciones</th></tr>";
    
    foreach ($pacientes as $paciente) {
        echo "<tr>";
        echo "<td>" . $paciente['id'] . "</td>";
        echo "<td>" . htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']) . "</td>";
        echo "<td>" . ($paciente['foto'] ? htmlspecialchars($paciente['foto']) : 'Sin foto') . "</td>";
        echo "<td>";
        echo "<a href='ver_paciente.php?id=" . $paciente['id'] . "' target='_blank' style='margin-right: 10px;'>Ver Detalles</a>";
        if ($paciente['foto']) {
            echo "<a href='uploads/pacientes/" . htmlspecialchars($paciente['foto']) . "' target='_blank'>Ver Foto</a>";
        }
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No se encontraron pacientes.</p>";
}

echo "</div>";
?>
