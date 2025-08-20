<?php
$urls = [
    "Inicio" => "https://dtpsolutions.xyz:4359/Consultorio2/index.php",
    "Login" => "https://dtpsolutions.xyz:4359/Consultorio2/login.php",
    // "Dashboard" => "https://dtpsolutions.xyz:8000/Consultorio2/dashboard.php",
    // Agrega aquí más páginas que desees monitorear
];

function medirTiempo($url) {
    $start = microtime(true);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 segundos de timeout
    curl_exec($ch);

    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $end = microtime(true);
    $time = round($end - $start, 4);

    return [
        "url" => $url,
        "tiempo" => $time,
        "codigo" => $httpCode,
        "error" => $error
    ];
}

$resultados = [];
foreach ($urls as $nombre => $url) {
    $resultado = medirTiempo($url);
    $resultado["nombre"] = $nombre;
    $resultados[] = $resultado;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Monitor de velocidad</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .ok { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Monitor de velocidad del sistema PHP</h1>
    <table>
        <tr>
            <th>Página</th>
            <th>URL</th>
            <th>Tiempo (s)</th>
            <th>HTTP</th>
            <th>Estado</th>
        </tr>
        <?php foreach ($resultados as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r["nombre"]) ?></td>
            <td><a href="<?= htmlspecialchars($r["url"]) ?>" target="_blank"><?= htmlspecialchars($r["url"]) ?></a></td>
            <td><?= $r["tiempo"] ?></td>
            <td><?= $r["codigo"] ?></td>
            <td class="<?= $r["error"] ? 'error' : 'ok' ?>">
                <?= $r["error"] ? "Error: " . $r["error"] : "OK" ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
