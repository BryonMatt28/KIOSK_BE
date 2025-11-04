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
				<a href="/KIOSK/public/admin/dashboard.php" class="px-3 py-1 border rounded text-sm">Dashboard</a>
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
			<input id="pprice" type="number" class="border rounded px-3 py-2" placeholder="Price" step="any" min="0" inputmode="decimal" />
			<input id="pcategory" class="border rounded px-3 py-2" placeholder="Category" />
			<input id="pimg" type="url" class="border rounded px-3 py-2" placeholder="Image URL (optional)" />
		</div>
		<div class="flex gap-2 mb-6">
			<button id="addBtn" class="bg-red-600 text-white rounded px-4 py-2">Add Product</button>
			<button id="updateBtn" class="hidden bg-blue-600 text-white rounded px-4 py-2">Update Product</button>
			<button id="cancelBtn" class="hidden bg-gray-200 text-gray-700 rounded px-4 py-2">Cancel</button>
		</div>
		<input type="hidden" id="editProductId" value="" />
		<hr class="my-6">
		<table class="w-full text-left">
			<thead><tr><th class="py-2">Name</th><th>Category</th><th>Price</th><th>Added By</th><th>Date</th><th>Actions</th></tr></thead>
			<tbody id="rows"></tbody>
		</table>
	</div>
	<script>
		let editingProductId = null;
		let productsData = [];

		async function loadProducts(){
			const res = await fetch('/KIOSK/public/api/products.php');
			const data = await res.json();
			productsData = data;
			document.getElementById('rows').innerHTML = data.map((p, index) => {
				return `
				<tr class="border-t" data-product-id="${p.id}">
					<td class="py-2">${escapeHtml(p.name)}</td>
					<td>${escapeHtml(p.category || '')}</td>
					<td>₱${parseFloat(p.price).toFixed(2)}</td>
					<td>${escapeHtml(p.added_by ?? '')}</td>
					<td>${escapeHtml(p.date_added)}</td>
					<td>
						<button data-edit-index="${index}" class="edit-btn text-blue-600 hover:text-blue-800 text-sm underline mr-2">Edit</button>
						<button data-delete-id="${p.id}" class="delete-btn text-red-600 hover:text-red-800 text-sm underline">Delete</button>
					</td>
				</tr>
			`;
			}).join('');
			
			// Attach event listeners to edit buttons
			document.querySelectorAll('.edit-btn').forEach(btn => {
				btn.addEventListener('click', function() {
					const index = parseInt(this.getAttribute('data-edit-index'));
					const product = productsData[index];
					editProduct(product.id, product.name, product.price, product.category || '', product.image_url || '');
				});
			});
			
			// Attach event listeners to delete buttons
			document.querySelectorAll('.delete-btn').forEach(btn => {
				btn.addEventListener('click', function() {
					const id = parseInt(this.getAttribute('data-delete-id'));
					deleteProduct(id);
				});
			});
		}
		
		function escapeHtml(text) {
			if (!text) return '';
			const div = document.createElement('div');
			div.textContent = text;
			return div.innerHTML;
		}

		function editProduct(id, name, price, category, image_url) {
			editingProductId = id;
			document.getElementById('editProductId').value = id;
			document.getElementById('pname').value = name || '';
			document.getElementById('pprice').value = price || '';
			document.getElementById('pcategory').value = category || '';
			document.getElementById('pimg').value = image_url || '';
			
			const addBtn = document.getElementById('addBtn');
			const updateBtn = document.getElementById('updateBtn');
			const cancelBtn = document.getElementById('cancelBtn');
			
			if (addBtn) addBtn.classList.add('hidden');
			if (updateBtn) updateBtn.classList.remove('hidden');
			if (cancelBtn) cancelBtn.classList.remove('hidden');
			
			// Scroll to form
			const nameInput = document.getElementById('pname');
			if (nameInput) {
				nameInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
				nameInput.focus();
			}
		}

		function cancelEdit() {
			editingProductId = null;
			document.getElementById('editProductId').value = '';
			document.getElementById('pname').value = '';
			document.getElementById('pprice').value = '';
			document.getElementById('pcategory').value = '';
			document.getElementById('pimg').value = '';
			
			document.getElementById('addBtn').classList.remove('hidden');
			document.getElementById('updateBtn').classList.add('hidden');
			document.getElementById('cancelBtn').classList.add('hidden');
		}

		// Price validation - only allow numbers
		document.getElementById('pprice').addEventListener('input', function(e) {
			let value = e.target.value;
			// Remove any non-numeric characters except decimal point
			value = value.replace(/[^0-9.]/g, '');
			// Ensure only one decimal point
			const parts = value.split('.');
			if (parts.length > 2) {
				value = parts[0] + '.' + parts.slice(1).join('');
			}
			e.target.value = value;
		});

		document.getElementById('addBtn').addEventListener('click', async ()=>{
			const name = document.getElementById('pname').value.trim();
			const priceValue = document.getElementById('pprice').value.trim();
			const price = parseFloat(priceValue);
			const image_url = document.getElementById('pimg').value.trim();
			const category = document.getElementById('pcategory').value.trim() || 'Uncategorized';
			
			if(!name){ alert('Product name is required'); return; }
			if(!priceValue || isNaN(price) || price <= 0){ alert('Please enter a valid price (must be a number greater than 0)'); return; }
			
			const res = await fetch('/KIOSK/public/api/products.php',{method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({name, price, image_url, category})});
			if(!res.ok){ const e = await res.json(); alert(e.error||'Failed'); return; }
			cancelEdit();
			await loadProducts();
		});

		document.getElementById('updateBtn').addEventListener('click', async ()=>{
			const id = editingProductId;
			if(!id){ alert('No product selected for editing'); return; }
			const name = document.getElementById('pname').value.trim();
			const priceValue = document.getElementById('pprice').value.trim();
			const price = parseFloat(priceValue);
			const image_url = document.getElementById('pimg').value.trim();
			const category = document.getElementById('pcategory').value.trim() || 'Uncategorized';
			
			if(!name){ alert('Product name is required'); return; }
			if(!priceValue || isNaN(price) || price <= 0){ alert('Please enter a valid price (must be a number greater than 0)'); return; }
			
			const res = await fetch(`/KIOSK/public/api/products.php?id=${id}`,{method:'PATCH', headers:{'Content-Type':'application/json'}, body:JSON.stringify({name, price, image_url, category})});
			if(!res.ok){ const e = await res.json(); alert(e.error||'Failed'); return; }
			cancelEdit();
			await loadProducts();
		});

		document.getElementById('cancelBtn').addEventListener('click', cancelEdit);

		async function deleteProduct(id) {
			if(!confirm('Are you sure you want to delete this product?')) return;
			const res = await fetch(`/KIOSK/public/api/products.php?id=${id}`, {method:'DELETE'});
			if(!res.ok){ const e = await res.json(); alert(e.error||'Failed'); return; }
			await loadProducts();
		}

		async function logout(){ await fetch('/KIOSK/public/api/logout.php'); window.location.href='/KIOSK/public/admin/login.php'; }
		loadProducts();
	</script>
</body>
</html>
