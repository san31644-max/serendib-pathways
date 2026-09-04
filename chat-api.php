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

function fetchJson(string $url, int $timeout = 15): ?array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 7, CURLOPT_TIMEOUT => $timeout, CURLOPT_USERAGENT => 'SerendibPathways/1.0']);
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if (!is_string($body) || $status < 200 || $status >= 300) return null;
    $data = json_decode($body, true);
    return is_array($data) ? $data : null;
}

function weatherLabel(int $code): string
{
    return match (true) {
        $code === 0 => 'clear skies',
        in_array($code, [1, 2], true) => 'mainly clear to partly cloudy skies',
        $code === 3 => 'overcast skies',
        in_array($code, [45, 48], true) => 'foggy conditions',
        in_array($code, [51, 53, 55, 56, 57], true) => 'drizzle',
        in_array($code, [61, 63, 65, 66, 67], true) => 'rain',
        in_array($code, [71, 73, 75, 77], true) => 'snow',
        in_array($code, [80, 81, 82], true) => 'rain showers',
        in_array($code, [85, 86], true) => 'snow showers',
        in_array($code, [95, 96, 99], true) => 'thunderstorms',
        default => 'changing conditions',
    };
}

function weatherPlace(string $message, array $history): string
{
    $clean = preg_replace('/[?!.]+$/u', '', trim($message));
    if (preg_match('/\b(?:in|at|for|near)\s+([\p{L}\p{N}\s,.-]{2,80})$/iu', $clean, $m)) return trim($m[1]);
    $candidate = preg_replace('/\b(?:what(?:\'s| is)?|how(?:\'s| is)?|tell me|give me|show me|will|would|can|could|it|be|current|today(?:\'s)?|tomorrow(?:\'s)?|now|right now|the|weather|forecast|temperature|climate|rain|raining|sunny|conditions|like)\b/iu', ' ', $clean);
    $candidate = trim((string) preg_replace('/\s+/', ' ', $candidate), " ,.-");
    if (mb_strlen($candidate) >= 2) return $candidate;
    foreach (array_reverse($history) as $item) {
        if (($item['role'] ?? '') !== 'user' || !is_string($item['text'] ?? null)) continue;
        if (preg_match('/\b(?:weather|forecast|temperature|rain)\b/iu', $item['text'])) {
            $place = weatherPlace($item['text'], []);
            if ($place !== '') return $place;
        }
    }
    return '';
}

function liveWeatherReply(string $place): ?string
{
    $geoUrl = 'https://geocoding-api.open-meteo.com/v1/search?name=' . rawurlencode($place) . '&count=10&language=en&format=json';
    $geo = fetchJson($geoUrl);
    if (!$geo || empty($geo['results'])) return null;
    $matches = $geo['results'];
    usort($matches, fn($a, $b) => (($b['country_code'] ?? '') === 'LK') <=> (($a['country_code'] ?? '') === 'LK'));
    $hit = $matches[0];
    $lat = (float) $hit['latitude']; $lon = (float) $hit['longitude'];
    $url = 'https://api.open-meteo.com/v1/forecast?latitude=' . $lat . '&longitude=' . $lon
        . '&current=temperature_2m,apparent_temperature,relative_humidity_2m,precipitation,weather_code,wind_speed_10m'
        . '&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max,sunrise,sunset&forecast_days=3&timezone=auto';
    $data = fetchJson($url);
    if (!$data || empty($data['current']) || empty($data['daily'])) return null;
    $c = $data['current']; $d = $data['daily'];
    $location = implode(', ', array_filter([$hit['name'] ?? $place, $hit['admin1'] ?? null, $hit['country'] ?? null]));
    $lines = [
        "Live weather for **{$location}**",
        sprintf('Now: %.1f°C, feels like %.1f°C, with %s.', $c['temperature_2m'], $c['apparent_temperature'], weatherLabel((int) $c['weather_code'])),
        sprintf('Humidity: %d%% · Wind: %.1f km/h · Current precipitation: %.1f mm', $c['relative_humidity_2m'], $c['wind_speed_10m'], $c['precipitation']),
        '',
        '**3-day outlook**',
    ];
    for ($i = 0; $i < min(3, count($d['time'] ?? [])); $i++) {
        $day = $i === 0 ? 'Today' : ($i === 1 ? 'Tomorrow' : date('D', strtotime($d['time'][$i])));
        $lines[] = sprintf('- %s: %.0f–%.0f°C, %s, rain chance up to %d%%.', $day, $d['temperature_2m_min'][$i], $d['temperature_2m_max'][$i], weatherLabel((int) $d['weather_code'][$i]), $d['precipitation_probability_max'][$i]);
    }
    $lines[] = '';
    $lines[] = 'Updated ' . str_replace('T', ' ', (string) $c['time']) . ' (' . ($data['timezone_abbreviation'] ?? 'local time') . '). Live data: Open-Meteo.';
    return implode("\n", $lines);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, ['error' => 'Method not allowed.']);
}

