<?php
header('Content-Type: application/json');

$apiKey = "USE YOUR API KEY HERE"; 
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=" . $apiKey;

$systemPrompt = "You are a neutral news aggregator. Provide a list of the top 5 trending political news headlines in India, based on current search results. For each headline, provide a 1-sentence summary. Format the output as an HTML bulleted list (`<ul>`). Do not add any introductory or concluding text, just the `<ul>`.";
$userQuery = "Top 5 trending Indian political news";

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

// --- Use cURL to make the API request ---
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

// --- Handle the response ---
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
    echo json_encode(['success' => true, 'news' => $result['candidates'][0]['content']['parts'][0]['text']]);
} elseif (isset($result['candidates'][0]['finishReason']) && $result['candidates'][0]['finishReason'] === 'SAFETY') {
     echo json_encode(['success' => false, 'error' => 'Content blocked by safety settings.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid API response structure.']);
}
?>
