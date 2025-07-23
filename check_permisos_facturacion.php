<?php
require_once 'config.php';

echo "Verificando permisos actuales de facturación...\n";

try {
    // Verificar permisos existentes de facturación
    $stmt = $conn->query("SELECT nombre, descripcion, categoria FROM permisos WHERE categoria = 'facturacion' ORDER BY nombre");
    $permisos_actuales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Permisos actuales con categoría 'facturacion':\n";
    foreach ($permisos_actuales as $permiso) {
        echo "  - " . $permiso['nombre'] . " (" . $permiso['descripcion'] . ")\n";
    }
    
    // Verificar todas las categorías existentes
    echo "\nCategorías de permisos existentes:\n";
    $stmt = $conn->query("SELECT DISTINCT categoria FROM permisos ORDER BY categoria");
    $categorias = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($categorias as $categoria) {
        echo "  - " . $categoria . "\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
