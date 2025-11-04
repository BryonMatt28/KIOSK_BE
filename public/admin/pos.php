<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$user = $_SESSION['user'] ?? null;
if (!$user) { header('Location: /KIOSK/public/admin/login.php'); exit; }
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
	<title>POS - Point of Sale</title>
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
				<a href="/KIOSK/public/admin/dashboard.php" class="px-3 py-1 border rounded text-sm">Dashboard</a>
				<a href="/KIOSK/public/admin/login.php" onclick="logout(); return false;" class="px-3 py-1 border rounded text-sm">Logout</a>
			</div>
		</div>
	</div>

	<div class="max-w-6xl mx-auto mt-10">
		<div class="flex justify-between items-center mb-6">
			<h1 class="text-2xl font-bold">Point of Sale (POS)</h1>
			<div class="text-sm text-gray-600">Logged in as: <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</div>
		</div>

		<!-- Filter tabs -->
		<div class="bg-white rounded-xl shadow p-4 mb-6">
			<div class="flex gap-2">
				<button id="filterAll" onclick="filterOrders('all')" class="px-4 py-2 bg-red-600 text-white rounded text-sm font-semibold">All Orders</button>
				<button id="filterPending" onclick="filterOrders('pending')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300">Pending</button>
				<button id="filterReceived" onclick="filterOrders('received')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300">Received</button>
				<button id="filterCompleted" onclick="filterOrders('completed')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300">Completed</button>
				<button onclick="refreshOrders()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300">🔄 Refresh</button>
			</div>
		</div>

		<!-- Orders grid -->
		<div id="ordersContainer" class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
			<!-- Orders will be loaded here -->
		</div>

		<!-- Empty state -->
		<div id="emptyState" class="hidden bg-white rounded-xl shadow p-8 text-center">
			<div class="text-4xl mb-4">📦</div>
			<p class="text-gray-600">No orders found</p>
		</div>

		<!-- Order detail modal -->
		<div id="orderModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
			<div class="bg-white rounded-xl shadow-lg p-6 max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
				<div class="flex justify-between items-center mb-4">
					<h2 id="modalOrderNumber" class="text-xl font-bold">Order #</h2>
					<button onclick="closeOrderModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
				</div>
				<div id="modalOrderContent"></div>
				<div id="modalOrderActions" class="mt-4 flex gap-2"></div>
			</div>
		</div>
	</div>

	<script>
		let orders = [];
		let currentFilter = 'all';
		let refreshInterval = null;

		async function fetchOrders(status = null) {
			try {
				const url = status && status !== 'all' ? `/KIOSK/public/api/orders.php?status=${status}` : '/KIOSK/public/api/orders.php';
				const res = await fetch(url);
				if (!res.ok) {
					const data = await res.json();
					throw new Error(data.error || 'Failed to fetch orders');
				}
				orders = await res.json();
				renderOrders();
			} catch (e) {
				console.error('Error fetching orders:', e);
				alert('Failed to load orders: ' + e.message);
			}
		}

		function filterOrders(status) {
			currentFilter = status;
			fetchOrders(status === 'all' ? null : status);
			
			// Update button styles
			document.querySelectorAll('[id^="filter"]').forEach(btn => {
				btn.classList.remove('bg-red-600', 'text-white');
				btn.classList.add('bg-gray-200', 'text-gray-700');
			});
			
			const activeBtn = document.getElementById(`filter${status.charAt(0).toUpperCase() + status.slice(1)}`) || document.getElementById('filterAll');
			if (activeBtn) {
				activeBtn.classList.remove('bg-gray-200', 'text-gray-700');
				activeBtn.classList.add('bg-red-600', 'text-white');
			}
		}

		function renderOrders() {
			const container = document.getElementById('ordersContainer');
			const emptyState = document.getElementById('emptyState');
			
			if (orders.length === 0) {
				container.classList.add('hidden');
				emptyState.classList.remove('hidden');
				return;
			}
			
			container.classList.remove('hidden');
			emptyState.classList.add('hidden');
			
			container.innerHTML = orders.map(order => {
				const status = order.status || 'pending';
				const statusColors = {
					pending: 'bg-yellow-100 text-yellow-800 border-yellow-300',
					received: 'bg-blue-100 text-blue-800 border-blue-300',
					completed: 'bg-green-100 text-green-800 border-green-300'
				};
				const statusColor = statusColors[status] || statusColors.pending;
				const date = new Date(order.date_added);
				const timeStr = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
				
				return `
					<div class="bg-white rounded-xl shadow p-4 hover:shadow-lg transition cursor-pointer" onclick="showOrderDetails(${order.id})">
						<div class="flex justify-between items-start mb-3">
							<div>
								<h3 class="font-bold text-lg">Order #${order.id}</h3>
								<p class="text-sm text-gray-600">${timeStr}</p>
							</div>
							<span class="px-2 py-1 rounded text-xs font-semibold border ${statusColor}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>
						</div>
						<div class="border-t pt-3 mt-3">
							<div class="flex justify-between items-center mb-2">
								<span class="text-gray-600">Total:</span>
								<span class="font-bold text-lg text-red-600">₱${parseFloat(order.total_amount).toFixed(2)}</span>
							</div>
							<div class="flex justify-between items-center">
								<span class="text-sm text-gray-600">Payment:</span>
								<span class="text-sm font-semibold">${order.payment_method || 'Not specified'}</span>
							</div>
						</div>
					</div>
				`;
			}).join('');
		}

		async function showOrderDetails(orderId) {
			try {
				const res = await fetch(`/KIOSK/public/api/orders.php?id=${orderId}`);
				if (!res.ok) {
					const data = await res.json();
					throw new Error(data.error || 'Failed to fetch order details');
				}
				const order = await res.json();
				
				const modal = document.getElementById('orderModal');
				const modalNumber = document.getElementById('modalOrderNumber');
				const modalContent = document.getElementById('modalOrderContent');
				const modalActions = document.getElementById('modalOrderActions');
				
				modalNumber.textContent = `Order #${order.id}`;
				
				const status = order.status || 'pending';
				const date = new Date(order.date_added);
				const dateStr = date.toLocaleString('en-US');
				
				modalContent.innerHTML = `
					<div class="space-y-4">
						<div class="flex justify-between items-center">
							<span class="text-gray-600">Status:</span>
							<span class="px-2 py-1 rounded text-sm font-semibold ${status === 'pending' ? 'bg-yellow-100 text-yellow-800' : status === 'received' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>
						</div>
						<div class="flex justify-between items-center">
							<span class="text-gray-600">Date:</span>
							<span class="font-semibold">${dateStr}</span>
						</div>
						<div class="flex justify-between items-center">
							<span class="text-gray-600">Payment Method:</span>
							<span class="font-semibold">${order.payment_method || 'Not specified'}</span>
						</div>
						<hr>
						<div class="space-y-2">
							<h3 class="font-bold">Items:</h3>
							${order.items && order.items.length > 0 ? order.items.map(item => `
								<div class="flex justify-between items-center p-2 bg-gray-50 rounded">
									<div>
										<span class="font-semibold">${item.product_name}</span>
										<span class="text-sm text-gray-600 ml-2">x${item.quantity}</span>
									</div>
									<span class="font-semibold">₱${parseFloat(item.line_total).toFixed(2)}</span>
								</div>
							`).join('') : '<p class="text-gray-600">No items found</p>'}
						</div>
						<hr>
						<div class="flex justify-between items-center font-bold text-lg">
							<span>Total:</span>
							<span class="text-red-600">₱${parseFloat(order.total_amount).toFixed(2)}</span>
						</div>
					</div>
				`;
				
				// Action buttons based on status
				let actionButtons = '';
				if (status === 'pending') {
					actionButtons = `
						<button onclick="updateOrderStatus(${order.id}, 'received')" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Mark as Received</button>
						<button onclick="updateOrderStatus(${order.id}, 'completed')" class="flex-1 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Mark as Completed</button>
					`;
				} else if (status === 'received') {
					actionButtons = `
						<button onclick="updateOrderStatus(${order.id}, 'completed')" class="flex-1 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Mark as Completed</button>
						<button onclick="updateOrderStatus(${order.id}, 'pending')" class="flex-1 px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">Reopen</button>
					`;
				} else {
					actionButtons = `
						<button onclick="updateOrderStatus(${order.id}, 'received')" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Reopen as Received</button>
					`;
				}
				
				modalActions.innerHTML = actionButtons + '<button onclick="closeOrderModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Close</button>';
				
				modal.classList.remove('hidden');
			} catch (e) {
				console.error('Error fetching order details:', e);
				alert('Failed to load order details: ' + e.message);
			}
		}

		function closeOrderModal() {
			document.getElementById('orderModal').classList.add('hidden');
		}

		async function updateOrderStatus(orderId, status) {
			try {
				const res = await fetch(`/KIOSK/public/api/orders.php?id=${orderId}`, {
					method: 'PATCH',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({ status })
				});
				
				if (!res.ok) {
					const data = await res.json();
					throw new Error(data.error || 'Failed to update order status');
				}
				
				closeOrderModal();
				fetchOrders(currentFilter === 'all' ? null : currentFilter);
			} catch (e) {
				console.error('Error updating order status:', e);
				alert('Failed to update order status: ' + e.message);
			}
		}

		function refreshOrders() {
			fetchOrders(currentFilter === 'all' ? null : currentFilter);
		}

		function startAutoRefresh() {
			if (refreshInterval) clearInterval(refreshInterval);
			refreshInterval = setInterval(() => {
				fetchOrders(currentFilter === 'all' ? null : currentFilter);
			}, 10000); // Refresh every 10 seconds
		}

		function stopAutoRefresh() {
			if (refreshInterval) {
				clearInterval(refreshInterval);
				refreshInterval = null;
			}
		}

		async function logout() {
			await fetch('/KIOSK/public/api/logout.php');
			window.location.href = '/KIOSK/public/admin/login.php';
		}

		// Initialize
		fetchOrders();
		startAutoRefresh();
		
		// Stop auto-refresh when tab is not visible
		document.addEventListener('visibilitychange', () => {
			if (document.hidden) {
				stopAutoRefresh();
			} else {
				startAutoRefresh();
				refreshOrders();
			}
		});
	</script>
</body>
</html>

