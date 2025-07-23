<?php
// Verificar la especialidad configurada
require_once "config.php";
try {
    $stmt = $conn->prepare("SELECT c.id, e.id as especialidad_id, e.nombre FROM configuracion c 
                           JOIN especialidades e ON c.especialidad_id = e.id 
                           WHERE c.id = 1");
    $stmt->execute();
    $especialidad = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Configuración de especialidad:<br>";
    echo "ID Configuración: " . $especialidad['id'] . "<br>";
    echo "ID Especialidad: " . $especialidad['especialidad_id'] . "<br>";
    echo "Nombre Especialidad: " . $especialidad['nombre'] . "<br>";
    
    // Verificar si es odontología
    $nombreEspecialidad = strtolower(trim($especialidad['nombre']));
    $especialidadesOdontologicas = ['odontologia', 'odontología', 'dental', 
                                  'odontologica', 'odontológica', 'dentista', 
                                  'odontopediatria', 'odontopediatría'];
    
    $esOdontologia = in_array($nombreEspecialidad, $especialidadesOdontologicas) || 
                    strpos($nombreEspecialidad, 'odonto') !== false ||
                    strpos($nombreEspecialidad, 'dental') !== false;
    
    echo "¿Es especialidad odontológica?: " . ($esOdontologia ? "SÍ" : "NO") . "<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Listar todas las especialidades disponibles
echo "<hr>Todas las especialidades disponibles:<br>";
try {
    $stmt = $conn->query("SELECT id, nombre FROM especialidades ORDER BY nombre");
    while ($esp = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: " . $esp['id'] . " - Nombre: " . $esp['nombre'] . "<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
