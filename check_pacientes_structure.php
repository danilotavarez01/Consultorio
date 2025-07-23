<?php
require_once 'config.php';

echo "=== VERIFICANDO ESTRUCTURA DE TABLA PACIENTES ===\n\n";

try {
    $result = $conn->query('DESCRIBE pacientes');
    $columns = [];
    echo "Columnas en la tabla pacientes:\n";
    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
        $columns[] = $row['Field'];
    }
    
    echo "\n=== VERIFICANDO COLUMNAS NECESARIAS ===\n";
    
    $needed_columns = ['cedula', 'documento', 'identificacion'];
    $cedula_column = null;
    
    foreach ($needed_columns as $col) {
        if (in_array($col, $columns)) {
            echo "✓ Encontrada columna: $col\n";
            if ($col === 'cedula' || $col === 'documento' || $col === 'identificacion') {
                $cedula_column = $col;
            }
        }
    }
    
    if (!$cedula_column) {
        echo "\n⚠️  No se encontró columna para cédula/documento\n";
        echo "Columnas disponibles que podrían usarse: " . implode(', ', $columns) . "\n";
    } else {
        echo "\n✓ Usar columna: $cedula_column para cédula/documento\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
