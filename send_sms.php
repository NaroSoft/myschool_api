<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json");

// ======================
// CONFIG
// ======================
$apiKey = getenv('ARKESEL_API_KEY');

if (!$apiKey) {
    echo json_encode(["status" => "error", "message" => "Missing API key"]);
    exit();
}

// ======================
// READ JSON INPUT
// ======================
$input = json_decode(file_get_contents("php://input"), true);

$to = $input['to'] ?? '';
$message = $input['message'] ?? '';
$sender = $input['sender'] ?? 'MyApp';

if (!$to || !$message) {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    exit();
}

// ======================
// FORMAT NUMBER (Ghana)
// ======================
$to = preg_replace('/[^0-9]/', '', $to);

if (strpos($to, '0') === 0) {
    $to = '233' . substr($to, 1);
}

// ======================
// ARKESEL ENDPOINT
// ======================
$url = "https://sms.arkesel.com/sms/api?action=send-sms";

// Build query string (THIS is important)
$postFields = [
    "api_key" => $apiKey,
    "to" => $to,
    "from" => $sender,
    "sms" => $message
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url . '&' . http_build_query($postFields));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode([
        "status" => "error",
        "curl_error" => curl_error($ch)
    ]);
    curl_close($ch);
    exit();
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Return FULL Arkesel response for debugging
echo json_encode([
    "status" => "success",
    "http_code" => $httpCode,
    "arkesel_response" => $response
]);
