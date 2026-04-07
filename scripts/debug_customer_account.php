<?php
// Usage:
//   php scripts/debug_customer_account.php user@example.com
// Prints a small JSON report to help debug login issues.

require __DIR__ . '/../admin/inc/config.php';
require __DIR__ . '/../admin/inc/functions.php';

if ($argc < 2) {
    fwrite(STDERR, "Usage: php scripts/debug_customer_account.php <email>\n");
    exit(2);
}

$email = trim((string)$argv[1]);
if ($email === '') {
    fwrite(STDERR, "Email is required.\n");
    exit(2);
}

$report = array(
    'email' => $email,
    'db' => array(
        'connected' => false,
        'cust_password_column' => null,
    ),
    'customer' => array(
        'found' => false,
        'count' => 0,
        'min_id' => null,
        'max_id' => null,
        'latest' => null,
    ),
);

try {
    // If we got here, config.php created a PDO instance successfully.
    $report['db']['connected'] = isset($pdo) && ($pdo instanceof PDO);

    // Column metadata.
    $statement = $pdo->prepare("SHOW COLUMNS FROM `tbl_customer` LIKE 'cust_password'");
    $statement->execute();
    $column = $statement->fetch(PDO::FETCH_ASSOC);
    if ($column) {
        $report['db']['cust_password_column'] = array(
            'type' => $column['Type'] ?? null,
            'null' => $column['Null'] ?? null,
            'default' => $column['Default'] ?? null,
        );
    }

    // Duplicate check.
    $statement = $pdo->prepare('SELECT COUNT(*) AS c, MIN(cust_id) AS min_id, MAX(cust_id) AS max_id FROM tbl_customer WHERE cust_email=?');
    $statement->execute(array($email));
    $meta = $statement->fetch(PDO::FETCH_ASSOC);
    if ($meta) {
        $report['customer']['count'] = (int)$meta['c'];
        $report['customer']['min_id'] = $meta['min_id'] !== null ? (int)$meta['min_id'] : null;
        $report['customer']['max_id'] = $meta['max_id'] !== null ? (int)$meta['max_id'] : null;
    }

    // Latest row.
    $statement = $pdo->prepare('SELECT cust_id, cust_email, cust_status, cust_password FROM tbl_customer WHERE cust_email=? ORDER BY cust_id DESC LIMIT 1');
    $statement->execute(array($email));
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $pwd = (string)$row['cust_password'];
        $pwd_trim = trim($pwd);

        $looks_md5 = (bool)preg_match('/^[a-f0-9]{32}$/i', $pwd_trim);
        $password_info = function_exists('password_get_info') ? password_get_info($pwd_trim) : array('algo' => 0, 'algoName' => 'unknown');
        $looks_password_hash = ((int)($password_info['algo'] ?? 0) !== 0);

        $report['customer']['found'] = true;
        $report['customer']['latest'] = array(
            'cust_id' => (int)$row['cust_id'],
            'cust_status' => (int)$row['cust_status'],
            'pwd_len' => strlen($pwd),
            'pwd_trim_len' => strlen($pwd_trim),
            'pwd_prefix' => substr($pwd_trim, 0, 12),
            'looks_md5' => $looks_md5,
            'looks_password_hash' => $looks_password_hash,
            'algoName' => (string)($password_info['algoName'] ?? ''),
        );
    }

    echo json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    echo "\n";
} catch (Exception $exception) {
    $report['error'] = array(
        'message' => $exception->getMessage(),
        'class' => get_class($exception),
    );

    echo json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    echo "\n";
    exit(1);
}
