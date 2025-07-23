<?php
session_start();
require_once 'config.php';

// Verificar si hay sesión activa o simular una
if (!isset($_SESSION['id'])) {
    // Simular usuario para pruebas
    $_SESSION['id'] = 1;
    $_SESSION['nombre'] = 'Doctor Test';
}

echo "<h1>Test Rápido: Crear Consulta con Dientes</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_consulta'])) {
    try {
        // Obtener especialidad configurada
        $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        $especialidad_id = $config['especialidad_id'];
        
        // Preparar el array de campos personalizados
        $campos_adicionales = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'campo_') === 0) {
                $campo_nombre = substr($key, 6);
                $campos_adicionales[$campo_nombre] = $value;
            }
        }
        
        // Agregar los dientes seleccionados al array de campos adicionales
        if (isset($_POST['dientes_seleccionados']) && !empty($_POST['dientes_seleccionados'])) {
            $campos_adicionales['dientes_seleccionados'] = $_POST['dientes_seleccionados'];
        }
        
        $campos_adicionales_json = !empty($campos_adicionales) ? json_encode($campos_adicionales) : null;
        
        // Insertar consulta
        $sql = "INSERT INTO historial_medico (
                    paciente_id, 
                    doctor_id, 
                    fecha, 
                    motivo_consulta, 
                    diagnostico, 
                    tratamiento, 
                    observaciones,
                    campos_adicionales,
                    especialidad_id,
                    dientes_seleccionados
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([
            $_POST['paciente_id'],
            $_POST['doctor_id'],
            $_POST['fecha'],
            $_POST['motivo_consulta'],
            $_POST['diagnostico'],
            $_POST['tratamiento'],
            $_POST['observaciones'],
            $campos_adicionales_json,
            $especialidad_id,
            $_POST['dientes_seleccionados']
        ]);
        
        if ($success) {
            $consulta_id = $conn->lastInsertId();
            
            echo "<div style='background: lightgreen; padding: 15px; margin: 10px 0; border: 2px solid green;'>";
            echo "<h2>✅ ¡Consulta creada exitosamente!</h2>";
            echo "<p><strong>ID de consulta:</strong> " . $consulta_id . "</p>";
            echo "</div>";
            
            // Verificar los datos guardados
            $stmt = $conn->prepare("SELECT * FROM historial_medico WHERE id = ?");
            $stmt->execute([$consulta_id]);
            $consulta_guardada = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<h3>Verificación de datos guardados:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>Campo</th><th>Valor</th></tr>";
            echo "<tr><td>Dientes seleccionados (columna)</td><td>" . htmlspecialchars($consulta_guardada['dientes_seleccionados'] ?? 'NULL') . "</td></tr>";
            echo "<tr><td>Campos adicionales (JSON)</td><td>" . htmlspecialchars($consulta_guardada['campos_adicionales'] ?? 'NULL') . "</td></tr>";
            
            // Verificar si los dientes están en el JSON
            $campos_json = json_decode($consulta_guardada['campos_adicionales'], true);
            $dientes_en_json = isset($campos_json['dientes_seleccionados']) ? $campos_json['dientes_seleccionados'] : 'No encontrados';
            
            echo "<tr><td>Dientes extraídos del JSON</td><td>" . htmlspecialchars($dientes_en_json) . "</td></tr>";
            echo "<tr><td>¿Coinciden los valores?</td><td style='color: " . ($consulta_guardada['dientes_seleccionados'] === $dientes_en_json ? 'green' : 'red') . ";'>";
            echo ($consulta_guardada['dientes_seleccionados'] === $dientes_en_json ? 'SÍ ✅' : 'NO ❌');
            echo "</td></tr>";
            echo "</table>";
            
            echo "<h3>Enlaces de verificación:</h3>";
            echo "<ul>";
            echo "<li><a href='ver_consulta.php?id=" . $consulta_id . "' target='_blank' style='font-weight: bold; color: blue;'>🔍 Ver esta consulta en detalle</a></li>";
            echo "<li><a href='test_completo_dientes.php' target='_blank'>📊 Ver test completo</a></li>";
            echo "</ul>";
            
        } else {
            echo "<div style='background: lightcoral; padding: 15px; margin: 10px 0; border: 2px solid red;'>";
            echo "<h2>❌ Error al crear la consulta</h2>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background: lightcoral; padding: 15px; margin: 10px 0; border: 2px solid red;'>";
        echo "<h2>❌ Error:</h2>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
}

