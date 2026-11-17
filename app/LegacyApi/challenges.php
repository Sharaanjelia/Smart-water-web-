<?php
/**
 * Backend Endpoint: api/challenges.php
 * Terhubung ke Database MySQL via ChallengeController & Eloquent ORM.
 */

use App\Http\Controllers\ChallengeController;

if (!headers_sent()) {
    @header('Content-Type: application/json; charset=utf-8');
}

$request = request();
$raw = file_get_contents('php://input');
if ((!$raw || $raw === '') && function_exists('request')) {
    $raw = $request->getContent();
}
$data = json_decode($raw ?: '{}', true);
if (!is_array($data)) {
    $data = $_POST;
}

$action = (string)($data['action'] ?? $request->input('action', ''));
$controller = app(ChallengeController::class);

if ($action === 'enroll') {
    $response = $controller->enroll($request);
    http_response_code($response->getStatusCode());
    echo $response->getContent();
    return;
}

if ($action === 'complete_mission') {
    $response = $controller->completeMission($request);
    http_response_code($response->getStatusCode());
    echo $response->getContent();
    return;
}

// Default fallback
$response = $controller->index();
http_response_code($response->getStatusCode());
echo $response->getContent();
return;
