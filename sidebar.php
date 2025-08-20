<?php
if(!isset($_SESSION)) {
    session_start();
}
require_once "permissions.php";
echo '<link rel="stylesheet" href="assets/css/fontawesome.min.css">';
echo '<link rel="stylesheet" href="assets/css/form-style.css">';
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
        <?php endif; ?>

        <!-- Seguros Médicos -->
        <?php if(hasPermission('seguros_medicos') || hasPermission('manage_users') || (isset($_SESSION["username"]) && $_SESSION["username"] === "admin")): ?>
        <a href="seguro_medico.php"><i class="fas fa-shield-alt"></i> Seguros Médicos</a>
        <?php endif; ?>

        <!-- Sección de Seguridad -->
        <?php if(hasPermission('manage_users') || hasPermission('manage_doctors') || hasPermission('manage_receptionist_permissions')): ?>
        <div class="nav-item-dropdown">
            <a href="#" class="nav-link-dropdown" onclick="toggleDropdown('seguridad')">
                <i class="fas fa-shield-alt"></i> Seguridad <i class="fas fa-chevron-down dropdown-arrow" id="arrow-seguridad"></i>
            </a>
            <div class="dropdown-content" id="dropdown-seguridad">
                <?php if(hasPermission('manage_users')): ?>
                <a href="usuarios.php" class="dropdown-item"><i class="fas fa-user-md"></i> Usuarios</a>
                <?php endif; ?>
                
                <?php if(hasPermission('manage_users') || hasPermission('manage_doctors')): ?>
                <a href="gestionar_doctores.php" class="dropdown-item"><i class="fas fa-user-md"></i> Médicos</a>
                <?php endif; ?>
                
                <?php if(hasPermission('manage_receptionist_permissions') || hasPermission('manage_users')): ?>
                <a href="user_permissions.php" class="dropdown-item"><i class="fas fa-key"></i> Permisos</a>
                <?php endif; ?>
            </div>
        </div>
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

        <a href="logout.php" onclick="return confirm('¿Estás seguro de que deseas cerrar sesión?');" title="Cerrar Sesión"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
    </nav>
</div>

<style>
.nav-item-dropdown {
    position: relative;
}

.nav-link-dropdown {
    color: #fff;
    padding: 10px 15px;
    display: block;
    text-decoration: none;
    cursor: pointer;
    position: relative;
}

.nav-link-dropdown:hover {
    background-color: #454d55;
    text-decoration: none;
    color: #fff;
}

.dropdown-arrow {
    float: right;
    margin-top: 2px;
    transition: transform 0.3s ease;
}

.dropdown-arrow.rotated {
    transform: rotate(180deg);
}

.dropdown-content {
    display: none;
    background-color: #2c3237;
    padding-left: 20px;
    border-left: 3px solid #007bff;
    margin-left: 10px;
}

.dropdown-content.show {
    display: block;
    animation: slideDown 0.3s ease;
}

.dropdown-item {
    color: #adb5bd;
    padding: 8px 15px;
    display: block;
    text-decoration: none;
    font-size: 14px;
}

.dropdown-item:hover {
    background-color: #454d55;
    text-decoration: none;
    color: #fff;
}

.dropdown-item i {
    width: 20px;
    margin-right: 8px;
}

@keyframes slideDown {
    from {
        opacity: 0;
        max-height: 0;
    }
    to {
        opacity: 1;
        max-height: 200px;
    }
}
</style>

<script>
function toggleDropdown(menuId) {
    const dropdown = document.getElementById('dropdown-' + menuId);
    const arrow = document.getElementById('arrow-' + menuId);
    
    if (dropdown.classList.contains('show')) {
        dropdown.classList.remove('show');
        arrow.classList.remove('rotated');
    } else {
        // Cerrar otros dropdowns primero
        const allDropdowns = document.querySelectorAll('.dropdown-content');
        const allArrows = document.querySelectorAll('.dropdown-arrow');
        
        allDropdowns.forEach(d => d.classList.remove('show'));
        allArrows.forEach(a => a.classList.remove('rotated'));
        
        // Abrir el dropdown seleccionado
        dropdown.classList.add('show');
        arrow.classList.add('rotated');
    }
}

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

// Cerrar dropdowns al hacer clic fuera
document.addEventListener('click', function(event) {
    const isDropdownClick = event.target.closest('.nav-item-dropdown');
    if (!isDropdownClick) {
        const allDropdowns = document.querySelectorAll('.dropdown-content');
        const allArrows = document.querySelectorAll('.dropdown-arrow');
        
        allDropdowns.forEach(d => d.classList.remove('show'));
        allArrows.forEach(a => a.classList.remove('rotated'));
    }
});
</script>