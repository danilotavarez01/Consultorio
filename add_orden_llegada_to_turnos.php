<?php
// Archivo para agregar la columna orden_llegada a la tabla turnos
require_once "config.php";

// Función para verificar la conexión y reconectar si es necesario
function verificarConexion($conn) {
    try {
        $conn->query("SELECT 1");
        return $conn;
    } catch (PDOException $e) {
        // Si hay error, intentar reconectar
        try {
            $conn = new PDO(
                "mysql:host=" . DB_SERVER . 
                ";port=" . DB_PORT . 
                ";dbname=" . DB_NAME . 
                ";charset=utf8", 
                DB_USER, 
                DB_PASS, 
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
            return $conn;
        } catch (PDOException $e) {
            throw new PDOException("Error de conexión: " . $e->getMessage());
        }
    }
}

// Imprimir información para depuración
echo "<h1>Agregar columna orden_llegada a la tabla turnos</h1>";
echo "<p>Verificando conexión a la base de datos...</p>";

try {
    // Verificar la conexión
    $conn = verificarConexion($conn);
    echo "<p>Conexión establecida correctamente.</p>";
    
    // Mostrar información de la tabla turnos antes de modificarla
    echo "<h2>Estructura actual de la tabla turnos:</h2>";
    $columnas = $conn->query("SHOW COLUMNS FROM turnos");
    echo "<ul>";
    while ($col = $columnas->fetch(PDO::FETCH_ASSOC)) {
        echo "<li>" . $col['Field'] . " - " . $col['Type'] . "</li>";
    }
    echo "</ul>";
    
    // Verificar si la columna ya existe
    $checkColumn = $conn->query("SHOW COLUMNS FROM turnos LIKE 'orden_llegada'");
    if ($checkColumn->rowCount() == 0) {
        // La columna no existe, crearla
        echo "<p>La columna orden_llegada no existe. Creándola...</p>";
        $conn->exec("ALTER TABLE turnos ADD COLUMN orden_llegada INT DEFAULT NULL");
        echo "<p style='color:green;'>La columna orden_llegada se ha agregado correctamente a la tabla turnos.</p>";
        
        // Actualizar registros existentes con un orden basado en ID
        echo "<p>Actualizando registros existentes...</p>";
        $conn->exec("SET @counter = 0");
        $conn->exec("UPDATE turnos SET orden_llegada = @counter:=@counter+1 ORDER BY fecha_turno, id");
        echo "<p style='color:green;'>Los registros existentes han sido actualizados con un orden de llegada basado en la fecha y el ID (orden de creación).</p>";
    } else {
        echo "<p style='color:blue;'>La columna orden_llegada ya existe en la tabla turnos.</p>";
    }
    
    // Mostrar información de la tabla turnos después de modificarla
    echo "<h2>Estructura final de la tabla turnos:</h2>";
    $columnas = $conn->query("SHOW COLUMNS FROM turnos");
    echo "<ul>";
    while ($col = $columnas->fetch(PDO::FETCH_ASSOC)) {
        echo "<li>" . $col['Field'] . " - " . $col['Type'] . "</li>";
    }
    echo "</ul>";
    
    echo "<p style='color:green;'>Operación completada correctamente.</p>";
    echo "<p><a href='turnos.php'>Volver a la página de Turnos</a></p>";
} catch(PDOException $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
?>
