<?php
// Start output buffering immediately - must be first line
if (ob_get_level()) {
	ob_end_clean();
}
ob_start();

// Suppress any output
error_reporting(E_ALL);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
$user = $_SESSION['user'] ?? null;
if (!$user) { 
	ob_end_clean();
	header('Location: /KIOSK/public/admin/login.php'); 
	exit; 
}

require_once __DIR__ . '/../../src/config/db.php';

// Include TCPDF library (autoconfig will be loaded by tcpdf.php)
$tcpdf_path = __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';
if (!file_exists($tcpdf_path)) {
	ob_end_clean();
	header('Content-Type: text/html');
	die('Error: TCPDF library not found at: ' . htmlspecialchars($tcpdf_path));
}
require_once $tcpdf_path;

// Verify TCPDF class exists
if (!class_exists('TCPDF')) {
	ob_end_clean();
	header('Content-Type: text/html');
	die('Error: TCPDF class not found after including library.');
}

// Clean output buffer before PDF generation
ob_end_clean();

// Get date filters from GET parameters
$start = $_GET['date_start'] ?? '';
$end = $_GET['date_end'] ?? '';
$params = [];
$sql = "SELECT o.id, o.total_amount, o.payment_method, o.date_added FROM orders o WHERE 1=1";
if ($start !== '') { $sql .= " AND DATE(o.date_added) >= ?"; $params[] = $start; }
if ($end !== '') { $sql .= " AND DATE(o.date_added) <= ?"; $params[] = $end; }
$sql .= " ORDER BY o.date_added DESC";

$stmt = null;
$rows = [];
if (count($params) > 0) {
	$types = str_repeat('s', count($params));
	$stmt = $mysqli->prepare($sql);
	$stmt->bind_param($types, ...$params);
	$stmt->execute();
	$res = $stmt->get_result();
} else {
	$res = $mysqli->query($sql);
}

$sum = 0;
while ($row = $res->fetch_assoc()) {
	$rows[] = $row;
	$sum += (float)$row['total_amount'];
}

// Create PDF instance
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('SavorBite KIOSK System');
$pdf->SetAuthor('SavorBite Grill');
$pdf->SetTitle('Transaction Report');
$pdf->SetSubject('Order Transactions Report');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(15, 15, 15);

// Add a page
$pdf->AddPage();

// Set font - Use DejaVu Sans (Unicode font) to support peso symbol
$pdf->SetFont('dejavusans', 'B', 20);
$pdf->Cell(0, 10, 'SavorBite Grill', 0, 1, 'C');

$pdf->SetFont('dejavusans', '', 12);
$pdf->Cell(0, 5, 'Transaction Report', 0, 1, 'C');

// Date range information
$pdf->SetFont('dejavusans', '', 10);
$dateRange = 'All Transactions';
if ($start !== '' && $end !== '') {
	$dateRange = 'From ' . date('M d, Y', strtotime($start)) . ' to ' . date('M d, Y', strtotime($end));
} elseif ($start !== '') {
	$dateRange = 'From ' . date('M d, Y', strtotime($start));
} elseif ($end !== '') {
	$dateRange = 'Until ' . date('M d, Y', strtotime($end));
}
$pdf->Cell(0, 5, $dateRange, 0, 1, 'C');
$pdf->Cell(0, 5, 'Generated: ' . date('M d, Y h:i A'), 0, 1, 'C');

$pdf->Ln(5);

// Table header
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->SetFillColor(220, 220, 220);
$pdf->Cell(30, 8, 'Order #', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'Date', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'Payment Method', 1, 0, 'C', true);
$pdf->Cell(60, 8, 'Total Amount', 1, 1, 'R', true);

// Table data
$pdf->SetFont('dejavusans', '', 9);
$pdf->SetFillColor(245, 245, 245);
$fill = false;

foreach ($rows as $row) {
	$pdf->Cell(30, 7, '#' . $row['id'], 1, 0, 'C', $fill);
	$pdf->Cell(50, 7, date('M d, Y h:i A', strtotime($row['date_added'])), 1, 0, 'C', $fill);
	$pdf->Cell(50, 7, $row['payment_method'] ?? 'Not specified', 1, 0, 'C', $fill);
	$pdf->Cell(60, 7, '₱' . number_format((float)$row['total_amount'], 2), 1, 1, 'R', $fill);
	$fill = !$fill;
}

// Total row
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->SetFillColor(220, 220, 220);
$pdf->Cell(130, 8, 'Total:', 1, 0, 'R', true);
$pdf->Cell(60, 8, '₱' . number_format($sum, 2), 1, 1, 'R', true);

// Summary statistics
$pdf->Ln(5);
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->Cell(0, 5, 'Summary', 0, 1, 'L');
$pdf->SetFont('dejavusans', '', 9);
$pdf->Cell(0, 5, 'Total Transactions: ' . count($rows), 0, 1, 'L');
$pdf->Cell(0, 5, 'Total Amount: ₱' . number_format($sum, 2), 0, 1, 'L');
if (count($rows) > 0) {
	$avg = $sum / count($rows);
	$pdf->Cell(0, 5, 'Average Transaction: ₱' . number_format($avg, 2), 0, 1, 'L');
}

// Ensure no output before PDF output
while (ob_get_level()) {
	ob_end_clean();
}

// Generate filename
$filename = 'transaction_report_' . date('Y-m-d_His') . '.pdf';
if ($start !== '' || $end !== '') {
	$filename = 'transaction_report_' . str_replace(['-', ' '], '_', ($start ?: 'start')) . '_' . str_replace(['-', ' '], '_', ($end ?: 'end')) . '.pdf';
}

// Output PDF with 'D' to force download
// This will send proper headers and the PDF content
try {
	// Ensure no output buffers remain
	while (ob_get_level() > 0) {
		ob_end_clean();
	}
	
	// Output PDF
	$pdf->Output($filename, 'D');
	
	// Exit immediately after output
	exit;
} catch (Exception $e) {
	// If PDF generation fails, clean output and show error
	while (ob_get_level() > 0) {
		ob_end_clean();
	}
	header('Content-Type: text/html; charset=UTF-8');
	header('HTTP/1.1 500 Internal Server Error');
	echo '<!DOCTYPE html><html><head><title>PDF Generation Error</title></head><body>';
	echo '<h1>Error generating PDF</h1>';
	echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
	echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
	echo '</body></html>';
	exit;
} catch (Error $e) {
	// Catch fatal errors too
	while (ob_get_level() > 0) {
		ob_end_clean();
	}
	header('Content-Type: text/html; charset=UTF-8');
	header('HTTP/1.1 500 Internal Server Error');
	echo '<!DOCTYPE html><html><head><title>PDF Generation Error</title></head><body>';
	echo '<h1>Error generating PDF</h1>';
	echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
	echo '</body></html>';
	exit;
}

