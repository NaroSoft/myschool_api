<?php
// ==========================
// CORS HEADERS (REQUIRED)
// ==========================
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ==========================
// CONFIG
// ==========================
$apiKey = getenv('ARKESEL_API_KEY'); // safer (Render env variable)

// If not using env variable, temporarily:
// $apiKey = "YOUR_ARKESEL_API_KEY";

// ==========================
// CALL ARKESEL
// ==========================
$url = "https://sms.arkesel.com/sms/api?action=check-balance&api_key=" . $apiKey;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode([
        "status" => "error",
        "message" => curl_error($ch)
    ]);
    curl_close($ch);
    exit();
}

curl_close($ch);

// Decode Arkesel response
$data = json_decode($response, true);

if (!$data) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid response from Arkesel"
    ]);
    exit();
}

// Return clean JSON to Flutter
echo json_encode([
    "status" => "success",
    "balance" => $data['balance'] ?? 0
]);
