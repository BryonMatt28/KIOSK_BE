<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$user = $_SESSION['user'] ?? null;
if (!$user) { header('Location: /KIOSK/public/admin/login.php'); exit; }
if ($user['role'] !== 'superadmin') { header('Location: /KIOSK/public/admin/products.php'); exit; }
require_once __DIR__ . '/../../src/config/db.php';
$rows = $mysqli->query("SELECT u.id, u.username, r.name role, u.suspended, u.date_added FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.date_added DESC");
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
	<title>User Management</title>
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
			<h1 class="text-xl font-bold">User Management</h1>
			<div class="text-sm text-gray-600">Logged in as: <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</div>
		</div>
		<div class="grid md:grid-cols-3 gap-3 mb-4">
			<input id="uname" class="border rounded px-3 py-2" placeholder="Admin username" />
			<input id="upass" type="password" class="border rounded px-3 py-2" placeholder="Temp password" />
			<button id="createBtn" class="bg-red-600 text-white rounded px-4 py-2">Create Admin</button>
		</div>
		<table class="w-full text-left">
			<thead><tr><th class="py-2">Username</th><th>Role</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
			<tbody>
				<?php while($row = $rows->fetch_assoc()): ?>
				<tr class="border-t">
					<td class="py-2"><?php echo htmlspecialchars($row['username']); ?></td>
					<td><?php echo htmlspecialchars($row['role']); ?></td>
					<td><?php echo ($row['suspended'] ? 'Suspended' : 'Active'); ?></td>
					<td><?php echo htmlspecialchars($row['date_added']); ?></td>
					<td>
						<?php if ($row['role']==='admin'): ?>
						<button class="text-sm underline" onclick="toggleSuspend(<?php echo (int)$row['id']; ?>, <?php echo (int)$row['suspended'] ? 'false':'true'; ?>)"><?php echo ((int)$row['suspended'] ? 'Unsuspend' : 'Suspend'); ?></button>
						<?php endif; ?>
					</td>
				</tr>
				<?php endwhile; ?>
			</tbody>
		</table>
	</div>
	<script>
		document.getElementById('createBtn').addEventListener('click', async ()=>{
			const username = document.getElementById('uname').value.trim();
			const password = document.getElementById('upass').value;
			if(!username || !password){ alert('Username and password required'); return; }
			const res = await fetch('/KIOSK/public/api/users.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({username, password, role:'admin'}) });
			if(!res.ok){ const e = await res.json(); alert(e.error||'Failed'); return; }
			location.reload();
		});
		async function toggleSuspend(id, suspend){
			const res = await fetch('/KIOSK/public/api/users.php?id='+id, { method:'PATCH', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ suspended: suspend })});
			if(!res.ok){ const e = await res.json(); alert(e.error||'Failed'); return; }
			location.reload();
		}
		async function logout(){ await fetch('/KIOSK/public/api/logout.php'); window.location.href='/KIOSK/public/admin/login.php'; }
	</script>
</body>
</html>
