<?php
require_once __DIR__ . '/../admin/inc/config.php';

$targetColumns = ['before_head', 'after_body', 'before_body'];

try {
    $dbNameStmt = $pdo->query('SELECT DATABASE() AS db_name');
    $dbName = $dbNameStmt->fetch(PDO::FETCH_ASSOC)['db_name'];

    if (!$dbName) {
        throw new RuntimeException('Không xác định được tên database hiện tại.');
    }

    $existingColumnsStmt = $pdo->prepare(
        'SELECT COLUMN_NAME
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?'
    );
    $existingColumnsStmt->execute([$dbName, 'tbl_settings']);

    $existing = [];
    foreach ($existingColumnsStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $existing[$row['COLUMN_NAME']] = true;
    }

    $dropList = [];
    foreach ($targetColumns as $column) {
        if (isset($existing[$column])) {
            $dropList[] = 'DROP COLUMN `' . $column . '`';
        }
    }

    if (empty($dropList)) {
        echo "Không có cột before_head/after_body/before_body trong tbl_settings.\n";
        exit(0);
    }

    $sql = 'ALTER TABLE `tbl_settings` ' . implode(', ', $dropList);
    $pdo->exec($sql);

    echo "Đã xóa cột thành công: " . implode(', ', $targetColumns) . "\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Lỗi dọn dẹp database: " . $e->getMessage() . "\n");
    exit(1);
}
