<?php
require_once "config.php";

try {
    // Verificar estructura de tabla especialidad_campos
    $stmt = $conn->query("DESCRIBE especialidad_campos");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h1>Estructura de la tabla especialidad_campos</h1>";
    echo "<pre>";
    print_r($columnas);
    echo "</pre>";
    
    // Insertar campos de prueba para Medicina General si no existen
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM especialidad_campos WHERE especialidad_id = (SELECT id FROM especialidades WHERE codigo = 'MG')");
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado && $resultado['total'] == 0) {
        echo "<h2>No se encontraron campos para Medicina General. Insertando campos de prueba...</h2>";
        
        $campos = [
            ['nombre_campo' => 'temperatura', 'etiqueta' => 'Temperatura (°C)', 'tipo_campo' => 'numero', 'requerido' => 1, 'orden' => 1],
            ['nombre_campo' => 'tension_arterial', 'etiqueta' => 'Tensión Arterial', 'tipo_campo' => 'texto', 'requerido' => 1, 'orden' => 2],
            ['nombre_campo' => 'notas_adicionales', 'etiqueta' => 'Notas Adicionales', 'tipo_campo' => 'textarea', 'requerido' => 0, 'orden' => 3],
            ['nombre_campo' => 'tipo_consulta', 'etiqueta' => 'Tipo de Consulta', 'tipo_campo' => 'seleccion', 'opciones' => 'Primera vez,Seguimiento,Urgencia,Control', 'requerido' => 1, 'orden' => 4],
            ['nombre_campo' => 'requiere_seguimiento', 'etiqueta' => 'Requiere seguimiento', 'tipo_campo' => 'checkbox', 'requerido' => 0, 'orden' => 5]
        ];
        
        $especialidad_id = $conn->query("SELECT id FROM especialidades WHERE codigo = 'MG'")->fetchColumn();
        
        foreach ($campos as $campo) {
            $sql = "INSERT INTO especialidad_campos (
                        especialidad_id, 
                        nombre_campo, 
                        etiqueta, 
                        tipo_campo, 
                        opciones, 
                        requerido, 
                        orden
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $especialidad_id,
                $campo['nombre_campo'],
                $campo['etiqueta'],
                $campo['tipo_campo'],
                $campo['opciones'] ?? null,
                $campo['requerido'],
                $campo['orden']
            ]);
        }
        
        echo "<p>Se insertaron " . count($campos) . " campos para Medicina General.</p>";
    } else {
        echo "<h2>Ya existen " . $resultado['total'] . " campos para Medicina General.</h2>";
    }
    
    // Verificar la configuración global
    $stmt = $conn->query("SELECT nombre_consultorio, especialidad_id FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Configuración global</h2>";
    echo "<pre>";
    print_r($config);
    echo "</pre>";
    
    // Verificar si la especialidad_id en configuración está establecida
    if (!$config || empty($config['especialidad_id'])) {
        echo "<h2>No hay especialidad configurada. Estableciendo Medicina General como predeterminada...</h2>";
        
        $especialidad_id = $conn->query("SELECT id FROM especialidades WHERE codigo = 'MG'")->fetchColumn();
        
        $stmt = $conn->prepare("UPDATE configuracion SET especialidad_id = ? WHERE id = 1");
        $stmt->execute([$especialidad_id]);
        
        echo "<p>Se estableció Medicina General como especialidad predeterminada.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
