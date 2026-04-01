<?php
/**
 * Converts current database (configured in admin/inc/config.php) to utf8mb4.
 *
 * Usage (CLI):
 *   php scripts/convert_db_to_utf8mb4.php
 *   php scripts/convert_db_to_utf8mb4.php --dry-run
 */

if (PHP_SAPI !== 'cli') {
	header('Content-Type: text/plain; charset=utf-8');
	echo "This script is intended to run from CLI.\n";
	exit(1);
}

$dryRun = in_array('--dry-run', $argv, true);

require_once __DIR__ . '/../admin/inc/config.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
	fwrite(STDERR, "PDO connection not available. Check admin/inc/config.php.\n");
	exit(1);
}

// Try to detect DB name from DSN in config.
$dbName = null;
try {
	$stmt = $pdo->query('SELECT DATABASE() AS db');
	$row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
	if ($row && !empty($row['db'])) {
		$dbName = (string)$row['db'];
	}
} catch (Throwable $e) {
	// ignore
}

if (!$dbName) {
	fwrite(STDERR, "Could not detect current database name.\n");
	exit(1);
}

$targetCharset = 'utf8mb4';
$targetCollation = 'utf8mb4_unicode_ci';

function out($message)
{
	fwrite(STDOUT, $message . "\n");
}

function quoteIdent($name)
{
	return '`' . str_replace('`', '``', $name) . '`';
}

out('Database: ' . $dbName);
out('Target: ' . $targetCharset . ' / ' . $targetCollation);
out('Mode: ' . ($dryRun ? 'DRY RUN (no changes)' : 'APPLY'));
out('');

$queries = [];
$queries[] = 'ALTER DATABASE ' . quoteIdent($dbName) . ' CHARACTER SET ' . $targetCharset . ' COLLATE ' . $targetCollation;
$queries[] = 'SET NAMES ' . $targetCharset . ' COLLATE ' . $targetCollation;

// List tables.
$tables = [];
$stmt = $pdo->prepare('SELECT TABLE_NAME, ENGINE, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME');
$stmt->execute([$dbName]);
$tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$tables) {
	out('No tables found.');
	exit(0);
}

foreach ($tables as $t) {
	$tableName = (string)$t['TABLE_NAME'];
	$queries[] = 'ALTER TABLE ' . quoteIdent($tableName) . ' CONVERT TO CHARACTER SET ' . $targetCharset . ' COLLATE ' . $targetCollation;
}

out('Planned operations: ' . count($queries));
foreach ($queries as $q) {
	out('  ' . $q . ';');
}

if ($dryRun) {
	out('');
	out('Done (dry-run).');
	exit(0);
}

out('');
out('Executing...');

try {
	foreach ($queries as $q) {
		$pdo->exec($q);
	}
	out('Success.');
	out('Note: If you had garbled Vietnamese already stored under wrong charset, you may need data repair; this script only fixes charset/collation going forward.');
} catch (Throwable $e) {
	if ($pdo->inTransaction()) {
		$pdo->rollBack();
	}
	fwrite(STDERR, 'Failed: ' . $e->getMessage() . "\n");
	fwrite(STDERR, "Tip: Run with --dry-run to inspect queries, and ensure you have a DB backup.\n");
	exit(1);
}
