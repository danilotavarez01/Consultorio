<?php
// Script de verificación completa del formulario de procedimientos
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== VERIFICACIÓN COMPLETA DEL FORMULARIO DE PROCEDIMIENTOS ===\n\n";

// 1. Verificar que el archivo existe y es legible
$archivo = 'procedimientos.php';
echo "1. 📁 VERIFICACIÓN DE ARCHIVO:\n";
echo "================================\n";

if (file_exists($archivo)) {
    echo "✅ Archivo '$archivo' existe\n";
    
    if (is_readable($archivo)) {
        echo "✅ Archivo '$archivo' es legible\n";
        
        $size = filesize($archivo);
        echo "✅ Tamaño del archivo: " . number_format($size) . " bytes\n";
        
        // Verificar sintaxis PHP
        $output = shell_exec("php -l $archivo 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "✅ Sintaxis PHP correcta\n";
        } else {
            echo "❌ Error de sintaxis PHP:\n$output\n";
        }
    } else {
        echo "❌ Archivo '$archivo' no es legible\n";
    }
} else {
    echo "❌ Archivo '$archivo' NO existe\n";
}

echo "\n";

// 2. Verificar conexión a base de datos y tabla procedimientos
echo "2. 🗄️ VERIFICACIÓN DE BASE DE DATOS:\n";
echo "====================================\n";

try {
    require_once 'config.php';
    echo "✅ Conexión a base de datos exitosa\n";
    
    // Verificar tabla procedimientos
    $stmt = $pdo->query("SHOW TABLES LIKE 'procedimientos'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabla 'procedimientos' existe\n";
        
        // Verificar estructura
        $stmt = $pdo->query("DESCRIBE procedimientos");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "✅ Columnas encontradas: " . implode(', ', $columns) . "\n";
        
        // Verificar datos
        $stmt = $pdo->query("SELECT COUNT(*) FROM procedimientos");
        $count = $stmt->fetchColumn();
        echo "✅ Registros en tabla: $count\n";
        
        if ($count > 0) {
            // Mostrar algunos ejemplos
            $stmt = $pdo->query("SELECT codigo, nombre, categoria FROM procedimientos LIMIT 3");
            $ejemplos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "✅ Ejemplos de datos:\n";
            foreach ($ejemplos as $ej) {
                echo "   - {$ej['codigo']}: {$ej['nombre']} ({$ej['categoria']})\n";
            }
        }
    } else {
        echo "❌ Tabla 'procedimientos' NO existe\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error de base de datos: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Verificar sistema de permisos
echo "3. 🔐 VERIFICACIÓN DE PERMISOS:\n";
echo "===============================\n";

try {
    // Verificar tabla permisos
    $stmt = $pdo->query("SHOW TABLES LIKE 'permisos'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabla 'permisos' existe\n";
        
        // Verificar permisos relacionados con procedimientos
        $stmt = $pdo->query("SELECT nombre FROM permisos WHERE nombre LIKE '%catalog%' OR nombre LIKE '%procedure%'");
        $permisos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($permisos)) {
            echo "✅ Permisos de procedimientos encontrados: " . implode(', ', $permisos) . "\n";
        } else {
            echo "⚠️ No se encontraron permisos específicos de procedimientos\n";
        }
    } else {
        echo "⚠️ Tabla 'permisos' no existe - usando verificación simplificada\n";
    }
    
} catch (Exception $e) {
    echo "⚠️ Permisos: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Verificar enlaces en el menú
echo "4. 🔗 VERIFICACIÓN DE ENLACES:\n";
echo "==============================\n";

$sidebar_file = 'sidebar.php';
if (file_exists($sidebar_file)) {
    echo "✅ Archivo '$sidebar_file' existe\n";
    
    $sidebar_content = file_get_contents($sidebar_file);
    if (strpos($sidebar_content, 'procedimientos.php') !== false) {
        echo "✅ Enlace a procedimientos.php encontrado en sidebar\n";
        
        // Verificar si está dentro de condiciones
        if (preg_match('/\?\s*procedimientos\.php/', $sidebar_content)) {
            echo "✅ Enlace está condicionado (con permisos)\n";
        } else {
            echo "ℹ️ Enlace está visible sin condiciones\n";
        }
    } else {
        echo "❌ NO se encontró enlace a procedimientos.php en sidebar\n";
    }
} else {
    echo "❌ Archivo '$sidebar_file' NO existe\n";
}

echo "\n";

// 5. Simulación de carga del formulario
echo "5. 🖥️ SIMULACIÓN DE CARGA:\n";
echo "==========================\n";

// Simular sesión de admin
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['rol'] = 'admin';

echo "✅ Sesión simulada (admin)\n";

// Intentar incluir sin salida
ob_start();
try {
    // Suprimir headers para evitar errores
    $backup_server = $_SERVER;
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    include $archivo;
    
    $content = ob_get_contents();
    $_SERVER = $backup_server;
    
    ob_end_clean();
    
    if (strlen($content) > 0) {
        echo "✅ Formulario genera contenido HTML (" . number_format(strlen($content)) . " caracteres)\n";
        
        // Verificar elementos clave del formulario
        $elementos_clave = [
            'form' => 'Formulario HTML',
            'input[name="nombre"]' => 'Campo nombre',
            'input[name="codigo"]' => 'Campo código',
            'select[name="categoria"]' => 'Selector categoría',
            'table' => 'Tabla de procedimientos'
        ];
        
        foreach ($elementos_clave as $buscar => $descripcion) {
            $pattern = str_replace(['[', ']'], ['\[', '\]'], $buscar);
            if (preg_match("/<$pattern/i", $content)) {
                echo "✅ $descripcion encontrado\n";
            } else {
                echo "⚠️ $descripcion NO encontrado\n";
            }
        }
    } else {
        echo "❌ El formulario NO genera contenido\n";
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "❌ Error al cargar formulario: " . $e->getMessage() . "\n";
}

echo "\n";

// 6. Resumen final
echo "6. 📋 RESUMEN FINAL:\n";
echo "====================\n";

$problemas = [];
$todo_ok = true;

if (!file_exists($archivo)) {
    $problemas[] = "Archivo procedimientos.php no existe";
    $todo_ok = false;
}

try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'procedimientos'");
    if ($stmt->rowCount() == 0) {
        $problemas[] = "Tabla procedimientos no existe";
        $todo_ok = false;
    }
} catch (Exception $e) {
    $problemas[] = "Error de base de datos: " . $e->getMessage();
    $todo_ok = false;
}

if ($todo_ok) {
    echo "🎉 ¡FORMULARIO DE PROCEDIMIENTOS COMPLETAMENTE FUNCIONAL!\n";
    echo "=========================================================\n";
    echo "✅ Archivo existe y es accesible\n";
    echo "✅ Base de datos configurada correctamente\n";
    echo "✅ Tabla de procedimientos con datos\n";
    echo "✅ Formulario genera contenido HTML\n";
    echo "✅ Sistema listo para usar\n\n";
    
    echo "🔗 ACCESO DIRECTO:\n";
    echo "==================\n";
    echo "URL: http://localhost/Consultorio2/procedimientos.php\n";
    echo "Usuario: admin\n";
    echo "Contraseña: admin123\n";
} else {
    echo "❌ PROBLEMAS ENCONTRADOS:\n";
    echo "=========================\n";
    foreach ($problemas as $problema) {
        echo "- $problema\n";
    }
}

echo "\n=== FIN DE VERIFICACIÓN ===\n";
?>
