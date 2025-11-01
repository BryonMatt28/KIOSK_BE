<?php
// One-time seeder: creates superadmin if not exists
// Visit once: http://localhost/KIOSK/public/admin/seed_superadmin.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/../../src/config/db.php';

$username = 'superadmin';
$password = 'P@ssw0rd123';

// Ensure roles exist (migrations should have done this)
$mysqli->query("INSERT IGNORE INTO roles (id, name) VALUES (1, 'superadmin'), (2, 'admin')");

// Check if user exists
$stmt = $mysqli->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();
if ($res->fetch_assoc()) {
	echo "User 'superadmin' already exists. Nothing to do.\n";
	exit;
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$roleId = 1; // superadmin
$ins = $mysqli->prepare('INSERT INTO users (username, password_hash, role_id) VALUES (?, ?, ?)');
$ins->bind_param('ssi', $username, $hash, $roleId);
$ins->execute();

if ($ins->affected_rows > 0) {
	echo "Superadmin created.\nUsername: superadmin\nPassword: P@ssw0rd123\n";
	echo "Please delete this file after use: public/admin/seed_superadmin.php\n";
} else {
	echo "Failed to create superadmin.\n";
}
