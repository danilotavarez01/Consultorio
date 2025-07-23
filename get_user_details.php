<?php
session_start();
require_once "permissions.php";
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

if (!hasPermission('manage_users')) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No tiene permisos']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        $stmt = $conn->prepare("SELECT id, username, nombre, rol, especialidad_id FROM usuarios WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            header('Content-Type: application/json');
            echo json_encode($user);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Usuario no encontrado']);
        }
    } catch(PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID no proporcionado']);
}
?>
