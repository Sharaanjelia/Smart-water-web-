<?php
/**
 * Backend Endpoint: api/academy.php
 * Terhubung ke Database MySQL via AcademyController & Eloquent ORM.
 */

require_once __DIR__ . '/_bootstrap.php';

use App\Http\Controllers\AcademyController;

if (!headers_sent()) {
    @header('Content-Type: application/json; charset=utf-8');
}

$setHttpCode = static function (int $code) {
    if (!headers_sent()) {
        @http_response_code($code);
    }
};

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
$controller = app(AcademyController::class);

if ($action === 'complete_lesson') {
    $response = $controller->completeLesson($request);
    $setHttpCode($response->getStatusCode());
    echo $response->getContent();
    return;
}

if ($action === 'complete_quiz') {
    $response = $controller->completeQuiz($request);
    $setHttpCode($response->getStatusCode());
    echo $response->getContent();
    return;
}

// Default fallback
$response = $controller->index();
$setHttpCode($response->getStatusCode());
echo $response->getContent();
return;
