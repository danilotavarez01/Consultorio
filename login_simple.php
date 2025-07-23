<?php
// Simple login page with minimal styling
session_start();
require_once "config.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// If already logged in, redirect to index
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}

// Initialize variables
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Process form data when submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Por favor ingrese su nombre de usuario.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Validate password
    if(empty($_POST["password"])){
        $password_err = "Por favor ingrese su contraseña.";
    } else{
        $password = $_POST["password"];
    }
    
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        try {
            // Prepare a select statement
            $sql = "SELECT id, username, password, nombre, rol FROM usuarios WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(1, $username, PDO::PARAM_STR);
            $stmt->execute();
            
            // Check if username exists
            if($stmt->rowCount() == 1){
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password
                if(password_verify($password, $row['password'])){
                    // Success - set session variables
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $row['id'];
                    $_SESSION["username"] = $row['username'];
                    $_SESSION["nombre"] = $row['nombre'];
                    $_SESSION["rol"] = $row['rol'];
                    
                    // Redirect to welcome page
                    header("location: index.php");
                    exit;
                } else{
                    // Password is not valid
                    $login_err = "La contraseña ingresada no es válida.";
                }
            } else{
                // Username doesn't exist
                $login_err = "Usuario no encontrado.";
            }
            
            // Close statement
            $stmt = null;
        } catch(PDOException $e) {
            $login_err = "Error al intentar iniciar sesión: " . $e->getMessage();
        }
    }
}
?>
 
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Simplificado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-form {
            background-color: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .alert {
            padding: 10px;
            background-color: #f8d7da;
            color: #721c24;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .invalid-feedback {
            color: #dc3545;
            font-size: 80%;
            margin-top: 5px;
        }
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            width: 100%;
        }
        .btn:hover {
            background-color: #0069d9;
        }
    </style>
</head>
<body>
    <div class="login-form">
        <h1>Inicio de Sesión Simplificado</h1>
        
        <?php 
        if(!empty($login_err)){
            echo '<div class="alert">' . $login_err . '</div>';
        }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn" value="Ingresar">
            </div>
        </form>
        
        <div style="text-align:center; margin-top:20px;">
            <a href="login.php">Volver al login normal</a>
        </div>
    </div>
</body>
</html>
