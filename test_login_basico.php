<?php
require_once 'session_config.php';
session_start();

echo "<!DOCTYPE html>";
echo "<html><head><title>🧪 Test de Login Básico</title>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .ok{color:green;} .error{color:red;} .btn{padding:10px 20px;margin:5px;border:none;border-radius:3px;text-decoration:none;display:inline-block;cursor:pointer;background:#007bff;color:white;}</style>";
echo "</head><body>";

echo "<h1>🧪 Test de Login Básico</h1>";

// Si hay datos POST, procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Login básico para test (usar las credenciales reales de su sistema)
    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['loggedin'] = true;
        $_SESSION['id'] = 1;
        $_SESSION['username'] = 'admin';
        $_SESSION['nombre'] = 'Administrador';
        $_SESSION['last_activity'] = time();
        
        echo "<div class='ok'>";
        echo "✅ <strong>Login exitoso!</strong><br>";
        echo "Usuario: {$_SESSION['username']}<br>";
        echo "ID: {$_SESSION['id']}<br>";
        echo "Session ID: " . session_id() . "<br>";
        echo "</div>";
        
        echo "<h3>🔗 Páginas de Prueba</h3>";
        echo "<a href='facturacion.php' class='btn'>🧾 Facturación</a> ";
        echo "<a href='configuracion_impresora_80mm.php' class='btn'>⚙️ Configuración</a> ";
        echo "<a href='index.php' class='btn'>🏠 Inicio</a>";
        
    } else {
        echo "<div class='error'>❌ Credenciales incorrectas</div>";
    }
}

// Mostrar estado actual
echo "<h3>📊 Estado Actual</h3>";
echo "<p><strong>Loggedin:</strong> " . (isset($_SESSION['loggedin']) ? ($_SESSION['loggedin'] ? '<span class="ok">✅ SÍ</span>' : '<span class="error">❌ NO</span>') : '<span class="error">❌ No existe</span>') . "</p>";
echo "<p><strong>Usuario:</strong> " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'No definido') . "</p>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";

// Formulario de login si no está logueado
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "<h3>🔐 Login de Prueba</h3>";
    echo "<form method='POST'>";
    echo "<p>Usuario: <input type='text' name='username' value='admin' style='padding:5px;'></p>";
    echo "<p>Contraseña: <input type='password' name='password' value='admin' style='padding:5px;'></p>";
    echo "<p><button type='submit' class='btn'>🚀 Login de Prueba</button></p>";
    echo "</form>";
    echo "<small>Credenciales de prueba: admin/admin</small>";
}

echo "<hr>";
echo "<a href='restaurar_emergencia.php' class='btn' style='background:#dc3545;'>🚨 Emergencia</a> ";
echo "<a href='login.php' class='btn' style='background:#28a745;'>🚪 Login Real</a>";

echo "</body></html>";
?>
