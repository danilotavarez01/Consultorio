<?php
require_once 'config.php';

echo "Actualizando categoría de permisos de facturación...\n";

try {
    // Actualizar la categoría de facturación para que sea consistente
    $stmt = $conn->prepare("UPDATE permisos SET categoria = 'Facturación' WHERE categoria = 'facturacion'");
    $stmt->execute();
    
    $rows_affected = $stmt->rowCount();
    echo "✓ $rows_affected permisos actualizados de 'facturacion' a 'Facturación'\n";
    
    // Verificar el resultado
    echo "\nVerificando permisos actualizados:\n";
    $stmt = $conn->query("SELECT nombre, descripcion, categoria FROM permisos WHERE categoria = 'Facturación' ORDER BY nombre");
    $permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($permisos as $permiso) {
        echo "  ✓ " . $permiso['nombre'] . " (" . $permiso['descripcion'] . ") - Categoría: " . $permiso['categoria'] . "\n";
    }
    
    // Verificar todas las categorías actualizadas
    echo "\nCategorías de permisos después de la actualización:\n";
    $stmt = $conn->query("SELECT DISTINCT categoria FROM permisos ORDER BY categoria");
    $categorias = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($categorias as $categoria) {
        echo "  - " . $categoria . "\n";
    }
    
    echo "\n✅ Categoría de facturación actualizada exitosamente!\n";
    echo "📋 Ahora 'Facturación' aparecerá correctamente en la gestión de permisos\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
