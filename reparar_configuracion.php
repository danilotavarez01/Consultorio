<?php
/**
 * Script para crear/reparar la tabla configuración
 */

require_once 'config.php';

echo "<h2>Reparación: Tabla Configuración</h2>";

try {
    // 1. Verificar si la tabla existe
    $stmt = $conn->query("SHOW TABLES LIKE 'configuracion'");
    $tabla_existe = $stmt->rowCount() > 0;
    
    if (!$tabla_existe) {
        echo "<h3>Creando tabla configuración...</h3>";
        
        $sql_create = "CREATE TABLE configuracion (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre_consultorio VARCHAR(255) DEFAULT 'Consultorio Médico',
            email_contacto VARCHAR(255) NULL,
            telefono VARCHAR(50) NULL,
            direccion TEXT NULL,
            duracion_cita INT DEFAULT 30,
            hora_inicio TIME DEFAULT '09:00:00',
            hora_fin TIME DEFAULT '18:00:00',
            especialidad_id INT NULL,
            dias_laborables VARCHAR(20) DEFAULT '1,2,3,4,5',
            intervalo_citas INT DEFAULT 30,
            require_https TINYINT(1) DEFAULT 0,
            modo_mantenimiento TINYINT(1) DEFAULT 0,
            moneda VARCHAR(10) DEFAULT 'RD$',
            zona_horaria VARCHAR(50) DEFAULT 'America/Santo_Domingo',
            formato_fecha VARCHAR(20) DEFAULT 'Y-m-d',
            idioma VARCHAR(10) DEFAULT 'es',
            tema_color VARCHAR(20) DEFAULT 'light',
            mostrar_alertas_stock TINYINT(1) DEFAULT 1,
            notificaciones_email TINYINT(1) DEFAULT 0,
            medico_nombre VARCHAR(255) DEFAULT 'Dr. Médico',
            multi_medico TINYINT(1) DEFAULT 0,
            whatsapp_server VARCHAR(255) DEFAULT 'https://api.whatsapp.com',
            logo LONGBLOB NULL,
            updated_by VARCHAR(50) NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        
        $conn->exec($sql_create);
        echo "✅ Tabla creada exitosamente.<br>";
    } else {
        echo "✅ Tabla 'configuracion' ya existe.<br>";
    }
    
    // 2. Verificar si existe el registro principal
    $stmt = $conn->query("SELECT COUNT(*) as count FROM configuracion WHERE id = 1");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($count['count'] == 0) {
        echo "<h3>Insertando configuración por defecto...</h3>";
        
        $sql_insert = "INSERT INTO configuracion (
            id, nombre_consultorio, medico_nombre, duracion_cita, 
            hora_inicio, hora_fin, dias_laborables, intervalo_citas,
            moneda, zona_horaria, formato_fecha, idioma, tema_color,
            mostrar_alertas_stock, multi_medico, whatsapp_server,
            email_contacto, telefono, direccion, especialidad_id,
            require_https, modo_mantenimiento, notificaciones_email
        ) VALUES (
            1, 'Consultorio Médico', 'Dr. Médico', 30, 
            '09:00:00', '18:00:00', '1,2,3,4,5', 30,
            'RD$', 'America/Santo_Domingo', 'Y-m-d', 'es', 'light',
            1, 0, 'https://api.whatsapp.com',
            '', '', '', NULL,
            0, 0, 0
        )";
        
        $conn->exec($sql_insert);
        echo "✅ Configuración por defecto insertada.<br>";
    } else {
        echo "✅ Registro de configuración ya existe.<br>";
    }
    
    // 3. Verificar que todos los campos necesarios existen
    echo "<h3>Verificando campos de la tabla...</h3>";
    $stmt = $conn->query("DESCRIBE configuracion");
    $columnas_existentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $campos_requeridos = [
        'nombre_consultorio', 'email_contacto', 'telefono', 'direccion',
        'duracion_cita', 'hora_inicio', 'hora_fin', 'especialidad_id',
        'dias_laborables', 'intervalo_citas', 'require_https', 'modo_mantenimiento',
        'moneda', 'zona_horaria', 'formato_fecha', 'idioma', 'tema_color',
        'mostrar_alertas_stock', 'notificaciones_email', 'medico_nombre',
        'multi_medico', 'whatsapp_server', 'logo'
    ];
    
    $campos_faltantes = array_diff($campos_requeridos, $columnas_existentes);
    
    if (!empty($campos_faltantes)) {
        echo "<h4>Agregando campos faltantes:</h4>";
        foreach ($campos_faltantes as $campo) {
            switch ($campo) {
                case 'medico_nombre':
                    $conn->exec("ALTER TABLE configuracion ADD COLUMN medico_nombre VARCHAR(255) DEFAULT 'Dr. Médico'");
                    break;
                case 'multi_medico':
                    $conn->exec("ALTER TABLE configuracion ADD COLUMN multi_medico TINYINT(1) DEFAULT 0");
                    break;
                case 'whatsapp_server':
                    $conn->exec("ALTER TABLE configuracion ADD COLUMN whatsapp_server VARCHAR(255) DEFAULT 'https://api.whatsapp.com'");
                    break;
                case 'logo':
                    $conn->exec("ALTER TABLE configuracion ADD COLUMN logo LONGBLOB NULL");
                    break;
                case 'telefono':
                    $conn->exec("ALTER TABLE configuracion ADD COLUMN telefono VARCHAR(50) NULL");
                    break;
                case 'direccion':
                    $conn->exec("ALTER TABLE configuracion ADD COLUMN direccion TEXT NULL");
                    break;
                default:
                    echo "⚠️ Campo '$campo' no agregado automáticamente.<br>";
                    continue 2;
            }
            echo "✅ Campo '$campo' agregado.<br>";
        }
    } else {
        echo "✅ Todos los campos requeridos están presentes.<br>";
    }
    
    // 4. Mostrar configuración final
    echo "<h3>Configuración actual:</h3>";
    $stmt = $conn->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Valor</th></tr>";
        foreach ($config as $campo => $valor) {
            if ($campo !== 'logo') {
                echo "<tr>";
                echo "<td style='padding: 5px;'>" . htmlspecialchars($campo) . "</td>";
                echo "<td style='padding: 5px;'>" . htmlspecialchars($valor ?? 'NULL') . "</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
        
        echo "<br><div style='background-color: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
        echo "<strong>✅ ¡Tabla configuración reparada exitosamente!</strong><br>";
        echo "Ahora puedes ir a <a href='configuracion.php' target='_blank'>configuracion.php</a> y debería cargar correctamente.";
        echo "</div>";
    } else {
        echo "<div style='background-color: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
        echo "❌ Error: No se pudo cargar la configuración después de la reparación.";
        echo "</div>";
    }

} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "❌ Error durante la reparación: " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h2, h3, h4 { color: #333; }
    table { margin: 10px 0; }
    th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    th { background-color: #f8f9fa; }
    a { color: #007bff; text-decoration: none; }
    a:hover { text-decoration: underline; }
</style>";
?>
