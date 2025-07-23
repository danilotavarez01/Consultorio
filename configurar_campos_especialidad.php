<?php
require_once "config.php";

echo "<h2>Configuración de Campos para Especialidad</h2>";

try {
    // 1. Verificar configuración actual
    $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$config || !$config['especialidad_id']) {
        echo "<p style='color: red;'>No hay especialidad configurada en la configuración global.</p>";
        
        // Mostrar especialidades disponibles
        $stmt = $conn->prepare("SELECT id, nombre FROM especialidades ORDER BY nombre");
        $stmt->execute();
        $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($especialidades) {
            echo "<p>Especialidades disponibles:</p>";
            echo "<ul>";
            foreach ($especialidades as $esp) {
                echo "<li>ID: {$esp['id']} - {$esp['nombre']}</li>";
            }
            echo "</ul>";
            
            // Configurar la primera especialidad por defecto
            $primera_especialidad = $especialidades[0]['id'];
            echo "<p>Configurando especialidad por defecto: {$especialidades[0]['nombre']} (ID: {$primera_especialidad})</p>";
            
            $stmt = $conn->prepare("UPDATE configuracion SET especialidad_id = ? WHERE id = 1");
            $stmt->execute([$primera_especialidad]);
            
            $config = ['especialidad_id' => $primera_especialidad];
        } else {
            echo "<p style='color: red;'>No hay especialidades en la base de datos.</p>";
            exit;
        }
    }
    
    $especialidad_id = $config['especialidad_id'];
    echo "<p><strong>Especialidad configurada:</strong> ID {$especialidad_id}</p>";
    
    // 2. Verificar si ya hay campos para esta especialidad
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM especialidad_campos WHERE especialidad_id = ?");
    $stmt->execute([$especialidad_id]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado['total'] > 0) {
        echo "<p>Ya hay {$resultado['total']} campos configurados para esta especialidad.</p>";
        
        // Mostrar campos existentes
        $stmt = $conn->prepare("SELECT * FROM especialidad_campos WHERE especialidad_id = ? ORDER BY orden");
        $stmt->execute([$especialidad_id]);
        $campos_existentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Campos existentes:</h3>";
        echo "<ul>";
        foreach ($campos_existentes as $campo) {
            echo "<li><strong>{$campo['etiqueta']}</strong> ({$campo['nombre_campo']}) - Tipo: {$campo['tipo_campo']}</li>";
        }
        echo "</ul>";
        
    } else {
        echo "<p>No hay campos configurados. Agregando campos de ejemplo...</p>";
        
        // Agregar campos de ejemplo
        $campos_ejemplo = [
            [
                'nombre_campo' => 'temperatura',
                'etiqueta' => 'Temperatura (°C)',
                'tipo_campo' => 'numero',
                'requerido' => 1,
                'orden' => 1
            ],
            [
                'nombre_campo' => 'presion_arterial',
                'etiqueta' => 'Presión Arterial',
                'tipo_campo' => 'texto',
                'requerido' => 1,
                'orden' => 2
            ],
            [
                'nombre_campo' => 'frecuencia_cardiaca',
                'etiqueta' => 'Frecuencia Cardíaca (bpm)',
                'tipo_campo' => 'numero',
                'requerido' => 0,
                'orden' => 3
            ],
            [
                'nombre_campo' => 'tipo_consulta',
                'etiqueta' => 'Tipo de Consulta',
                'tipo_campo' => 'seleccion',
                'opciones' => 'Primera vez,Seguimiento,Control,Urgencia',
                'requerido' => 1,
                'orden' => 4
            ],
            [
                'nombre_campo' => 'observaciones_especialidad',
                'etiqueta' => 'Observaciones de la Especialidad',
                'tipo_campo' => 'textarea',
                'requerido' => 0,
                'orden' => 5
            ],
            [
                'nombre_campo' => 'requiere_seguimiento',
                'etiqueta' => 'Requiere cita de seguimiento',
                'tipo_campo' => 'checkbox',
                'requerido' => 0,
                'orden' => 6
            ]
        ];
        
        $stmt = $conn->prepare("
            INSERT INTO especialidad_campos 
            (especialidad_id, nombre_campo, etiqueta, tipo_campo, opciones, requerido, orden) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($campos_ejemplo as $campo) {
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
        
        echo "<p style='color: green;'>Se agregaron " . count($campos_ejemplo) . " campos de ejemplo.</p>";
        
        echo "<h3>Campos agregados:</h3>";
        echo "<ul>";
        foreach ($campos_ejemplo as $campo) {
            echo "<li><strong>{$campo['etiqueta']}</strong> ({$campo['nombre_campo']}) - Tipo: {$campo['tipo_campo']}</li>";
        }
        echo "</ul>";
    }
    
    echo "<h3>Test del endpoint:</h3>";
    echo "<p><a href='get_campos_simple.php' target='_blank'>Probar get_campos_simple.php</a></p>";
    echo "<p><a href='nueva_consulta.php' target='_blank'>Ir a Nueva Consulta</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
