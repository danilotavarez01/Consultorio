<?php
require_once 'config.php';

echo "<h1>Verificación y Corrección de Columna dientes_seleccionados</h1>";

try {
    // 1. Verificar si la columna existe
    echo "<h2>1. Verificando si existe la columna dientes_seleccionados</h2>";
    
    $stmt = $conn->prepare("SHOW COLUMNS FROM historial_medico LIKE 'dientes_seleccionados'");
    $stmt->execute();
    $columna_existe = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($columna_existe) {
        echo "<div style='background: lightgreen; padding: 10px; border: 1px solid green;'>";
        echo "✅ La columna 'dientes_seleccionados' SÍ existe";
        echo "<br>Tipo: " . $columna_existe['Type'];
        echo "<br>Null: " . $columna_existe['Null'];
        echo "<br>Key: " . $columna_existe['Key'];
        echo "<br>Default: " . $columna_existe['Default'];
        echo "</div>";
        
        $necesita_crear = false;
    } else {
        echo "<div style='background: lightcoral; padding: 10px; border: 1px solid red;'>";
        echo "❌ La columna 'dientes_seleccionados' NO existe";
        echo "<br>Esta es la causa del problema. Necesitamos crearla.";
        echo "</div>";
        
        $necesita_crear = true;
    }
    
    // 2. Crear la columna si no existe
    if ($necesita_crear) {
        echo "<h2>2. Creando la columna dientes_seleccionados</h2>";
        
        if (isset($_POST['crear_columna'])) {
            try {
                $sql = "ALTER TABLE historial_medico ADD COLUMN dientes_seleccionados TEXT NULL COMMENT 'Dientes seleccionados en formato CSV'";
                $conn->exec($sql);
                
                echo "<div style='background: lightgreen; padding: 10px; border: 1px solid green;'>";
                echo "✅ ¡Columna creada exitosamente!";
                echo "<br>Tipo: TEXT";
                echo "<br>Permite NULL: SÍ";
                echo "<br>Comentario: Dientes seleccionados en formato CSV";
                echo "</div>";
                
                echo "<script>setTimeout(function(){ location.reload(); }, 2000);</script>";
                
            } catch (Exception $e) {
                echo "<div style='background: lightcoral; padding: 10px; border: 1px solid red;'>";
                echo "❌ Error al crear la columna: " . htmlspecialchars($e->getMessage());
                echo "</div>";
            }
        } else {
            echo "<form method='post'>";
            echo "<p>La columna no existe. ¿Desea crearla ahora?</p>";
            echo "<button type='submit' name='crear_columna' value='1' style='background: #dc3545; color: white; padding: 15px 30px; border: none; font-size: 16px; cursor: pointer;'>";
            echo "🔧 SÍ, CREAR COLUMNA AHORA";
            echo "</button>";
            echo "</form>";
        }
    }
    
    // 3. Si la columna existe, hacer pruebas
    if (!$necesita_crear) {
        echo "<h2>3. Pruebas de funcionamiento</h2>";
        
        // Test 1: Insertar un registro de prueba
        echo "<h3>Test 1: Insertar consulta con dientes</h3>";
        
        if (isset($_POST['test_insert'])) {
            try {
                // Obtener especialidad
                $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
                $stmt->execute();
                $config = $stmt->fetch(PDO::FETCH_ASSOC);
                $especialidad_id = $config['especialidad_id'] ?? 1;
                
                // Preparar datos
                $dientes_test = "11,12,21,22";
                $campos_adicionales = [
                    'presion' => '120/80',
                    'temperatura' => '36.5',
                    'dientes_seleccionados' => $dientes_test
                ];
                $campos_json = json_encode($campos_adicionales);
                
                // Insertar
                $sql = "INSERT INTO historial_medico (
                    paciente_id, doctor_id, fecha, motivo_consulta, diagnostico, 
                    tratamiento, observaciones, campos_adicionales, especialidad_id, dientes_seleccionados
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([
                    1, // paciente_id
                    1, // doctor_id  
                    date('Y-m-d'), // fecha
                    'Test de dientes seleccionados', // motivo_consulta
                    'Caries en incisivos centrales superiores', // diagnostico
                    'Empastes compuestos', // tratamiento
                    'Test de funcionalidad del sistema', // observaciones
                    $campos_json, // campos_adicionales
                    $especialidad_id, // especialidad_id
                    $dientes_test // dientes_seleccionados
                ]);
                
                if ($result) {
                    $nuevo_id = $conn->lastInsertId();
                    echo "<div style='background: lightgreen; padding: 10px; border: 1px solid green;'>";
                    echo "✅ ¡Consulta insertada exitosamente!";
                    echo "<br>ID: $nuevo_id";
                    echo "<br>Dientes guardados: $dientes_test";
                    echo "</div>";
                    
                    // Verificar que se guardó correctamente
                    $stmt = $conn->prepare("SELECT dientes_seleccionados, campos_adicionales FROM historial_medico WHERE id = ?");
                    $stmt->execute([$nuevo_id]);
                    $verificacion = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    echo "<h4>Verificación de datos guardados:</h4>";
                    echo "<ul>";
                    echo "<li><strong>Columna dientes_seleccionados:</strong> '" . htmlspecialchars($verificacion['dientes_seleccionados']) . "'</li>";
                    echo "<li><strong>JSON campos_adicionales:</strong> " . htmlspecialchars($verificacion['campos_adicionales']) . "</li>";
                    echo "</ul>";
                    
                    $json_data = json_decode($verificacion['campos_adicionales'], true);
                    if (isset($json_data['dientes_seleccionados'])) {
                        echo "<p>✅ Los dientes también están en el JSON: '" . htmlspecialchars($json_data['dientes_seleccionados']) . "'</p>";
                    }
                    
                    echo "<p><a href='ver_consulta.php?id=$nuevo_id' target='_blank' style='background: #007bff; color: white; padding: 10px; text-decoration: none;'>Ver esta consulta</a></p>";
                }
                
            } catch (Exception $e) {
                echo "<div style='background: lightcoral; padding: 10px; border: 1px solid red;'>";
                echo "❌ Error en el test: " . htmlspecialchars($e->getMessage());
                echo "</div>";
            }
        } else {
            echo "<form method='post'>";
            echo "<button type='submit' name='test_insert' value='1' style='background: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer;'>";
            echo "🧪 Ejecutar test de inserción";
            echo "</button>";
            echo "</form>";
        }
        
        // Test 2: Verificar consultas existentes
        echo "<h3>Test 2: Consultas existentes</h3>";
        
        $stmt = $conn->prepare("
            SELECT id, fecha, dientes_seleccionados, campos_adicionales 
            FROM historial_medico 
            ORDER BY id DESC 
            LIMIT 5
        ");
        $stmt->execute();
        $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($consultas)) {
            echo "<p>No hay consultas en la base de datos.</p>";
        } else {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Fecha</th><th>Dientes (Columna)</th><th>Dientes (JSON)</th><th>Estado</th></tr>";
            
            foreach ($consultas as $consulta) {
                $dientes_columna = $consulta['dientes_seleccionados'];
                $json_data = json_decode($consulta['campos_adicionales'] ?? '{}', true);
                $dientes_json = $json_data['dientes_seleccionados'] ?? '';
                
                $estado = '';
                if (!empty($dientes_columna) && !empty($dientes_json)) {
                    $estado = '✅ Ambos';
                } elseif (!empty($dientes_columna)) {
                    $estado = '🟡 Solo columna';
                } elseif (!empty($dientes_json)) {
                    $estado = '🟠 Solo JSON';
                } else {
                    $estado = '❌ Ninguno';
                }
                
                echo "<tr>";
                echo "<td>" . $consulta['id'] . "</td>";
                echo "<td>" . htmlspecialchars($consulta['fecha']) . "</td>";
                echo "<td>" . htmlspecialchars($dientes_columna ?: 'Vacío') . "</td>";
                echo "<td>" . htmlspecialchars($dientes_json ?: 'Vacío') . "</td>";
                echo "<td>$estado</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='background: lightcoral; padding: 10px; border: 1px solid red;'>";
    echo "❌ Error general: " . htmlspecialchars($e->getMessage());
    echo "</div>";
}
?>

<div style="background: #e7f3ff; padding: 15px; border: 1px solid #b3d9ff; margin: 20px 0;">
    <h3>📋 Siguientes pasos:</h3>
    <ol>
        <li>Si la columna no existía, créela usando el botón arriba</li>
        <li>Ejecute el test de inserción para verificar que funciona</li>
        <li>Pruebe crear una consulta real en <a href="nueva_consulta.php" target="_blank">nueva_consulta.php</a></li>
        <li>Verifique que los dientes aparecen en <a href="ver_consulta.php" target="_blank">ver_consulta.php</a></li>
    </ol>
</div>

<style>
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
h1, h2, h3 { color: #333; }
</style>
