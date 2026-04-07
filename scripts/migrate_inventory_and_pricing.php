<?php
// One-time migration script for inventory, pricing, and import receipt features.
// Run: php scripts/migrate_inventory_and_pricing.php

declare(strict_types=1);

require_once __DIR__ . '/../admin/inc/config.php';
require_once __DIR__ . '/../admin/inc/functions.php';

if(!isset($pdo) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "[ERROR] PDO connection not available. Check admin/inc/config.php and MySQL service.\n");
    exit(1);
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "[OK] Connected. Ensuring schema...\n";
ensure_project_schema($pdo);

echo "[OK] Schema check complete.\n";

echo "[INFO] Current table status:\n";
$tables = array('tbl_product', 'tbl_import_receipt', 'tbl_import_receipt_item');
foreach($tables as $tableName) {
    $exists = schema_table_exists($pdo, $tableName) ? 'YES' : 'NO';
    echo "  - {$tableName}: {$exists}\n";
}

echo "[DONE] Migration finished.\n";
