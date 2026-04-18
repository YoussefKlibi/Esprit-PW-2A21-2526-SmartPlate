<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

function jsonOut(array $payload, int $code = 200): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function env(string $key): string {
    $v = getenv($key);
    if (is_string($v) && trim($v) !== '') {
        return trim($v);
    }
    if (isset($_SERVER[$key]) && is_string($_SERVER[$key]) && trim($_SERVER[$key]) !== '') {
        return trim((string)$_SERVER[$key]);
    }
    if (isset($_ENV[$key]) && is_string($_ENV[$key]) && trim($_ENV[$key]) !== '') {
        return trim((string)$_ENV[$key]);
    }
    return '';
}

/**
 * Token Hugging Face : HF_TOKEN, HUGGINGFACE_API_KEY, HUGGINGFACE_HUB_TOKEN,
 * ou fichier local Controller/huggingface_api_token.local (une ligne).
 * Le token doit avoir la permission Inference Providers (Hub > Settings > Tokens).
 */
function huggingFaceToken(): string {
    foreach (['HF_TOKEN', 'HUGGINGFACE_API_KEY', 'HUGGINGFACE_HUB_TOKEN'] as $k) {
        $v = env($k);
        if ($v !== '') {
            return $v;
        }
    }
    $local = __DIR__ . DIRECTORY_SEPARATOR . 'huggingface_api_token.local';
    if (is_readable($local)) {
        $line = trim((string)file_get_contents($local));
        if ($line !== '' && $line[0] !== '#') {
            return $line;
        }
    }
    return '';
}

function nutritionSystemPrompt(): string {
    return 'You are a nutrition expert. Answer only nutrition and healthy food questions. '
        . 'If the question is not about nutrition, say: Je ne réponds qu\'à des questions nutritionnelles seulement.';
}

/**
 * Réponse de l’API Chat Completions (router HF, compatible OpenAI).
 */
function extractChatCompletionContent(mixed $decoded): ?string {
    if (!is_array($decoded)) {
        return null;
    }
    $c = $decoded['choices'][0]['message']['content'] ?? null;
    if (is_string($c) && trim($c) !== '') {
        return $c;
    }
    return null;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    jsonOut(['ok' => false, 'error' => 'Méthode non autorisée'], 405);
}

$action = isset($_POST['action']) ? trim((string)$_POST['action']) : '';
if ($action !== 'ask') {
    jsonOut(['ok' => false, 'error' => 'Action invalide'], 400);
}

$question = isset($_POST['question']) ? trim((string)$_POST['question']) : '';
if ($question === '') {
    jsonOut(['ok' => false, 'error' => 'Question vide'], 400);
}

$token = huggingFaceToken();
if ($token === '') {
    jsonOut([
        'ok' => false,
        'error' => 'Token Hugging Face manquant. Définis HF_TOKEN (ou HUGGINGFACE_API_KEY) '
            . 'ou crée Controller/huggingface_api_token.local avec ton token sur une ligne.',
    ], 500);
}

$model = env('HF_MODEL');
if ($model === '') {
    $model = 'google/gemma-4-31B-it:novita';
}

// L’ancienne URL api-inference.huggingface.co/models/... n’est plus servie (Cannot POST /models/...).
// API actuelle : router compatible OpenAI — https://huggingface.co/docs/inference-providers
$chatUrl = env('HF_CHAT_URL');
if ($chatUrl === '') {
    $chatUrl = 'https://router.huggingface.co/v1/chat/completions';
}

$q = str_replace(["\r\n", "\r"], "\n", $question);

$payload = [
    'model' => $model,
    'messages' => [
        ['role' => 'system', 'content' => nutritionSystemPrompt()],
        ['role' => 'user', 'content' => $q],
    ],
    'max_tokens' => 512,
    'temperature' => 0.7,
    'top_p' => 0.95,
];

$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token,
];

$maxAttempts = 2;
$lastResponse = '';
$lastHttp = 0;
$lastCurlErr = '';

for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
    $ch = curl_init($chatUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);

    $lastResponse = curl_exec($ch);
    $lastCurlErr = curl_error($ch);
    $lastHttp = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($lastResponse === false) {
        jsonOut([
            'ok' => false,
            'error' => 'Erreur réseau cURL vers Hugging Face',
            'details' => $lastCurlErr,
        ], 500);
    }

    if ($lastHttp === 503 && $attempt < $maxAttempts) {
        sleep(3);
        continue;
    }
    break;
}

if ($lastHttp < 200 || $lastHttp >= 300) {
    $maybeJson = json_decode($lastResponse, true);
    $details = is_array($maybeJson) ? $maybeJson : $lastResponse;
    jsonOut([
        'ok' => false,
        'error' => 'Erreur API Hugging Face',
        'httpCode' => $lastHttp,
        'details' => $details,
    ], 500);
}

$result = json_decode($lastResponse, true);
if (!is_array($result)) {
    jsonOut([
        'ok' => false,
        'error' => 'Réponse Hugging Face invalide (non-JSON)',
        'details' => $lastResponse,
    ], 500);
}

if (isset($result['error'])) {
    $err = $result['error'];
    $msg = is_array($err)
        ? (string)($err['message'] ?? json_encode($err, JSON_UNESCAPED_UNICODE))
        : (string)$err;
    jsonOut([
        'ok' => false,
        'error' => 'Erreur Hugging Face : ' . $msg,
        'details' => $result,
    ], 500);
}

$answer = extractChatCompletionContent($result);
if ($answer === null || trim($answer) === '') {
    jsonOut([
        'ok' => false,
        'error' => 'Aucun texte généré par le modèle.',
        'details' => $result,
    ], 500);
}

$answer = trim($answer);

jsonOut([
    'ok' => true,
    'answer' => $answer,
    'meta' => [
        'mode' => 'huggingface_router_chat',
        'model' => $model,
        'timestamp' => time(),
    ],
]);
