<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$user = $_SESSION['user'] ?? null;
if (!$user) { header('Location: /KIOSK/public/admin/login.php'); exit; }
require_once __DIR__ . '/../../src/config/db.php';

// KPIs
$today = (new DateTime('today'))->format('Y-m-d');
$kpi = [ 'today_sales' => 0, 'today_orders' => 0, 'total_products' => 0, 'total_admins' => 0 ];

$stmt = $mysqli->prepare("SELECT COALESCE(SUM(total_amount),0) AS s, COUNT(*) AS c FROM orders WHERE DATE(date_added) = ?");
$stmt->bind_param('s', $today);
$stmt->execute(); $res = $stmt->get_result()->fetch_assoc();
$kpi['today_sales'] = (float)$res['s'];
$kpi['today_orders'] = (int)$res['c'];

$res = $mysqli->query("SELECT COUNT(*) AS n FROM products");
$kpi['total_products'] = (int)$res->fetch_assoc()['n'];

$res = $mysqli->query("SELECT COUNT(*) AS n FROM users WHERE role_id IN (1,2)");
$kpi['total_admins'] = (int)$res->fetch_assoc()['n'];
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Dashboard</title>
	<link rel="stylesheet" href="/KIOSK/public/assets/style.css">
	<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-red-50 to-white">
	<!-- Navbar (persistent pattern) -->
	<div class="bg-white shadow">
		<div class="max-w-6xl mx-auto px-6 py-3 flex justify-between items-center">
			<div class="font-bold">Admin</div>
			<div class="flex gap-2">
				<a href="/KIOSK/public/index.php" class="px-3 py-1 bg-red-600 text-white rounded text-sm">Home</a>
				<a href="/KIOSK/public/admin/login.php" onclick="logout(); return false;" class="px-3 py-1 border rounded text-sm">Logout</a>
			</div>
		</div>
	</div>

	<div class="max-w-6xl mx-auto mt-10">
		<div class="flex justify-between items-center mb-6">
			<h1 class="text-2xl font-bold">Dashboard</h1>
			<div class="text-sm text-gray-600">Logged in as: <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</div>
		</div>
		<div class="grid md:grid-cols-4 gap-4">
			<div class="bg-white rounded-xl shadow p-5">
				<div class="text-gray-500 text-sm">Today Sales</div>
				<div class="text-2xl font-bold">₱<?php echo number_format($kpi['today_sales'], 2); ?></div>
			</div>
			<div class="bg-white rounded-xl shadow p-5">
				<div class="text-gray-500 text-sm">Today Orders</div>
				<div class="text-2xl font-bold"><?php echo (int)$kpi['today_orders']; ?></div>
			</div>
			<div class="bg-white rounded-xl shadow p-5">
				<div class="text-gray-500 text-sm">Total Products</div>
				<div class="text-2xl font-bold"><?php echo (int)$kpi['total_products']; ?></div>
			</div>
			<div class="bg-white rounded-xl shadow p-5">
				<div class="text-gray-500 text-sm">Total Admins</div>
				<div class="text-2xl font-bold"><?php echo (int)$kpi['total_admins']; ?></div>
			</div>
		</div>

		<div class="mt-8">
			<h2 class="text-lg font-semibold mb-4">Modules</h2>
			<div class="grid md:grid-cols-3 gap-4">
				<a href="/KIOSK/public/admin/products.php" class="block bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
					<div class="text-3xl mb-2">📦</div>
					<div class="text-xl font-bold mb-1">Products</div>
					<div class="text-gray-600 text-sm">Add and manage menu items</div>
				</a>
				<a href="/KIOSK/public/admin/reports.php" class="block bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
					<div class="text-3xl mb-2">📈</div>
					<div class="text-xl font-bold mb-1">Reports</div>
					<div class="text-gray-600 text-sm">Order history and totals</div>
				</a>
				<?php if ($user['role']==='superadmin'): ?>
				<a href="/KIOSK/public/admin/users.php" class="block bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
					<div class="text-3xl mb-2">👤</div>
					<div class="text-xl font-bold mb-1">User Management</div>
					<div class="text-gray-600 text-sm">Create/suspend admin accounts</div>
				</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<script>
		async function logout(){ await fetch('/KIOSK/public/api/logout.php'); window.location.href='/KIOSK/public/admin/login.php'; }
	</script>
</body>
</html>
