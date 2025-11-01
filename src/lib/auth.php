<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function require_json() {
	header('Content-Type: application/json');
}

function respond($data, $code = 200) {
	require_json(); http_response_code($code); echo json_encode($data); exit;
}

function current_user() {
	return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function require_login() {
	if (!current_user()) { respond(['error' => 'Unauthorized'], 401); }
}

function require_role($roleName) {
	$user = current_user();
	if (!$user) { respond(['error' => 'Unauthorized'], 401); }
	if ($roleName === 'superadmin' && $user['role'] !== 'superadmin') { respond(['error' => 'Forbidden'], 403); }
	if ($roleName === 'admin' && !in_array($user['role'], ['admin','superadmin'])) { respond(['error' => 'Forbidden'], 403); }
}
?>
