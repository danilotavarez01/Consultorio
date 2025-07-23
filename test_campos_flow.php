<?php
require_once "config.php";

echo "<h1>🔍 Test del Flujo de Campos de Especialidad</h1>";

try {
    // 1. Verificar configuración
    echo "<h2>1. Configuración Actual:</h2>";
    $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config && $config['especialidad_id']) {
        $especialidad_id = $config['especialidad_id'];
        echo "<p>✅ Especialidad configurada: <strong>{$especialidad_id}</strong></p>";
        
        // 2. Verificar que la especialidad existe
        $stmt = $conn->prepare("SELECT id, codigo, nombre FROM especialidades WHERE id = ?");
        $stmt->execute([$especialidad_id]);
        $especialidad = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($especialidad) {
            echo "<p>✅ Especialidad encontrada: <strong>{$especialidad['nombre']}</strong> ({$especialidad['codigo']})</p>";
            
            // 3. Buscar campos para esta especialidad
            echo "<h2>2. Campos para esta Especialidad:</h2>";
            $stmt = $conn->prepare("
                SELECT nombre_campo, etiqueta, tipo_campo, opciones, requerido, orden
                FROM especialidad_campos 
                WHERE especialidad_id = ? 
                ORDER BY orden
            ");
            $stmt->execute([$especialidad_id]);
            $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($campos) {
                echo "<p>✅ Se encontraron <strong>" . count($campos) . "</strong> campos:</p>";
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr style='background: #f0f0f0;'>";
                echo "<th>Orden</th><th>Nombre Campo</th><th>Etiqueta</th><th>Tipo</th><th>Requerido</th><th>Opciones</th>";
                echo "</tr>";
                
                foreach ($campos as $campo) {
                    echo "<tr>";
                    echo "<td>{$campo['orden']}</td>";
                    echo "<td>{$campo['nombre_campo']}</td>";
                    echo "<td>{$campo['etiqueta']}</td>";
                    echo "<td>{$campo['tipo_campo']}</td>";
                    echo "<td>" . ($campo['requerido'] ? 'Sí' : 'No') . "</td>";
                    echo "<td>{$campo['opciones']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                // 4. Test del endpoint
                echo "<h2>3. Test del Endpoint get_campos_simple.php:</h2>";
                
                // Simular la llamada interna
                ob_start();
                $_SERVER['REQUEST_METHOD'] = 'GET';
                include 'get_campos_simple.php';
                $response = ob_get_clean();
                
                echo "<h3>Respuesta del endpoint:</h3>";
                echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
                echo htmlspecialchars($response);
                echo "</pre>";
                
                // Validar JSON
                $json_data = json_decode($response, true);
                if ($json_data) {
                    echo "<h3>✅ JSON válido decodificado:</h3>";
                    echo "<pre style='background: #e8f5e8; padding: 10px; border: 1px solid #ccc;'>";
                    print_r($json_data);
                    echo "</pre>";
                    
                    if (isset($json_data['success']) && $json_data['success']) {
                        echo "<p style='color: green;'><strong>✅ El endpoint funciona correctamente</strong></p>";
                        
                        if (isset($json_data['campos']) && count($json_data['campos']) > 0) {
                            echo "<p>✅ Campos devueltos: " . count($json_data['campos']) . "</p>";
                        } else {
                            echo "<p style='color: orange;'>⚠️ El endpoint no devuelve campos</p>";
                        }
                    } else {
                        echo "<p style='color: red;'>❌ El endpoint indica error</p>";
                    }
                } else {
                    echo "<p style='color: red;'>❌ La respuesta no es JSON válido</p>";
                    echo "<p>Error JSON: " . json_last_error_msg() . "</p>";
                }
                
            } else {
                echo "<p style='color: red;'>❌ No se encontraron campos para la especialidad {$especialidad_id}</p>";
                
                // Sugerir solución
                echo "<h3>💡 Solución:</h3>";
                echo "<p>Necesitas ejecutar el script de configuración para agregar campos:</p>";
                echo "<p><a href='configurar_especialidades_completas.php' target='_blank' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🔧 Configurar Especialidades</a></p>";
            }
            
        } else {
            echo "<p style='color: red;'>❌ La especialidad configurada ({$especialidad_id}) no existe en la tabla especialidades</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ No hay especialidad configurada en la tabla configuracion</p>";
        
        // Mostrar especialidades disponibles
        $stmt = $conn->query("SELECT id, codigo, nombre FROM especialidades ORDER BY nombre");
        $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($especialidades) {
            echo "<h3>📋 Especialidades disponibles:</h3>";
            echo "<ul>";
            foreach ($especialidades as $esp) {
                echo "<li>ID: {$esp['id']} - {$esp['nombre']} ({$esp['codigo']})</li>";
            }
            echo "</ul>";
            
            echo "<h3>💡 Solución:</h3>";
            echo "<p>Ejecuta el script para configurar la especialidad por defecto:</p>";
            echo "<p><a href='configurar_especialidades_completas.php' target='_blank' style='background: #28a745; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🔧 Configurar Sistema</a></p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
}
?>
