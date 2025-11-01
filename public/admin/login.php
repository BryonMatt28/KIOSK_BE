<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } if (isset($_SESSION['user'])) { header('Location: /KIOSK/public/admin/dashboard.php'); exit; } ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Admin Login</title>
	<link rel="stylesheet" href="/KIOSK/public/assets/style.css">
	<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-red-50 to-white">
	<div class="max-w-md mx-auto mt-24 bg-white rounded-xl shadow p-8">
		<h1 class="text-2xl font-bold mb-4 text-center">Admin Login</h1>
		<div class="space-y-3">
			<input id="username" class="w-full border rounded px-3 py-2" placeholder="Username" />
			<input id="password" type="password" class="w-full border rounded px-3 py-2" placeholder="Password" />
			<button id="loginBtn" class="w-full bg-red-600 text-white rounded py-2">Login</button>
			<p id="err" class="text-red-600 text-sm"></p>
		</div>
		<div class="mt-4 text-center"><a class="text-sm text-gray-600" href="/KIOSK/public/index.php">Back to site</a></div>
	</div>
	<script>
		document.getElementById('loginBtn').addEventListener('click', async () => {
			const username = document.getElementById('username').value.trim();
			const password = document.getElementById('password').value;
			document.getElementById('err').textContent = '';
			try {
				const res = await fetch('/KIOSK/public/api/login.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ username, password }) });
				const data = await res.json();
				if (!res.ok) throw new Error(data.error || 'Login failed');
				window.location.href = '/KIOSK/public/admin/dashboard.php';
			} catch (e) {
				document.getElementById('err').textContent = e.message;
			}
		});
	</script>
</body>
</html>
