<?php
// One-time migration to add category to products
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/../../src/config/db.php';

$exists = false;
$check = $mysqli->prepare("SELECT COUNT(*) AS n FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'category'");
$check->execute(); $res = $check->get_result()->fetch_assoc();
$exists = ((int)$res['n'] > 0);

if ($exists) {
	echo "products.category already exists.\n";
	exit;
}

$sql = "ALTER TABLE products ADD COLUMN category VARCHAR(100) NOT NULL DEFAULT 'Uncategorized' AFTER image_url";
if ($mysqli->query($sql) === TRUE) {
	echo "Added products.category successfully.\n";
} else {
	http_response_code(500);
	echo "Failed to add category: ".$mysqli->error."\n";
}
