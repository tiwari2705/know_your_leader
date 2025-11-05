<?php
require_once '../config/db.php';
start_secure_session();

header('Content-Type: application/json');

if (!is_citizen()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$userQuery = $data['query'] ?? null;

if (empty($userQuery)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No query provided.']);
    exit;
}

$apiKey = "USE YOUR API KEY HERE"; 
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=" . $apiKey;

$systemPrompt = "You are a helpful AI Political Assistant for a website called 'Know Your Leader'. You can answer general political questions about India and the world using Google Search. Be neutral, factual, and concise. Format your answers clearly. If you provide lists, use bullet points. Do not make up information. and answer in as shortest as you can";

$payload = [
    'contents' => [
        [
            'parts' => [['text' => $userQuery]]
        ]
    ],
    'tools' => [
        ['google_search' => new stdClass()] 
    ],
    'systemInstruction' => [
        'parts' => [['text' => $systemPrompt]]
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'cURL Error: ' . $curl_error]);
    exit;
}

if ($httpcode != 200) {
    http_response_code($httpcode);
    echo json_encode(['success' => false, 'error' => 'API Error: ' . $response, 'status' => $httpcode]);
    exit;
}

$result = json_decode($response, true);

if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    echo json_encode(['success' => true, 'reply' => $result['candidates'][0]['content']['parts'][0]['text']]);
} elseif (isset($result['candidates'][0]['finishReason']) && $result['candidates'][0]['finishReason'] === 'SAFETY') {
     echo json_encode(['success' => false, 'error' => "I'm sorry, I can't respond to that topic due to safety guidelines."]);
} else {
    echo json_encode(['success' => false, 'error' => 'Sorry, I couldn\'t find an answer to that.']);
}
?>

