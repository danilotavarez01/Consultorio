<?php
require_once "config.php";

$username = 'admin';
$new_password = '820416Dts';

// Generar el hash de la nueva contraseña
$hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

// Actualizar la contraseña en la base de datos
$sql = "UPDATE usuarios SET password = ? WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bindParam(1, $hashed_password, PDO::PARAM_STR);
$stmt->bindParam(2, $username, PDO::PARAM_STR);

if ($stmt->execute()) {
    echo "Contraseña actualizada exitosamente\n";
    echo "Usuario: " . $username . "\n";
    echo "Nueva contraseña: " . $new_password . "\n";
    echo "Nuevo hash: " . $hashed_password . "\n";
} else {
    echo "Error al actualizar la contraseña";
}