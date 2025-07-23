<!DOCTYPE html>
<html>
<head>
    <title>Panel de Configuración - Sistema Consultorio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card-hover {
            transition: transform 0.2s;
        }
        .card-hover:hover {
            transform: translateY(-5px);
        }
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .status-success { background-color: #28a745; }
        .status-warning { background-color: #ffc107; }
        .status-danger { background-color: #dc3545; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="fas fa-tools text-primary"></i>
                    Panel de Configuración del Sistema
                </h1>
                <p class="text-center text-muted mb-5">Herramientas para configurar y diagnosticar el sistema de consultorio médico</p>
            </div>
        </div>

        <!-- Diagnóstico -->
        <div class="row mb-4">
            <div class="col-12">
                <h3><i class="fas fa-stethoscope text-info"></i> Diagnóstico del Sistema</h3>
            </div>
            <div class="col-md-6">
                <div class="card card-hover">
                    <div class="card-body text-center">
                        <i class="fas fa-database fa-3x text-info mb-3"></i>
                        <h5>Diagnóstico de Base de Datos</h5>
                        <p class="text-muted">Verificar conectividad y estructura de la base de datos</p>
                        <a href="diagnostico_db.php" class="btn btn-info">
                            <i class="fas fa-search"></i> Ejecutar Diagnóstico
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-hover">
                    <div class="card-body text-center">
                        <i class="fas fa-table fa-3x text-success mb-3"></i>
                        <h5>Verificar Tablas</h5>
                        <p class="text-muted">Verificar estructura y contenido de todas las tablas</p>
                        <a href="verificar_tablas_completo.php" class="btn btn-success">
                            <i class="fas fa-check-circle"></i> Verificar Tablas
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instalación y Configuración -->
        <div class="row mb-4">
            <div class="col-12">
                <h3><i class="fas fa-cogs text-primary"></i> Instalación y Configuración</h3>
            </div>
            <div class="col-md-4">
                <div class="card card-hover">
                    <div class="card-body text-center">
                        <i class="fas fa-download fa-3x text-primary mb-3"></i>
                        <h5>Importar Estructura</h5>
                        <p class="text-muted">Crear todas las tablas desde database_structure.sql</p>
                        <a href="importar_estructura.php" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Importar Estructura
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-hover">
                    <div class="card-body text-center">
                        <i class="fas fa-seedling fa-3x text-warning mb-3"></i>
                        <h5>Datos Iniciales</h5>
                        <p class="text-muted">Importar datos de ejemplo y configuración básica</p>
                        <a href="initial_data_import.php" class="btn btn-warning">
                            <i class="fas fa-database"></i> Importar Datos
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-hover">
                    <div class="card-body text-center">
                        <i class="fas fa-user-shield fa-3x text-secondary mb-3"></i>
                        <h5>Configurar Permisos</h5>
                        <p class="text-muted">Configurar permisos para el módulo de procedimientos</p>
                        <a href="setup_procedimientos.php" class="btn btn-secondary">
                            <i class="fas fa-key"></i> Configurar Permisos
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Módulos Específicos -->
        <div class="row mb-4">
            <div class="col-12">
                <h3><i class="fas fa-puzzle-piece text-success"></i> Configuración de Módulos</h3>
            </div>
            <div class="col-md-4">
                <div class="card card-hover">
                    <div class="card-body text-center">
                        <i class="fas fa-teeth fa-3x text-info mb-3"></i>
                        <h5>Tabla Procedimientos</h5>
                        <p class="text-muted">Crear y verificar tabla de procedimientos odontológicos</p>
                        <a href="verificar_procedimientos.php" class="btn btn-info">
                            <i class="fas fa-check"></i> Verificar Procedimientos
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-hover">
                    <div class="card-body text-center">
                        <i class="fas fa-wrench fa-3x text-warning mb-3"></i>
                        <h5>Corregir Procedimientos</h5>
                        <p class="text-muted">Actualizar estructura de tabla existente</p>
                        <a href="corregir_procedimientos.php" class="btn btn-warning">
                            <i class="fas fa-tools"></i> Corregir Tabla
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-hover">
                    <div class="card-body text-center">
                        <i class="fas fa-notes-medical fa-3x text-primary mb-3"></i>
                        <h5>Actualizar Historial</h5>
                        <p class="text-muted">Agregar campos adicionales al historial médico</p>
                        <a href="update_historial_medico.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Actualizar Historial
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acceso al Sistema -->
        <div class="row mb-4">
            <div class="col-12">
                <h3><i class="fas fa-door-open text-success"></i> Acceso al Sistema</h3>
            </div>
            <div class="col-md-4">
                <div class="card card-hover border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-teeth fa-3x text-success mb-3"></i>
                        <h5>Gestión de Procedimientos</h5>
                        <p class="text-muted">Acceder al módulo de procedimientos odontológicos</p>
                        <a href="procedimientos.php" class="btn btn-success">
                            <i class="fas fa-arrow-right"></i> Ir a Procedimientos
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-hover border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-home fa-3x text-primary mb-3"></i>
                        <h5>Panel Principal</h5>
                        <p class="text-muted">Ir al panel principal del sistema</p>
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i> Ir al Sistema
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-hover border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-sign-in-alt fa-3x text-info mb-3"></i>
                        <h5>Iniciar Sesión</h5>
                        <p class="text-muted">Acceder al sistema con credenciales</p>
                        <a href="login.php" class="btn btn-info">
                            <i class="fas fa-arrow-right"></i> Login
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información -->
        <div class="row">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5><i class="fas fa-info-circle text-info"></i> Información Importante</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Orden de Configuración Recomendado:</h6>
                                <ol>
                                    <li><strong>Diagnóstico de Base de Datos</strong> - Verificar conectividad</li>
                                    <li><strong>Importar Estructura</strong> - Crear todas las tablas</li>
                                    <li><strong>Importar Datos Iniciales</strong> - Agregar datos de ejemplo</li>
                                    <li><strong>Configurar Permisos</strong> - Establecer permisos de usuario</li>
                                    <li><strong>Verificar Módulos</strong> - Comprobar funcionalidad específica</li>
                                </ol>
                            </div>
                            <div class="col-md-6">
                                <h6>Credenciales por Defecto:</h6>
                                <ul>
                                    <li><strong>Usuario:</strong> admin</li>
                                    <li><strong>Contraseña:</strong> password</li>
                                </ul>
                                <div class="alert alert-warning">
                                    <small><i class="fas fa-exclamation-triangle"></i> Se recomienda cambiar estas credenciales después del primer acceso.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
