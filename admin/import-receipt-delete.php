<?php
require_once('inc/config.php');
require_once('inc/functions.php');

if(session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if(!isset($_SESSION['user'])) {
    safe_redirect('login.php');
}

$receipt_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($receipt_id <= 0) {
    safe_redirect('import-receipt.php');
}

try {
    $statement = $pdo->prepare("SELECT status FROM tbl_import_receipt WHERE receipt_id=? LIMIT 1");
    $statement->execute(array($receipt_id));
    $receipt = $statement->fetch(PDO::FETCH_ASSOC);

    if(!$receipt) {
        safe_redirect('import-receipt.php');
    }

    if((string)$receipt['status'] === 'Completed') {
        safe_redirect('import-receipt.php');
    }

    $pdo->beginTransaction();

    $statement = $pdo->prepare("DELETE FROM tbl_import_receipt_item WHERE receipt_id=?");
    $statement->execute(array($receipt_id));

    $statement = $pdo->prepare("DELETE FROM tbl_import_receipt WHERE receipt_id=?");
    $statement->execute(array($receipt_id));

    $pdo->commit();
    safe_redirect('import-receipt.php?deleted=1');
} catch(Exception $e) {
    if($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    safe_redirect('import-receipt.php');
}
