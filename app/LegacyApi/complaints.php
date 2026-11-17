<?php
/**
 * Backend Endpoint: api/complaints.php
 * Terhubung langsung ke Database MySQL via ComplaintController & Eloquent ORM.
 */

use App\Http\Controllers\ComplaintController;
use Illuminate\Http\Request;

if (!headers_sent()) {
    @header('Content-Type: application/json; charset=utf-8');
}

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
    http_response_code($response->getStatusCode());
    echo $response->getContent();
    return;
}

if ($action === 'simulate_progress') {
    $response = $controller->simulateProgress($request);
    http_response_code($response->getStatusCode());
    echo $response->getContent();
    return;
}

if ($action === 'assign') {
    $id = (string)($data['id'] ?? $request->input('id', ''));
    $response = $controller->assign($request, $id);
    http_response_code($response->getStatusCode());
    echo $response->getContent();
    return;
}

if ($action === 'response') {
    $id = (string)($data['id'] ?? $request->input('id', ''));
    $response = $controller->respond($request, $id);
    http_response_code($response->getStatusCode());
    echo $response->getContent();
    return;
}

if ($action === 'resolve') {
    $id = (string)($data['id'] ?? $request->input('id', ''));
    $response = $controller->resolve($request, $id);
    http_response_code($response->getStatusCode());
    echo $response->getContent();
    return;
}

if ($action === 'update_status') {
    $id = (string)($data['id'] ?? $request->input('id', ''));
    $response = $controller->updateStatus($request, $id);
    http_response_code($response->getStatusCode());
    echo $response->getContent();
    return;
}

// Default fallback: list complaints
$response = $controller->index($request);
http_response_code($response->getStatusCode());
echo $response->getContent();
return;
