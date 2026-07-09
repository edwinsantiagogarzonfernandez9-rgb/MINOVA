<?php
require_once __DIR__ . "/../config/conexion.php"; 
require_once __DIR__ . "/datos_minovita.php";
require_once __DIR__ . "/config_minovita.php";    
require_once __DIR__ . "/gemini_key.php";          

$apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
if (!$apiKey || $apiKey === 'PON_AQUI_TU_CLAVE_DE_GEMINI') {
    header("Content-Type: application/json; charset=utf-8");
    http_response_code(500);
    echo json_encode(["error" => "Falta configurar GEMINI_API_KEY en minovita/gemini_key.php"]);
    exit;
}

$body     = json_decode(file_get_contents("php://input"), true);
$mensajes = $body["messages"] ?? null;

if (!$mensajes || !is_array($mensajes)) {
    header("Content-Type: application/json; charset=utf-8");
    http_response_code(400);
    echo json_encode(["error" => "Falta el arreglo 'messages' en la solicitud."]);
    exit;
}

/** @var mysqli $conn */
$contextoReal = obtenerContextoMinovita($conn);
$systemFinal  = SYSTEM_BASE. "\n\n[DATOS EN VIVO]\n" . $contextoReal;

$contents = array_map(function ($m) {
    return [
        "role"  => $m["role"] === "assistant" ? "model" : "user",
        "parts" => [["text" => $m["content"]]],
    ];
}, $mensajes);

$payload = [
    "system_instruction" => ["parts" => [["text" => $systemFinal]]],
    "contents"           => $contents,
    "generationConfig"   => ["maxOutputTokens" => 768],
];

$modelo = "gemini-2.5-flash"; 
$url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key={$apiKey}";

header("Content-Type: application/json; charset=utf-8");

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS     => json_encode($payload),
]);

$respuestaCruda = curl_exec($ch);
$httpCode       = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError      = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión con la API de Gemini: {$curlError}"]);
    exit;
}

if ($httpCode === 429) {
    http_response_code(429);
    echo json_encode([
        "error" => [
            "code"    => 429,
            "type"    => "quota_exceeded",
            "message" => "Se agotó la cuota gratuita de MINOVITA por ahora. Intenta de nuevo en un minuto.",
        ],
    ]);
    exit;
}

if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo $respuestaCruda;
    exit;
}

$data  = json_decode($respuestaCruda, true);
$texto = $data["candidates"][0]["content"]["parts"][0]["text"] ?? "";

echo json_encode(["content" => [["type" => "text", "text" => $texto]]]);