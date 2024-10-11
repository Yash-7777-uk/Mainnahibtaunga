<?php
// CORS headers - restrict to your domain
header("Access-Control-Allow-Origin: https://study-uk.great-site.net");
header("Content-Type: application/json");

// Sirf POST requests ko allow karein
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// JSON input parse karein
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['destinationUrl']) || !isset($input['alias'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

$destinationUrl = filter_var($input['destinationUrl'], FILTER_VALIDATE_URL);
$alias = preg_replace('/[^A-Za-z0-9\-]/', '', $input['alias']); // Sanitize alias

if (!$destinationUrl) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Invalid destination URL']);
    exit;
}

// External API details
$apiKey = 'd587f6def6b4974a2bed804b7452f694815f0ee4';
$apiUrl = "https://omegalinks.in/api?api={$apiKey}&url=" . urlencode($destinationUrl) . "&alias={$alias}&format=json";

// Initialize cURL
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute cURL request
$response = curl_exec($ch);

// Check for cURL errors
if ($response === false) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Failed to connect to external API']);
    curl_close($ch);
    exit;
}

// Close cURL
curl_close($ch);

// Decode external API response
$apiResponse = json_decode($response, true);

// Return the response to the client
echo json_encode($apiResponse);
?>