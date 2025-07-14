<?php
// Script de debug para verificar CORS
// Accede a: /api/debug-cors.php

header('Content-Type: application/json');

$origin = $_SERVER['HTTP_ORIGIN'] ?? 'No origin header';
$method = $_SERVER['REQUEST_METHOD'];
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'No user agent';

$debug = [
    'origin' => $origin,
    'method' => $method,
    'user_agent' => $userAgent,
    'all_headers' => [],
    'timestamp' => date('Y-m-d H:i:s')
];

// Obtener todos los headers
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_') === 0) {
        $header = str_replace('HTTP_', '', $key);
        $header = str_replace('_', '-', $header);
        $debug['all_headers'][$header] = $value;
    }
}

echo json_encode($debug, JSON_PRETTY_PRINT);
?>
