<?php
// Activar reportes de errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir configuración
$config_path = __DIR__ . '/config.php';
if (!file_exists($config_path)) {
    die("Error: No se encuentra el archivo config.php en: " . $config_path);
}

require_once $config_path;

// Verificar inmediatamente que las constantes estén definidas
$required_constants = ['DB_SERVER', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'];
$missing_constants = [];

foreach ($required_constants as $constant) {
    if (!defined($constant)) {
        $missing_constants[] = $constant;
    }
}

if (!empty($missing_constants)) {
    die("Error: Las siguientes constantes no están definidas: " . implode(', ', $missing_constants));
}

try {
    // Verificar conexión
    if (!isset($pdo) || !$pdo) {
        die("Error: No se pudo establecer conexión con la base de datos\n");
    }

    // Función para verificar si una columna existe
    function columnExists($pdo, $table, $column) {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM $table LIKE ?");
        $stmt->execute([$column]);
        return $stmt->rowCount() > 0;
    }

    // Añadir columnas necesarias a la tabla historial_medico (verificando si existen)
    $columns_to_add = [
        'especialidad_id' => 'INT NULL',
        'temperatura' => 'DECIMAL(4,1) NULL',
        'presion_arterial' => 'VARCHAR(20) NULL',
        'frecuencia_cardiaca' => 'INT NULL',
        'frecuencia_respiratoria' => 'INT NULL',
        'saturacion_oxigeno' => 'INT NULL',
        'estado' => 'ENUM(\'pendiente\', \'en_proceso\', \'completada\', \'cancelada\') DEFAULT \'pendiente\''
    ];
    
    foreach ($columns_to_add as $column => $definition) {
        try {
            if (!columnExists($pdo, 'historial_medico', $column)) {
                $sql = "ALTER TABLE historial_medico ADD COLUMN $column $definition";
                $pdo->exec($sql);
                echo "✅ Columna '$column' agregada.\n";
            } else {
                echo "⚠️ Columna '$column' ya existe.\n";
            }
        } catch(PDOException $e) {
            echo "❌ Error al agregar columna '$column': " . $e->getMessage() . "\n";
        }
    }
    
    // Agregar foreign key por separado (puede fallar si ya existe)
    try {
        $fk_sql = "ALTER TABLE historial_medico ADD CONSTRAINT fk_historial_especialidad_new FOREIGN KEY (especialidad_id) REFERENCES especialidades(id)";
        $pdo->exec($fk_sql);
        echo "Foreign key agregada exitosamente.\n";
    } catch(PDOException $fk_e) {
        echo "Nota: Foreign key ya existe o no se pudo agregar: " . $fk_e->getMessage() . "\n";
    }
    
} catch(PDOException $e) {
    echo "Error al modificar la tabla historial_medico: " . $e->getMessage() . "\n";
}
?>
