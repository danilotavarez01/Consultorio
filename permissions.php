<?php
require_once 'config.php';

// NO hacer session_start() aquí - debe hacerse antes de incluir este archivo
// Verificar que la sesión ya esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    // Si no hay sesión activa, esto indica un error en el flujo
    error_log("WARNING: permissions.php incluido sin sesión activa");
}

// Definir constantes para los roles
define('ROLE_ADMIN', 'admin');
define('ROLE_DOCTOR', 'doctor');
define('ROLE_RECEPTIONIST', 'recepcionista');
define('ROLE_SUPPORT', 'soporte');

// Definir permisos por rol
$PERMISSIONS = [
    ROLE_ADMIN => [
        'manage_users',
        'manage_patients',
        'manage_appointments', // Este permiso cubre tanto turnos como citas
        'view_appointments', // Nuevo permiso para visualizar citas
        'manage_turnos', // Gestionar turnos específicamente
        'view_turnos', // Ver turnos
        'create_turnos', // Crear turnos
        'edit_turnos', // Editar turnos
        'delete_turnos', // Eliminar turnos
        'manage_citas', // Gestionar citas específicamente
        'view_citas', // Ver citas
        'create_citas', // Crear citas
        'edit_citas', // Editar citas
        'delete_citas', // Eliminar citas
        'manage_prescriptions',
        'manage_diseases',
        'view_medical_history',
        'edit_medical_history',
        'manage_receptionist_permissions',
        'manage_doctors', // Permiso para gestionar médicos
        'manage_procedures', // Permiso para gestionar procedimientos
        'view_procedures', // Permiso para ver procedimientos
        'gestionar_catalogos', // Permiso general para catálogos
        'ver_facturacion', // Permiso para ver facturación
        'crear_factura', // Permiso para crear facturas
        'editar_factura', // Permiso para editar facturas
        'anular_factura', // Permiso para anular facturas
        'ver_reportes_facturacion', // Permiso para ver reportes de facturación
        'seguros_medicos' // Permiso para gestionar seguros médicos
    ],    ROLE_DOCTOR => [
        'manage_patients',
        'manage_appointments',
        'view_appointments', // Nuevo permiso para visualizar citas
        'view_turnos', // Ver turnos
        'view_citas', // Ver citas
        'create_turnos', // Crear turnos (para médicos senior)
        'create_citas', // Crear citas
        'manage_prescriptions',
        'manage_diseases',
        'view_medical_history',
        'edit_medical_history',
        'manage_receptionist_permissions',
        'manage_doctors', // Permiso para gestionar médicos (para médicos senior/jefes de departamento)
        'view_procedures', // Los doctores pueden ver procedimientos pero no modificarlos por defecto
        'ver_facturacion', // Permiso para ver facturación
        'crear_factura', // Permiso para crear facturas
        'ver_reportes_facturacion', // Permiso para ver reportes de facturación
        'seguros_medicos' // Permiso para gestionar seguros médicos
    ],
    ROLE_RECEPTIONIST => [],  // Base permissions, will be loaded from database
    ROLE_SUPPORT => [
        'manage_users',
        'view_patients',
        'view_appointments',
        'view_turnos',
        'view_citas',
        'view_medical_history',
        'view_procedures',
        'ver_facturacion',
        'ver_reportes_facturacion',
        'manage_receptionist_permissions'
    ]
];

/**
 * Verifica si el usuario tiene un permiso específico
 */
function hasPermission($permission) {
    global $PERMISSIONS, $conn;
    
    if (!isset($_SESSION["loggedin"]) || !isset($_SESSION["rol"])) {
        return false;
    }
    
    $userRole = $_SESSION["rol"];
    
    // Los administradores siempre tienen acceso a todo
    if ($userRole === ROLE_ADMIN) {
        return true;
    }
      // Si es recepcionista o médico, verificar permisos personalizados
    if ($userRole === ROLE_RECEPTIONIST || $userRole === ROLE_DOCTOR) {
        $stmt = $conn->prepare("SELECT 1 FROM receptionist_permissions WHERE receptionist_id = ? AND permission = ?");
        $stmt->execute([$_SESSION['id'], $permission]);
        return $stmt->rowCount() > 0;
    }
    
    // Si es soporte o cualquier otro rol, usar permisos predefinidos
    return in_array($permission, $PERMISSIONS[$userRole] ?? []);
}

/**
 * Verifica si el usuario tiene acceso a una página específica
 */
function checkPageAccess($permission) {
    if (!hasPermission($permission)) {
        header("location: unauthorized.php");
        exit;
    }
}

/**
 * Retorna true si el usuario es admin
 */
function isAdmin() {
    return isset($_SESSION["rol"]) && $_SESSION["rol"] === ROLE_ADMIN;
}

/**
 * Retorna true si el usuario es doctor
 */
function isDoctor() {
    return isset($_SESSION["rol"]) && $_SESSION["rol"] === ROLE_DOCTOR;
}

/**
 * Retorna true si el usuario es recepcionista
 */
function isReceptionist() {
    return isset($_SESSION["rol"]) && $_SESSION["rol"] === ROLE_RECEPTIONIST;
}

/**
 * Retorna true si el usuario es de soporte
 */
function isSupport() {
    return isset($_SESSION["rol"]) && $_SESSION["rol"] === ROLE_SUPPORT;
}