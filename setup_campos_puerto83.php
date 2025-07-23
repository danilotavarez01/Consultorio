<?php
require_once "config.php";

echo "<h1>Configuración de Campos Dinámicos - Puerto 83</h1>";

try {
    // 1. Verificar tabla configuracion
    echo "<h2>1. Verificando configuración...</h2>";
    $stmt = $conn->query("SELECT id, nombre_consultorio, especialidad_id FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$config) {
        echo "<p style='color: red;'>No existe configuración. Creando...</p>";
        $conn->exec("INSERT INTO configuracion (id, nombre_consultorio) VALUES (1, 'Consultorio Médico')");
        $config = ['id' => 1, 'nombre_consultorio' => 'Consultorio Médico', 'especialidad_id' => null];
    }
    
    echo "<pre>";
    print_r($config);
    echo "</pre>";
    
    // 2. Verificar especialidades
    echo "<h2>2. Verificando especialidades...</h2>";
    $stmt = $conn->query("SELECT * FROM especialidades");
    $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($especialidades)) {
        echo "<p style='color: orange;'>No hay especialidades. Creando Medicina General...</p>";
        $conn->exec("INSERT INTO especialidades (codigo, nombre, descripcion) VALUES ('MG', 'Medicina General', 'Atención médica general')");
        $especialidades = $conn->query("SELECT * FROM especialidades")->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo "<p>Especialidades encontradas: " . count($especialidades) . "</p>";
    foreach ($especialidades as $esp) {
        echo "<p>- {$esp['codigo']}: {$esp['nombre']} (ID: {$esp['id']})</p>";
    }
    
    // 3. Asignar especialidad a configuración si no tiene
    if (!$config['especialidad_id']) {
        $primera_especialidad = $especialidades[0]['id'];
        echo "<h2>3. Asignando especialidad {$primera_especialidad} a configuración...</h2>";
        $stmt = $conn->prepare("UPDATE configuracion SET especialidad_id = ? WHERE id = 1");
        $stmt->execute([$primera_especialidad]);
        $config['especialidad_id'] = $primera_especialidad;
        echo "<p style='color: green;'>✓ Especialidad asignada</p>";
    } else {
        echo "<h2>3. Especialidad ya configurada: {$config['especialidad_id']}</h2>";
    }
    
    // 4. Verificar campos de especialidad
    echo "<h2>4. Verificando campos de especialidad...</h2>";
    $stmt = $conn->prepare("SELECT * FROM especialidad_campos WHERE especialidad_id = ?");
    $stmt->execute([$config['especialidad_id']]);
    $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Campos encontrados para especialidad {$config['especialidad_id']}: " . count($campos) . "</p>";
    
    if (empty($campos)) {
        echo "<p style='color: orange;'>No hay campos. Creando campos de prueba...</p>";
        
        $campos_insertar = [
            ['nombre_campo' => 'temperatura', 'etiqueta' => 'Temperatura (°C)', 'tipo_campo' => 'numero', 'requerido' => 1, 'orden' => 1],
            ['nombre_campo' => 'presion_arterial', 'etiqueta' => 'Presión Arterial', 'tipo_campo' => 'texto', 'requerido' => 1, 'orden' => 2],
            ['nombre_campo' => 'observaciones_especialidad', 'etiqueta' => 'Observaciones de la Especialidad', 'tipo_campo' => 'textarea', 'requerido' => 0, 'orden' => 3],
            ['nombre_campo' => 'tipo_consulta', 'etiqueta' => 'Tipo de Consulta', 'tipo_campo' => 'seleccion', 'opciones' => 'Primera vez,Seguimiento,Control,Urgencia', 'requerido' => 1, 'orden' => 4],
            ['nombre_campo' => 'requiere_seguimiento', 'etiqueta' => 'Requiere cita de seguimiento', 'tipo_campo' => 'checkbox', 'requerido' => 0, 'orden' => 5]
        ];
        
        foreach ($campos_insertar as $campo) {
            $sql = "INSERT INTO especialidad_campos (especialidad_id, nombre_campo, etiqueta, tipo_campo, opciones, requerido, orden) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $config['especialidad_id'],
                $campo['nombre_campo'],
                $campo['etiqueta'],
                $campo['tipo_campo'],
                $campo['opciones'] ?? null,
                $campo['requerido'],
                $campo['orden']
            ]);
        }
        
        echo "<p style='color: green;'>✓ Se insertaron " . count($campos_insertar) . " campos</p>";
        
        // Volver a obtener los campos
        $stmt = $conn->prepare("SELECT * FROM especialidad_campos WHERE especialidad_id = ?");
        $stmt->execute([$config['especialidad_id']]);
        $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo "<h3>Campos actuales:</h3>";
    foreach ($campos as $campo) {
        echo "<p>- {$campo['nombre_campo']}: {$campo['etiqueta']} ({$campo['tipo_campo']})</p>";
    }
    
    // 5. Test del endpoint
    echo "<h2>5. Probando endpoint...</h2>";
    
    $url = "http://localhost:83/Consultorio2/get_campos_simple.php";
    echo "<p>Probando: <a href='{$url}' target='_blank'>{$url}</a></p>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "<p style='color: green;'>✓ Endpoint responde correctamente</p>";
        echo "<pre>";
        $data = json_decode($response, true);
        print_r($data);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>✗ Error en endpoint (HTTP {$httpCode})</p>";
        echo "<pre>{$response}</pre>";
    }
    
    echo "<h2>6. Resumen</h2>";
    echo "<p style='color: green;'>✓ Configuración completa para puerto 83</p>";
    echo "<p>- Especialidad configurada: {$config['especialidad_id']}</p>";
    echo "<p>- Campos disponibles: " . count($campos) . "</p>";
    echo "<p>- Endpoint funcionando: " . ($httpCode == 200 ? 'Sí' : 'No') . "</p>";
    
    echo "<h3>Siguiente paso:</h3>";
    echo "<p>Ir a: <a href='http://localhost:83/Consultorio2/nueva_consulta.php?paciente_id=1' target='_blank'>Nueva Consulta</a></p>";
    echo "<p>Y verificar la consola del navegador para ver los logs de JavaScript.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
