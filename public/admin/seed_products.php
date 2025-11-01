<?php
// One-time product seeder: visit http://localhost/KIOSK/public/admin/seed_products.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/../../src/config/db.php';

// ensure category column exists
$check = $mysqli->prepare("SELECT COUNT(*) AS n FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'category'");
$check->execute(); $exists = (int)$check->get_result()->fetch_assoc()['n'] > 0;
if (!$exists) {
	$mysqli->query("ALTER TABLE products ADD COLUMN category VARCHAR(100) NOT NULL DEFAULT 'Uncategorized' AFTER image_url");
}

// if we already have products, skip
$res = $mysqli->query("SELECT COUNT(*) AS n FROM products");
if ((int)$res->fetch_assoc()['n'] > 0) { echo "Products already exist. Nothing to seed.\n"; exit; }

$items = [
	['Classic Smash Burger Meal', 5.50, 'https://images.unsplash.com/photo-1550547660-d9450f859349?w=800', 'Budget Meal'],
	['Chicken Nuggets Meal Classic', 5.20, 'https://images.unsplash.com/photo-1561758033-d89a9ad46330?w=800', 'Budget Meal'],
	['Family Feast Combo Standard', 24.99, 'https://images.unsplash.com/photo-1514516870926-2059896d54a8?w=800', 'Family Meal'],
	['Coca-Cola Classic', 1.50, 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=800', 'Drinks'],
	['Iced Tea Lemon', 1.80, 'https://images.unsplash.com/photo-1542990253-0d0f5be5f0ed?w=800', 'Drinks'],
	['Apple Pie Classic', 1.00, 'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=800', 'Desserts'],
	['Classic Smash Burger', 3.80, 'https://images.unsplash.com/photo-1550547660-7a4b1d1bb78e?w=800', 'Fries and Burgers'],
];

$uid = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
$stmt = $mysqli->prepare('INSERT INTO products(name, price, image_url, added_by, category) VALUES (?, ?, ?, ?, ?)');
foreach ($items as $it) {
	list($name,$price,$img,$cat) = [$it[0],$it[1],$it[2],$it[3]];
	$stmt->bind_param('sdsis', $name, $price, $img, $uid, $cat);
	$stmt->execute();
}

echo "Seeded sample products. Open the kiosk to see them.\n";
