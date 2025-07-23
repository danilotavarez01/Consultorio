<?php
// Script simple para probar que las constantes están definidas correctamente
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Test de Constantes de Base de Datos ===\n\n";

// Incluir configuración
$config_path = __DIR__ . '/config.php';
echo "Cargando config desde: $config_path\n";

if (!file_exists($config_path)) {
    die("❌ Error: No se encuentra el archivo config.php\n");
}

require_once $config_path;

// Verificar constantes
$required_constants = ['DB_SERVER', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'];
$all_defined = true;

foreach ($required_constants as $constant) {
    if (defined($constant)) {
        $value = constant($constant);
        // Ocultar password por seguridad
        if ($constant === 'DB_PASS') {
            $value = str_repeat('*', strlen($value));
        }
        echo "✅ $constant = '$value'\n";
    } else {
        echo "❌ $constant = NO DEFINIDO\n";
        $all_defined = false;
    }
}

echo "\n";

if ($all_defined) {
    echo "✅ TODAS LAS CONSTANTES ESTÁN DEFINIDAS CORRECTAMENTE\n";
    
    // Probar conexión
    try {
        echo "\n🔗 Probando conexión...\n";
        
        // Usar las variables globales
        if (isset($pdo) && $pdo instanceof PDO) {
            echo "✅ Conexión PDO exitosa\n";
            
            // Probar una consulta simple
            $stmt = $pdo->query("SELECT 1 as test");
            if ($stmt) {
                echo "✅ Consulta de prueba exitosa\n";
            }
        } else {
            echo "❌ Variable \$pdo no está disponible\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Error en conexión: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "❌ FALTAN CONSTANTES\n";
}

echo "\n=== Fin del Test ===\n";
?>
