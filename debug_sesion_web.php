<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Sesión Web</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .session-info { background: #f0f0f0; padding: 15px; border-radius: 5px; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h2>Debug de Sesión - Entorno Web</h2>
    
    <div class="session-info">
        <h3>Variables de Sesión:</h3>
        <?php
        echo "<strong>Sesión iniciada:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? "SÍ" : "NO") . "<br>";
        echo "<strong>ID de Sesión:</strong> " . session_id() . "<br><br>";
        
        if (!empty($_SESSION)) {
            echo "<h4>Contenido de \$_SESSION:</h4>";
            foreach ($_SESSION as $key => $value) {
                echo "<strong>$key:</strong> ";
                if (is_array($value) || is_object($value)) {
                    echo "<pre>" . print_r($value, true) . "</pre>";
                } else {
                    echo htmlspecialchars($value) . "<br>";
                }
            }
        } else {
            echo "<span class='error'>No hay variables de sesión establecidas</span><br>";
        }
        ?>
    </div>
    
    <h3>Verificación de Login:</h3>
    <?php
    $logueado_checks = [
        'user_id' => isset($_SESSION['user_id']),
        'loggedin' => isset($_SESSION['loggedin']),
        'username' => isset($_SESSION['username']),
        'id' => isset($_SESSION['id']),
        'rol' => isset($_SESSION['rol'])
    ];
    
    foreach ($logueado_checks as $check => $result) {
        $class = $result ? 'success' : 'error';
        echo "<span class='$class'>$check: " . ($result ? 'EXISTE' : 'NO EXISTE') . "</span><br>";
    }
    ?>
    
    <h3>Test de Acceso a Procedimientos:</h3>
    <?php
    // Simular las mismas verificaciones que hace procedimientos.php
    echo "<strong>¿Usuario logueado? (user_id)</strong> ";
    if (!isset($_SESSION['user_id'])) {
        echo "<span class='error'>NO - Se redirigiría a index.php</span><br>";
    } else {
        echo "<span class='success'>SÍ</span><br>";
    }
    
    echo "<strong>¿Puede gestionar procedimientos?</strong> ";
    $usuario_puede_gestionar = (
        (isset($_SESSION['username']) && $_SESSION['username'] === 'admin') ||
        (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin')
    );
    
    if ($usuario_puede_gestionar) {
        echo "<span class='success'>SÍ - Tiene permisos</span><br>";
    } else {
        echo "<span class='error'>NO - No tiene permisos</span><br>";
    }
    ?>
    
    <h3>Verificar Base de Datos:</h3>
    <?php
    try {
        require_once 'config.php';
        echo "<span class='success'>Conexión a BD: OK</span><br>";
        
        // Verificar tabla procedimientos
        $stmt = $conn->query("SELECT COUNT(*) as count FROM procedimientos");
        $count = $stmt->fetch()['count'];
        echo "<span class='success'>Tabla procedimientos: $count registros</span><br>";
        
    } catch (Exception $e) {
        echo "<span class='error'>Error BD: " . $e->getMessage() . "</span><br>";
    }
    ?>
    
    <hr>
    <p><a href="index.php">Volver al inicio</a></p>
    <p><a href="procedimientos.php">Intentar acceder a procedimientos</a></p>
</body>
</html>
