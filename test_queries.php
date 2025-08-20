<?php
require_once 'config.php';

try {
    echo "Probando consulta de pacientes...\n";
    $stmt_pacientes = $conn->query("SELECT id, nombre, apellido FROM pacientes LIMIT 5");
    $pacientes = $stmt_pacientes->fetchAll(PDO::FETCH_ASSOC);
    echo "✓ Consulta de pacientes exitosa: " . count($pacientes) . " registros encontrados\n";
    
    echo "\nProbando consulta de usuarios (doctores)...\n";
    $stmt_usuarios = $conn->query("SELECT id, nombre, username FROM usuarios WHERE active = 1 LIMIT 5");
    $usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);
    echo "✓ Consulta de usuarios exitosa: " . count($usuarios) . " registros encontrados\n";
    
    echo "\nProbando consulta de citas...\n";
    $stmt_citas = $conn->query("SELECT id, fecha, hora, paciente_id, doctor_id FROM citas ORDER BY fecha DESC LIMIT 5");
    $citas = $stmt_citas->fetchAll(PDO::FETCH_ASSOC);
    echo "✓ Consulta de citas exitosa: " . count($citas) . " registros encontrados\n";
    
    echo "\n✅ Todas las consultas principales funcionan correctamente!\n";
    
} catch (PDOException $e) {
    echo "❌ Error de base de datos: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
}
?>
