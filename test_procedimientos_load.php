<?php
// Script para probar el acceso a procedimientos.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Test de procedimientos.php ===\n\n";

// Simular una sesión
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['rol'] = 'admin';
$_SESSION['loggedin'] = true;

echo "Sesión iniciada como admin\n";

// Verificar que el archivo existe
$file = 'procedimientos.php';
if (file_exists($file)) {
    echo "✅ Archivo $file existe\n";
    
    // Verificar permisos de lectura
    if (is_readable($file)) {
        echo "✅ Archivo $file es legible\n";
        
        // Intentar incluir sin salida
        ob_start();
        try {
            include $file;
            $content = ob_get_contents();
            ob_end_clean();
            
            echo "✅ Archivo incluido exitosamente\n";
            echo "Contenido generado: " . strlen($content) . " caracteres\n";
            
            if (strlen($content) > 0) {
                echo "✅ El archivo genera contenido HTML\n";
            } else {
                echo "❌ El archivo no genera contenido\n";
            }
            
        } catch (Exception $e) {
            ob_end_clean();
            echo "❌ Error al incluir archivo: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "❌ Archivo $file no es legible\n";
    }
    
} else {
    echo "❌ Archivo $file NO existe\n";
}

echo "\n=== Fin del test ===\n";
?>
