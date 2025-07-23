<?php
// Script para probar send_whatsapp.php
header('Content-Type: text/html; charset=UTF-8');

// Parámetros para la prueba (puedes modificarlos según necesites)
$phone = isset($_GET['phone']) ? $_GET['phone'] : '8091234567'; // Teléfono predeterminado
$message = isset($_GET['message']) ? $_GET['message'] : 'Mensaje de prueba desde test_whatsapp_cli.php';
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'check'; // Modos: check, send, both

echo "<h1>Prueba del sistema de WhatsApp</h1>";
echo "<p>Fecha y hora: " . date('Y-m-d H:i:s') . "</p>";

// Función para mostrar resultados
function showResult($title, $data, $isError = false) {
    echo "<div style='margin: 10px 0; padding: 10px; border-radius: 5px; " . 
         "border: 1px solid " . ($isError ? "#f88" : "#8f8") . "; " . 
         "background-color: " . ($isError ? "#fee" : "#efe") . ";'>";
    echo "<h3>" . htmlspecialchars($title) . "</h3>";
    echo "<pre style='background-color: #f5f5f5; padding: 10px; border-radius: 3px;'>";
    echo htmlspecialchars(print_r($data, true));
    echo "</pre></div>";
}

// Verificar servidor
if ($mode == 'check' || $mode == 'both') {
    echo "<h2>1. Verificando disponibilidad del servidor:</h2>";
    
    try {
        // Hacer solicitud a send_whatsapp.php para verificar
        $url = "http://" . $_SERVER['HTTP_HOST'] . "/Consultorio2/send_whatsapp.php?check=1";
        echo "<p>URL: " . htmlspecialchars($url) . "</p>";
        
        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'GET',
                'timeout' => 5
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            showResult("Error al verificar el servidor", "No se pudo conectar al servidor", true);
        } else {
            $response = json_decode($result, true);
            showResult("Respuesta recibida", $response, !($response && isset($response['status']) && $response['status'] == 'success'));
        }
    } catch (Exception $e) {
        showResult("Excepción al verificar el servidor", $e->getMessage(), true);
    }
}

// Enviar mensaje
if ($mode == 'send' || $mode == 'both') {
    echo "<h2>2. Enviando mensaje de WhatsApp:</h2>";
    echo "<p>Número: " . htmlspecialchars($phone) . "</p>";
    echo "<p>Mensaje: " . htmlspecialchars($message) . "</p>";
    
    try {
        // Preparar datos para enviar
        $data = json_encode([
            'phone' => $phone,
            'message' => $message
        ]);
        
        // Hacer solicitud a send_whatsapp.php para enviar mensaje
        $url = "http://" . $_SERVER['HTTP_HOST'] . "/Consultorio2/send_whatsapp.php";
        echo "<p>URL: " . htmlspecialchars($url) . "</p>";
        
        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => $data,
                'timeout' => 15
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            showResult("Error al enviar el mensaje", "No se pudo conectar al servidor", true);
        } else {
            $response = json_decode($result, true);
            showResult("Respuesta recibida", $response, !($response && !isset($response['error'])));
        }
    } catch (Exception $e) {
        showResult("Excepción al enviar el mensaje", $e->getMessage(), true);
    }
}

// Mostrar links para otras pruebas
echo "<h2>Otras opciones de prueba:</h2>";
echo "<ul>";
echo "<li><a href='test_whatsapp_cli.php?mode=check'>Solo verificar servidor</a></li>";
echo "<li><a href='test_whatsapp_cli.php?mode=send'>Solo enviar mensaje (con teléfono predeterminado)</a></li>";
echo "<li><a href='test_whatsapp_cli.php?mode=both'>Ambas pruebas</a></li>";
echo "<li><a href='test_whatsapp_cli.php?mode=send&phone=1809XXXXXXX'>Enviar mensaje a un número personalizado</a></li>";
echo "</ul>";

echo "<p><a href='test_whatsapp.html'>Ir a la interfaz interactiva de prueba</a></p>";

// Información adicional para depuración
echo "<h2>Información del sistema:</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "User Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "</pre>";
?>
