<?php
require_once('inc/config.php');
require_once('inc/functions.php');

if(session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if(!isset($_SESSION['user'])) {
    http_response_code(403);
    exit;
}

$p_id = isset($_GET['p_id']) ? (int)$_GET['p_id'] : 0;
header('Content-Type: text/html; charset=utf-8');

echo '<option value="">Chọn size - màu</option>';
if($p_id <= 0) {
    exit;
}

try {
    $statement = $pdo->prepare("SHOW TABLES LIKE 'tbl_product_variant'");
    $statement->execute();
    $variant_table_exists = ($statement->rowCount() > 0);

    if(!$variant_table_exists) {
        // No variant support in DB.
        exit;
    }

    $statement = $pdo->prepare(
        "SELECT v.size_id, s.size_name, v.color_id, c.color_name, v.pv_qty
         FROM tbl_product_variant v
         LEFT JOIN tbl_size s ON s.size_id = v.size_id
         LEFT JOIN tbl_color c ON c.color_id = v.color_id
         WHERE v.p_id = ?
         ORDER BY v.size_id ASC, v.color_id ASC"
    );
    $statement->execute(array($p_id));
    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach($rows as $row) {
        $size_id = (int)$row['size_id'];
        $color_id = (int)$row['color_id'];
        if($size_id <= 0 || $color_id <= 0) {
            continue;
        }

        $size_name = isset($row['size_name']) ? (string)$row['size_name'] : '';
        $color_name = isset($row['color_name']) ? (string)$row['color_name'] : '';
        $pv_qty = isset($row['pv_qty']) ? (int)$row['pv_qty'] : 0;

        $label = ($size_name !== '' ? $size_name : ('Size '.$size_id)).' - '.($color_name !== '' ? $color_name : ('Màu '.$color_id));
        $label .= ' (Tồn: '.$pv_qty.')';

        $value = $size_id.'|'.$color_id;
        echo '<option value="'.htmlspecialchars($value, ENT_QUOTES, 'UTF-8').'">'.htmlspecialchars($label, ENT_QUOTES, 'UTF-8').'</option>';
    }
} catch(Exception $e) {
    // Keep output minimal.
    exit;
}
