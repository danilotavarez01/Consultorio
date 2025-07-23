<?php
require_once "config.php";

echo "<h1>Verificación de Pacientes - Puerto 83</h1>";

try {
    // Verificar si hay pacientes
    $stmt = $conn->query("SELECT * FROM pacientes LIMIT 5");
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($pacientes)) {
        echo "<p style='color: orange;'>No hay pacientes. Creando paciente de prueba...</p>";
        
        $stmt = $conn->prepare("INSERT INTO pacientes (nombre, apellido, dni, fecha_nacimiento, telefono, email, direccion) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'Juan',
            'Pérez',
            '12345678901',
            '1990-01-01',
            '809-123-4567',
            'juan.perez@email.com',
            'Calle Principal #123'
        ]);
        
        echo "<p style='color: green;'>✓ Paciente de prueba creado</p>";
        
        // Volver a obtener pacientes
        $stmt = $conn->query("SELECT * FROM pacientes LIMIT 5");
        $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo "<h2>Pacientes disponibles:</h2>";
    foreach ($pacientes as $paciente) {
        echo "<p>- ID: {$paciente['id']} - {$paciente['nombre']} {$paciente['apellido']}</p>";
        echo "<p>  <a href='http://localhost:83/Consultorio2/nueva_consulta.php?paciente_id={$paciente['id']}' target='_blank'>Nueva Consulta para este paciente</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
