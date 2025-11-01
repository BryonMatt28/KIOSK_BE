<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../src/config/db.php';
require_once __DIR__ . '/../../src/lib/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
	$res = $mysqli->query('SELECT id, name, price, image_url, added_by, category, date_added FROM products ORDER BY date_added DESC');
	$rows = [];
	while ($row = $res->fetch_assoc()) { $rows[] = $row; }
	echo json_encode($rows);
	exit;
}

if ($method === 'POST') {
	require_role('admin');
	$input = json_decode(file_get_contents('php://input'), true);
	$name = trim($input['name'] ?? '');
	$price = (float)($input['price'] ?? 0);
	$image = trim($input['image_url'] ?? '');
	$category = trim($input['category'] ?? 'Uncategorized');
	if ($name === '' || $price <= 0) { http_response_code(400); echo json_encode(['error' => 'Invalid name or price']); exit; }
	$uid = $_SESSION['user']['id'];
	$stmt = $mysqli->prepare('INSERT INTO products(name, price, image_url, added_by, category) VALUES (?, ?, ?, ?, ?)');
	$stmt->bind_param('sdsis', $name, $price, $image, $uid, $category);
	$stmt->execute();
	echo json_encode(['ok' => true, 'id' => $stmt->insert_id]);
	exit;
}

http_response_code(405); echo json_encode(['error' => 'Method not allowed']);
