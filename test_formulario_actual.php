<?php
// Script de prueba para simular el envío del formulario actual y verificar que los dientes se guarden

session_start();
require_once "config.php";

// Simular datos de sesión si no existen
if (!isset($_SESSION['id'])) {
    $_SESSION['id'] = 1; // ID de prueba
    $_SESSION['loggedin'] = true;
}

echo "<h2>🧪 Prueba del Formulario Actual (Sin Modificar)</h2>";

try {
    // Verificar que existe al menos un paciente
    $stmt = $conn->query("SELECT * FROM pacientes LIMIT 1");
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$paciente) {
        echo "<p>❌ No hay pacientes en la base de datos. Crea un paciente primero.</p>";
        exit;
    }
    
    echo "<p>✅ Paciente de prueba: " . htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']) . "</p>";
    
    // Simular POST data tal como lo enviaría el formulario actual
    $_POST = [
        'action' => 'crear_consulta',
        'paciente_id' => $paciente['id'],
        'doctor_id' => $_SESSION['id'],
        'fecha' => date('Y-m-d'),
        'dientes_seleccionados' => '11,12,13,21,22,23' // Dientes de prueba seleccionados en el odontograma
    ];
    
    echo "<h3>Datos que enviaría el formulario actual:</h3>";
    echo "<ul>";
    foreach ($_POST as $campo => $valor) {
        echo "<li><strong>$campo:</strong> " . htmlspecialchars($valor) . "</li>";
    }
    echo "</ul>";
    
    // Ejecutar el mismo código que está en nueva_consulta.php
    $transactionStarted = false;
    try {
        $conn->beginTransaction();
        $transactionStarted = true;
        
        // Obtener ID de la especialidad configurada
        $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        $especialidad_id = $config['especialidad_id'];
        
        // Preparar el array de campos personalizados
        $campos_adicionales = [];
        foreach ($_POST as $key => $value) {
            // Si el campo comienza con 'campo_' es un campo dinámico
            if (strpos($key, 'campo_') === 0) {
                $campo_nombre = substr($key, 6); // Remover el prefijo 'campo_'
                $campos_adicionales[$campo_nombre] = $value;
            }
        }
        $campos_adicionales = !empty($campos_adicionales) ? json_encode($campos_adicionales) : null;
        
        // Insertar consulta con campos adicionales y dientes seleccionados
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
        $stmt->execute([
            $_POST['paciente_id'],
            $_POST['doctor_id'] ?? $_SESSION['id'], // Usar ID de la sesión si no se proporciona
            $_POST['fecha'],
            $_POST['motivo_consulta'] ?? 'Consulta médica general', // Valor por defecto si no se proporciona
            $_POST['diagnostico'] ?? null,
            $_POST['tratamiento'] ?? null,
            $_POST['observaciones'] ?? null,
            $campos_adicionales,
            $especialidad_id,
            $_POST['dientes_seleccionados'] ?? null // Guardar los dientes seleccionados
        ]);
        
        $consulta_id = $conn->lastInsertId();
        
        // Guardar valores de campos personalizados en tabla consulta_campos_valores
        if (!empty($_POST)) {
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'campo_') === 0) {
                    $campo_nombre = substr($key, 6); // Remover el prefijo 'campo_'
                    
                    // Obtener el ID del campo desde la tabla especialidad_campos
                    $stmt = $conn->prepare("
                        SELECT id FROM especialidad_campos 
                        WHERE especialidad_id = ? AND nombre_campo = ?
                    ");
                    $stmt->execute([$especialidad_id, $campo_nombre]);
                    $campo = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($campo) {
                        // Insertar el valor en consulta_campos_valores
                        $stmt = $conn->prepare("
                            INSERT INTO consulta_campos_valores (consulta_id, campo_id, valor)
                            VALUES (?, ?, ?)
                        ");
                        $stmt->execute([$consulta_id, $campo['id'], $value]);
                    }
                }
            }
        }
        
        $conn->commit();
        
        echo "<h3>✅ ¡Éxito!</h3>";
        echo "<p>Consulta creada exitosamente con ID: <strong>$consulta_id</strong></p>";
        echo "<p>Dientes guardados: <strong>" . htmlspecialchars($_POST['dientes_seleccionados']) . "</strong></p>";
        echo "<p>Motivo de consulta: <strong>Consulta médica general</strong> (valor por defecto)</p>";
        
    } catch (Exception $e) {
        // Solo hacer rollback si la transacción se inició
        if ($transactionStarted && $conn->inTransaction()) {
            $conn->rollBack();
        }
        throw $e; // Re-lanzar para el catch exterior
    }
    
    echo "<h3>Verificación en la Base de Datos:</h3>";
    
    // Verificar que se guardó correctamente
    $stmt = $conn->prepare("SELECT * FROM historial_medico WHERE id = ?");
    $stmt->execute([$consulta_id]);
    $consultaGuardada = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($consultaGuardada) {
        echo "<div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>Datos guardados en la base de datos:</h4>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> " . htmlspecialchars($consultaGuardada['id']) . "</li>";
        echo "<li><strong>Paciente ID:</strong> " . htmlspecialchars($consultaGuardada['paciente_id']) . "</li>";
        echo "<li><strong>Fecha:</strong> " . htmlspecialchars($consultaGuardada['fecha']) . "</li>";
        echo "<li><strong>Motivo Consulta:</strong> " . htmlspecialchars($consultaGuardada['motivo_consulta']) . "</li>";
        echo "<li><strong>Dientes Seleccionados:</strong> <span style='background: yellow; padding: 2px 5px;'>" . htmlspecialchars($consultaGuardada['dientes_seleccionados']) . "</span></li>";
        echo "<li><strong>Especialidad ID:</strong> " . htmlspecialchars($consultaGuardada['especialidad_id']) . "</li>";
        echo "</ul>";
        echo "</div>";
    }
    
    echo "<h3>Enlaces:</h3>";
    echo "<p>";
    echo "<a href='verificar_dientes_guardados.php' style='padding: 10px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📊 Ver Todas las Consultas con Dientes</a>";
    echo "<a href='nueva_consulta.php?paciente_id=" . $paciente['id'] . "' style='padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>🧪 Probar Formulario Real</a>";
    echo "</p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    
    echo "<h4>Información de depuración:</h4>";
    echo "<pre>";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "</pre>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #0056b3; }
h3 { color: #333; }
</style>
