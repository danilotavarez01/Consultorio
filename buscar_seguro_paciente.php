<?php
// buscar_seguro_paciente.php
require_once "config.php";
if (isset($_POST['paciente_id'])) {
    $paciente_id = intval($_POST['paciente_id']);
    
    // Consulta actualizada para usar la tabla seguro_medico con JOIN
    $stmt = $conn->prepare("SELECT sm.descripcion as seguro_nombre 
                           FROM pacientes p 
                           LEFT JOIN seguro_medico sm ON p.seguro_medico_id = sm.id 
                           WHERE p.id = ? LIMIT 1");
    $stmt->execute([$paciente_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Devolver el nombre del seguro o 'Sin seguro médico' si no tiene
    echo isset($row['seguro_nombre']) && !empty($row['seguro_nombre']) ? $row['seguro_nombre'] : 'Sin seguro médico';
} else {
    echo 'Sin seguro médico';
}
?>
