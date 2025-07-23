<?php
require_once "config.php";

// Datos de prueba
$username = trim('admin');
$password = trim('admin123');

// Depuración
echo "Longitud de la contraseña: " . strlen($password) . "\n";
echo "Bytes de la contraseña: ";
for($i = 0; $i < strlen($password); $i++) {
    echo ord($password[$i]) . " ";
}
echo "\n";

// Crear un nuevo hash para comparar
$new_hash = password_hash($password, PASSWORD_BCRYPT);
echo "Nuevo hash generado: " . $new_hash . "\n";

// Consulta para obtener el usuario
$sql = "SELECT id, username, password FROM usuarios WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bindParam(1, $username, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Hash almacenado: " . $row['password'] . "\n";
    
    if (password_verify($password, $row['password'])) {
        echo "¡La contraseña es correcta!";
    } else {
        echo "La contraseña es incorrecta";
        
        // Verificar si el nuevo hash funciona
        echo "\nPrueba con el nuevo hash: ";
        echo password_verify($password, $new_hash) ? "FUNCIONA" : "FALLA";
    }
} else {
    echo "Usuario no encontrado";
}