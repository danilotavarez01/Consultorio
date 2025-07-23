<?php
// Archivo para enviar mensajes de WhatsApp
header('Content-Type: application/json');

// Incluir archivo de configuración
require_once "config.php";

// Obtener la URL del servidor de WhatsApp desde la configuración
$stmt = $conn->query("SELECT whatsapp_server FROM configuracion WHERE id = 1");
$config = $stmt->fetch(PDO::FETCH_ASSOC);
$whatsapp_server = $config['whatsapp_server'] ?? 'https://api.whatsapp.com';

// Permitir solicitudes desde cualquier origen (para desarrollo)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Log function for debugging
function logDebug($message, $data = null) {
    $logFile = __DIR__ . '/whatsapp_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    
    if ($data !== null) {
        $logMessage .= ': ' . json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    
    file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
}

// Start logging
logDebug('Request received', ['method' => $_SERVER['REQUEST_METHOD']]);
logDebug('Using WhatsApp server', $whatsapp_server);

// Verificar si es una solicitud para comprobar disponibilidad
if (isset($_GET['check']) && $_GET['check'] == '1') {
    try {
        // Intentar verificar si el servidor está activo usando la URL de la configuración
        $ch = curl_init($whatsapp_server);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Verificar si respondió
        if ($httpcode >= 200 && $httpcode < 300) {
            echo json_encode(['status' => 'success', 'message' => 'Servidor disponible']);
        } else {
            throw new Exception("Servidor no responde correctamente. Código: " . $httpcode);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Verificar que sea una solicitud POST para envío de mensajes
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Solo se permiten solicitudes POST']);
    exit;
}

// Obtener el cuerpo de la solicitud
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validar los datos recibidos
if (!$data || !isset($data['phone']) || !isset($data['message'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Datos incompletos. Se requieren los campos "phone" y "message"']);
    exit;
}

// Validar el teléfono
$phone = preg_replace('/[^0-9]/', '', $data['phone']);
logDebug('Original phone', $data['phone']);
logDebug('Cleaned phone', $phone);

if (strlen($phone) < 8) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Número de teléfono inválido']);
    exit;
}

// Asegurarse de que el número tiene formato internacional 
// Para República Dominicana (código de país 1)
if (strlen($phone) == 10 && substr($phone, 0, 1) != "1") {
    // Si empieza con 8 es un número dominicano sin el código de país
    $phone = "1" . $phone;
}
// Si el usuario solo ingresó los últimos 8 dígitos sin código de área
else if (strlen($phone) == 8) {
    // Agregar código de país 1 + código de área 809/829/849 para República Dominicana 
    // Usando 1809 como predeterminado
    $phone = "1809" . $phone;
}

// Asegurarnos que no tenga ningún caracter adicional
$phone = preg_replace('/[^0-9]/', '', $phone);

logDebug('Formatted phone for WhatsApp', $phone);

$message = $data['message'];

try {
    logDebug('Preparing to send message', ['phone' => $phone, 'messageLength' => strlen($message)]);
    
    // Configurar la solicitud a la API externa usando la URL de la configuración
    $apiUrl = $whatsapp_server . '/send-message';
    logDebug('API URL', $apiUrl);
    $ch = curl_init($apiUrl);    // Configurar los datos a enviar 
    // El API espera el número en formato internacional sin el signo +
    // Registrar el número que vamos a enviar para propósitos de debugging
    logDebug('Sending WhatsApp to formatted phone', $phone);
    
    $payload = json_encode([
        'phone' => $phone, 
        'message' => $message
    ]);
    
    // Configurar opciones de cURL
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout de 10 segundos
    
    // Ejecutar la solicitud
    logDebug('Executing cURL request');
    $result = curl_exec($ch);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    logDebug('cURL response', ['result' => $result, 'error' => $error, 'http_code' => $info['http_code']]);
    
    // Verificar si hubo errores
    if ($error) {
        throw new Exception('Error en la solicitud cURL: ' . $error);
    }
    
    // Verificar código de respuesta HTTP
    if ($info['http_code'] != 200) {
        throw new Exception('Error de la API: Código ' . $info['http_code']);
    }
    
    // Devolver la respuesta
    // Asegurarse de que la respuesta sea un objeto JSON válido
    $response_data = json_decode($result);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Si no es JSON válido, envolver en un objeto JSON
        echo json_encode(['status' => 'success', 'originalResponse' => $result]);
    } else {
        // Si ya es JSON válido, pasarlo como está
        echo $result;
    }
    
} catch (Exception $e) {
    logDebug('Exception occurred', ['message' => $e->getMessage()]);
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Error al enviar el mensaje',
        'detail' => $e->getMessage()
    ]);
}

// Log end of request
logDebug('Request completed');
?>
