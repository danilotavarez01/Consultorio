<?php
require_once 'config.php';

echo "Verificando estructura de la tabla pacientes...\n";

try {
    $stmt = $conn->query('SHOW COLUMNS FROM pacientes');
    echo "Columnas de la tabla pacientes:\n";
    while ($row = $stmt->fetch()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    echo "\nVerificando datos de pacientes...\n";
    $stmt = $conn->query("SELECT id, nombre, apellido FROM pacientes LIMIT 5");
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($pacientes) > 0) {
        echo "Pacientes encontrados:\n";
        foreach ($pacientes as $paciente) {
            echo "  - ID: " . $paciente['id'] . 
                 " | Nombre: " . ($paciente['nombre'] ?? 'NULL') . 
                 " | Apellido: " . ($paciente['apellido'] ?? 'NULL') . "\n";
        }
    } else {
        echo "No hay pacientes en la base de datos\n";
    }
    
    echo "\nVerificando facturas con pacientes...\n";
    $stmt = $conn->query("
        SELECT f.numero_factura, f.paciente_id, p.nombre, p.apellido,
               CONCAT(p.nombre, ' ', p.apellido) as nombre_completo
        FROM facturas f
        LEFT JOIN pacientes p ON f.paciente_id = p.id
        LIMIT 5
    ");
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($facturas) > 0) {
        echo "Facturas con datos de pacientes:\n";
        foreach ($facturas as $factura) {
            echo "  - " . $factura['numero_factura'] . 
                 " | Paciente ID: " . $factura['paciente_id'] . 
                 " | Nombre: " . ($factura['nombre'] ?? 'NULL') . 
                 " | Apellido: " . ($factura['apellido'] ?? 'NULL') . 
                 " | Completo: " . ($factura['nombre_completo'] ?? 'NULL') . "\n";
        }
    } else {
        echo "No hay facturas con datos de pacientes\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
