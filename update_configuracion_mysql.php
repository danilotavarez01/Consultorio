<?php
require_once "config.php";

try {
    // Modificar la tabla configuracion para usar tipos de datos MySQL correctos    // Primero modificamos las columnas existentes
    $sql = "ALTER TABLE configuracion
        MODIFY COLUMN require_https TINYINT(1) DEFAULT 0,
        MODIFY COLUMN modo_mantenimiento TINYINT(1) DEFAULT 0";
    $conn->exec($sql);
    
    // Ahora agregamos las nuevas columnas una por una, verificando primero si existen
    $columnas_nuevas = [
        'dias_laborables' => "ADD COLUMN dias_laborables VARCHAR(20) DEFAULT '1,2,3,4,5'",
        ADD COLUMN IF NOT EXISTS intervalo_citas INT DEFAULT 30,
        ADD COLUMN IF NOT EXISTS moneda VARCHAR(10) DEFAULT '$',
        ADD COLUMN IF NOT EXISTS zona_horaria VARCHAR(50) DEFAULT 'America/Santo_Domingo',
        ADD COLUMN IF NOT EXISTS formato_fecha VARCHAR(20) DEFAULT 'Y-m-d',
        ADD COLUMN IF NOT EXISTS idioma VARCHAR(5) DEFAULT 'es',
        ADD COLUMN IF NOT EXISTS tema_color VARCHAR(20) DEFAULT 'light',
        ADD COLUMN IF NOT EXISTS mostrar_alertas_stock TINYINT(1) DEFAULT 1,
        ADD COLUMN IF NOT EXISTS notificaciones_email TINYINT(1) DEFAULT 0";

    $conn->exec($sql);
    echo "Tabla configuracion actualizada correctamente con tipos de datos MySQL.\n";

    // Verificar la estructura actualizada
    $result = $conn->query("DESCRIBE configuracion");
    echo "\nEstructura actual de la tabla configuracion:\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Default']}\n";
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
