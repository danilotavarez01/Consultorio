<?php
require_once 'config.php';

echo "<h2>Agregando Permisos de Procedimientos</h2>";

// Crear tabla de permisos si no existe
try {
    $sql_permisos = "CREATE TABLE IF NOT EXISTS permisos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL UNIQUE,
        descripcion TEXT,
        categoria VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql_permisos);
    echo "✅ Tabla 'permisos' verificada<br>";
} catch (PDOException $e) {
    echo "❌ Error creando tabla permisos: " . $e->getMessage() . "<br>";
}

// Crear tabla usuario_permisos si no existe
try {
    $sql_usuario_permisos = "CREATE TABLE IF NOT EXISTS usuario_permisos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        permiso_nombre VARCHAR(100) NOT NULL,
        otorgado_por INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_usuario_permiso (usuario_id, permiso_nombre),
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )";
    $conn->exec($sql_usuario_permisos);
    echo "✅ Tabla 'usuario_permisos' verificada<br>";
} catch (PDOException $e) {
    echo "❌ Error creando tabla usuario_permisos: " . $e->getMessage() . "<br>";
}

// Permisos de procedimientos a agregar
$permisos_procedimientos = [
    [
        'nombre' => 'manage_procedures',
        'descripcion' => 'Gestionar procedimientos (crear, editar, eliminar)',
        'categoria' => 'Procedimientos'
    ],
    [
        'nombre' => 'view_procedures',
        'descripcion' => 'Ver lista de procedimientos',
        'categoria' => 'Procedimientos'
    ],
    [
        'nombre' => 'gestionar_catalogos',
        'descripcion' => 'Gestionar catálogos del sistema (procedimientos, materiales, etc.)',
        'categoria' => 'Administración'
    ]
];

echo "<h3>Agregando Permisos:</h3>";

foreach ($permisos_procedimientos as $permiso) {
    try {
        $stmt = $conn->prepare("INSERT IGNORE INTO permisos (nombre, descripcion, categoria) VALUES (?, ?, ?)");
        $stmt->execute([$permiso['nombre'], $permiso['descripcion'], $permiso['categoria']]);
        
        if ($stmt->rowCount() > 0) {
            echo "✅ Permiso agregado: <strong>{$permiso['nombre']}</strong> - {$permiso['descripcion']}<br>";
        } else {
            echo "ℹ️ Permiso ya existe: <strong>{$permiso['nombre']}</strong><br>";
        }
    } catch (PDOException $e) {
        echo "❌ Error agregando permiso {$permiso['nombre']}: " . $e->getMessage() . "<br>";
    }
}

// Asignar permisos al usuario admin
echo "<h3>Asignando Permisos al Administrador:</h3>";

try {
    // Buscar usuario admin
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = 'admin' OR rol = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        $admin_id = $admin['id'];
        
        foreach ($permisos_procedimientos as $permiso) {
            try {
                $stmt = $conn->prepare("INSERT IGNORE INTO usuario_permisos (usuario_id, permiso_nombre, otorgado_por) VALUES (?, ?, ?)");
                $stmt->execute([$admin_id, $permiso['nombre'], $admin_id]);
                
                if ($stmt->rowCount() > 0) {
                    echo "✅ Permiso <strong>{$permiso['nombre']}</strong> asignado al admin<br>";
                } else {
                    echo "ℹ️ Admin ya tiene el permiso: <strong>{$permiso['nombre']}</strong><br>";
                }
            } catch (PDOException $e) {
                echo "❌ Error asignando permiso {$permiso['nombre']} al admin: " . $e->getMessage() . "<br>";
            }
        }
    } else {
        echo "❌ No se encontró usuario administrador<br>";
    }
} catch (PDOException $e) {
    echo "❌ Error buscando usuario admin: " . $e->getMessage() . "<br>";
}

echo "<h3>Resumen de Permisos en el Sistema:</h3>";

try {
    $stmt = $conn->query("SELECT nombre, descripcion, categoria FROM permisos ORDER BY categoria, nombre");
    $permisos = $stmt->fetchAll();
    
    $categoria_actual = '';
    foreach ($permisos as $permiso) {
        if ($categoria_actual !== $permiso['categoria']) {
            if ($categoria_actual !== '') echo "<br>";
            echo "<strong>{$permiso['categoria']}:</strong><br>";
            $categoria_actual = $permiso['categoria'];
        }
        echo "• {$permiso['nombre']} - {$permiso['descripcion']}<br>";
    }
} catch (PDOException $e) {
    echo "❌ Error obteniendo permisos: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>✅ Configuración de permisos completada</strong></p>";
echo "<p><a href='procedimientos.php' class='btn btn-primary'>Ir a Gestión de Procedimientos</a></p>";
echo "<p><a href='index.php' class='btn btn-secondary'>Volver al Inicio</a></p>";
?>
