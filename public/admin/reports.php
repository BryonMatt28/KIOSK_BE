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
				<a href="/KIOSK/public/admin/dashboard.php" class="px-3 py-1 border rounded text-sm">Dashboard</a>
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
			<a href="/KIOSK/public/admin/reports_pdf.php?<?php echo http_build_query(['date_start' => $start, 'date_end' => $end]); ?>" class="bg-red-600 text-white rounded px-4 py-2 text-center no-underline hover:bg-red-700 transition">Print PDF</a>
		</form>
		<table class="w-full text-left">
			<thead><tr><th class="py-2">Order #</th><th>Date</th><th>Payment</th><th class="text-right">Total (PHP)</th><th class="no-print">Action</th></tr></thead>
			<tbody>
				<?php foreach($rows as $r): ?>
				<tr class="border-t hover:bg-gray-50 cursor-pointer" onclick="viewOrderDetails(<?php echo (int)$r['id']; ?>)">
					<td class="py-2">#<?php echo (int)$r['id']; ?></td>
					<td><?php echo htmlspecialchars($r['date_added']); ?></td>
					<td><?php echo htmlspecialchars($r['payment_method'] ?? ''); ?></td>
					<td class="text-right">₱<?php echo number_format((float)$r['total_amount'], 2); ?></td>
					<td class="no-print">
						<button type="button" onclick="event.stopPropagation(); viewOrderDetails(<?php echo (int)$r['id']; ?>)" class="text-blue-600 hover:text-blue-800 text-sm underline mr-2">View</button>
						<button type="button" onclick="event.stopPropagation(); printOrderPDF(<?php echo (int)$r['id']; ?>); return false;" class="text-red-600 hover:text-red-800 text-sm underline">Print PDF</button>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr class="border-t font-bold"><td colspan="3" class="py-2">Total</td><td class="text-right">₱<?php echo number_format($sum, 2); ?></td><td class="no-print"></td></tr>
			</tfoot>
		</table>
	</div>

	<!-- Order Details Modal -->
	<div id="orderModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center no-print">
		<div class="bg-white rounded-xl shadow-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
			<div class="flex justify-between items-center mb-4">
				<h2 id="modalOrderNumber" class="text-xl font-bold">Order Details</h2>
				<button onclick="closeOrderModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
			</div>
			<div id="modalOrderContent"></div>
			<div id="modalOrderActions" class="mt-4 flex gap-2">
				<button onclick="printCurrentOrderPDF()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Print PDF</button>
				<button onclick="closeOrderModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Close</button>
			</div>
		</div>
	</div>


	<script>
		let currentOrderId = null;

		async function viewOrderDetails(orderId) {
			try {
				const res = await fetch(`/KIOSK/public/api/orders.php?id=${orderId}`);
				if (!res.ok) {
					const data = await res.json();
					throw new Error(data.error || 'Failed to fetch order details');
				}
				const order = await res.json();
				currentOrderId = orderId;
				
				const modal = document.getElementById('orderModal');
				const modalNumber = document.getElementById('modalOrderNumber');
				const modalContent = document.getElementById('modalOrderContent');
				
				modalNumber.textContent = `Order #${order.id}`;
				
				const date = new Date(order.date_added);
				const dateStr = date.toLocaleString('en-US');
				
				modalContent.innerHTML = `
					<div class="space-y-4">
						<div class="grid grid-cols-2 gap-4">
							<div>
								<span class="text-gray-600">Order #:</span>
								<span class="font-semibold ml-2">#${order.id}</span>
							</div>
							<div>
								<span class="text-gray-600">Date:</span>
								<span class="font-semibold ml-2">${dateStr}</span>
							</div>
							<div>
								<span class="text-gray-600">Payment Method:</span>
								<span class="font-semibold ml-2">${order.payment_method || 'Not specified'}</span>
							</div>
							<div>
								<span class="text-gray-600">Status:</span>
								<span class="font-semibold ml-2 px-2 py-1 rounded text-sm ${order.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : order.status === 'received' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'}">${(order.status || 'pending').charAt(0).toUpperCase() + (order.status || 'pending').slice(1)}</span>
							</div>
						</div>
						<hr>
						<div class="space-y-2">
							<h3 class="font-bold text-lg">Items:</h3>
							<table class="w-full">
								<thead>
									<tr class="border-b">
										<th class="text-left py-2">Item</th>
										<th class="text-right py-2">Quantity</th>
										<th class="text-right py-2">Unit Price</th>
										<th class="text-right py-2">Total</th>
									</tr>
								</thead>
								<tbody>
									${order.items && order.items.length > 0 ? order.items.map(item => `
										<tr class="border-b">
											<td class="py-2">${escapeHtml(item.product_name)}</td>
											<td class="text-right py-2">${item.quantity}</td>
											<td class="text-right py-2">₱${parseFloat(item.unit_price).toFixed(2)}</td>
											<td class="text-right py-2 font-semibold">₱${parseFloat(item.line_total).toFixed(2)}</td>
										</tr>
									`).join('') : '<tr><td colspan="4" class="text-center py-4 text-gray-600">No items found</td></tr>'}
								</tbody>
								<tfoot>
									<tr class="border-t font-bold">
										<td colspan="3" class="py-2 text-right">Total:</td>
										<td class="text-right py-2 text-red-600">₱${parseFloat(order.total_amount).toFixed(2)}</td>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
				`;
				
				modal.classList.remove('hidden');
			} catch (e) {
				console.error('Error fetching order details:', e);
				alert('Failed to load order details: ' + e.message);
			}
		}

		function closeOrderModal() {
			document.getElementById('orderModal').classList.add('hidden');
			currentOrderId = null;
		}

		function escapeHtml(text) {
			if (!text) return '';
			const div = document.createElement('div');
			div.textContent = text;
			return div.innerHTML;
		}

		async function printOrderPDF(orderId) {
			currentOrderId = orderId;
			await printCurrentOrderPDF();
			return false; // Prevent any default behavior
		}

		async function printCurrentOrderPDF() {
			const orderId = currentOrderId || arguments[0];
			if (!orderId) {
				alert('No order selected');
				return;
			}
			
			try {
				const res = await fetch(`/KIOSK/public/api/orders.php?id=${orderId}`);
				if (!res.ok) {
					const data = await res.json();
					throw new Error(data.error || 'Failed to fetch order details');
				}
				const order = await res.json();
				
				if (!order || !order.items || order.items.length === 0) {
					alert('Order has no items to print');
					return;
				}
				
				const date = new Date(order.date_added);
				const dateStr = date.toLocaleString('en-US');
				
				// Create a new window with printable content
				const printWindow = window.open('', '_blank');
				if (!printWindow) {
					alert('Please allow pop-ups to print the receipt');
					return;
				}
				
				const htmlContent = `
					<!DOCTYPE html>
					<html>
					<head>
						<meta charset="UTF-8">
						<title>Order #${order.id} - Receipt</title>
						<style>
							body {
								font-family: Arial, sans-serif;
								margin: 0;
								padding: 20px;
								max-width: 800px;
								margin: 0 auto;
							}
							.header {
								text-align: center;
								margin-bottom: 20px;
								border-bottom: 2px solid #333;
								padding-bottom: 15px;
							}
							.header h1 {
								margin: 0;
								color: #DA291C;
								font-size: 24px;
							}
							.header p {
								margin: 5px 0;
								color: #666;
							}
							.order-info {
								margin-bottom: 20px;
							}
							.order-info p {
								margin: 5px 0;
							}
							table {
								width: 100%;
								border-collapse: collapse;
								margin-bottom: 20px;
							}
							th, td {
								border: 1px solid #ddd;
								padding: 10px;
								text-align: left;
							}
							th {
								background-color: #f5f5f5;
								font-weight: bold;
							}
							.text-right {
								text-align: right;
							}
							.total-row {
								font-weight: bold;
								background-color: #f9f9f9;
							}
							.footer {
								text-align: center;
								margin-top: 30px;
								padding-top: 15px;
								border-top: 2px solid #333;
								color: #666;
								font-size: 12px;
							}
							@media print {
								body { margin: 0; padding: 10px; }
							}
						</style>
					</head>
					<body>
						<div class="header">
							<h1>SavorBite Grill</h1>
							<p>Order Receipt</p>
						</div>
						<div class="order-info">
							<p><strong>Order #:</strong> #${order.id}</p>
							<p><strong>Date:</strong> ${dateStr}</p>
							<p><strong>Payment Method:</strong> ${order.payment_method || 'Not specified'}</p>
							<p><strong>Status:</strong> ${(order.status || 'pending').charAt(0).toUpperCase() + (order.status || 'pending').slice(1)}</p>
						</div>
						<table>
							<thead>
								<tr>
									<th>Item</th>
									<th class="text-right">Quantity</th>
									<th class="text-right">Unit Price</th>
									<th class="text-right">Total</th>
								</tr>
							</thead>
							<tbody>
								${order.items.map(item => `
									<tr>
										<td>${escapeHtml(item.product_name)}</td>
										<td class="text-right">${item.quantity}</td>
										<td class="text-right">₱${parseFloat(item.unit_price).toFixed(2)}</td>
										<td class="text-right">₱${parseFloat(item.line_total).toFixed(2)}</td>
									</tr>
								`).join('')}
							</tbody>
							<tfoot>
								<tr class="total-row">
									<td colspan="3" class="text-right"><strong>Total:</strong></td>
									<td class="text-right"><strong>₱${parseFloat(order.total_amount).toFixed(2)}</strong></td>
								</tr>
							</tfoot>
						</table>
						<div class="footer">
							<p>Thank you for your order!</p>
							<p>SavorBite Grill</p>
						</div>
					</body>
					</html>
				`;
				
				printWindow.document.write(htmlContent);
				printWindow.document.close();
				
				// Wait for content to load, then print
				printWindow.onload = function() {
					setTimeout(() => {
						printWindow.print();
					}, 250);
				};
				
				// Fallback in case onload doesn't fire
				setTimeout(() => {
					if (!printWindow.closed) {
						printWindow.print();
					}
				}, 500);
			} catch (e) {
				console.error('Error printing order:', e);
				alert('Failed to print order: ' + e.message);
			}
		}

		async function logout(){ 
			await fetch('/KIOSK/public/api/logout.php'); 
			window.location.href='/KIOSK/public/admin/login.php'; 
		}
	</script>
</body>
</html>
