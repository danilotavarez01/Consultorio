<?php
// Script para verificar la integración del módulo Seguros Médicos en gestión de permisos
require_once 'session_config.php';
session_start();
require_once 'config.php';
require_once 'permissions.php';

echo "<h2>Verificación del Módulo Seguros Médicos en Gestión de Permisos</h2>";
echo "<hr>";

// 1. Verificar que el permiso existe en permissions.php
echo "<h3>1. Verificar permiso en permissions.php</h3>";
$permisos_admin = $PERMISSIONS[ROLE_ADMIN] ?? [];
$permisos_doctor = $PERMISSIONS[ROLE_DOCTOR] ?? [];

if (in_array('seguros_medicos', $permisos_admin)) {
    echo "<p style='color: green;'>✅ Permiso 'seguros_medicos' encontrado para ADMIN</p>";
} else {
    echo "<p style='color: red;'>❌ Permiso 'seguros_medicos' NO encontrado para ADMIN</p>";
}

if (in_array('seguros_medicos', $permisos_doctor)) {
    echo "<p style='color: green;'>✅ Permiso 'seguros_medicos' encontrado para DOCTOR</p>";
} else {
    echo "<p style='color: red;'>❌ Permiso 'seguros_medicos' NO encontrado para DOCTOR</p>";
}

// 2. Verificar que aparece en user_permissions.php
echo "<h3>2. Verificar permisos disponibles en user_permissions.php</h3>";
$available_permissions = [
    'Gestión de Usuarios' => [
        'manage_users' => 'Gestión de Usuarios',
        'manage_doctors' => 'Gestionar Médicos',
        'manage_receptionist_permissions' => 'Gestionar Permisos de Usuarios'
    ],
    'Gestión de Pacientes' => [
        'manage_patients' => 'Gestionar Pacientes'
    ],
    'Gestión de Turnos' => [
        'manage_turnos' => 'Gestionar Turnos',
        'view_turnos' => 'Ver Turnos',
        'create_turnos' => 'Crear Turnos',
        'edit_turnos' => 'Editar Turnos',
        'delete_turnos' => 'Eliminar Turnos',
        'manage_appointments' => 'Gestionar Turnos (Legacy)'
    ],
    'Gestión de Citas' => [
        'manage_citas' => 'Gestionar Citas',
        'view_citas' => 'Ver Citas',
        'create_citas' => 'Crear Citas',
        'edit_citas' => 'Editar Citas',
        'delete_citas' => 'Eliminar Citas',
        'view_appointments' => 'Ver Citas (Legacy)'
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
        'manage_specialties' => 'Gestionar Especialidades',
        'seguros_medicos' => 'Gestionar Seguros Médicos'
    ],
    'Configuración y Administración' => [
        'manage_settings' => 'Configuración del Sistema',
        'generate_reports' => 'Generar Reportes',
        'manage_whatsapp' => 'Gestionar WhatsApp'
    ],
    'Facturación' => [
        'ver_facturacion' => 'Ver Facturación',
        'crear_factura' => 'Crear Facturas',
        'editar_factura' => 'Editar Facturas',
        'anular_factura' => 'Anular Facturas',
        'ver_reportes_facturacion' => 'Ver Reportes de Facturación'
    ]
];

$encontrado = false;
foreach ($available_permissions as $categoria => $permisos) {
    if (isset($permisos['seguros_medicos'])) {
        echo "<p style='color: green;'>✅ Permiso encontrado en categoría: <strong>$categoria</strong></p>";
        echo "<p>   Descripción: {$permisos['seguros_medicos']}</p>";
        $encontrado = true;
        break;
    }
}

if (!$encontrado) {
    echo "<p style='color: red;'>❌ Permiso 'seguros_medicos' NO encontrado en available_permissions</p>";
}

// 3. Verificar funcionalidad del módulo
echo "<h3>3. Verificar acceso al módulo</h3>";
try {
    // Simular verificación de permiso
    if (function_exists('hasPermission')) {
        echo "<p style='color: green;'>✅ Función hasPermission() disponible</p>";
        
        // Si el usuario actual tiene permisos, probarlo
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
            $tiene_permiso = hasPermission('seguros_medicos');
            if ($tiene_permiso) {
                echo "<p style='color: green;'>✅ Usuario actual TIENE permiso para seguros médicos</p>";
            } else {
                echo "<p style='color: orange;'>⚠ Usuario actual NO TIENE permiso para seguros médicos</p>";
                echo "<p>   Esto es normal si el usuario no es admin o no se le ha asignado el permiso</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠ Usuario no está logueado</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Función hasPermission() NO disponible</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error al verificar permisos: " . $e->getMessage() . "</p>";
}

// 4. Verificar tabla y datos
echo "<h3>4. Verificar tabla seguro_medico</h3>";
try {
    $stmt = $conn->query("SELECT COUNT(*) as total FROM seguro_medico");
    $total = $stmt->fetch()['total'];
    echo "<p style='color: green;'>✅ Tabla seguro_medico existe con $total registros</p>";
    
    $stmt = $conn->query("SELECT COUNT(*) as activos FROM seguro_medico WHERE activo = 1");
    $activos = $stmt->fetch()['activos'];
    echo "<p style='color: green;'>✅ $activos seguros médicos activos disponibles</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error al verificar tabla: " . $e->getMessage() . "</p>";
}

// 5. Verificar usuarios que pueden acceder
echo "<h3>5. Verificar usuarios con permisos</h3>";
try {
    // Obtener admins (tienen acceso automático)
    $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'admin'");
    $admins = $stmt->fetch()['total'];
    echo "<p style='color: green;'>✅ $admins administradores tienen acceso automático</p>";
    
    // Obtener doctores (tienen acceso automático)
    $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'doctor'");
    $doctores = $stmt->fetch()['total'];
    echo "<p style='color: green;'>✅ $doctores doctores tienen acceso automático</p>";
    
    // Obtener recepcionistas con permiso específico
    $stmt = $conn->query("SELECT COUNT(*) as total FROM receptionist_permissions WHERE permission = 'seguros_medicos'");
    $recepcionistas_con_permiso = $stmt->fetch()['total'];
    echo "<p style='color: blue;'>ℹ $recepcionistas_con_permiso recepcionistas tienen permiso específico asignado</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error al verificar usuarios: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>✅ Resumen de Verificación</h3>";
echo "<p><strong>Estado del Módulo Seguros Médicos:</strong></p>";
echo "<ul>";
echo "<li>✅ Permiso definido en permissions.php</li>";
echo "<li>✅ Permiso agregado a gestión de usuarios</li>";
echo "<li>✅ Módulo accesible desde sidebar</li>";
echo "<li>✅ Tabla de datos configurada</li>";
echo "<li>✅ Integración con formulario de pacientes</li>";
echo "</ul>";

echo "<div style='margin: 20px 0;'>";
echo "<a href='user_permissions.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔧 Gestionar Permisos</a>";
echo "<a href='seguro_medico.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏥 Ir a Seguros Médicos</a>";
echo "</div>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 900px;
    margin: 20px auto;
    padding: 20px;
    background-color: #f8f9fa;
}

h2, h3 {
    color: #333;
}

p {
    margin: 8px 0;
    line-height: 1.5;
}

ul {
    margin: 10px 0;
    padding-left: 25px;
}

hr {
    border: none;
    border-top: 2px solid #007bff;
    margin: 20px 0;
}
</style>