// Obtener pacientes y doctores para el formulario
try {
    $stmt = $conn->prepare("SELECT id, nombre FROM pacientes ORDER BY nombre LIMIT 10");
    $stmt->execute();
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $conn->prepare("SELECT id, nombre FROM usuarios WHERE rol = 'doctor' ORDER BY nombre LIMIT 10");
    $stmt->execute();
    $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $pacientes = [];
    $doctores = [];
}
?>

<form method="post" style="background: #f8f9fa; padding: 20px; border: 1px solid #ddd; margin: 20px 0;">
    <h2>Crear Consulta de Prueba</h2>
    
    <div style="margin: 10px 0;">
        <label><strong>Paciente:</strong></label><br>
        <select name="paciente_id" required style="width: 200px; padding: 5px;">
            <?php if (empty($pacientes)): ?>
                <option value="1">Paciente Test (ID: 1)</option>
            <?php else: ?>
                <?php foreach ($pacientes as $paciente): ?>
                    <option value="<?php echo $paciente['id']; ?>">
                        <?php echo htmlspecialchars($paciente['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    
    <div style="margin: 10px 0;">
        <label><strong>Doctor:</strong></label><br>
        <select name="doctor_id" required style="width: 200px; padding: 5px;">
            <?php if (empty($doctores)): ?>
                <option value="1">Doctor Test (ID: 1)</option>
            <?php else: ?>
                <?php foreach ($doctores as $doctor): ?>
                    <option value="<?php echo $doctor['id']; ?>">
                        <?php echo htmlspecialchars($doctor['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    
    <div style="margin: 10px 0;">
        <label><strong>Fecha:</strong></label><br>
        <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" required style="padding: 5px;">
    </div>
    
    <div style="margin: 10px 0;">
        <label><strong>Motivo de consulta:</strong></label><br>
        <input type="text" name="motivo_consulta" value="Test de odontograma y dientes seleccionados" required style="width: 400px; padding: 5px;">
    </div>
    
    <div style="margin: 10px 0;">
        <label><strong>Diagnóstico:</strong></label><br>
        <textarea name="diagnostico" style="width: 400px; height: 60px; padding: 5px;">Caries en incisivos superiores (11, 12, 21, 22)</textarea>
    </div>
    
    <div style="margin: 10px 0;">
        <label><strong>Tratamiento:</strong></label><br>
        <textarea name="tratamiento" style="width: 400px; height: 60px; padding: 5px;">Empastes y limpieza dental</textarea>
    </div>
    
    <div style="margin: 10px 0;">
        <label><strong>Observaciones:</strong></label><br>
        <textarea name="observaciones" style="width: 400px; height: 60px; padding: 5px;">Consulta de prueba para verificar funcionalidad del odontograma</textarea>
    </div>
    
    <div style="margin: 10px 0;">
        <label><strong>Dientes seleccionados:</strong></label><br>
        <input type="text" name="dientes_seleccionados" value="11,12,21,22" placeholder="Ej: 11,12,21,22" style="width: 200px; padding: 5px;">
        <small style="color: #666; display: block;">Separados por comas</small>
    </div>
    
    <div style="margin: 10px 0;">
        <label><strong>Campo personalizado - Presión arterial:</strong></label><br>
        <input type="text" name="campo_presion_arterial" value="120/80" style="width: 100px; padding: 5px;">
    </div>
    
    <div style="margin: 10px 0;">
        <label><strong>Campo personalizado - Temperatura:</strong></label><br>
        <input type="text" name="campo_temperatura" value="36.5" style="width: 100px; padding: 5px;">
    </div>
    
    <div style="margin: 20px 0;">
        <button type="submit" name="crear_consulta" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">
            🦷 Crear Consulta de Prueba
        </button>
    </div>
</form>

<div style="background: #e7f3ff; padding: 15px; border: 1px solid #b3d9ff; margin: 20px 0;">
    <h3>📋 Instrucciones:</h3>
    <ol>
        <li>Llene el formulario arriba (ya tiene valores de prueba)</li>
        <li>Haga clic en "Crear Consulta de Prueba"</li>
        <li>Verifique que los dientes se guardan en ambos lugares (columna y JSON)</li>
        <li>Use el enlace "Ver esta consulta en detalle" para verificar que el odontograma se muestra</li>
        <li>Verifique que los dientes seleccionados aparecen marcados en el odontograma</li>
    </ol>
</div>

<div style="background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; margin: 20px 0;">
    <h3>🔧 Enlaces útiles:</h3>
    <ul>
        <li><a href="test_completo_dientes.php" target="_blank">Test completo del sistema</a></li>
        <li><a href="nueva_consulta.php" target="_blank">Formulario real de nueva consulta</a></li>
        <li><a href="test_dientes_guardado.php" target="_blank">Verificar datos guardados</a></li>
    </ul>
</div>
