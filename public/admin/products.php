<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$user = $_SESSION['user'] ?? null;
if (!$user) { header('Location: /KIOSK/public/admin/login.php'); exit; }
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Products</title>
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

	<div class="max-w-5xl mx-auto mt-10 bg-white rounded-xl shadow p-6">
		<div class="flex justify-between items-center mb-4">
			<h1 class="text-xl font-bold">Products</h1>
			<div class="text-sm text-gray-600">Logged in as: <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</div>
		</div>
		<div class="grid md:grid-cols-4 gap-3 mb-6">
			<input id="pname" class="border rounded px-3 py-2" placeholder="Name" />
			<input id="pprice" type="number" class="border rounded px-3 py-2" placeholder="Price" step="0.01" />
			<input id="pcategory" class="border rounded px-3 py-2" placeholder="Category" />
			<input id="pimg" class="border rounded px-3 py-2" placeholder="Image URL (optional)" />
		</div>
		<button id="addBtn" class="bg-red-600 text-white rounded px-4 py-2">Add Product</button>
		<hr class="my-6">
		<table class="w-full text-left">
			<thead><tr><th class="py-2">Name</th><th>Category</th><th>Price</th><th>Added By</th><th>Date</th></tr></thead>
			<tbody id="rows"></tbody>
		</table>
	</div>
	<script>
		async function loadProducts(){
			const res = await fetch('/KIOSK/public/api/products.php');
			const data = await res.json();
			document.getElementById('rows').innerHTML = data.map(p => `
				<tr class=\"border-t\"><td class=\"py-2\">${p.name}</td><td>${p.category || ''}</td><td>₱${parseFloat(p.price).toFixed(2)}</td><td>${p.added_by ?? ''}</td><td>${p.date_added}</td></tr>
			`).join('');
		}
		document.getElementById('addBtn').addEventListener('click', async ()=>{
			const name = document.getElementById('pname').value.trim();
			const price = parseFloat(document.getElementById('pprice').value);
			const image_url = document.getElementById('pimg').value.trim();
			const category = document.getElementById('pcategory').value.trim() || 'Uncategorized';
			if(!name||!price){ alert('Name and price are required'); return; }
			const res = await fetch('/KIOSK/public/api/products.php',{method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({name, price, image_url, category})});
			if(!res.ok){ const e = await res.json(); alert(e.error||'Failed'); return; }
			await loadProducts();
		});
		async function logout(){ await fetch('/KIOSK/public/api/logout.php'); window.location.href='/KIOSK/public/admin/login.php'; }
		loadProducts();
	</script>
</body>
</html>
