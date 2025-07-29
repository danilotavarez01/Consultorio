<?php
// buscar_seguro_paciente.php
require_once "config.php";
if (isset($_POST['paciente_id'])) {
    $paciente_id = intval($_POST['paciente_id']);
    $stmt = $conn->prepare("SELECT seguro_medico FROM pacientes WHERE id = ? LIMIT 1");
    $stmt->execute([$paciente_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo isset($row['seguro_medico']) ? $row['seguro_medico'] : '';
} else {
    echo '';
}
