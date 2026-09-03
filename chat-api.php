<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(int $status, array $body): never
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, ['error' => 'Method not allowed.']);
}

$configFile = __DIR__ . '/config/gemini.local.php';
$config = is_file($configFile) ? require $configFile : [];
$apiKey = (string) (($config['api_key'] ?? '') ?: getenv('GEMINI_API_KEY'));
$model = (string) ($config['model'] ?? 'gemini-2.5-flash');

if ($apiKey === '' || $apiKey === 'PASTE_YOUR_GEMINI_API_KEY_HERE') {
    respond(503, ['error' => 'The chatbot is not configured yet. Add the Gemini API key in config/gemini.local.php.']);
}

$raw = file_get_contents('php://input');
$input = json_decode($raw ?: '', true);
if (!is_array($input) || !isset($input['message']) || !is_string($input['message'])) {
    respond(400, ['error' => 'Please enter a message.']);
}

$message = trim($input['message']);
if ($message === '') {
    respond(400, ['error' => 'Please enter a message.']);
}
if (mb_strlen($message) > 2000) {
    respond(400, ['error' => 'Please keep your message under 2,000 characters.']);
}

$contents = [];
$history = isset($input['history']) && is_array($input['history']) ? array_slice($input['history'], -10) : [];
foreach ($history as $item) {
    if (!is_array($item) || !isset($item['role'], $item['text']) || !is_string($item['text'])) {
        continue;
    }
    $role = $item['role'] === 'assistant' ? 'model' : ($item['role'] === 'user' ? 'user' : null);
    $text = trim($item['text']);
    if ($role && $text !== '') {
        $contents[] = ['role' => $role, 'parts' => [['text' => mb_substr($text, 0, 2000)]]];
    }
}
$contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

$payload = json_encode([
    'systemInstruction' => [
        'parts' => [[
            'text' => 'You are the friendly AI travel assistant for Serendib Pathways, a Sri Lankan eco-tourism website. Answer the visitor directly and concisely. Be especially useful about Sri Lanka destinations, activities, culture, packing, and trip planning. Never invent live availability, prices, bookings, or company policies; direct users to the Contact page when those details are needed. You may answer reasonable general questions too. Use plain text with short paragraphs and occasional simple bullet points.',
        ]],
    ],
    'contents' => $contents,
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 700,
        'thinkingConfig' => ['thinkingLevel' => 'LOW'],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

if (!function_exists('curl_init')) {
    respond(500, ['error' => 'PHP cURL is required for the chatbot. Enable the curl extension in XAMPP.']);
}

$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent';
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 90,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'x-goog-api-key: ' . $apiKey,
    ],
    CURLOPT_POSTFIELDS => $payload,
]);

$response = curl_exec($ch);
$status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false || $curlError !== '') {
    respond(502, ['error' => 'I could not reach Gemini right now. Please try again shortly.']);
}

$data = json_decode($response, true);
$reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
if ($status < 200 || $status >= 300 || !is_string($reply) || trim($reply) === '') {
    $apiMessage = $data['error']['message'] ?? '';
    error_log('Gemini API error (' . $status . '): ' . $apiMessage);
    respond(502, ['error' => 'Gemini could not answer that request. Check the API key and try again.']);
}

respond(200, ['reply' => trim($reply)]);
