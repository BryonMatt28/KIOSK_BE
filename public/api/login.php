<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../src/config/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

if (!$username || !$password) { http_response_code(400); echo json_encode(['error' => 'Missing credentials']); exit; }

$stmt = $mysqli->prepare('SELECT u.id, u.username, u.password_hash, u.suspended, r.name role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.username = ? LIMIT 1');
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
if (!$user || !password_verify($password, $user['password_hash'])) { http_response_code(401); echo json_encode(['error' => 'Invalid credentials']); exit; }
if ((int)$user['suspended'] === 1) { http_response_code(403); echo json_encode(['error' => 'Account suspended']); exit; }

$_SESSION['user'] = ['id' => (int)$user['id'], 'username' => $user['username'], 'role' => $user['role']];
echo json_encode(['ok' => true, 'user' => $_SESSION['user']]);
