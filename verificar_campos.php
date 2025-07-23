<?php
require_once "config.php";

echo "<h1>Verificación de Campos de Especialidad</h1>";

try {
    // Verificar la tabla configuracion
    $stmt = $conn->query("SELECT id, nombre_consultorio, especialidad_id FROM configuracion");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Configuración</h2>";
    echo "<pre>";
    print_r($config);
    echo "</pre>";
    
    if ($config && isset($config['especialidad_id'])) {
        // Verificar la especialidad
        $stmt = $conn->prepare("SELECT * FROM especialidades WHERE id = ?");
        $stmt->execute([$config['especialidad_id']]);
        $especialidad = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h2>Especialidad configurada</h2>";
        echo "<pre>";
        print_r($especialidad);
        echo "</pre>";
        
        // Verificar los campos de especialidad
        $stmt = $conn->prepare("
            SELECT * FROM especialidad_campos 
            WHERE especialidad_id = ? AND estado = 'activo'
            ORDER BY orden
        ");
        $stmt->execute([$config['especialidad_id']]);
        $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>Campos de especialidad (" . count($campos) . " encontrados)</h2>";
        echo "<pre>";
        print_r($campos);
        echo "</pre>";
    } else {
        echo "<p>No se encontró una especialidad configurada</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
