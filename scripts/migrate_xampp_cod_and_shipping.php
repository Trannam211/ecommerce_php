<?php
// One-time migration script for local XAMPP MySQL.
// - Adds tbl_settings.cod_on_off (if missing)
// - Adds tbl_payment.order_total_amount (if missing)
// - Normalizes some legacy shipping_status values
// - Enforces COD rule for existing orders: unpaid until delivered

declare(strict_types=1);

require_once __DIR__ . '/../admin/inc/config.php';

if(!isset($pdo) || !($pdo instanceof PDO)) {
	fwrite(STDERR, "[ERROR] PDO connection not available. Check admin/inc/config.php and MySQL service.\n");
	exit(1);
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function table_exists(PDO $pdo, string $table): bool
{
	$stmt = $pdo->prepare('SHOW TABLES LIKE ?');
	$stmt->execute(array($table));
	return $stmt->rowCount() > 0;
}

if(!table_exists($pdo, 'tbl_settings')) {
	fwrite(STDERR, "[ERROR] Missing table tbl_settings in current database.\n");
	exit(1);
}

if(!table_exists($pdo, 'tbl_payment')) {
	fwrite(STDERR, "[ERROR] Missing table tbl_payment in current database.\n");
	exit(1);
}

echo "[OK] Connected. Running migrations...\n";

// 1) Add cod_on_off if missing.
try {
	$pdo->exec('ALTER TABLE tbl_settings ADD COLUMN cod_on_off TINYINT(1) NOT NULL DEFAULT 1');
	echo "[OK] Added column tbl_settings.cod_on_off\n";
} catch(PDOException $e) {
	$msg = $e->getMessage();
	if(stripos($msg, 'Duplicate column') !== false || stripos($msg, 'already exists') !== false) {
		echo "[SKIP] Column tbl_settings.cod_on_off already exists\n";
	} else {
		fwrite(STDERR, "[WARN] Could not add tbl_settings.cod_on_off: {$msg}\n");
	}
}

// 1.1) Add order_total_amount if missing.
try {
	$pdo->exec('ALTER TABLE tbl_payment ADD COLUMN order_total_amount INT(11) NOT NULL DEFAULT 0');
	echo "[OK] Added column tbl_payment.order_total_amount\n";
} catch(PDOException $e) {
	$msg = $e->getMessage();
	if(stripos($msg, 'Duplicate column') !== false || stripos($msg, 'already exists') !== false) {
		echo "[SKIP] Column tbl_payment.order_total_amount already exists\n";
	} else {
		fwrite(STDERR, "[WARN] Could not add tbl_payment.order_total_amount: {$msg}\n");
	}
}

// Initialize order_total_amount from existing paid_amount where empty.
try {
	$affected = $pdo->exec("UPDATE tbl_payment SET order_total_amount = paid_amount WHERE order_total_amount <= 0");
	echo "[OK] order_total_amount initialized from paid_amount: {$affected} row(s)\n";
} catch(PDOException $e) {
	fwrite(STDERR, "[WARN] Could not initialize order_total_amount: {$e->getMessage()}\n");
}

// 2) Normalize legacy shipping statuses to new codes (safe no-op if none exist).
$shipping_normalizations = array(
	"UPDATE tbl_payment SET shipping_status='Pending' WHERE shipping_status='Processing'" => 'Processing -> Pending',
	"UPDATE tbl_payment SET shipping_status='Pending' WHERE shipping_status='Preparing'" => 'Preparing -> Pending',
	"UPDATE tbl_payment SET shipping_status='Shipping' WHERE shipping_status='Shipped'" => 'Shipped -> Shipping',
	"UPDATE tbl_payment SET shipping_status='Completed' WHERE shipping_status='Delivered'" => 'Delivered -> Completed',
	"UPDATE tbl_payment SET shipping_status='Canceled' WHERE shipping_status='Cancelled'" => 'Cancelled -> Canceled'
);

foreach($shipping_normalizations as $sql => $label) {
	try {
		$affected = $pdo->exec($sql);
		echo "[OK] {$label}: {$affected} row(s)\n";
	} catch(PDOException $e) {
		fwrite(STDERR, "[WARN] Failed normalization ({$label}): {$e->getMessage()}\n");
	}
}

// 3) Enforce COD payment status rule for existing rows.
try {
	$sql = "UPDATE tbl_payment
			SET
				payment_status = CASE WHEN shipping_status='Completed' THEN 'Completed' ELSE 'Pending' END,
				paid_amount = CASE WHEN shipping_status='Completed' THEN order_total_amount ELSE 0 END
			WHERE payment_method='Cash On Delivery'";
	$affected = $pdo->exec($sql);
	echo "[OK] COD payment_status and paid_amount normalized: {$affected} row(s)\n";
} catch(PDOException $e) {
	fwrite(STDERR, "[ERROR] Failed COD normalization: {$e->getMessage()}\n");
	exit(1);
}

echo "[DONE] Migration complete.\n";
