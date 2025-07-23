<?php
require_once 'config.php';

echo "Insertando pacientes de ejemplo...\n";

try {
    // Verificar si ya existen pacientes
    $stmt = $conn->query("SELECT COUNT(*) FROM pacientes");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insertar pacientes de ejemplo
        $pacientes = [
            ['Juan', 'Pérez', '12345678', '1985-03-15', '555-0101', 'juan.perez@email.com', 'Calle 123, Ciudad', 'M'],
            ['María', 'García', '87654321', '1990-07-22', '555-0102', 'maria.garcia@email.com', 'Avenida 456, Ciudad', 'F'],
            ['Carlos', 'López', '11223344', '1978-11-10', '555-0103', 'carlos.lopez@email.com', 'Plaza 789, Ciudad', 'M'],
            ['Ana', 'Martínez', '44332211', '1992-05-18', '555-0104', 'ana.martinez@email.com', 'Boulevard 321, Ciudad', 'F'],
            ['Luis', 'Rodríguez', '55667788', '1988-09-03', '555-0105', 'luis.rodriguez@email.com', 'Paseo 654, Ciudad', 'M']
        ];
        
        $stmt = $conn->prepare("
            INSERT INTO pacientes (nombre, apellido, dni, fecha_nacimiento, telefono, email, direccion, sexo, fecha_registro) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        foreach ($pacientes as $paciente) {
            $stmt->execute($paciente);
            echo "✓ Paciente insertado: " . $paciente[0] . " " . $paciente[1] . "\n";
        }
        
        echo "\n✅ Pacientes de ejemplo insertados exitosamente!\n";
        
        // Verificar la consulta de facturas ahora
        echo "\nVerificando facturas con pacientes actualizados...\n";
        $stmt = $conn->query("
            SELECT f.numero_factura, f.paciente_id, 
                   CONCAT(p.nombre, ' ', p.apellido) as paciente_nombre
            FROM facturas f
            LEFT JOIN pacientes p ON f.paciente_id = p.id
        ");
        $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($facturas as $factura) {
            echo "  - " . $factura['numero_factura'] . 
                 " | Paciente: " . ($factura['paciente_nombre'] ?: 'Sin paciente') . "\n";
        }
        
    } else {
        echo "Ya existen $count pacientes en la base de datos\n";
    }

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
