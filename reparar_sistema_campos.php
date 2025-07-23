<?php
require_once "config.php";

echo "<h1>🔧 Reparación Completa del Sistema de Campos</h1>";

try {
    $conn->beginTransaction();
    
    echo "<h2>1. Verificando estructura de tablas...</h2>";
    
    // Verificar si existe la tabla especialidades
    $tables = $conn->query("SHOW TABLES LIKE 'especialidades'")->fetchAll();
    if (empty($tables)) {
        echo "<p>⚠️ Creando tabla especialidades...</p>";
        $conn->exec("
            CREATE TABLE especialidades (
                id INT AUTO_INCREMENT PRIMARY KEY,
                codigo VARCHAR(10) UNIQUE NOT NULL,
                nombre VARCHAR(100) NOT NULL,
                descripcion TEXT,
                estado ENUM('activo', 'inactivo') DEFAULT 'activo',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
    }
    echo "<p>✅ Tabla especialidades OK</p>";
    
    // Verificar tabla especialidad_campos
    $tables = $conn->query("SHOW TABLES LIKE 'especialidad_campos'")->fetchAll();
    if (empty($tables)) {
        echo "<p>⚠️ Creando tabla especialidad_campos...</p>";
        $conn->exec("
            CREATE TABLE especialidad_campos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                especialidad_id INT NOT NULL,
                nombre_campo VARCHAR(50) NOT NULL,
                etiqueta VARCHAR(100) NOT NULL,
                tipo_campo ENUM('texto', 'numero', 'fecha', 'seleccion', 'checkbox', 'textarea') NOT NULL,
                opciones TEXT,
                requerido BOOLEAN DEFAULT FALSE,
                orden INT DEFAULT 0,
                estado ENUM('activo', 'inactivo') DEFAULT 'activo',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (especialidad_id) REFERENCES especialidades(id) ON DELETE CASCADE,
                UNIQUE KEY unique_campo_especialidad (especialidad_id, nombre_campo)
            )
        ");
    }
    echo "<p>✅ Tabla especialidad_campos OK</p>";
    
    // Verificar tabla configuracion
    $tables = $conn->query("SHOW TABLES LIKE 'configuracion'")->fetchAll();
    if (empty($tables)) {
        echo "<p>⚠️ Creando tabla configuracion...</p>";
        $conn->exec("
            CREATE TABLE configuracion (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre_consultorio VARCHAR(100) DEFAULT 'Consultorio Médico',
                especialidad_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (especialidad_id) REFERENCES especialidades(id)
            )
        ");
        
        // Insertar configuración inicial
        $conn->exec("INSERT INTO configuracion (id, nombre_consultorio) VALUES (1, 'Consultorio Médico')");
    }
    echo "<p>✅ Tabla configuracion OK</p>";
    
    echo "<h2>2. Insertando especialidades básicas...</h2>";
    
    $especialidades = [
        ['MG', 'Medicina General', 'Especialidad médica básica y general'],
        ['PED', 'Pediatría', 'Especialidad médica que estudia al niño y sus enfermedades'],
        ['GIN', 'Ginecología', 'Especialidad médica de la salud femenina']
    ];
    
    foreach ($especialidades as $esp) {
        $stmt = $conn->prepare("INSERT IGNORE INTO especialidades (codigo, nombre, descripcion) VALUES (?, ?, ?)");
        $stmt->execute($esp);
        if ($stmt->rowCount() > 0) {
            echo "<p>✅ Especialidad agregada: {$esp[1]}</p>";
        } else {
            echo "<p>ℹ️ Especialidad ya existe: {$esp[1]}</p>";
        }
    }
    
    echo "<h2>3. Configurando campos para Medicina General...</h2>";
    
    // Obtener ID de Medicina General
    $stmt = $conn->prepare("SELECT id FROM especialidades WHERE codigo = 'MG'");
    $stmt->execute();
    $mg = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($mg) {
        // Configurar Medicina General como especialidad por defecto
        $stmt = $conn->prepare("UPDATE configuracion SET especialidad_id = ? WHERE id = 1");
        $stmt->execute([$mg['id']]);
        echo "<p>✅ Medicina General configurada como especialidad por defecto</p>";
        
        // Limpiar campos existentes
        $stmt = $conn->prepare("DELETE FROM especialidad_campos WHERE especialidad_id = ?");
        $stmt->execute([$mg['id']]);
        
        // Insertar campos básicos
        $campos = [
            ['temperatura', 'Temperatura (°C)', 'numero', null, 1, 1],
            ['presion_arterial', 'Presión Arterial', 'texto', null, 1, 2],
            ['frecuencia_respiratoria', 'Frecuencia Respiratoria', 'numero', null, 0, 3],
            ['saturacion_oxigeno', 'Saturación de Oxígeno (%)', 'numero', null, 0, 4],
            ['sintomas_generales', 'Síntomas Generales', 'textarea', null, 0, 5],
            ['tipo_consulta', 'Tipo de Consulta', 'seleccion', 'Primera vez,Control,Seguimiento,Urgencia', 1, 6]
        ];
        
        $stmt = $conn->prepare("
            INSERT INTO especialidad_campos 
            (especialidad_id, nombre_campo, etiqueta, tipo_campo, opciones, requerido, orden) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($campos as $campo) {
            $stmt->execute(array_merge([$mg['id']], $campo));
        }
        
        echo "<p>✅ " . count($campos) . " campos configurados para Medicina General</p>";
    }
    
    $conn->commit();
    
    echo "<h2>4. Verificación final...</h2>";
    
    // Verificar configuración
    $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config && $config['especialidad_id']) {
        echo "<p>✅ Especialidad configurada: ID {$config['especialidad_id']}</p>";
        
        // Contar campos
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM especialidad_campos WHERE especialidad_id = ?");
        $stmt->execute([$config['especialidad_id']]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p>✅ Campos configurados: {$count['total']}</p>";
        
        if ($count['total'] > 0) {
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h3>🎉 ¡Sistema reparado exitosamente!</h3>";
            echo "<p>El sistema de campos dinámicos debería funcionar ahora.</p>";
            echo "<p><strong>Próximos pasos:</strong></p>";
            echo "<ol>";
            echo "<li><a href='get_campos_simple_debug.php' target='_blank'>Probar endpoint de debug</a></li>";
            echo "<li><a href='nueva_consulta.php?paciente_id=1' target='_blank'>Probar formulario de nueva consulta</a></li>";
            echo "</ol>";
            echo "</div>";
        }
    } else {
        echo "<p>❌ Error: No se pudo configurar la especialidad</p>";
    }
    
} catch (Exception $e) {
    $conn->rollBack();
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ Error durante la reparación:</h3>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}
?>
