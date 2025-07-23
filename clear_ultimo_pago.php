<?php
require_once 'session_config.php';
session_start();

// Verificar que el usuario esté logueado antes de proceder
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
    exit();
}

// Determinar qué acción realizar
$action = $_POST['action'] ?? 'clear_all';

$cleared = [];
$message = '';

switch ($action) {
    case 'clear_show_modal_only':
        // Solo limpiar la variable show_print_modal, conservar datos de pago
        if (isset($_SESSION['show_print_modal'])) {
            unset($_SESSION['show_print_modal']);
            $cleared[] = 'show_print_modal';
        }
        $message = 'Variable show_print_modal limpiada (datos de pago conservados)';
        break;
        
    case 'clear_modal':
        // Limpiar variables del modal pero conservar último_pago
        if (isset($_SESSION['show_print_modal'])) {
            unset($_SESSION['show_print_modal']);
            $cleared[] = 'show_print_modal';
        }
        if (isset($_SESSION['success_message'])) {
            unset($_SESSION['success_message']);
            $cleared[] = 'success_message';
        }
        $message = 'Variables del modal limpiadas (datos de pago conservados para reimpresión)';
        break;
        
    case 'clear_all':
    default:
        // Limpiar TODAS las variables relacionadas con el modal de impresión
        if (isset($_SESSION['ultimo_pago'])) {
            unset($_SESSION['ultimo_pago']);
            $cleared[] = 'ultimo_pago';
        }
        if (isset($_SESSION['show_print_modal'])) {
            unset($_SESSION['show_print_modal']);
            $cleared[] = 'show_print_modal';
        }
        if (isset($_SESSION['success_message'])) {
            unset($_SESSION['success_message']);
            $cleared[] = 'success_message';
        }
        if (isset($_SESSION['ultimo_pago_timestamp'])) {
            unset($_SESSION['ultimo_pago_timestamp']);
            $cleared[] = 'ultimo_pago_timestamp';
        }
        $message = 'Todas las variables de modal limpiadas correctamente';
        break;
}

// Responder con éxito
http_response_code(200);
echo json_encode([
    'status' => 'success', 
    'message' => $message,
    'action' => $action,
    'cleared' => $cleared,
    'session_preserved' => true,
    'timestamp' => date('Y-m-d H:i:s')
]);
?>
