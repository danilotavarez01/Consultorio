<?php
require_once "config.php";

echo "<h1>🧪 Prueba Completa del Sistema de Campos Dinámicos</h1>";

try {
    echo "<h2>1. ✅ Verificación de Configuración</h2>";
    
    // Verificar configuración actual
    $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config && $config['especialidad_id']) {
        echo "<p>✅ Especialidad configurada: ID {$config['especialidad_id']}</p>";
        
        // Obtener nombre de la especialidad
        $stmt = $conn->prepare("SELECT codigo, nombre FROM especialidades WHERE id = ?");
        $stmt->execute([$config['especialidad_id']]);
        $especialidad = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($especialidad) {
            echo "<p>📋 <strong>{$especialidad['nombre']}</strong> ({$especialidad['codigo']})</p>";
        }
        
        // Contar campos
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM especialidad_campos WHERE especialidad_id = ?");
        $stmt->execute([$config['especialidad_id']]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p>🔢 Campos disponibles: <strong>{$count['total']}</strong></p>";
        
        if ($count['total'] > 0) {
            echo "<h2>2. 📋 Lista de Campos Configurados</h2>";
            
            $stmt = $conn->prepare("
                SELECT nombre_campo, etiqueta, tipo_campo, opciones, requerido, orden
                FROM especialidad_campos 
                WHERE especialidad_id = ? 
                ORDER BY orden
            ");
            $stmt->execute([$config['especialidad_id']]);
            $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
            echo "<tr style='background: #f8f9fa;'>";
            echo "<th style='padding: 10px;'>Orden</th>";
            echo "<th style='padding: 10px;'>Campo</th>";
            echo "<th style='padding: 10px;'>Etiqueta</th>";
            echo "<th style='padding: 10px;'>Tipo</th>";
            echo "<th style='padding: 10px;'>Requerido</th>";
            echo "<th style='padding: 10px;'>Opciones</th>";
            echo "</tr>";
            
            foreach ($campos as $campo) {
                echo "<tr>";
                echo "<td style='padding: 8px; text-align: center;'>{$campo['orden']}</td>";
                echo "<td style='padding: 8px;'><code>{$campo['nombre_campo']}</code></td>";
                echo "<td style='padding: 8px;'>{$campo['etiqueta']}</td>";
                echo "<td style='padding: 8px;'><span style='background: #e9ecef; padding: 2px 6px; border-radius: 3px;'>{$campo['tipo_campo']}</span></td>";
                echo "<td style='padding: 8px; text-align: center;'>" . ($campo['requerido'] ? '✅' : '⚪') . "</td>";
                echo "<td style='padding: 8px;'>" . ($campo['opciones'] ? htmlspecialchars($campo['opciones']) : '-') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<h2>3. 🔌 Prueba del Endpoint API</h2>";
            
            // Simular llamada al endpoint
            ob_start();
            $_SERVER['REQUEST_METHOD'] = 'GET';
            include 'get_campos_simple_debug.php';
            $response = ob_get_clean();
            
            echo "<h3>Respuesta del endpoint:</h3>";
            echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<pre style='margin: 0; white-space: pre-wrap;'>" . htmlspecialchars($response) . "</pre>";
            echo "</div>";
            
            // Validar JSON
            $json_data = json_decode($response, true);
            if ($json_data) {
                echo "<h3>✅ JSON válido decodificado:</h3>";
                
                if (isset($json_data['success']) && $json_data['success']) {
                    echo "<p style='color: green;'>✅ <strong>Endpoint funciona correctamente</strong></p>";
                    
                    if (isset($json_data['campos']) && is_array($json_data['campos'])) {
                        echo "<p>📋 Campos devueltos: <strong>" . count($json_data['campos']) . "</strong></p>";
                        
                        echo "<h4>Campos procesados:</h4>";
                        echo "<ul>";
                        foreach ($json_data['campos'] as $nombre => $config_campo) {
                            echo "<li>";
                            echo "<strong>{$config_campo['label']}</strong> ";
                            echo "(<code>{$nombre}</code>) ";
                            echo "- Tipo: <span style='background: #e9ecef; padding: 1px 4px; border-radius: 2px;'>{$config_campo['tipo']}</span>";
                            if ($config_campo['requerido']) {
                                echo " <span style='color: red;'>*</span>";
                            }
                            if (isset($config_campo['opciones']) && $config_campo['opciones']) {
                                echo " - Opciones: " . implode(', ', $config_campo['opciones']);
                            }
                            echo "</li>";
                        }
                        echo "</ul>";
                    }
                    
                    if (isset($json_data['debug_info'])) {
                        echo "<h4>Información de debug:</h4>";
                        echo "<ul>";
                        foreach ($json_data['debug_info'] as $key => $value) {
                            echo "<li><strong>{$key}:</strong> {$value}</li>";
                        }
                        echo "</ul>";
                    }
                } else {
                    echo "<p style='color: red;'>❌ El endpoint indica error</p>";
                    if (isset($json_data['error'])) {
                        echo "<p>Error: {$json_data['error']}</p>";
                    }
                }
            } else {
                echo "<p style='color: red;'>❌ La respuesta no es JSON válido</p>";
                echo "<p>Error JSON: " . json_last_error_msg() . "</p>";
            }
            
            echo "<h2>4. 🌐 Enlaces de Prueba</h2>";
            echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h4>🔗 Enlaces para probar el sistema:</h4>";
            echo "<ul>";
            echo "<li><a href='get_campos_simple_debug.php' target='_blank' style='color: #007bff;'>🔌 Probar endpoint de debug</a></li>";
            echo "<li><a href='nueva_consulta.php?paciente_id=1' target='_blank' style='color: #007bff;'>📝 Formulario de nueva consulta</a></li>";
            echo "<li><a href='nueva_consulta_avanzada.php?paciente_id=1' target='_blank' style='color: #007bff;'>🚀 Formulario avanzado</a></li>";
            echo "<li><a href='pacientes.php' target='_blank' style='color: #007bff;'>👥 Lista de pacientes</a></li>";
            echo "</ul>";
            echo "</div>";
            
            echo "<h2>5. 📋 Instrucciones de Uso</h2>";
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h4>🎯 Cómo usar el sistema:</h4>";
            echo "<ol>";
            echo "<li><strong>Ir a la lista de pacientes:</strong> <a href='pacientes.php'>pacientes.php</a></li>";
            echo "<li><strong>Hacer clic en 'Nueva Consulta'</strong> junto a cualquier paciente</li>";
            echo "<li><strong>Los campos específicos aparecerán automáticamente</strong> según la especialidad configurada</li>";
            echo "<li><strong>Completar el formulario</strong> con los campos personalizados</li>";
            echo "<li><strong>Guardar la consulta</strong> normalmente</li>";
            echo "</ol>";
            
            echo "<h4>⚙️ Para configurar más especialidades:</h4>";
            echo "<p>Ejecuta: <a href='configurar_especialidades_completas.php' style='color: #007bff;'>configurar_especialidades_completas.php</a></p>";
            echo "</div>";
            
        } else {
            echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
            echo "<h3>⚠️ No hay campos configurados</h3>";
            echo "<p>La especialidad está configurada pero no tiene campos definidos.</p>";
            echo "<p><a href='configurar_especialidades_completas.php' style='background: #ffc107; color: #212529; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🔧 Configurar Campos</a></p>";
            echo "</div>";
        }
        
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h3>❌ No hay especialidad configurada</h3>";
        echo "<p>Es necesario configurar una especialidad por defecto.</p>";
        echo "<p><a href='reparar_sistema_campos.php' style='background: #dc3545; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🔧 Reparar Sistema</a></p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ Error en la prueba:</h3>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}
?>
