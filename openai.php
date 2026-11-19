<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function respond($status, $body)
{
    http_response_code($status);
    echo json_encode($body);
    exit;
}

function loadProjectEnv($path)
{
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        respond(500, array('error' => array('message' => 'Unable to read the project .env file.')));
    }

    foreach ($lines as $lineNumber => $line) {
        if ($lineNumber === 0) {
            $line = preg_replace('/^\xEF\xBB\xBF/', '', $line);
        }
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (!preg_match('/^([A-Z][A-Z0-9_]*)\s*=\s*(.*)$/', $line, $matches)) {
            respond(500, array('error' => array('message' => 'Invalid .env format. Use KEY=value on each line.')));
        }

        $name = $matches[1];
        $value = trim($matches[2]);
        if (strlen($value) >= 2 &&
            (($value[0] === '"' && substr($value, -1) === '"') ||
                ($value[0] === "'" && substr($value, -1) === "'"))) {
            $value = substr($value, 1, -1);
        }

        $existingValue = getenv($name);
        if ($existingValue === false || trim($existingValue) === '') {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, array('error' => array('message' => 'Method not allowed.')));
}

$rawInput = file_get_contents('php://input');
if ($rawInput === false || strlen($rawInput) > 32768) {
    respond(413, array('error' => array('message' => 'Request is too large.')));
}

$input = json_decode($rawInput, true);
if (!is_array($input) || !isset($input['mode']) || !is_string($input['mode'])) {
    respond(400, array('error' => array('message' => 'Invalid request. Send a JSON object with a mode.')));
}

if ($input['mode'] === 'quote') {
    if (isset($_SESSION['agri_quote'])) {
        respond(200, array('quote' => $_SESSION['agri_quote']));
    }

    $messages = array(array(
        'role' => 'user',
        'content' => 'Give one short quote about agriculture or farming, followed by " - " and the author.',
    ));
} elseif ($input['mode'] === 'chat') {
    if (empty($_SESSION['farmer_login_user']) || !is_string($_SESSION['farmer_login_user'])) {
        respond(401, array('error' => array('message' => 'Sign in as a farmer to use chat.')));
    }
    if (!isset($input['messages']) || !is_array($input['messages']) ||
        count($input['messages']) < 1 || count($input['messages']) > 40) {
        respond(400, array('error' => array('message' => 'Invalid chat messages.')));
    }

    $messages = array();
    $totalLength = 0;
    foreach ($input['messages'] as $message) {
        if (!is_array($message) ||
            !isset($message['role'], $message['content']) ||
            !in_array($message['role'], array('user', 'assistant'), true) ||
            !is_string($message['content']) ||
            trim($message['content']) === '' ||
            strlen($message['content']) > 12000) {
            respond(400, array('error' => array('message' => 'Invalid chat message.')));
        }
        $totalLength += strlen($message['content']);
        if ($totalLength > 24000) {
            respond(400, array('error' => array('message' => 'Chat messages are too long.')));
        }
        $messages[] = array(
            'role' => $message['role'],
            'content' => $message['content'],
        );
    }
} else {
    respond(400, array('error' => array('message' => 'Unsupported request mode.')));
}

loadProjectEnv(__DIR__ . DIRECTORY_SEPARATOR . '.env');

$apiKey = getenv('OPENAI_API_KEY');
if ($apiKey === false || trim($apiKey) === '') {
    respond(503, array('error' => array('message' => 'OpenAI is not configured. Add OPENAI_API_KEY to the project root .env file.')));
}

$model = getenv('OPENAI_MODEL');
if ($model === false || trim($model) === '') {
    $model\ = 'gpt-4o-mini';
}
$model = trim($model);
if (!preg_match('/^[A-Za-z0-9._:-]{1,100}$/', $model)) {
    respond(500, array('error' => array('message' => 'OPENAI_MODEL in .env has an invalid value.')));
}

$requestBody = json_encode(array(
    'model' => $model,
    'messages' => $messages,
));
if ($requestBody === false) {
    respond(500, array('error' => array('message' => 'Could not encode the OpenAI request.')));
}

if (!function_exists('curl_init')) {
    respond(500, array('error' => array('message' => 'PHP cURL is not enabled. Enable the cURL extension in XAMPP PHP.')));
}

$curl = curl_init('https://api.openai.com/v1/chat/completions');
if ($curl === false) {
    respond(500, array('error' => array('message' => 'Could not initialize the OpenAI request.')));
}

if (!curl_setopt_array($curl, array(
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $requestBody,
    CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer ' . trim($apiKey),
        'Content-Type: application/json',
    ),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
))) {
    $curlError = curl_error($curl);
    curl_close($curl);
    error_log('OpenAI cURL setup failed: ' . $curlError);
    respond(500, array('error' => array('message' => 'Could not configure the OpenAI request.')));
}

$response = curl_exec($curl);
$curlError = curl_error($curl);
$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($response === false) {
    error_log('OpenAI cURL request failed: ' . $curlError);
    respond(502, array('error' => array('message' => 'A network error prevented the request from reaching OpenAI. Check your connection and try again.')));
}

$responseBody = json_decode($response, true);
if (!is_array($responseBody)) {
    respond(502, array('error' => array('message' => 'OpenAI returned an invalid response. Please try again.')));
}

if ($status < 200 || $status >= 300) {
    $errorCode = isset($responseBody['error']['code']) && is_string($responseBody['error']['code'])
        ? $responseBody['error']['code']
        : '';
    $errorType = isset($responseBody['error']['type']) && is_string($responseBody['error']['type'])
        ? $responseBody['error']['type']
        : '';

    if ($status === 401) {
        respond(503, array('error' => array('message' => 'OpenAI rejected the API key. Check OPENAI_API_KEY in the project root .env file.')));
    }
    if ($status === 429 && ($errorCode === 'insufficient_quota' || $errorType === 'insufficient_quota')) {
        respond(503, array('error' => array('message' => 'The OpenAI account has insufficient quota. Check its billing and usage limits.')));
    }
    if ($status === 429) {
        respond(429, array('error' => array('message' => 'OpenAI rate limit reached. Wait a moment and try again.')));
    }
    if ($status >= 500) {
        respond(503, array('error' => array('message' => 'OpenAI is temporarily unavailable. Please try again shortly.')));
    }

    respond(502, array('error' => array('message' => 'OpenAI rejected the request. Check the configured model and try again.')));
}

if (!isset($responseBody['choices'][0]['message']['content']) ||
    !is_string($responseBody['choices'][0]['message']['content']) ||
    trim($responseBody['choices'][0]['message']['content']) === '') {
    respond(502, array('error' => array('message' => 'OpenAI returned no message. Please try again.')));
}

$content = $responseBody['choices'][0]['message']['content'];
if ($input['mode'] === 'quote') {
    $_SESSION['agri_quote'] = $content;
    respond(200, array('quote' => $content));
}

respond(200, array(
    'choices' => array(
        array(
            'message' => array(
                'role' => 'assistant',
                'content' => $content,
            ),
        ),
    ),
));
