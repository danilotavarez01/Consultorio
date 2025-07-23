<?php
// Archivo para probar la conexión del servidor de WhatsApp
header('Content-Type: application/json');

// Permitir solicitudes desde cualquier origen
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS, GET');
header('Access-Control-Allow-Headers: Content-Type');

// Log function para depuración
function logDebug($message, $data = null) {
    $logFile = __DIR__ . '/whatsapp_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    
    if ($data !== null) {
        $logMessage .= ': ' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    
    file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
}

logDebug('Request received', [
    'method' => $_SERVER['REQUEST_METHOD'],
    'query' => $_GET,
    'headers' => getallheaders()
]);

// Modo de verificación del servidor
if (isset($_GET['check']) && $_GET['check'] == '1') {
    try {
        logDebug('Checking server availability');
        
        // Verificar si el servidor está activo con tiempo de espera reducido
        $ch = curl_init('http://localhost:5000');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_HEADER, false);
        
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        logDebug('Server check results', [
            'result' => $result,
            'error' => $error,
            'http_code' => $httpcode
        ]);
        
        if ($httpcode >= 200 && $httpcode < 300) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Servidor WhatsApp disponible',
                'http_code' => $httpcode
            ]);
        } else {
            throw new Exception("El servidor no responde correctamente. Código: " . $httpcode);
        }
    } catch (Exception $e) {
        logDebug('Server check exception', ['message' => $e->getMessage()]);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Manejar la solicitud POST para enviar mensajes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtener el cuerpo de la solicitud
        $json = file_get_contents('php://input');
        logDebug('Received POST data', ['raw' => $json]);
        
        $data = json_decode($json, true);
        
        if (!$data) {
            throw new Exception("Los datos JSON son inválidos: " . json_last_error_msg());
        }
        
        // Validar los datos recibidos
        if (!isset($data['phone']) || !isset($data['message'])) {
            throw new Exception("Datos incompletos. Se requieren los campos 'phone' y 'message'");
        }
        
        // Procesar el número de teléfono
        $phone = preg_replace('/[^0-9]/', '', $data['phone']);
        logDebug('Phone number processing', [
            'original' => $data['phone'],
            'cleaned' => $phone
        ]);
        
        if (strlen($phone) < 8) {
            throw new Exception("Número de teléfono inválido: demasiado corto");
        }
        
        // Formato para República Dominicana
        if (strlen($phone) == 10 && substr($phone, 0, 1) != "1") {
            $phone = "1" . $phone;
        } else if (strlen($phone) == 8) {
            $phone = "1809" . $phone;
        }
        
        logDebug('Final formatted phone', $phone);
        
        $message = $data['message'];
        
        // Enviar el mensaje al servidor WhatsApp
        $apiUrl = 'http://localhost:5000/send-message';
        $payload = json_encode([
            'phone' => $phone,
            'message' => $message
        ]);
        
        logDebug('Sending request to WhatsApp server', [
            'url' => $apiUrl,
            'payload' => $payload
        ]);
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload)
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        
        curl_close($ch);
        
        logDebug('WhatsApp server response', [
            'result' => $result,
            'error' => $error,
            'http_code' => $info['http_code']
        ]);
        
        if ($error) {
            throw new Exception("Error en la solicitud cURL: " . $error);
        }
        
        if ($info['http_code'] != 200) {
            throw new Exception("Error de la API: Código " . $info['http_code'] . " - " . $result);
        }
        
        // Procesar la respuesta y devolverla al cliente
        $response_data = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                'status' => 'success',
                'originalResponse' => $result
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'data' => $response_data
            ]);
        }
        
    } catch (Exception $e) {
        logDebug('Exception occurred', ['message' => $e->getMessage()]);
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al enviar el mensaje',
            'detail' => $e->getMessage()
        ]);
    }
    exit;
}

// Si no es una solicitud POST o GET con check=1
http_response_code(400);
echo json_encode([
    'status' => 'error',
    'message' => 'Solicitud inválida'
]);
?>
