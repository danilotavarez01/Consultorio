<?php
require_once 'config.php';

echo "=== VERIFICACIÓN DE DATOS PARA TEST ===\n";

// Verificar pacientes
$stmt = $conn->query('SELECT COUNT(*) as total FROM pacientes');
$pacientes = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Pacientes en BD: " . $pacientes['total'] . "\n";

// Verificar usuarios/médicos
$stmt = $conn->query('SELECT COUNT(*) as total FROM usuarios');
$usuarios = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Usuarios en BD: " . $usuarios['total'] . "\n";

// Verificar facturas
$stmt = $conn->query('SELECT COUNT(*) as total FROM facturas');
$facturas = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Facturas en BD: " . $facturas['total'] . "\n";

// Verificar pagos
$stmt = $conn->query('SELECT COUNT(*) as total FROM pagos');
$pagos = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Pagos en BD: " . $pagos['total'] . "\n";

// Si hay pocos datos, mostrar contenido
if ($pacientes['total'] < 3) {
    echo "\n=== PACIENTES EXISTENTES ===\n";
    $stmt = $conn->query('SELECT id, nombre, apellido, dni FROM pacientes LIMIT 5');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']}, Nombre: {$row['nombre']} {$row['apellido']}, DNI: {$row['dni']}\n";
    }
}

if ($usuarios['total'] < 3) {
    echo "\n=== USUARIOS EXISTENTES ===\n";
    $stmt = $conn->query('SELECT id, nombre, username FROM usuarios LIMIT 5');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']}, Nombre: {$row['nombre']}, Usuario: {$row['username']}\n";
    }
}

echo "\n=== ÚLTIMAS FACTURAS ===\n";
$stmt = $conn->query('SELECT f.id, f.numero_factura, f.total, f.estado, p.nombre, p.apellido FROM facturas f LEFT JOIN pacientes p ON f.paciente_id = p.id ORDER BY f.id DESC LIMIT 3');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Factura {$row['id']}: {$row['numero_factura']} - {$row['nombre']} {$row['apellido']} - \${$row['total']} - {$row['estado']}\n";
}

echo "\n=== ÚLTIMOS PAGOS ===\n";
$stmt = $conn->query('SELECT p.id, p.monto, p.metodo_pago, f.numero_factura, DATE_FORMAT(p.fecha_pago, \'%Y-%m-%d %H:%i\') as fecha FROM pagos p LEFT JOIN facturas f ON p.factura_id = f.id ORDER BY p.id DESC LIMIT 3');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Pago {$row['id']}: \${$row['monto']} - {$row['metodo_pago']} - {$row['numero_factura']} - {$row['fecha']}\n";
}

echo "\n=== TEST COMPLETADO ===\n";
?>
