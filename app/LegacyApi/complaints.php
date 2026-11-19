<?php
/**
 * Backend Endpoint: api/complaints.php
 * Terhubung langsung ke Database MySQL via ComplaintController & Eloquent ORM.
 */

require_once __DIR__ . '/_bootstrap.php';

use App\Http\Controllers\ComplaintController;
use Illuminate\Http\Request;

if (!headers_sent()) {
    @header('Content-Type: application/json; charset=utf-8');
}

$setHttpCode = static function (int $code) {
    if (!headers_sent()) {
        @http_response_code($code);
    }
};

$request = request();
$rawInput = file_get_contents('php://input');
if ((!$rawInput || $rawInput === '') && function_exists('request')) {
    $rawInput = $request->getContent();
}
$data = json_decode($rawInput ?: '{}', true);
if (!is_array($data)) {
    $data = $_POST;
}

$action = (string)($data['action'] ?? $request->input('action', ''));
$controller = app(ComplaintController::class);

if ($action === 'create') {
    $response = $controller->store($request);
    $setHttpCode($response->getStatusCode());
    echo $response->getContent();
    return;
}

if ($action === 'simulate_progress') {
    $response = $controller->simulateProgress($request);
    $setHttpCode($response->getStatusCode());
    echo $response->getContent();
    return;
}

if ($action === 'assign') {
    $id = (string)($data['id'] ?? $request->input('id', ''));
    $response = $controller->assign($request, $id);
    $setHttpCode($response->getStatusCode());
    echo $response->getContent();
    return;
}

if ($action === 'response') {
    $id = (string)($data['id'] ?? $request->input('id', ''));
    $response = $controller->respond($request, $id);
    $setHttpCode($response->getStatusCode());
    echo $response->getContent();
    return;
}

if ($action === 'resolve') {
    $id = (string)($data['id'] ?? $request->input('id', ''));
    $response = $controller->resolve($request, $id);
    $setHttpCode($response->getStatusCode());
    echo $response->getContent();
    return;
}

if ($action === 'update_status') {
    $id = (string)($data['id'] ?? $request->input('id', ''));
    $response = $controller->updateStatus($request, $id);
    $setHttpCode($response->getStatusCode());
    echo $response->getContent();
    return;
}

// Default fallback: list complaints
$response = $controller->index($request);
$setHttpCode($response->getStatusCode());
echo $response->getContent();
return;
