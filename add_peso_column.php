<?php
// Script para agregar columna peso a la tabla historial_medico
require_once "config.php"; // Incluye la configuración de conexión a la base de datos

// Función para guardar mensajes en un archivo de log
function log_message($message) {
    file_put_contents('db_alteration_log.txt', date('Y-m-d H:i:s') . ': ' . $message . PHP_EOL, FILE_APPEND);
    echo $message . PHP_EOL;
}

try {
    // Intenta agregar la columna
    $sql = "ALTER TABLE historial_medico ADD COLUMN peso VARCHAR(10) DEFAULT NULL";
    $conn->exec($sql);
    log_message("La columna 'peso' ha sido agregada exitosamente a la tabla 'historial_medico'.");
} catch (PDOException $e) {
    // Si hay un error (ej. la columna ya existe), muestra un mensaje
    log_message("Error: " . $e->getMessage());
}
?>
