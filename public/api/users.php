<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../src/config/db.php';
require_once __DIR__ . '/../../src/lib/auth.php';

$method = $_SERVER['REQUEST_METHOD'];
require_role('superadmin');

if ($method === 'POST') {
	$input = json_decode(file_get_contents('php://input'), true);
	$username = trim($input['username'] ?? '');
	$password = (string)($input['password'] ?? '');
	$role = strtolower((string)($input['role'] ?? 'admin'));
	if ($role !== 'admin') { echo json_encode(['error' => 'Only admin accounts can be created']); http_response_code(400); exit; }
	if ($username === '' || $password === '' ) { http_response_code(400); echo json_encode(['error' => 'Missing username or password']); exit; }
	$hash = password_hash($password, PASSWORD_BCRYPT);
	$roleId = 2; // admin
	$stmt = $mysqli->prepare('INSERT INTO users(username, password_hash, role_id) VALUES (?, ?, ?)');
	$stmt->bind_param('ssi', $username, $hash, $roleId);
	$stmt->execute();
	echo json_encode(['ok' => true, 'id' => $stmt->insert_id]);
	exit;
}

if ($method === 'PATCH') {
	parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
	$uid = (int)($qs['id'] ?? 0);
	$input = json_decode(file_get_contents('php://input'), true);
	$suspended = isset($input['suspended']) ? (int)!!$input['suspended'] : null;
	if ($uid <= 0 || $suspended === null) { http_response_code(400); echo json_encode(['error' => 'Invalid request']); exit; }
	$stmt = $mysqli->prepare('UPDATE users SET suspended = ? WHERE id = ? AND role_id = 2');
	$stmt->bind_param('ii', $suspended, $uid);
	$stmt->execute();
	echo json_encode(['ok' => true]);
	exit;
}

http_response_code(405); echo json_encode(['error' => 'Method not allowed']);
