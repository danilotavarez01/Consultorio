<?php
require_once "config.php";

try {
    // Function to check if a column exists
    function columnExists($conn, $table, $column) {
        try {
            $stmt = $conn->query("SHOW COLUMNS FROM {$table} LIKE '{$column}'");
            return $stmt->rowCount() > 0;
        } catch(PDOException $e) {
            return false;
        }
    }

    // Function to safely execute SQL
    function safeExec($conn, $sql, $description) {
        try {
            $conn->exec($sql);
            echo "Success: {$description}\n";
            return true;
        } catch(PDOException $e) {
            if (strpos($e->getMessage(), "Duplicate") !== false) {
                echo "Notice: {$description} - already exists\n";
                return true;
            }
            echo "Warning: {$description} - " . $e->getMessage() . "\n";
            return false;
        }
    }

    // Start transaction
    $conn->beginTransaction();
    
    echo "Starting configuration table update...\n";

    // 1. First, try to update existing boolean columns one by one
    $boolean_columns = [
        'require_https' => "MODIFY COLUMN require_https TINYINT(1) DEFAULT 0",
        'modo_mantenimiento' => "MODIFY COLUMN modo_mantenimiento TINYINT(1) DEFAULT 0"
    ];

    foreach ($boolean_columns as $column => $sql) {
        if (columnExists($conn, 'configuracion', $column)) {
            safeExec($conn, "ALTER TABLE configuracion {$sql}", "Updated {$column} to TINYINT(1)");
        }
    }

    // 2. Add new columns one by one
    $new_columns = [
        'dias_laborables' => "VARCHAR(20) DEFAULT '1,2,3,4,5'",
        'intervalo_citas' => "INT DEFAULT 30",
        'moneda' => "VARCHAR(10) DEFAULT '$'",
        'zona_horaria' => "VARCHAR(50) DEFAULT 'America/Santo_Domingo'",
        'formato_fecha' => "VARCHAR(20) DEFAULT 'Y-m-d'",
        'idioma' => "VARCHAR(5) DEFAULT 'es'",
        'tema_color' => "VARCHAR(20) DEFAULT 'light'",
        'mostrar_alertas_stock' => "TINYINT(1) DEFAULT 1",
        'notificaciones_email' => "TINYINT(1) DEFAULT 0"
    ];

    foreach ($new_columns as $column => $definition) {
        if (!columnExists($conn, 'configuracion', $column)) {
            safeExec($conn, 
                "ALTER TABLE configuracion ADD COLUMN {$column} {$definition}", 
                "Added column {$column}"
            );
        }
    }

    // 3. Add especialidad_id with foreign key
    if (!columnExists($conn, 'configuracion', 'especialidad_id')) {
        if (safeExec($conn, 
            "ALTER TABLE configuracion ADD COLUMN especialidad_id INT", 
            "Added especialidad_id column"
        )) {
            safeExec($conn, 
                "ALTER TABLE configuracion ADD CONSTRAINT FK_config_especialidad 
                 FOREIGN KEY (especialidad_id) REFERENCES especialidades(id)", 
                "Added foreign key constraint for especialidad_id"
            );
        }
    }

    // Commit all changes
    $conn->commit();
    echo "\nAll changes committed successfully.\n";

    // Print final table structure
    echo "\nFinal table structure:\n";
    echo "=====================\n";
    $stmt = $conn->query("SHOW FULL COLUMNS FROM configuracion");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        printf("%-20s %-20s %-10s %s\n", 
            $row['Field'], 
            $row['Type'], 
            $row['Null'], 
            $row['Default'] ?? 'NULL'
        );
    }

} catch(PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch(Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "General Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
