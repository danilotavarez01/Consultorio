<?php
require_once "config.php";

echo "<h1>Debug de Campos de Especialidad</h1>";

try {
    // 1. Verificar especialidades
    echo "<h2>1. Especialidades en BD:</h2>";
    $stmt = $conn->query("SELECT * FROM especialidades ORDER BY id");
    $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($especialidades) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Código</th><th>Nombre</th><th>Estado</th></tr>";
        foreach ($especialidades as $esp) {
            echo "<tr>";
            echo "<td>{$esp['id']}</td>";
            echo "<td>{$esp['codigo']}</td>";
            echo "<td>{$esp['nombre']}</td>";
            echo "<td>{$esp['estado']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ No hay especialidades en la base de datos</p>";
    }
    
    // 2. Verificar campos para cada especialidad
    echo "<h2>2. Campos por Especialidad:</h2>";
    foreach ($especialidades as $esp) {
        echo "<h3>Especialidad: {$esp['nombre']} (ID: {$esp['id']})</h3>";
        
        $stmt = $conn->prepare("SELECT * FROM especialidad_campos WHERE especialidad_id = ? ORDER BY orden");
        $stmt->execute([$esp['id']]);
        $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($campos) {
            echo "<ul>";
            foreach ($campos as $campo) {
                echo "<li><strong>{$campo['etiqueta']}</strong> ({$campo['nombre_campo']}) - Tipo: {$campo['tipo_campo']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>❌ No hay campos para esta especialidad</p>";
        }
    }
    
    // 3. Test del endpoint directo
    echo "<h2>3. Test del Endpoint:</h2>";
    if ($especialidades) {
        $primera_esp = $especialidades[0]['id'];
        echo "<p>Testeando con especialidad ID: {$primera_esp}</p>";
        
        // Simular la llamada al endpoint
        $_GET['especialidad_id'] = $primera_esp;
        
        ob_start();
        include 'get_campos_especialidad_por_id.php';
        $response = ob_get_clean();
        
        echo "<h4>Respuesta del endpoint:</h4>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        
        // Verificar si es JSON válido
        $json_data = json_decode($response, true);
        if ($json_data) {
            echo "<h4>JSON decodificado:</h4>";
            echo "<pre>" . print_r($json_data, true) . "</pre>";
        } else {
            echo "<p>❌ La respuesta no es JSON válido</p>";
        }
    }
    
    // 4. Verificar configuración
    echo "<h2>4. Configuración Global:</h2>";
    $stmt = $conn->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        echo "<p>✅ Especialidad configurada: {$config['especialidad_id']}</p>";
    } else {
        echo "<p>❌ No hay configuración global</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
}
?>
