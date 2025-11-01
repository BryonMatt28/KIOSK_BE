<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../src/config/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$items = $input['items'] ?? [];
$payment = $input['payment_method'] ?? null;
if (!is_array($items) || count($items) === 0) { http_response_code(400); echo json_encode(['error' => 'No items']); exit; }

$total = 0.0;
foreach ($items as $it) {
	$qty = max(1, (int)($it['quantity'] ?? 1));
	$price = (float)($it['price'] ?? 0);
	$total += $qty * $price;
}

$mysqli->begin_transaction();
try {
	$stmt = $mysqli->prepare('INSERT INTO orders(total_amount, payment_method) VALUES (?, ?)');
	$stmt->bind_param('ds', $total, $payment);
	$stmt->execute();
	$orderId = $stmt->insert_id;

	$oi = $mysqli->prepare('INSERT INTO order_items(order_id, product_name, unit_price, quantity, line_total) VALUES (?, ?, ?, ?, ?)');
	foreach ($items as $it) {
		$name = substr((string)($it['name'] ?? 'Item'), 0, 255);
		$qty = max(1, (int)($it['quantity'] ?? 1));
		$price = (float)($it['price'] ?? 0);
		$line = $qty * $price;
		$oi->bind_param('isdid', $orderId, $name, $price, $qty, $line);
		$oi->execute();
	}
	$mysqli->commit();
	echo json_encode(['ok' => true, 'order_id' => $orderId, 'total' => $total]);
} catch (Throwable $e) {
	$mysqli->rollback();
	http_response_code(500);
	echo json_encode(['error' => 'Could not create order']);
}
