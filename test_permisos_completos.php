<?php
require_once 'config.php';

echo "<h2>✅ Verificación de Sistema de Permisos Actualizado</h2>";

// Verificar estructura de permisos
echo "<h3>📋 Permisos Disponibles por Categoría:</h3>";

$available_permissions = [
    'Gestión de Usuarios' => [
        'manage_users' => 'Gestión de Usuarios',
        'manage_doctors' => 'Gestionar Médicos',
        'manage_receptionist_permissions' => 'Gestionar Permisos de Usuarios'
    ],
    'Gestión de Pacientes' => [
        'manage_patients' => 'Gestionar Pacientes'
    ],
    'Citas y Turnos' => [
        'manage_appointments' => 'Gestionar Turnos/Citas',
        'view_appointments' => 'Ver Citas'
    ],
    'Recetas y Prescripciones' => [
        'manage_prescriptions' => 'Gestionar Recetas',
        'view_prescriptions' => 'Ver Recetas'
    ],
    'Historiales Médicos' => [
        'view_medical_history' => 'Ver Historial Médico',
        'edit_medical_history' => 'Editar Historial Médico'
    ],
    'Catálogos y Procedimientos' => [
        'manage_diseases' => 'Gestionar Enfermedades',
        'manage_procedures' => 'Gestionar Procedimientos',
        'view_procedures' => 'Ver Procedimientos',
        'gestionar_catalogos' => 'Gestionar Catálogos',
        'manage_specialties' => 'Gestionar Especialidades'
    ],
    'Configuración y Administración' => [
        'manage_settings' => 'Configuración del Sistema',
        'generate_reports' => 'Generar Reportes',
        'manage_whatsapp' => 'Gestionar WhatsApp'
    ]
];

foreach ($available_permissions as $categoria => $permisos) {
    echo "<h4 style='color: #007bff; border-bottom: 1px solid #ddd; padding-bottom: 5px;'>📁 $categoria</h4>";
    echo "<ul>";
    foreach ($permisos as $codigo => $nombre) {
        echo "<li><strong>$codigo</strong> - $nombre</li>";
    }
    echo "</ul>";
}

echo "<h3>🔍 Verificación de Base de Datos:</h3>";

try {
    // Verificar permisos en la BD
    $stmt = $conn->query("SELECT nombre, categoria, descripcion FROM permisos ORDER BY categoria, nombre");
    $permisos_bd = $stmt->fetchAll();
    
    echo "<p><strong>Total de permisos en BD:</strong> " . count($permisos_bd) . "</p>";
    
    // Verificar que existen los permisos de procedimientos
    $procedimientos_permisos = ['manage_procedures', 'view_procedures', 'gestionar_catalogos'];
    echo "<h4>Permisos de Procedimientos en BD:</h4>";
    
    foreach ($procedimientos_permisos as $permiso) {
        $stmt = $conn->prepare("SELECT * FROM permisos WHERE nombre = ?");
        $stmt->execute([$permiso]);
        if ($stmt->rowCount() > 0) {
            echo "✅ $permiso - Existe<br>";
        } else {
            echo "❌ $permiso - NO existe<br>";
        }
    }
    
    // Verificar usuarios
    echo "<h4>Usuarios en el Sistema:</h4>";
    $stmt = $conn->query("SELECT id, username, nombre, rol FROM usuarios ORDER BY rol, nombre");
    $usuarios = $stmt->fetchAll();
    
    foreach ($usuarios as $usuario) {
        echo "<div style='margin: 5px 0; padding: 8px; background: #f8f9fa; border-left: 3px solid #007bff;'>";
        echo "<strong>{$usuario['nombre']}</strong> ({$usuario['username']}) - Rol: {$usuario['rol']}";
        
        // Contar permisos asignados
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM receptionist_permissions WHERE receptionist_id = ?");
        $stmt->execute([$usuario['id']]);
        $count = $stmt->fetch()['count'];
        echo " | Permisos asignados: $count";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error de BD: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>📌 Resumen de Cambios:</h3>";
echo "<ul>";
echo "<li>✅ <strong>Todos los permisos</strong> ahora están disponibles para cualquier usuario</li>";
echo "<li>✅ <strong>Sin restricciones por rol</strong> - El administrador decide qué asignar</li>";
echo "<li>✅ <strong>Permisos organizados por categorías</strong> para facilitar la gestión</li>";
echo "<li>✅ <strong>Incluye todos los permisos de procedimientos</strong></li>";
echo "<li>✅ <strong>Interface mejorada</strong> con información clara</li>";
echo "</ul>";

echo "<hr>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border-left: 5px solid #28a745;'>";
echo "<h4 style='color: #155724;'>🎯 Cómo usar el nuevo sistema:</h4>";
echo "<ol>";
echo "<li>Ve a <strong>Menú → Permisos</strong></li>";
echo "<li>Selecciona cualquier usuario (admin, doctor, recepcionista)</li>";
echo "<li>Verás TODOS los permisos organizados por categorías</li>";
echo "<li>Marca los permisos que consideres apropiados</li>";
echo "<li>Guarda los cambios</li>";
echo "</ol>";
echo "</div>";

echo "<p style='margin-top: 20px;'>";
echo "<a href='user_permissions.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔧 Ir a Gestión de Permisos</a> ";
echo "<a href='procedimientos.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📋 Ir a Procedimientos</a>";
echo "</p>";
?>
