<?php
// Script de prueba para verificar que todo esté funcionando correctamente

require_once "config.php";

echo "<h2>🧪 Prueba del Sistema de Odontograma</h2>";

echo "<h3>1. Verificación de la Base de Datos</h3>";

try {
    // Verificar que la columna dientes_seleccionados existe
    $stmt = $conn->prepare("SHOW COLUMNS FROM historial_medico LIKE 'dientes_seleccionados'");
    $stmt->execute();
    $columnExists = $stmt->fetch();
    
    if ($columnExists) {
        echo "✅ Columna 'dientes_seleccionados' existe en historial_medico<br>";
    } else {
        echo "❌ Columna 'dientes_seleccionados' NO existe en historial_medico<br>";
    }
    
    // Verificar la especialidad configurada
    $stmt = $conn->prepare("SELECT e.nombre FROM configuracion c JOIN especialidades e ON c.especialidad_id = e.id WHERE c.id = 1");
    $stmt->execute();
    $especialidad = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($especialidad) {
        echo "✅ Especialidad configurada: " . htmlspecialchars($especialidad['nombre']) . "<br>";
        
        $nombreEspecialidad = strtolower(trim($especialidad['nombre']));
        $esOdontologia = (strpos($nombreEspecialidad, 'odonto') !== false || 
                         strpos($nombreEspecialidad, 'dental') !== false ||
                         in_array($nombreEspecialidad, ['odontologia', 'odontología', 'dentista']));
        
        if ($esOdontologia) {
            echo "✅ La especialidad ES odontología - el odontograma debería mostrarse<br>";
        } else {
            echo "⚠️ La especialidad NO es odontología - el odontograma no se mostrará<br>";
        }
    } else {
        echo "❌ No se encontró especialidad configurada<br>";
    }

} catch (PDOException $e) {
    echo "❌ Error de base de datos: " . $e->getMessage() . "<br>";
}

echo "<h3>2. Verificación de Archivos</h3>";

$archivosNecesarios = [
    'nueva_consulta.php' => 'Formulario principal',
    'odontograma_svg.php' => 'Odontograma SVG',
    'forzar_odontograma_corregido.php' => 'Script de carga del odontograma',
    'config.php' => 'Configuración de base de datos'
];

foreach ($archivosNecesarios as $archivo => $descripcion) {
    if (file_exists($archivo)) {
        echo "✅ $archivo ($descripcion) - existe<br>";
    } else {
        echo "❌ $archivo ($descripcion) - NO existe<br>";
    }
}

echo "<h3>3. Prueba de Inserción Simulada</h3>";

// Simular una inserción de consulta con dientes seleccionados
$dientesEjemplo = "11,12,21,22,31,32";
echo "Datos de prueba: dientes_seleccionados = '$dientesEjemplo'<br>";

try {
    // Solo verificar que la consulta SQL sea válida (no ejecutar)
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
    echo "✅ Consulta SQL preparada correctamente<br>";
    echo "✅ El sistema está listo para guardar dientes seleccionados<br>";
    
} catch (PDOException $e) {
    echo "❌ Error en la consulta SQL: " . $e->getMessage() . "<br>";
}

echo "<h3>4. Enlaces de Prueba</h3>";
echo "<p>";
echo "<a href='nueva_consulta.php?paciente_id=1' target='_blank' style='padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🧪 Probar Nueva Consulta</a>";
echo "<a href='verificar_dientes_guardados.php' target='_blank' style='padding: 10px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📊 Ver Consultas Guardadas</a>";
echo "<a href='pacientes.php' target='_blank' style='padding: 10px 15px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;'>👥 Ver Pacientes</a>";
echo "</p>";

echo "<h3>5. Instrucciones de Prueba</h3>";
echo "<ol>";
echo "<li><strong>Abrir Nueva Consulta:</strong> Haz clic en el enlace de arriba o ve directamente a una consulta con un paciente</li>";
echo "<li><strong>Verificar Odontograma:</strong> Si la especialidad es odontología, deberías ver el odontograma SVG</li>";
echo "<li><strong>Seleccionar Dientes:</strong> Haz clic en varios dientes para seleccionarlos</li>";
echo "<li><strong>Verificar Lista:</strong> Los dientes seleccionados deben aparecer en 'Dientes seleccionados'</li>";
echo "<li><strong>Guardar Consulta:</strong> Completa los campos necesarios y presiona 'Guardar Consulta'</li>";
echo "<li><strong>Verificar Guardado:</strong> Ve a 'Ver Consultas Guardadas' para confirmar que se guardaron los dientes</li>";
echo "</ol>";

echo "<h3>6. Depuración</h3>";
echo "<p>Si algo no funciona:</p>";
echo "<ul>";
echo "<li>Abre las herramientas de desarrollo del navegador (F12)</li>";
echo "<li>Ve a la pestaña 'Console' para ver mensajes de depuración</li>";
echo "<li>Busca mensajes que empiecen con '[ODONTOGRAMA]'</li>";
echo "<li>Verifica que no haya errores JavaScript</li>";
echo "</ul>";

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #0056b3; }
h3 { color: #333; border-bottom: 2px solid #ddd; padding-bottom: 5px; }
</style>