$configFile = __DIR__ . '/config/gemini.local.php';
$config = is_file($configFile) ? require $configFile : [];
$apiKey = (string) (($config['api_key'] ?? '') ?: getenv('GEMINI_API_KEY'));
$model = (string) ($config['model'] ?? 'gemini-2.5-flash');

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

$history = isset($input['history']) && is_array($input['history']) ? array_slice($input['history'], -10) : [];
$isWeather = (bool) preg_match('/\b(?:weather|forecast|temperature|rain|raining|sunny)\b/iu', $message);
if ($isWeather) {
    if (!function_exists('curl_init')) respond(500, ['error' => 'Live weather requires PHP cURL.']);
    $place = weatherPlace($message, $history);
    if ($place === '') respond(200, ['reply' => 'Which city or destination would you like the live weather for? For example: “weather in Kegalle”.']);
    $weatherReply = liveWeatherReply($place);
    if ($weatherReply === null) respond(200, ['reply' => "I couldn’t find live weather for “{$place}”. Please try a nearby town or include the country."]);
    respond(200, ['reply' => $weatherReply, 'source' => 'open-meteo']);
}

if ($apiKey === '' || $apiKey === 'PASTE_YOUR_GEMINI_API_KEY_HERE') {
    respond(503, ['error' => 'The chatbot is not configured yet. Add the Gemini API key in config/gemini.local.php.']);
}

$contents = [];
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

$models = array_values(array_unique([$model, 'gemini-3.6-flash', 'gemini-3.5-flash-lite', 'gemini-flash-latest']));
$lastStatus = 0;
$lastMessage = '';
$networkFailed = false;

foreach ($models as $candidateModel) {
    for ($attempt = 1; $attempt <= 2; $attempt++) {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($candidateModel) . ':generateContent';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'x-goog-api-key: ' . $apiKey],
            CURLOPT_POSTFIELDS => $payload,
        ]);
        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError !== '') {
            $networkFailed = true;
            $lastMessage = $curlError;
            break;
        }

        $data = json_decode($response, true);
        $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if ($status >= 200 && $status < 300 && is_string($reply) && trim($reply) !== '') {
            respond(200, ['reply' => trim($reply), 'model' => $candidateModel]);
        }

        $lastStatus = $status;
        $lastMessage = (string) ($data['error']['message'] ?? 'Unknown Gemini response');
        error_log('Gemini API error for ' . $candidateModel . ' (' . $status . '): ' . $lastMessage);
        if (!in_array($status, [429, 500, 502, 503, 504], true)) break;
        if ($attempt === 1) usleep(450000);
    }
}

if ($networkFailed && $lastStatus === 0) {
    respond(502, ['error' => 'I could not reach Gemini right now. Please try again shortly.']);
}
if ($lastStatus === 429 || str_contains(strtolower($lastMessage), 'credit')) {
    respond(503, ['error' => 'The AI assistant has reached its Gemini usage limit. Please try again later or contact us directly.']);
}
if (in_array($lastStatus, [500, 502, 503, 504], true)) {
    respond(503, ['error' => 'The AI assistant is temporarily busy. Please try again in a moment.']);
}
respond(502, ['error' => 'The Gemini connection needs attention. Please check the configured API key and model.']);
