<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Admin authorization required.']);
    exit;
}
if (($_SERVER['HTTP_X_SERENDIB_ADMIN'] ?? '') !== 'key-sync') {
    http_response_code(403);
    echo json_encode(['error' => 'Missing protected request header.']);
    exit;
}

$input = json_decode((string) file_get_contents('php://input'), true);
$keys = is_array($input['keys'] ?? null) ? $input['keys'] : [];
$keys = array_values(array_unique(array_filter(array_map(fn($key) => trim((string) $key), $keys), fn($key) => strlen($key) >= 30 && strlen($key) <= 200)));
if (!$keys || count($keys) > 5) {
    http_response_code(422);
    echo json_encode(['error' => 'Provide between one and five valid Gemini API keys.']);
    exit;
}

$model = trim((string) ($input['model'] ?? 'gemini-3.6-flash'));
if (!preg_match('/^[a-z0-9._-]{3,80}$/i', $model)) $model = 'gemini-3.6-flash';
$target = dirname(__DIR__) . '/config/gemini.local.php';
$temporary = $target . '.tmp';
$content = "<?php\nreturn " . var_export(['api_keys' => $keys, 'model' => $model], true) . ";\n";
if (file_put_contents($temporary, $content, LOCK_EX) === false || !rename($temporary, $target)) {
    @unlink($temporary);
    http_response_code(500);
    echo json_encode(['error' => 'Could not securely save the Gemini configuration.']);
    exit;
}
@chmod($target, 0600);
echo json_encode(['success' => true, 'key_count' => count($keys), 'model' => $model]);
