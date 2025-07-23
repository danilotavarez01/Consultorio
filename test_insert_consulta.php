<?php
require_once "config.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test de inserción en historial_medico</h1>";

try {
    // Obtener estructura de tabla
    $stmt = $conn->query("SHOW COLUMNS FROM historial_medico");
    
    echo "<h2>Estructura de tabla:</h2>";
    echo "<ul>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<li>" . $row['Field'] . " (" . $row['Type'] . ")</li>";
    }
    echo "</ul>";
    
    // Datos de prueba
    $paciente_id = 1; // Asume que este paciente existe
    $campos_adicionales = json_encode(['temp' => '37', 'presion' => '120/80']);
    
    // Preparar la consulta de inserción con todas las columnas explícitamente
    $sql = "INSERT INTO historial_medico 
            (paciente_id, doctor_id, fecha, motivo_consulta, diagnostico, tratamiento, 
             observaciones, campos_adicionales, especialidad_id) 
            VALUES 
            (?, 1, CURRENT_DATE(), 'Test motivo', 'Test diagnóstico', 'Test tratamiento', 
             'Test observaciones', ?, 1)";
    
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([$paciente_id, $campos_adicionales]);
    
    if($result) {
        $id = $conn->lastInsertId();
        echo "<p style='color:green'>Inserción exitosa! ID: $id</p>";
        
        // Verificar que se insertó correctamente
        $stmt = $conn->prepare("SELECT * FROM historial_medico WHERE id = ?");
        $stmt->execute([$id]);
        $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h2>Datos insertados:</h2>";
        echo "<pre>";
        print_r($consulta);
        echo "</pre>";
    } else {
        echo "<p style='color:red'>Error al insertar</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color:red'>Error de base de datos: " . $e->getMessage() . "</p>";
} catch(Exception $e) {
    echo "<p style='color:red'>Error general: " . $e->getMessage() . "</p>";
}
?>
