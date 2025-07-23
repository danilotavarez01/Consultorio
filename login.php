<?php
require_once 'session_config.php';
session_start();
require_once "config.php";

// Inicializar variables
$logo_path = '';
$config = null;
$error_msg = null;

// Obtener la configuración (incluyendo el logo)
try {
    // Verificar que la conexión a base de datos esté disponible
    if (!isset($conn) || $conn === null) {
        $error_msg = "La conexión a la base de datos no está disponible";
    } else {
        // Activar el modo de errores para PDO
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Consulta para obtener la configuración
        $stmt = $conn->query("SELECT logo, nombre_consultorio FROM configuracion WHERE id = 1");
        if ($stmt) {
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Procesar el logo si existe
            if ($config && isset($config['logo']) && $config['logo'] !== null && !empty($config['logo'])) {
                // Generar un hash único basado en el contenido del logo para cache busting
                $logoHash = md5($config['logo']);
                
                // Crear URL para servir el logo desde un archivo separado
                $logo_path = "get_logo.php?v=" . $logoHash;
                
                // También mantener la opción de data URI como fallback
                $imageType = 'image/png';
                $imageData = $config['logo'];
                
                // Verificar si ya está en base64 o es binario
                if (base64_decode($imageData, true) !== false && base64_encode(base64_decode($imageData)) === $imageData) {
                    // Ya está en base64
                    $logo_data_uri = 'data:' . $imageType . ';base64,' . $imageData;
                } else {
                    // Es binario, convertir a base64
                    $logo_data_uri = 'data:' . $imageType . ';base64,' . base64_encode($imageData);
                }
            }
        } else {
            $error_msg = "Error en la consulta de configuración";
        }
    }
} catch(PDOException $e) {
    $error_msg = "Error de base de datos: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    
    $sql = "SELECT id, username, password, nombre, rol FROM usuarios WHERE username = ?";
    if ($stmt = $conn->prepare($sql)) {  // Changed to PDO prepare
        $stmt->bindParam(1, $username, PDO::PARAM_STR);
        
        if ($stmt->execute()) {  // Changed to PDO execute
            if ($stmt->rowCount() == 1) {  // Changed to PDO rowCount
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {  // Add explicit check for fetched row
                    if (password_verify($password, $row['password'])) {
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $row['id'];
                        $_SESSION["username"] = $row['username'];
                        $_SESSION["nombre"] = $row['nombre'];
                        $_SESSION["rol"] = $row['rol'];
                        
                        header("location: index.php");
                        exit;
                    } else {
                        $login_err = "Contraseña incorrecta.";
                    }
                } else {
                    $login_err = "Error al obtener los datos del usuario.";
                }
            } else {
                $login_err = "Usuario no encontrado.";
            }
        } else {
            echo "Error en el sistema. Por favor intente más tarde.";
        }
        $stmt = null;  // Changed from mysqli_stmt_close() to PDO statement nullification
    }
}

// Manejar mensajes de logout y acceso
$logout_message = '';
if (isset($_GET['logout'])) {
    switch ($_GET['logout']) {
        case 'success':
            $logout_message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <strong>Sesión cerrada exitosamente.</strong> Has sido desconectado del sistema de forma segura.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
            break;
        case 'inactive':
            $logout_message = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-clock"></i> <strong>Sesión expirada.</strong> Tu sesión ha caducado por inactividad (más de 2 horas sin actividad).
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
            break;
        case 'forced':
            $logout_message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <strong>Sesión terminada.</strong> Tu sesión fue cerrada por motivos de seguridad.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
            break;
        default:
            $logout_message = '<div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle"></i> <strong>Sesión terminada.</strong> Por favor, inicia sesión nuevamente.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
            break;
    }
} elseif (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'login_required':
            $logout_message = '<div class="alert alert-primary alert-dismissible fade show" role="alert">
                <i class="fas fa-sign-in-alt"></i> <strong>Acceso requerido.</strong> Debes iniciar sesión para acceder a esta página.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
            break;
        case 'no_permissions':
            $logout_message = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-ban"></i> <strong>Sin permisos.</strong> No tienes permisos para acceder a esa página.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultorio Médico - Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .wrapper {
            width: 400px;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }
        .wrapper:hover {
            transform: translateY(-5px);
        }
        .logo-img {
            max-width: 180px;
            height: auto;
            display: block;
            margin: 0 auto 25px;
            transition: transform 0.3s ease;
        }
        .logo-img:hover {
            transform: scale(1.05);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #e1e1e1;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
            border-color: #0d6efd;
        }
        .btn-primary {
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-radius: 8px;
            background: linear-gradient(135deg, #0d6efd 0%, #0099ff 100%);
            border: none;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
            background: linear-gradient(135deg, #0099ff 0%, #0d6efd 100%);
        }
        h2 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        .alert {
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        label {
            color: #495057;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>    <?php if ($error_msg): ?>
    <div class="alert alert-danger">
        <strong>Error:</strong> <?php echo htmlspecialchars($error_msg); ?>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($logout_message)): ?>
        <?php echo $logout_message; ?>
    <?php endif; ?>
    
    <div class="wrapper">
        <?php if (!empty($logo_path)): ?>
            <img src="<?php echo htmlspecialchars($logo_path); ?>" alt="Logo" class="logo-img" 
                 onerror="this.onerror=null; this.src='<?php echo isset($logo_data_uri) ? htmlspecialchars($logo_data_uri) : 'medicina.png'; ?>';">
        <?php else: ?>
            <img src="medicina.png" alt="Logo Default" class="logo-img">
        <?php endif; ?>
        
        <?php if (isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
            <div class="alert alert-info" style="font-size: 12px;">
                <strong>Debug Info:</strong><br>
                Config cargado: <?php echo $config ? 'Sí' : 'No'; ?><br>
                Logo existe: <?php echo (isset($config['logo']) && !empty($config['logo'])) ? 'Sí' : 'No'; ?><br>
                Logo path: <?php echo !empty($logo_path) ? 'Generado' : 'Vacío'; ?><br>
                Tamaño logo: <?php echo isset($config['logo']) ? strlen($config['logo']) . ' bytes' : 'N/A'; ?><br>
                Logo hash: <?php echo isset($logoHash) ? $logoHash : 'N/A'; ?>
            </div>
        <?php endif; ?>
        
        <h2 class="text-center mb-4"><?php echo htmlspecialchars($config['nombre_consultorio'] ?? 'Consultorio Médico'); ?></h2>
        
        <?php 
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }        
        ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label><i class="fas fa-user mr-2"></i>Usuario</label>
                <input type="text" name="username" class="form-control" required 
                       placeholder="Ingrese su usuario">
            </div>    
            <div class="form-group">
                <label><i class="fas fa-lock mr-2"></i>Contraseña</label>
                <input type="password" name="password" class="form-control" required
                       placeholder="Ingrese su contraseña">
            </div>
            <div class="form-group mb-0">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt mr-2"></i>Ingresar
                </button>
            </div>
        </form>
    </div>

    <!-- Scripts para Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    
    <!-- Auto-ocultar alertas después de 5 segundos -->
    <script>
        $(document).ready(function() {
            // Auto-ocultar alertas de logout después de 5 segundos
            $('.alert').each(function() {
                var $alert = $(this);
                setTimeout(function() {
                    $alert.alert('close');
                }, 5000);
            });
        });
    </script>
</body>
</html>