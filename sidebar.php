<?php
if(!isset($_SESSION)) {
    session_start();
}
require_once "permissions.php";
?>
<div class="col-md-2 sidebar sidebar-dark">
    <!-- <h4 class="text-white text-center mb-4">Consultorio Médico</h4> -->
    <nav class="nav-dark">        <?php if(hasPermission('manage_patients') || hasPermission('manage_appointments') || 
              hasPermission('view_appointments') || hasPermission('manage_prescriptions') || 
              hasPermission('view_prescriptions') || hasPermission('manage_diseases') || 
              hasPermission('view_medical_history') || hasPermission('edit_medical_history') || 
              hasPermission('manage_users') || hasPermission('manage_doctors') || 
              hasPermission('manage_receptionist_permissions')): ?>
        <a href="index.php"><i class="fas fa-home"></i> Inicio</a>
        <?php endif; ?>
        
        <?php if(hasPermission('manage_patients')): ?>
        <a href="pacientes.php"><i class="fas fa-users"></i> Pacientes</a>
        <?php endif; ?>
        
        <?php if(hasPermission('manage_turnos') || hasPermission('view_turnos') || hasPermission('create_turnos') || hasPermission('manage_appointments')): ?>
        <a href="turnos.php"><i class="fas fa-calendar-alt"></i> Turnos</a>
        <?php endif; ?>
        
        <?php if(hasPermission('manage_citas') || hasPermission('view_citas') || hasPermission('create_citas') || hasPermission('view_appointments')): ?>
        <a href="Citas.php"><i class="fas fa-calendar-check"></i> Citas</a>
        <?php endif; ?>

        <?php if(hasPermission('manage_prescriptions') || hasPermission('view_prescriptions')): ?>
        <a href="recetas.php"><i class="fas fa-prescription"></i> Recetas</a>
        <?php endif; ?>

        <?php if(hasPermission('manage_diseases')): ?>
        <a href="enfermedades.php"><i class="fas fa-book-medical"></i> Enfermedades</a>
        <?php endif; ?>

        <!-- Procedimientos - Con permisos restaurados -->
        <?php if(hasPermission('gestionar_catalogos') || hasPermission('manage_procedures') || hasPermission('manage_users') || (isset($_SESSION["username"]) && $_SESSION["username"] === "admin")): ?>
        <a href="procedimientos.php"><i class="fas fa-teeth"></i> Procedimientos</a>
        <?php endif; ?>        <?php if(hasPermission('manage_users')): ?>
        <a href="usuarios.php"><i class="fas fa-user-md"></i> Usuarios</a>
        <?php endif; ?>
        
        <?php if(hasPermission('manage_users') || hasPermission('manage_doctors')): ?>
        <a href="gestionar_doctores.php"><i class="fas fa-user-md"></i> Médicos</a>
        <?php endif; ?>        <?php if(hasPermission('manage_receptionist_permissions') || hasPermission('manage_users')): ?>
        <a href="user_permissions.php"><i class="fas fa-key"></i> Permisos</a>
        <?php endif; ?>        <?php if(isset($_SESSION["username"]) && $_SESSION["username"] === "admin"): ?>
        <a href="configuracion.php"><i class="fas fa-cogs"></i> Configuración</a>
        <?php endif; ?>

        <!-- Facturación -->
        <?php if(hasPermission('ver_facturacion') || hasPermission('crear_factura') || isAdmin()): ?>
        <a href="facturacion.php"><i class="fas fa-file-invoice-dollar"></i> Facturación</a>
        <?php endif; ?>

        <!-- Reportes -->
        <?php if(hasPermission('ver_reportes_facturacion') || isAdmin()): ?>
        <a href="reportes_facturacion.php"><i class="fas fa-chart-bar"></i> Reportes</a>
        <?php endif; ?>

        <a href="logout.php" onclick="return confirmarLogout();" title="Cerrar Sesión"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
    </nav>
</div>

<script>
function confirmarLogout() {
    // Confirmación antes de cerrar sesión
    if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
        // Log para debug
        console.log('Cerrando sesión del usuario...');
        
        // Si existe el SessionManager, usarlo para logout
        if (window.sessionManager) {
            window.sessionManager.forceLogout('manual');
            return false;
        }
        
        // Fallback: redirigir directamente
        window.location.href = 'logout.php';
        return false; // Prevenir el enlace normal por si acaso
    }
    return false; // Cancelar si no confirma
}

// Función alternativa para logout sin confirmación (para casos especiales)
function logoutDirecto() {
    console.log('Logout directo iniciado...');
    if (window.sessionManager) {
        window.sessionManager.forceLogout('direct');
    } else {
        window.location.href = 'logout.php';
    }
}
</script>