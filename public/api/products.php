<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../src/config/db.php';
require_once __DIR__ . '/../../src/lib/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
	$res = $mysqli->query('SELECT p.id, p.name, p.price, p.image_url, p.added_by, p.category, p.date_added, u.username as added_by_username FROM products p LEFT JOIN users u ON p.added_by = u.id ORDER BY p.date_added DESC');
	$rows = [];
	while ($row = $res->fetch_assoc()) { 
		// Replace added_by ID with username, or keep ID if username is null
		$row['added_by'] = $row['added_by_username'] ? $row['added_by_username'] : ($row['added_by'] ? 'User #' . $row['added_by'] : '');
		unset($row['added_by_username']); // Remove the temporary field
		$rows[] = $row; 
	}
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

if ($method === 'PATCH' || $method === 'PUT') {
	require_role('admin');
	parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
	$productId = (int)($qs['id'] ?? 0);
	$input = json_decode(file_get_contents('php://input'), true);
	$name = trim($input['name'] ?? '');
	$price = (float)($input['price'] ?? 0);
	$image = trim($input['image_url'] ?? '');
	$category = trim($input['category'] ?? 'Uncategorized');
	
	if ($productId <= 0) { http_response_code(400); echo json_encode(['error' => 'Invalid product ID']); exit; }
	if ($name === '' || $price <= 0) { http_response_code(400); echo json_encode(['error' => 'Invalid name or price']); exit; }
	
	$stmt = $mysqli->prepare('UPDATE products SET name = ?, price = ?, image_url = ?, category = ? WHERE id = ?');
	$stmt->bind_param('sdssi', $name, $price, $image, $category, $productId);
	$stmt->execute();
	
	if ($mysqli->affected_rows === 0) {
		http_response_code(404);
		echo json_encode(['error' => 'Product not found']);
		exit;
	}
	
	echo json_encode(['ok' => true, 'id' => $productId]);
	exit;
}

if ($method === 'DELETE') {
	require_role('admin');
	parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
	$productId = (int)($qs['id'] ?? 0);
	
	if ($productId <= 0) { http_response_code(400); echo json_encode(['error' => 'Invalid product ID']); exit; }
	
	$stmt = $mysqli->prepare('DELETE FROM products WHERE id = ?');
	$stmt->bind_param('i', $productId);
	$stmt->execute();
	
	if ($mysqli->affected_rows === 0) {
		http_response_code(404);
		echo json_encode(['error' => 'Product not found']);
		exit;
	}
	
	echo json_encode(['ok' => true]);
	exit;
}

http_response_code(405); echo json_encode(['error' => 'Method not allowed']);
