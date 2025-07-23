<?php
// Test para verificar la detección de especialidad odontología
header('Content-Type: text/html; charset=utf-8');
require_once "config.php";

echo "<h1>Prueba de Detección de Especialidad Odontología</h1>";

// Obtener la especialidad configurada actual
try {
    $stmt = $conn->prepare("SELECT e.nombre, e.id FROM configuracion c 
                           JOIN especialidades e ON c.especialidad_id = e.id 
                           WHERE c.id = 1");
    $stmt->execute();
    $especialidad = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($especialidad) {
        echo "<div style='background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<strong>Especialidad configurada:</strong> " . htmlspecialchars($especialidad['nombre']) . " (ID: {$especialidad['id']})";
        echo "</div>";
        
        // Determinar si se debe mostrar el odontograma con la especialidad actual
        $mostrarOdontograma = (
            strtolower($especialidad['nombre']) == 'odontologia' || 
            strtolower($especialidad['nombre']) == 'odontología' || 
            strtolower($especialidad['nombre']) == 'dental'
        );
        
        echo "<div style='padding: 15px; border-radius: 5px; margin: 20px 0;" . 
             ($mostrarOdontograma ? " background-color: #d4edda;" : " background-color: #f8d7da;") . "'>";
        echo "<strong>¿Se muestra el odontograma?</strong> " . ($mostrarOdontograma ? "SÍ" : "NO");
        echo "</div>";
        
        // Mostrar todas las especialidades disponibles
        echo "<h2>Todas las especialidades disponibles:</h2>";
        $stmt = $conn->query("SELECT id, nombre FROM especialidades ORDER BY nombre");
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 20px;'>";
        echo "<tr style='background-color: #f2f2f2;'><th>ID</th><th>Nombre</th><th>¿Activaría odontograma?</th></tr>";
        
        while($esp = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $activariaOdontograma = (
                strtolower($esp['nombre']) == 'odontologia' || 
                strtolower($esp['nombre']) == 'odontología' || 
                strtolower($esp['nombre']) == 'dental'
            );
            
            echo "<tr>";
            echo "<td>{$esp['id']}</td>";
            echo "<td>" . htmlspecialchars($esp['nombre']) . "</td>";
            echo "<td style='" . ($activariaOdontograma ? "background-color: #d4edda;" : "background-color: #f8d7da;") . "'>";
            echo $activariaOdontograma ? "SÍ" : "NO";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Formulario para cambiar la especialidad de prueba
        echo "<h2>Cambiar especialidad para prueba:</h2>";
        echo "<form method='post' action=''>";
        echo "<input type='hidden' name='action' value='cambiar_especialidad'>";
        echo "<div style='margin-bottom: 15px;'>";
        echo "<label><strong>Seleccione una especialidad:</strong></label><br>";
        echo "<select name='especialidad_id' class='form-control' style='padding: 5px; margin-top: 10px; width: 300px;'>";
        
        $stmt = $conn->query("SELECT id, nombre FROM especialidades ORDER BY nombre");
        while($esp = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $selected = ($esp['id'] == $especialidad['id']) ? "selected" : "";
            echo "<option value='{$esp['id']}' {$selected}>" . htmlspecialchars($esp['nombre']) . "</option>";
        }
        echo "</select>";
        echo "</div>";
        
        echo "<button type='submit' style='background-color: #007bff; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer;'>
              Cambiar especialidad temporalmente</button>";
        echo "</form>";
        
    } else {
        echo "<div style='background-color: #f8d7da; padding: 15px; border-radius: 5px;'>";
        echo "No se encontró ninguna configuración de especialidad";
        echo "</div>";
    }
    
    // Procesar el cambio de especialidad
    if (isset($_POST['action']) && $_POST['action'] == "cambiar_especialidad" && isset($_POST['especialidad_id'])) {
        $esp_id = $_POST['especialidad_id'];
        
        // Actualizar la especialidad en la configuración
        $stmt = $conn->prepare("UPDATE configuracion SET especialidad_id = ? WHERE id = 1");
        $result = $stmt->execute([$esp_id]);
        
        if ($result) {
            echo "<div style='background-color: #d4edda; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
            echo "Especialidad cambiada correctamente. <a href=''>Actualizar página</a> para ver los cambios.";
            echo "</div>";
        } else {
            echo "<div style='background-color: #f8d7da; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
            echo "Error al cambiar la especialidad.";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "Error: " . $e->getMessage();
    echo "</div>";
}
?>

<div style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
    <p><a href="nueva_consulta.php?paciente_id=1" style="margin-right: 15px;">Probar Nueva Consulta</a> | 
    <a href="diagnostico_odontograma.php">Diagnóstico Odontograma</a></p>
</div>
