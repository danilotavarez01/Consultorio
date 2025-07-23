<?php
require_once "config.php";

echo "<h1>🏥 Configuración de Especialidades y Campos Dinámicos</h1>";

try {
    $conn->beginTransaction();
    
    // 1. Verificar y crear especialidades
    echo "<h2>1. 📋 Configurando Especialidades</h2>";
    
    $especialidades = [
        [
            'codigo' => 'MG',
            'nombre' => 'Medicina General',
            'descripcion' => 'Atención médica general y preventiva',
            'campos' => [
                ['nombre_campo' => 'temperatura', 'etiqueta' => 'Temperatura (°C)', 'tipo_campo' => 'numero', 'requerido' => 1, 'orden' => 1],
                ['nombre_campo' => 'presion_arterial', 'etiqueta' => 'Presión Arterial', 'tipo_campo' => 'texto', 'requerido' => 1, 'orden' => 2],
                ['nombre_campo' => 'sintomas_generales', 'etiqueta' => 'Síntomas Generales', 'tipo_campo' => 'textarea', 'requerido' => 0, 'orden' => 3],
                ['nombre_campo' => 'tipo_consulta', 'etiqueta' => 'Tipo de Consulta', 'tipo_campo' => 'seleccion', 'opciones' => 'Primera vez,Control,Seguimiento,Urgencia', 'requerido' => 1, 'orden' => 4]
            ]
        ],
        [
            'codigo' => 'PED',
            'nombre' => 'Pediatría',
            'descripcion' => 'Especialidad médica que estudia al niño y sus enfermedades',
            'campos' => [
                ['nombre_campo' => 'peso_pediatrico', 'etiqueta' => 'Peso (kg)', 'tipo_campo' => 'numero', 'requerido' => 1, 'orden' => 1],
                ['nombre_campo' => 'talla', 'etiqueta' => 'Talla (cm)', 'tipo_campo' => 'numero', 'requerido' => 1, 'orden' => 2],
                ['nombre_campo' => 'perimetro_cefalico', 'etiqueta' => 'Perímetro Cefálico (cm)', 'tipo_campo' => 'numero', 'requerido' => 0, 'orden' => 3],
                ['nombre_campo' => 'desarrollo', 'etiqueta' => 'Desarrollo', 'tipo_campo' => 'seleccion', 'opciones' => 'Normal,Retraso leve,Retraso moderado,Requiere evaluación', 'requerido' => 1, 'orden' => 4],
                ['nombre_campo' => 'vacunas_al_dia', 'etiqueta' => 'Vacunas al día', 'tipo_campo' => 'checkbox', 'requerido' => 0, 'orden' => 5],
                ['nombre_campo' => 'alimentacion', 'etiqueta' => 'Tipo de Alimentación', 'tipo_campo' => 'seleccion', 'opciones' => 'Lactancia materna,Fórmula,Mixta,Sólidos', 'requerido' => 0, 'orden' => 6]
            ]
        ],
        [
            'codigo' => 'GIN',
            'nombre' => 'Ginecología',
            'descripcion' => 'Especialidad médica de la salud femenina',
            'campos' => [
                ['nombre_campo' => 'fur', 'etiqueta' => 'Fecha Última Regla (FUR)', 'tipo_campo' => 'fecha', 'requerido' => 0, 'orden' => 1],
                ['nombre_campo' => 'gestas', 'etiqueta' => 'Número de Embarazos (G)', 'tipo_campo' => 'numero', 'requerido' => 0, 'orden' => 2],
                ['nombre_campo' => 'partos', 'etiqueta' => 'Número de Partos (P)', 'tipo_campo' => 'numero', 'requerido' => 0, 'orden' => 3],
                ['nombre_campo' => 'abortos', 'etiqueta' => 'Número de Abortos (A)', 'tipo_campo' => 'numero', 'requerido' => 0, 'orden' => 4],
                ['nombre_campo' => 'cesareas', 'etiqueta' => 'Número de Cesáreas (C)', 'tipo_campo' => 'numero', 'requerido' => 0, 'orden' => 5],
                ['nombre_campo' => 'metodo_anticonceptivo', 'etiqueta' => 'Método Anticonceptivo', 'tipo_campo' => 'seleccion', 'opciones' => 'Ninguno,DIU,Implante,Pastillas,Inyección,Preservativo,Otro', 'requerido' => 0, 'orden' => 6],
                ['nombre_campo' => 'papanicolau', 'etiqueta' => 'Fecha último Papanicolau', 'tipo_campo' => 'fecha', 'requerido' => 0, 'orden' => 7],
                ['nombre_campo' => 'mamografia', 'etiqueta' => 'Fecha última Mamografía', 'tipo_campo' => 'fecha', 'requerido' => 0, 'orden' => 8]
            ]
        ],
        [
            'codigo' => 'CAR',
            'nombre' => 'Cardiología',
            'descripcion' => 'Especialidad que trata enfermedades del corazón',
            'campos' => [
                ['nombre_campo' => 'presion_sistolica', 'etiqueta' => 'Presión Sistólica', 'tipo_campo' => 'numero', 'requerido' => 1, 'orden' => 1],
                ['nombre_campo' => 'presion_diastolica', 'etiqueta' => 'Presión Diastólica', 'tipo_campo' => 'numero', 'requerido' => 1, 'orden' => 2],
                ['nombre_campo' => 'frecuencia_cardiaca_cardio', 'etiqueta' => 'Frecuencia Cardíaca (lpm)', 'tipo_campo' => 'numero', 'requerido' => 1, 'orden' => 3],
                ['nombre_campo' => 'dolor_toracico', 'etiqueta' => 'Dolor Torácico', 'tipo_campo' => 'seleccion', 'opciones' => 'No,Leve,Moderado,Severo', 'requerido' => 0, 'orden' => 4],
                ['nombre_campo' => 'disnea', 'etiqueta' => 'Dificultad para Respirar', 'tipo_campo' => 'seleccion', 'opciones' => 'No,En reposo,Al esfuerzo,Nocturna', 'requerido' => 0, 'orden' => 5],
                ['nombre_campo' => 'edema', 'etiqueta' => 'Edema en Extremidades', 'tipo_campo' => 'checkbox', 'requerido' => 0, 'orden' => 6],
                ['nombre_campo' => 'antecedentes_cardiacos', 'etiqueta' => 'Antecedentes Cardíacos', 'tipo_campo' => 'textarea', 'requerido' => 0, 'orden' => 7]
            ]
        ],
        [
            'codigo' => 'DER',
            'nombre' => 'Dermatología',
            'descripcion' => 'Especialidad en enfermedades de la piel',
            'campos' => [
                ['nombre_campo' => 'tipo_lesion', 'etiqueta' => 'Tipo de Lesión', 'tipo_campo' => 'seleccion', 'opciones' => 'Mancha,Pápula,Nódulo,Vesícula,Úlcera,Escama,Otra', 'requerido' => 0, 'orden' => 1],
                ['nombre_campo' => 'localizacion_lesion', 'etiqueta' => 'Localización de la Lesión', 'tipo_campo' => 'texto', 'requerido' => 0, 'orden' => 2],
                ['nombre_campo' => 'tiempo_evolucion', 'etiqueta' => 'Tiempo de Evolución', 'tipo_campo' => 'seleccion', 'opciones' => 'Días,Semanas,Meses,Años', 'requerido' => 0, 'orden' => 3],
                ['nombre_campo' => 'prurito', 'etiqueta' => 'Presencia de Picazón', 'tipo_campo' => 'checkbox', 'requerido' => 0, 'orden' => 4],
                ['nombre_campo' => 'dolor_lesion', 'etiqueta' => 'Dolor en la Lesión', 'tipo_campo' => 'checkbox', 'requerido' => 0, 'orden' => 5],
                ['nombre_campo' => 'antecedentes_alergicos', 'etiqueta' => 'Antecedentes Alérgicos', 'tipo_campo' => 'textarea', 'requerido' => 0, 'orden' => 6],
                ['nombre_campo' => 'exposicion_solar', 'etiqueta' => 'Exposición Solar', 'tipo_campo' => 'seleccion', 'opciones' => 'Mínima,Moderada,Intensa,Ocupacional', 'requerido' => 0, 'orden' => 7]
            ]
        ],
        [
            'codigo' => 'OFT',
            'nombre' => 'Oftalmología',
            'descripcion' => 'Especialidad en enfermedades de los ojos',
            'campos' => [
                ['nombre_campo' => 'agudeza_visual_od', 'etiqueta' => 'Agudeza Visual OD', 'tipo_campo' => 'texto', 'requerido' => 0, 'orden' => 1],
                ['nombre_campo' => 'agudeza_visual_oi', 'etiqueta' => 'Agudeza Visual OI', 'tipo_campo' => 'texto', 'requerido' => 0, 'orden' => 2],
                ['nombre_campo' => 'presion_intraocular_od', 'etiqueta' => 'Presión Intraocular OD (mmHg)', 'tipo_campo' => 'numero', 'requerido' => 0, 'orden' => 3],
                ['nombre_campo' => 'presion_intraocular_oi', 'etiqueta' => 'Presión Intraocular OI (mmHg)', 'tipo_campo' => 'numero', 'requerido' => 0, 'orden' => 4],
                ['nombre_campo' => 'dolor_ocular', 'etiqueta' => 'Dolor Ocular', 'tipo_campo' => 'seleccion', 'opciones' => 'No,Leve,Moderado,Severo', 'requerido' => 0, 'orden' => 5],
                ['nombre_campo' => 'vision_borrosa', 'etiqueta' => 'Visión Borrosa', 'tipo_campo' => 'checkbox', 'requerido' => 0, 'orden' => 6],
                ['nombre_campo' => 'fotofobia', 'etiqueta' => 'Fotofobia', 'tipo_campo' => 'checkbox', 'requerido' => 0, 'orden' => 7],
                ['nombre_campo' => 'uso_lentes', 'etiqueta' => 'Uso de Lentes', 'tipo_campo' => 'seleccion', 'opciones' => 'No,De lectura,Permanentes,De contacto', 'requerido' => 0, 'orden' => 8]
            ]
        ]
    ];
    
    foreach ($especialidades as $esp_data) {
        // Insertar o actualizar especialidad
        $stmt = $conn->prepare("
            INSERT INTO especialidades (codigo, nombre, descripcion) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            nombre = VALUES(nombre), 
            descripcion = VALUES(descripcion)
        ");
        $stmt->execute([$esp_data['codigo'], $esp_data['nombre'], $esp_data['descripcion']]);
        
        // Obtener ID de la especialidad
        $stmt = $conn->prepare("SELECT id FROM especialidades WHERE codigo = ?");
        $stmt->execute([$esp_data['codigo']]);
        $especialidad_id = $stmt->fetchColumn();
        
        echo "<p>✅ <strong>{$esp_data['nombre']}</strong> (ID: {$especialidad_id})</p>";
        
        // Limpiar campos existentes para esta especialidad
        $stmt = $conn->prepare("DELETE FROM especialidad_campos WHERE especialidad_id = ?");
        $stmt->execute([$especialidad_id]);
        
        // Insertar campos específicos
        $stmt = $conn->prepare("
            INSERT INTO especialidad_campos 
            (especialidad_id, nombre_campo, etiqueta, tipo_campo, opciones, requerido, orden) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($esp_data['campos'] as $campo) {
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
        
        echo "<p style='margin-left: 20px; color: #666;'>→ {" . count($esp_data['campos']) . "} campos configurados</p>";
    }
    
    // 2. Configurar especialidad por defecto
    echo "<h2>2. ⚙️ Configuración por Defecto</h2>";
    
    $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$config || !$config['especialidad_id']) {
        // Establecer Medicina General como especialidad por defecto
        $stmt = $conn->prepare("SELECT id FROM especialidades WHERE codigo = 'MG'");
        $stmt->execute();
        $mg_id = $stmt->fetchColumn();
        
        if ($mg_id) {
            $stmt = $conn->prepare("UPDATE configuracion SET especialidad_id = ? WHERE id = 1");
            $stmt->execute([$mg_id]);
            echo "<p>✅ Medicina General establecida como especialidad por defecto (ID: {$mg_id})</p>";
        }
    } else {
        echo "<p>✅ Especialidad por defecto ya configurada (ID: {$config['especialidad_id']})</p>";
    }
    
    $conn->commit();
    
    // 3. Resumen final
    echo "<h2>3. 📊 Resumen Final</h2>";
    
    $stmt = $conn->query("
        SELECT e.id, e.codigo, e.nombre, COUNT(ec.id) as campos_count
        FROM especialidades e
        LEFT JOIN especialidad_campos ec ON e.id = ec.especialidad_id
        WHERE e.estado = 'activo'
        GROUP BY e.id, e.codigo, e.nombre
        ORDER BY e.nombre
    ");
    $resumen = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th style='padding: 10px;'>Código</th>";
    echo "<th style='padding: 10px;'>Especialidad</th>";
    echo "<th style='padding: 10px;'>Campos Configurados</th>";
    echo "<th style='padding: 10px;'>Acciones</th>";
    echo "</tr>";
    
    foreach ($resumen as $esp) {
        echo "<tr>";
        echo "<td style='padding: 10px; text-align: center;'><strong>{$esp['codigo']}</strong></td>";
        echo "<td style='padding: 10px;'>{$esp['nombre']}</td>";
        echo "<td style='padding: 10px; text-align: center;'>{$esp['campos_count']}</td>";
        echo "<td style='padding: 10px; text-align: center;'>";
        echo "<a href='get_campos_especialidad_por_id.php?especialidad_id={$esp['id']}' target='_blank'>Ver Campos</a>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎉 Configuración Completada Exitosamente</h3>";
    echo "<p><strong>Especialidades configuradas:</strong> " . count($resumen) . "</p>";
    echo "<p><strong>Total de campos personalizados:</strong> " . array_sum(array_column($resumen, 'campos_count')) . "</p>";
    echo "</div>";
    
    echo "<h2>🔗 Enlaces de Prueba</h2>";
    echo "<ul>";
    echo "<li><a href='nueva_consulta_avanzada.php?paciente_id=1' target='_blank'>🏥 Nueva Consulta Avanzada</a></li>";
    echo "<li><a href='get_campos_especialidad_por_id.php?especialidad_id=1' target='_blank'>🔌 Test Endpoint Campos</a></li>";
    echo "<li><a href='configuracion.php' target='_blank'>⚙️ Configuración del Sistema</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    $conn->rollBack();
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Error durante la configuración</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
