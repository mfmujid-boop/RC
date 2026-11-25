<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if (isset($_GET['info'])) {
    $host = (string)($_SERVER['SERVER_ADDR'] ?? '');
    if ($host === '' || $host === '127.0.0.1' || $host === '::1') { $host = gethostbyname(gethostname()); }
    echo json_encode(['host' => $host]); exit;
}
$path = __DIR__ . DIRECTORY_SEPARATOR . 'race-state.json';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode((string)file_get_contents('php://input'), true);
    if (!is_array($data)) { http_response_code(400); echo json_encode(['error' => 'Expected JSON']); exit; }
    $state = ['steer' => max(-1.0, min(1.0, (float)($data['steer'] ?? 0))), 'throttle' => !empty($data['throttle']), 'brake' => !empty($data['brake']), 'boost' => !empty($data['boost']), 'at' => microtime(true)];
    file_put_contents($path, json_encode($state), LOCK_EX);
    echo json_encode(['ok' => true]); exit;
}
$state = is_file($path) ? json_decode((string)file_get_contents($path), true) : null;
if (!is_array($state) || microtime(true) - (float)($state['at'] ?? 0) > 2) $state = ['steer' => 0, 'throttle' => false, 'brake' => false, 'boost' => false];
echo json_encode($state);

