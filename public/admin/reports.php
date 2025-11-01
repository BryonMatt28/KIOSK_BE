<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$user = $_SESSION['user'] ?? null;
if (!$user) { header('Location: /KIOSK/public/admin/login.php'); exit; }
require_once __DIR__ . '/../../src/config/db.php';

$start = $_GET['date_start'] ?? '';
$end = $_GET['date_end'] ?? '';
$params = [];
$sql = "SELECT o.id, o.total_amount, o.payment_method, o.date_added FROM orders o WHERE 1=1";
if ($start !== '') { $sql .= " AND DATE(o.date_added) >= ?"; $params[] = $start; }
if ($end !== '') { $sql .= " AND DATE(o.date_added) <= ?"; $params[] = $end; }
$sql .= " ORDER BY o.date_added DESC";

$stmt = null; $rows = [];
if (count($params) > 0) {
	$types = str_repeat('s', count($params));
	$stmt = $mysqli->prepare($sql);
	$stmt->bind_param($types, ...$params);
	$stmt->execute(); $res = $stmt->get_result();
} else {
	$res = $mysqli->query($sql);
}
$sum = 0; while ($row = $res->fetch_assoc()) { $rows[] = $row; $sum += (float)$row['total_amount']; }
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Reports</title>
	<link rel="stylesheet" href="/KIOSK/public/assets/style.css">
	<script src="https://cdn.tailwindcss.com"></script>
	<style>@media print { .no-print { display:none } }</style>
</head>
<body class="bg-gradient-to-b from-red-50 to-white">
	<!-- Navbar (persistent pattern) -->
	<div class="bg-white shadow no-print">
		<div class="max-w-6xl mx-auto px-6 py-3 flex justify-between items-center">
			<div class="font-bold">Admin</div>
			<div class="flex gap-2">
				<a href="/KIOSK/public/index.php" class="px-3 py-1 bg-red-600 text-white rounded text-sm">Home</a>
				<a href="/KIOSK/public/admin/login.php" onclick="logout(); return false;" class="px-3 py-1 border rounded text-sm">Logout</a>
			</div>
		</div>
	</div>

	<div class="max-w-5xl mx-auto mt-10 bg-white rounded-xl shadow p-6">
		<div class="flex justify-between items-center mb-4 no-print">
			<h1 class="text-xl font-bold">Order Reports</h1>
			<div class="text-sm text-gray-600">Logged in as: <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</div>
		</div>
		<form class="grid md:grid-cols-4 gap-3 mb-4 no-print" method="get">
			<input type="date" name="date_start" value="<?php echo htmlspecialchars($start); ?>" class="border rounded px-3 py-2" />
			<input type="date" name="date_end" value="<?php echo htmlspecialchars($end); ?>" class="border rounded px-3 py-2" />
			<button class="bg-red-600 text-white rounded px-4 py-2">Filter</button>
			<button type="button" onclick="window.print()" class="border rounded px-4 py-2">Print PDF</button>
		</form>
		<table class="w-full text-left">
			<thead><tr><th class="py-2">Order #</th><th>Date</th><th>Payment</th><th class="text-right">Total (PHP)</th></tr></thead>
			<tbody>
				<?php foreach($rows as $r): ?>
				<tr class="border-t">
					<td class="py-2">#<?php echo (int)$r['id']; ?></td>
					<td><?php echo htmlspecialchars($r['date_added']); ?></td>
					<td><?php echo htmlspecialchars($r['payment_method'] ?? ''); ?></td>
					<td class="text-right">₱<?php echo number_format((float)$r['total_amount'], 2); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr class="border-t font-bold"><td colspan="3" class="py-2">Total</td><td class="text-right">₱<?php echo number_format($sum, 2); ?></td></tr>
			</tfoot>
		</table>
	</div>
	<script>async function logout(){ await fetch('/KIOSK/public/api/logout.php'); window.location.href='/KIOSK/public/admin/login.php'; }</script>
</body>
</html>
