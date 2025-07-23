<?php
require_once "config.php";

echo "<h2>Verificación de Campos en Base de Datos</h2>";

try {
    // 1. Verificar configuración global
    echo "<h3>1. Configuración Global</h3>";
    $stmt = $conn->prepare("SELECT id, especialidad_id FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        echo "<p><strong>Especialidad configurada:</strong> " . $config['especialidad_id'] . "</p>";
        $especialidad_id = $config['especialidad_id'];
    } else {
        echo "<p style='color: red;'>No hay configuración global encontrada</p>";
        $especialidad_id = null;
    }
    
    // 2. Verificar especialidades disponibles
    echo "<h3>2. Especialidades Disponibles</h3>";
    $stmt = $conn->prepare("SELECT id, nombre FROM especialidades ORDER BY nombre");
    $stmt->execute();
    $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($especialidades) {
        echo "<ul>";
        foreach ($especialidades as $esp) {
            $destacar = ($esp['id'] == $especialidad_id) ? " <strong>(CONFIGURADA)</strong>" : "";
            echo "<li>ID: {$esp['id']} - {$esp['nombre']}{$destacar}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>No hay especialidades en la tabla</p>";
    }
    
    // 3. Verificar campos de especialidad
    echo "<h3>3. Campos de Especialidad</h3>";
    $stmt = $conn->prepare("SELECT * FROM especialidad_campos ORDER BY especialidad_id, orden");
    $stmt->execute();
    $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($campos) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Especialidad ID</th><th>Nombre Campo</th><th>Etiqueta</th><th>Tipo</th><th>Requerido</th><th>Orden</th></tr>";
        
        foreach ($campos as $campo) {
            $destacar = ($campo['especialidad_id'] == $especialidad_id) ? " style='background-color: #ffffcc;'" : "";
            echo "<tr{$destacar}>";
            echo "<td>{$campo['id']}</td>";
            echo "<td>{$campo['especialidad_id']}</td>";
            echo "<td>{$campo['nombre_campo']}</td>";
            echo "<td>{$campo['etiqueta']}</td>";
            echo "<td>{$campo['tipo_campo']}</td>";
            echo "<td>" . ($campo['requerido'] ? 'Sí' : 'No') . "</td>";
            echo "<td>{$campo['orden']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Filtrar solo los campos de la especialidad configurada
        if ($especialidad_id) {
            echo "<h4>Campos para la especialidad configurada (ID: {$especialidad_id}):</h4>";
            $campos_especialidad = array_filter($campos, function($campo) use ($especialidad_id) {
                return $campo['especialidad_id'] == $especialidad_id;
            });
            
            if ($campos_especialidad) {
                echo "<ul>";
                foreach ($campos_especialidad as $campo) {
                    echo "<li><strong>{$campo['etiqueta']}</strong> ({$campo['nombre_campo']}) - Tipo: {$campo['tipo_campo']}</li>";
                }
                echo "</ul>";
            } else {
                echo "<p style='color: orange;'>No hay campos definidos para esta especialidad</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>No hay campos en la tabla especialidad_campos</p>";
    }
    
    // 4. Probar el endpoint actual
    echo "<h3>4. Test del Endpoint Actual</h3>";
    echo "<p><a href='get_campos_simple.php' target='_blank'>Abrir get_campos_simple.php</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
