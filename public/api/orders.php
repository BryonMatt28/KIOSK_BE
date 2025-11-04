<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../src/config/db.php';
require_once __DIR__ . '/../../src/lib/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

// Require admin authentication
require_role('admin');

if ($method === 'GET') {
	$orderId = isset($_GET['id']) ? (int)$_GET['id'] : null;
	
	if ($orderId) {
		// Get single order with items
		$stmt = $mysqli->prepare('SELECT id, total_amount, payment_method, status, date_added FROM orders WHERE id = ?');
		$stmt->bind_param('i', $orderId);
		$stmt->execute();
		$order = $stmt->get_result()->fetch_assoc();
		
		if (!$order) {
			http_response_code(404);
			echo json_encode(['error' => 'Order not found']);
			exit;
		}
		
		// Get order items
		$stmt = $mysqli->prepare('SELECT id, product_name, unit_price, quantity, line_total FROM order_items WHERE order_id = ? ORDER BY id');
		$stmt->bind_param('i', $orderId);
		$stmt->execute();
		$items = [];
		$res = $stmt->get_result();
		while ($row = $res->fetch_assoc()) {
			$items[] = $row;
		}
		
		$order['items'] = $items;
		echo json_encode($order);
		exit;
	}
	
	// Get all orders with status filter
	$status = $_GET['status'] ?? null;
	
	// Check if status column exists, if not, return orders without status
	$check = $mysqli->prepare("SELECT COUNT(*) AS n FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'status'");
	$check->execute();
	$hasStatus = (int)$check->get_result()->fetch_assoc()['n'] > 0;
	
	if ($hasStatus) {
		$sql = 'SELECT id, total_amount, payment_method, status, date_added FROM orders WHERE 1=1';
		$params = [];
		$types = '';
		
		if ($status !== null && $status !== '') {
			$sql .= ' AND status = ?';
			$params[] = $status;
			$types .= 's';
		}
		
		$sql .= ' ORDER BY date_added DESC LIMIT 100';
		
		if (count($params) > 0) {
			$stmt = $mysqli->prepare($sql);
			$stmt->bind_param($types, ...$params);
			$stmt->execute();
			$res = $stmt->get_result();
		} else {
			$res = $mysqli->query($sql);
		}
	} else {
		// Fallback if status column doesn't exist yet
		$res = $mysqli->query('SELECT id, total_amount, payment_method, date_added FROM orders ORDER BY date_added DESC LIMIT 100');
	}
	
	$orders = [];
	while ($row = $res->fetch_assoc()) {
		if (!isset($row['status'])) {
			$row['status'] = 'pending';
		}
		$orders[] = $row;
	}
	
	echo json_encode($orders);
	exit;
}

if ($method === 'PATCH') {
	parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
	$orderId = (int)($qs['id'] ?? 0);
	$input = json_decode(file_get_contents('php://input'), true);
	$status = isset($input['status']) ? trim($input['status']) : null;
	
	if ($orderId <= 0 || $status === null) {
		http_response_code(400);
		echo json_encode(['error' => 'Invalid request']);
		exit;
	}
	
	// Validate status
	$allowedStatuses = ['pending', 'received', 'completed'];
	if (!in_array($status, $allowedStatuses)) {
		http_response_code(400);
		echo json_encode(['error' => 'Invalid status']);
		exit;
	}
	
	// Check if status column exists
	$check = $mysqli->prepare("SELECT COUNT(*) AS n FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'status'");
	$check->execute();
	$hasStatus = (int)$check->get_result()->fetch_assoc()['n'] > 0;
	
	if (!$hasStatus) {
		http_response_code(500);
		echo json_encode(['error' => 'Status column not found. Please run migration first.']);
		exit;
	}
	
	$stmt = $mysqli->prepare('UPDATE orders SET status = ? WHERE id = ?');
	$stmt->bind_param('si', $status, $orderId);
	$stmt->execute();
	
	if ($mysqli->affected_rows === 0) {
		http_response_code(404);
		echo json_encode(['error' => 'Order not found']);
		exit;
	}
	
	echo json_encode(['ok' => true, 'id' => $orderId, 'status' => $status]);
	exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
