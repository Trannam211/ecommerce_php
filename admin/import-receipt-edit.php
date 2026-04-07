<?php require_once('header.php'); ?>

<?php
function import_parse_decimal($value) {
    $normalized = str_replace(',', '', trim((string)$value));
    $normalized = preg_replace('/[^0-9.]/', '', $normalized);
    if($normalized === '') {
        return 0;
    }
    $parts = explode('.', $normalized);
    if(count($parts) > 2) {
        $normalized = array_shift($parts).'.'.implode('', $parts);
    }
    return (float)$normalized;
}

if(!isset($_GET['id'])) {
    safe_redirect('import-receipt.php');
}

$receipt_id = (int)$_GET['id'];
if($receipt_id <= 0) {
    safe_redirect('import-receipt.php');
}

$error_message = '';
$success_message = '';

$statement = $pdo->prepare("SELECT * FROM tbl_import_receipt WHERE receipt_id=? LIMIT 1");
$statement->execute(array($receipt_id));
$receipt = $statement->fetch(PDO::FETCH_ASSOC);
if(!$receipt) {
    safe_redirect('import-receipt.php');
}

$is_completed = ((string)$receipt['status'] === 'Completed');

if(isset($_POST['form_update_meta']) && !$is_completed) {
    $import_date_input = trim((string)($_POST['import_date'] ?? ''));
    $note = trim((string)($_POST['note'] ?? ''));

    $import_date = isset($receipt['import_date']) ? (string)$receipt['import_date'] : date('Y-m-d H:i:s');
    if($import_date_input !== '') {
        $ts = strtotime($import_date_input);
        if($ts !== false) {
            $import_date = date('Y-m-d H:i:s', $ts);
        }
    }

    $statement = $pdo->prepare("UPDATE tbl_import_receipt SET import_date=?, note=?, updated_at=? WHERE receipt_id=?");
    $statement->execute(array($import_date, $note, date('Y-m-d H:i:s'), $receipt_id));
    $success_message = 'Đã cập nhật thông tin phiếu nhập.';
}

if(isset($_POST['form_add_item']) && !$is_completed) {
    $p_id = (int)($_POST['p_id'] ?? 0);
    $simple_import_qty = (int)preg_replace('/[^0-9]/', '', (string)($_POST['simple_import_qty'] ?? '0'));
    $simple_import_price = import_parse_decimal($_POST['simple_import_price'] ?? '0');

    $variant_keys = $_POST['variant_key'] ?? array();
    $variant_qtys = $_POST['variant_import_qty'] ?? array();
    $variant_prices = $_POST['variant_import_price'] ?? array();
    if(!is_array($variant_keys)) {
        $variant_keys = array($variant_keys);
    }
    if(!is_array($variant_qtys)) {
        $variant_qtys = array($variant_qtys);
    }
    if(!is_array($variant_prices)) {
        $variant_prices = array($variant_prices);
    }

    if($p_id <= 0) {
        $error_message .= "Bạn phải chọn sản phẩm.<br>";
    }

    $rows_to_apply = array();

    if($error_message === '') {
        $statement = $pdo->prepare("SELECT p_id FROM tbl_product WHERE p_id=? LIMIT 1");
        $statement->execute(array($p_id));
        if(!$statement->fetch(PDO::FETCH_ASSOC)) {
            $error_message .= "Sản phẩm không tồn tại.<br>";
        }
    }

    // If the product is managed by size/color variants, require selecting valid variants.
    $variant_table_exists = false;
    $has_variants = false;
    if($error_message === '') {
        $statement = $pdo->prepare("SHOW TABLES LIKE 'tbl_product_variant'");
        $statement->execute();
        $variant_table_exists = ($statement->rowCount() > 0);
        if($variant_table_exists) {
            $statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_product_variant WHERE p_id=? AND size_id>0 AND color_id>0");
            $statement->execute(array($p_id));
            $has_variants = ((int)$statement->fetchColumn() > 0);
        }
    }

    if($error_message === '') {
        if($has_variants) {
            $max = max(count($variant_keys), count($variant_qtys), count($variant_prices));
            for($i=0; $i<$max; $i++) {
                $variant_key = trim((string)($variant_keys[$i] ?? ''));
                $import_qty = (int)preg_replace('/[^0-9]/', '', (string)($variant_qtys[$i] ?? '0'));
                $import_price = import_parse_decimal($variant_prices[$i] ?? '0');

                // Skip fully empty rows.
                if($variant_key === '' && $import_qty <= 0 && $import_price <= 0) {
                    continue;
                }

                if($variant_key === '') {
                    $error_message .= 'Dòng '.($i+1).': Vui lòng chọn size - màu.<br>';
                    continue;
                }
                if($import_qty <= 0) {
                    $error_message .= 'Dòng '.($i+1).': Số lượng nhập phải lớn hơn 0.<br>';
                    continue;
                }
                if($import_price <= 0) {
                    $error_message .= 'Dòng '.($i+1).': Đơn giá nhập phải lớn hơn 0.<br>';
                    continue;
                }

                $size_id = 0;
                $color_id = 0;
                $parts = explode('|', $variant_key);
                if(count($parts) === 2) {
                    $size_id = (int)$parts[0];
                    $color_id = (int)$parts[1];
                }
                if($size_id <= 0 || $color_id <= 0) {
                    $error_message .= 'Dòng '.($i+1).': Size - màu không hợp lệ.<br>';
                    continue;
                }

                $statement = $pdo->prepare("SELECT pv_qty FROM tbl_product_variant WHERE p_id=? AND size_id=? AND color_id=? LIMIT 1");
                $statement->execute(array($p_id, $size_id, $color_id));
                if(!$statement->fetch(PDO::FETCH_ASSOC)) {
                    $error_message .= 'Dòng '.($i+1).': Biến thể size - màu không tồn tại. Bạn hãy vào Sửa sản phẩm để thêm biến thể trước.<br>';
                    continue;
                }

                $rows_to_apply[] = array(
                    'size_id' => $size_id,
                    'color_id' => $color_id,
                    'import_qty' => $import_qty,
                    'import_price' => $import_price
                );
            }

            if($error_message === '' && count($rows_to_apply) === 0) {
                $error_message .= 'Bạn chưa nhập dòng size/màu nào.<br>';
            }
        } else {
            if($simple_import_qty <= 0) {
                $error_message .= "Số lượng nhập phải lớn hơn 0.<br>";
            }
            if($simple_import_price <= 0) {
                $error_message .= "Đơn giá nhập phải lớn hơn 0.<br>";
            }
            if($error_message === '') {
                $rows_to_apply[] = array(
                    'size_id' => 0,
                    'color_id' => 0,
                    'import_qty' => $simple_import_qty,
                    'import_price' => $simple_import_price
                );
            }
        }
    }

    if($error_message === '') {
        try {
            $pdo->beginTransaction();

            foreach($rows_to_apply as $row) {
                $size_id = (int)$row['size_id'];
                $color_id = (int)$row['color_id'];
                $import_qty = (int)$row['import_qty'];
                $import_price = (float)$row['import_price'];

                $statement = $pdo->prepare("SELECT item_id, import_qty, import_price FROM tbl_import_receipt_item WHERE receipt_id=? AND p_id=? AND size_id=? AND color_id=? LIMIT 1");
                $statement->execute(array($receipt_id, $p_id, $size_id, $color_id));
                $existing_item = $statement->fetch(PDO::FETCH_ASSOC);

                if($existing_item) {
                    $old_qty = (int)$existing_item['import_qty'];
                    $old_price = (float)$existing_item['import_price'];
                    $new_qty = $old_qty + $import_qty;
                    $new_price = (($old_qty * $old_price) + ($import_qty * $import_price)) / $new_qty;

                    $statement = $pdo->prepare("UPDATE tbl_import_receipt_item SET import_qty=?, import_price=? WHERE item_id=?");
                    $statement->execute(array($new_qty, $new_price, (int)$existing_item['item_id']));
                } else {
                    $statement = $pdo->prepare("INSERT INTO tbl_import_receipt_item (receipt_id, p_id, size_id, color_id, import_qty, import_price) VALUES (?,?,?,?,?,?)");
                    $statement->execute(array($receipt_id, $p_id, $size_id, $color_id, $import_qty, $import_price));
                }
            }

            $statement = $pdo->prepare("UPDATE tbl_import_receipt SET updated_at=? WHERE receipt_id=?");
            $statement->execute(array(date('Y-m-d H:i:s'), $receipt_id));

            $pdo->commit();
            $success_message = 'Đã thêm/cập nhật dòng nhập hàng.';
        } catch(Exception $e) {
            if($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error_message .= $e->getMessage().'<br>';
        }
    }
}

if(isset($_POST['form_delete_item']) && !$is_completed) {
    $item_id = (int)($_POST['item_id'] ?? 0);
    if($item_id > 0) {
        $statement = $pdo->prepare("DELETE FROM tbl_import_receipt_item WHERE item_id=? AND receipt_id=?");
        $statement->execute(array($item_id, $receipt_id));

        $statement = $pdo->prepare("UPDATE tbl_import_receipt SET updated_at=? WHERE receipt_id=?");
        $statement->execute(array(date('Y-m-d H:i:s'), $receipt_id));

        $success_message = 'Đã xóa dòng nhập hàng.';
    }
}

if(isset($_POST['form_complete']) && !$is_completed) {
    try {
        $pdo->beginTransaction();

        $statement = $pdo->prepare("SELECT * FROM tbl_import_receipt WHERE receipt_id=? FOR UPDATE");
        $statement->execute(array($receipt_id));
        $receipt_for_update = $statement->fetch(PDO::FETCH_ASSOC);
        if(!$receipt_for_update) {
            throw new Exception('Không tìm thấy phiếu nhập.');
        }
        if((string)$receipt_for_update['status'] === 'Completed') {
            throw new Exception('Phiếu nhập đã hoàn tất trước đó.');
        }

        $statement = $pdo->prepare("SELECT * FROM tbl_import_receipt_item WHERE receipt_id=?");
        $statement->execute(array($receipt_id));
        $import_items = $statement->fetchAll(PDO::FETCH_ASSOC);
        if(count($import_items) === 0) {
            throw new Exception('Phiếu nhập chưa có sản phẩm.');
        }

        $statement = $pdo->prepare("SHOW TABLES LIKE 'tbl_product_variant'");
        $statement->execute();
        $variant_table_exists = ($statement->rowCount() > 0);

        $product_imports = array();
        foreach($import_items as $item) {
            $p_id = (int)$item['p_id'];
            $import_qty = (int)$item['import_qty'];
            $import_price = (float)$item['import_price'];
            if($import_qty <= 0 || $import_price <= 0) {
                continue;
            }

            if(!isset($product_imports[$p_id])) {
                $product_imports[$p_id] = array(
                    'qty' => 0,
                    'value' => 0,
                    'items' => array()
                );
            }
            $product_imports[$p_id]['qty'] += $import_qty;
            $product_imports[$p_id]['value'] += ($import_qty * $import_price);
            $product_imports[$p_id]['items'][] = $item;
        }

        foreach($product_imports as $p_id => $payload) {
            $total_import_qty = (int)$payload['qty'];
            $total_import_value = (float)$payload['value'];
            if($total_import_qty <= 0) {
                continue;
            }

            $statement = $pdo->prepare("SELECT p_id, p_name, p_qty, p_cost_price, p_profit_percent FROM tbl_product WHERE p_id=? FOR UPDATE");
            $statement->execute(array((int)$p_id));
            $product_row = $statement->fetch(PDO::FETCH_ASSOC);
            if(!$product_row) {
                continue;
            }

            $product_name = isset($product_row['p_name']) ? (string)$product_row['p_name'] : '';
            $old_cost_price = (float)$product_row['p_cost_price'];
            $profit_percent = (float)$product_row['p_profit_percent'];
            if($profit_percent < 0) {
                $profit_percent = 0;
            }

            $product_has_variants = false;
            $variant_total_old_qty = 0;
            $variant_qty_map = array();

            if($variant_table_exists) {
                $statement = $pdo->prepare("SELECT size_id, color_id, pv_qty FROM tbl_product_variant WHERE p_id=? FOR UPDATE");
                $statement->execute(array((int)$p_id));
                $variant_rows = $statement->fetchAll(PDO::FETCH_ASSOC);
                if(count($variant_rows) > 0) {
                    $product_has_variants = true;
                    foreach($variant_rows as $vr) {
                        $sid = (int)$vr['size_id'];
                        $cid = (int)$vr['color_id'];
                        $qty = (int)$vr['pv_qty'];
                        if($sid > 0 && $cid > 0) {
                            $variant_qty_map[$sid.'|'.$cid] = $qty;
                            $variant_total_old_qty += $qty;
                        }
                    }
                }
            }

            // Apply stock to variants when variants exist; otherwise apply to product total stock.
            if($product_has_variants) {
                foreach($payload['items'] as $item) {
                    $import_qty = (int)$item['import_qty'];
                    $sid = isset($item['size_id']) ? (int)$item['size_id'] : 0;
                    $cid = isset($item['color_id']) ? (int)$item['color_id'] : 0;
                    if($import_qty <= 0) {
                        continue;
                    }
                    if($sid <= 0 || $cid <= 0) {
                        throw new Exception('Sản phẩm "'.$product_name.'" đang quản lý theo size/màu. Vui lòng chọn size - màu cho tất cả dòng nhập trước khi hoàn tất.');
                    }
                    $key = $sid.'|'.$cid;
                    if(!isset($variant_qty_map[$key])) {
                        throw new Exception('Biến thể size - màu của sản phẩm "'.$product_name.'" không tồn tại. Bạn hãy vào Sửa sản phẩm để thêm biến thể trước.');
                    }

                    $statement = $pdo->prepare("UPDATE tbl_product_variant SET pv_qty = pv_qty + ? WHERE p_id=? AND size_id=? AND color_id=?");
                    $statement->execute(array($import_qty, (int)$p_id, $sid, $cid));
                    $variant_qty_map[$key] += $import_qty;
                }

                $new_total_qty = 0;
                foreach($variant_qty_map as $q) {
                    $new_total_qty += (int)$q;
                }
                $old_qty_for_cost = $variant_total_old_qty;
            } else {
                $old_qty_for_cost = (int)$product_row['p_qty'];
                $new_total_qty = $old_qty_for_cost + $total_import_qty;
            }

            $new_qty_for_cost = $old_qty_for_cost + $total_import_qty;
            if($new_qty_for_cost <= 0) {
                continue;
            }

            $new_cost_price = (($old_qty_for_cost * $old_cost_price) + $total_import_value) / $new_qty_for_cost;
            $new_sale_price = round($new_cost_price * (1 + ($profit_percent / 100)), 0);
            if($new_sale_price < 1) {
                $new_sale_price = 1;
            }

            $statement_update = $pdo->prepare("UPDATE tbl_product SET p_qty=?, p_cost_price=?, p_current_price=? WHERE p_id=?");
            $statement_update->execute(array((int)$new_total_qty, $new_cost_price, $new_sale_price, (int)$p_id));
        }

        $now = date('Y-m-d H:i:s');
        $statement = $pdo->prepare("UPDATE tbl_import_receipt SET status='Completed', completed_at=?, updated_at=? WHERE receipt_id=?");
        $statement->execute(array($now, $now, $receipt_id));

        $pdo->commit();

        safe_redirect('import-receipt.php?completed=1');
    } catch(Exception $e) {
        if($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error_message .= $e->getMessage().'<br>';
    }
}

$statement = $pdo->prepare("SELECT * FROM tbl_import_receipt WHERE receipt_id=? LIMIT 1");
$statement->execute(array($receipt_id));
$receipt = $statement->fetch(PDO::FETCH_ASSOC);
$is_completed = ((string)$receipt['status'] === 'Completed');

$statement = $pdo->prepare("SELECT i.*, p.p_name, p.p_code, p.p_unit
							, s.size_name, c.color_name
                            FROM tbl_import_receipt_item i
                            LEFT JOIN tbl_product p ON p.p_id = i.p_id
							LEFT JOIN tbl_size s ON s.size_id = i.size_id
							LEFT JOIN tbl_color c ON c.color_id = i.color_id
                            WHERE i.receipt_id=?
                            ORDER BY i.item_id ASC");
$statement->execute(array($receipt_id));
$item_rows = $statement->fetchAll(PDO::FETCH_ASSOC);

$total_qty = 0;
$total_amount = 0;
foreach($item_rows as $item) {
    $qty = (int)$item['import_qty'];
    $price = (float)$item['import_price'];
    $total_qty += $qty;
    $total_amount += ($qty * $price);
}

$statement = $pdo->prepare("SELECT p_id, p_name, p_code, p_unit, p_qty FROM tbl_product ORDER BY p_name ASC");
$statement->execute();
$all_products = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Chi tiết phiếu nhập</h1>
    </div>
    <div class="content-header-right">
        <a href="import-receipt.php" class="btn btn-primary btn-sm">Quay lại danh sách</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php if($error_message !== ''): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if($success_message !== ''): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        Phiếu: <?php echo htmlspecialchars((string)$receipt['receipt_code'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php if($is_completed): ?>
                            <span class="label label-success" style="margin-left:6px;">Hoàn tất</span>
                        <?php else: ?>
                            <span class="label label-default" style="margin-left:6px;">Nháp</span>
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="box-body">
                    <form action="" method="post" class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Ngày nhập</label>
                            <div class="col-sm-4">
                                <input type="datetime-local" name="import_date" class="form-control" value="<?php echo htmlspecialchars(date('Y-m-d\\TH:i', strtotime((string)$receipt['import_date'])), ENT_QUOTES, 'UTF-8'); ?>" <?php if($is_completed){ echo 'disabled'; } ?>>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Ghi chú</label>
                            <div class="col-sm-8">
                                <textarea name="note" class="form-control" rows="3" <?php if($is_completed){ echo 'disabled'; } ?>><?php echo htmlspecialchars((string)$receipt['note'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                        </div>
                        <?php if(!$is_completed): ?>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-8">
                                <button type="submit" class="btn btn-primary" name="form_update_meta">Lưu thông tin phiếu</button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <?php if(!$is_completed): ?>
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Thêm sản phẩm nhập</h3>
                </div>
                <div class="box-body">
                    <form action="" method="post" class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Sản phẩm</label>
                            <div class="col-sm-8">
                                <select name="p_id" id="import_p_id" class="form-control select2" style="width:100%;">
                                    <option value="">Chọn sản phẩm</option>
                                    <?php foreach($all_products as $p): ?>
                                        <option value="<?php echo (int)$p['p_id']; ?>" <?php echo (isset($_POST['p_id']) && (int)$_POST['p_id'] === (int)$p['p_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars((string)$p['p_code'], ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars((string)$p['p_name'], ENT_QUOTES, 'UTF-8'); ?> (Tồn: <?php echo (int)$p['p_qty']; ?> <?php echo htmlspecialchars((string)$p['p_unit'], ENT_QUOTES, 'UTF-8'); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" id="import_variant_group" style="display:none;">
                            <label class="col-sm-2 control-label">Size - Màu</label>
                            <div class="col-sm-8">
                                <?php
                                $seed_variant_keys = isset($_POST['variant_key']) ? $_POST['variant_key'] : array('');
                                $seed_variant_qtys = isset($_POST['variant_import_qty']) ? $_POST['variant_import_qty'] : array('1');
                                $seed_variant_prices = isset($_POST['variant_import_price']) ? $_POST['variant_import_price'] : array('');
                                if(!is_array($seed_variant_keys)) { $seed_variant_keys = array($seed_variant_keys); }
                                if(!is_array($seed_variant_qtys)) { $seed_variant_qtys = array($seed_variant_qtys); }
                                if(!is_array($seed_variant_prices)) { $seed_variant_prices = array($seed_variant_prices); }
                                $seed_max = max(count($seed_variant_keys), count($seed_variant_qtys), count($seed_variant_prices), 1);
                                $seed_rows = array();
                                for($i=0; $i<$seed_max; $i++) {
                                    $seed_rows[] = array(
                                        'variant_key' => trim((string)($seed_variant_keys[$i] ?? '')),
                                        'qty' => (string)($seed_variant_qtys[$i] ?? '1'),
                                        'price' => (string)($seed_variant_prices[$i] ?? '')
                                    );
                                }
                                ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered" style="margin-bottom:0;">
                                        <thead>
                                            <tr>
                                                <th>Size - Màu</th>
                                                <th width="110">Số lượng</th>
                                                <th width="160">Đơn giá nhập</th>
                                                <th width="70">Xóa</th>
                                            </tr>
                                        </thead>
                                        <tbody id="importVariantBody">
                                            <?php foreach($seed_rows as $seed): ?>
                                            <tr>
                                                <td>
                                                    <select name="variant_key[]" class="form-control select2 import-variant-select" style="width:100%;" data-selected="<?php echo htmlspecialchars((string)$seed['variant_key'], ENT_QUOTES, 'UTF-8'); ?>">
                                                        <option value="">Chọn size - màu</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" min="1" name="variant_import_qty[]" class="form-control" value="<?php echo htmlspecialchars((string)$seed['qty'], ENT_QUOTES, 'UTF-8'); ?>">
                                                </td>
                                                <td>
                                                    <input type="text" name="variant_import_price[]" class="form-control currency-input" inputmode="numeric" placeholder="Ví dụ: 180000" value="<?php echo htmlspecialchars((string)$seed['price'], ENT_QUOTES, 'UTF-8'); ?>">
                                                </td>
                                                <td style="text-align:center;">
                                                    <button type="button" class="btn btn-danger btn-xs import-variant-remove">Xóa</button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div style="text-align:right;margin-top:8px;">
                                    <button type="button" class="btn btn-primary btn-sm" id="importVariantAddRowBtn">Thêm dòng</button>
                                </div>
                                <div style="text-align:right;margin-top:8px;">
                                    <span style="margin-right:14px;"><strong>Tổng SL:</strong> <span id="importVariantTotalQty">0</span></span>
                                    <span><strong>Tổng tiền:</strong> <span id="importVariantTotalAmount">0</span></span>
                                </div>
                                <p class="help-block" style="margin-bottom:0;">Chỉ hiển thị khi sản phẩm có quản lý size/màu.</p>
                            </div>
                        </div>
                        <div id="import_simple_group">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Số lượng</label>
                                <div class="col-sm-4">
                                    <input type="number" min="1" name="simple_import_qty" class="form-control" value="<?php echo isset($_POST['simple_import_qty']) ? htmlspecialchars((string)$_POST['simple_import_qty'], ENT_QUOTES, 'UTF-8') : '1'; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Đơn giá nhập</label>
                                <div class="col-sm-4">
                                    <input type="text" name="simple_import_price" class="form-control currency-input" inputmode="numeric" placeholder="Ví dụ: 180000" value="<?php echo isset($_POST['simple_import_price']) ? htmlspecialchars((string)$_POST['simple_import_price'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label"></label>
                                <div class="col-sm-8" style="text-align:right;padding-top:8px;">
                                    <span><strong>Thành tiền:</strong> <span id="importSimpleTotalAmount">0</span></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-8">
                                <button type="submit" class="btn btn-success" name="form_add_item">Thêm sản phẩm</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Danh sách sản phẩm trong phiếu</h3>
                </div>
                <div class="box-body table-responsive">
                    <div style="text-align:right;margin-bottom:8px;">
                        <span style="margin-right:14px;"><strong>Tổng SL:</strong> <?php echo (int)$total_qty; ?></span>
                        <span><strong>Tổng tiền:</strong> <?php echo format_price_vnd((float)$total_amount); ?></span>
                    </div>
                    <table id="example1" class="table table-bordered table-hover table-striped" data-empty-table="Chưa có sản phẩm nào.">
                        <thead>
                            <tr>
                                <th width="10">#</th>
                                <th width="110">Mã SP</th>
                                <th>Sản phẩm</th>
                                <th width="90">Size</th>
                                <th width="110">Màu</th>
                                <th width="90">Đơn vị</th>
                                <th width="110">Số lượng</th>
                                <th width="130">Đơn giá nhập</th>
                                <th width="150">Thành tiền</th>
                                <?php if(!$is_completed): ?>
                                    <th width="80">Thao tác</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($item_rows) > 0): ?>
                                <?php $idx = 0; foreach($item_rows as $item): $idx++; ?>
                                    <?php
                                    $qty = (int)$item['import_qty'];
                                    $price = (float)$item['import_price'];
                                    $line_total = $qty * $price;
                                    ?>
                                    <tr>
                                        <td><?php echo $idx; ?></td>
                                        <td><?php echo htmlspecialchars((string)$item['p_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)$item['p_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)($item['size_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)($item['color_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)$item['p_unit'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo $qty; ?></td>
                                        <td><?php echo format_price_vnd($price); ?></td>
                                        <td><?php echo format_price_vnd($line_total); ?></td>
                                        <?php if(!$is_completed): ?>
                                            <td>
                                                <form action="" method="post" onsubmit="return confirm('Xóa dòng này khỏi phiếu nhập?');">
                                                    <input type="hidden" name="item_id" value="<?php echo (int)$item['item_id']; ?>">
                                                    <button type="submit" name="form_delete_item" class="btn btn-danger btn-xs">Xóa</button>
                                                </form>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" style="text-align:right;">Tổng</th>
                                <th><?php echo $total_qty; ?></th>
                                <th></th>
                                <th><?php echo format_price_vnd($total_amount); ?></th>
                                <?php if(!$is_completed): ?>
                                    <th></th>
                                <?php endif; ?>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <?php if(!$is_completed): ?>
            <div class="box box-success">
                <div class="box-body">
                    <form action="" method="post" onsubmit="return confirm('Bạn chắc chắn muốn hoàn tất phiếu nhập? Hệ thống sẽ cập nhật tồn kho và giá vốn theo bình quân gia quyền.');">
                        <button type="submit" class="btn btn-success" name="form_complete">Hoàn tất phiếu nhập</button>
                        <span style="margin-left:8px;color:#666;">Sau khi hoàn tất, phiếu sẽ bị khóa chỉnh sửa.</span>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
    (function() {
        function initImportVariantUI() {
            if(!window.jQuery) {
                return;
            }
            var $ = window.jQuery;

            var $product = $('#import_p_id');
            if(!$product.length) {
                return;
            }

            var $variantGroup = $('#import_variant_group');
            var $simpleGroup = $('#import_simple_group');
            var $variantBody = $('#importVariantBody');
            var $addRowBtn = $('#importVariantAddRowBtn');

            var variantOptionsHtml = '<option value="">Chọn size - màu</option>';
            var isHydrating = false;

            function parseMoney(value) {
                var normalized = String(value || '').replace(/,/g, '').replace(/[^0-9.]/g, '');
                if(!normalized) {
                    return 0;
                }
                var n = parseFloat(normalized);
                return isNaN(n) ? 0 : n;
            }

            function formatMoney(value) {
                var rounded = Math.round(Number(value || 0));
                if(window.formatThousandsInput) {
                    return window.formatThousandsInput(rounded);
                }
                return String(rounded).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            function recalcVariantDraftTotals() {
                var totalQty = 0;
                var totalAmount = 0;
                if($variantBody.length) {
                    $variantBody.find('tr').each(function() {
                        var $tr = $(this);
                        var qty = parseInt(String($tr.find('input[name="variant_import_qty[]"]').val() || '0').replace(/\D/g, ''), 10);
                        if(isNaN(qty)) {
                            qty = 0;
                        }
                        var price = parseMoney($tr.find('input[name="variant_import_price[]"]').val());
                        totalQty += qty;
                        totalAmount += (qty * price);
                    });
                }
                $('#importVariantTotalQty').text(totalQty);
                $('#importVariantTotalAmount').text(formatMoney(totalAmount));
            }

            function recalcSimpleDraftTotal() {
                var qty = parseInt(String($('input[name="simple_import_qty"]').val() || '0').replace(/\D/g, ''), 10);
                if(isNaN(qty)) {
                    qty = 0;
                }
                var price = parseMoney($('input[name="simple_import_price"]').val());
                $('#importSimpleTotalAmount').text(formatMoney(qty * price));
            }

            function hasVariantOptions(html) {
                return /<option[^>]+value\s*=\s*"\d+\|\d+"/i.test(String(html || ''));
            }

            function showVariantMode() {
                if($variantGroup.length) {
                    $variantGroup.show();
                }
                if($simpleGroup.length) {
                    $simpleGroup.hide();
                }
                recalcVariantDraftTotals();
            }

            function showSimpleMode() {
                if($variantGroup.length) {
                    $variantGroup.hide();
                }
                if($simpleGroup.length) {
                    $simpleGroup.show();
                }
                recalcSimpleDraftTotal();
            }

            function ensureAtLeastOneVariantRow() {
                if(!$variantBody.length) {
                    return;
                }
                if($variantBody.find('tr').length === 0) {
                    addVariantRow({ variant_key: '', qty: '1', price: '' });
                }
            }

            function addVariantRow(seed) {
                if(!$variantBody.length) {
                    return;
                }
                var selected = seed && seed.variant_key ? String(seed.variant_key) : '';
                var qty = seed && seed.qty ? String(seed.qty) : '1';
                var price = seed && seed.price ? String(seed.price) : '';

                var $tr = $('<tr></tr>');
                var $select = $('<select name="variant_key[]" class="form-control select2 import-variant-select" style="width:100%;"></select>');
                $select.html(variantOptionsHtml);
                $tr.append($('<td></td>').append($select));
                $tr.append($('<td></td>').append($('<input type="number" min="1" name="variant_import_qty[]" class="form-control">').val(qty)));
                $tr.append($('<td></td>').append($('<input type="text" name="variant_import_price[]" class="form-control currency-input" inputmode="numeric" placeholder="Ví dụ: 180000">').val(price)));
                $tr.append($('<td style="text-align:center;"></td>').append('<button type="button" class="btn btn-danger btn-xs import-variant-remove">Xóa</button>'));
                $variantBody.append($tr);

                if($.fn.select2) {
                    $select.select2();
                }
                if(selected) {
                    $select.val(selected).trigger('change');
                }
                recalcVariantDraftTotals();
            }

            function hydrateVariantSelects(mode) {
                // mode: 'restore' | 'reset' | 'keep'
                if(isHydrating) {
                    return;
                }
                isHydrating = true;
                try {
                    $('.import-variant-select').each(function() {
                        var $sel = $(this);
                        var selected = '';
                        if(mode === 'restore') {
                            selected = String($sel.attr('data-selected') || $sel.val() || '');
                        } else if(mode === 'keep') {
                            selected = String($sel.val() || '');
                        }

                        $sel.html(variantOptionsHtml);
                        $sel.val(selected);
                        if($.fn.select2) {
                            $sel.trigger('change.select2');
                        } else {
                            $sel.trigger('change');
                        }
                    });
                } finally {
                    isHydrating = false;
                }
            }

            function loadVariantOptions(pId, mode) {
                if(!pId) {
                    showSimpleMode();
                    return;
                }
                $.ajax({
                    url: 'get-product-variant-options.php',
                    method: 'GET',
                    data: { p_id: pId },
                    success: function(resp) {
                        var html = String(resp || '');
                        if(hasVariantOptions(html)) {
                            variantOptionsHtml = html;
                            showVariantMode();
                            ensureAtLeastOneVariantRow();
                            hydrateVariantSelects(mode || 'keep');
                        } else {
                            showSimpleMode();
                        }
                    },
                    error: function() {
                        showSimpleMode();
                    }
                });
            }

            if($addRowBtn.length) {
                $addRowBtn.on('click', function(e) {
                    e.preventDefault();
                    addVariantRow({ variant_key: '', qty: '1', price: '' });
                });
            }

            $(document).on('click', '.import-variant-remove', function(e) {
                e.preventDefault();
                var $tr = $(this).closest('tr');
                $tr.remove();
                ensureAtLeastOneVariantRow();
                recalcVariantDraftTotals();
            });

            $(document).on('input', 'input[name="variant_import_qty[]"], input[name="variant_import_price[]"]', function() {
                recalcVariantDraftTotals();
            });
            $(document).on('input', 'input[name="simple_import_qty"], input[name="simple_import_price"]', function() {
                recalcSimpleDraftTotal();
            });

            // Initial load: restore selections from data-selected attributes.
            loadVariantOptions($product.val(), 'restore');
            recalcSimpleDraftTotal();
            $product.on('change', function() {
                $('.import-variant-select').attr('data-selected', '');
                loadVariantOptions($(this).val(), 'reset');
            });
        }

        function scheduleInit() {
            // Defer one tick so footer's Select2 init can run first.
            window.setTimeout(initImportVariantUI, 0);
        }

        if(document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', scheduleInit);
        } else {
            scheduleInit();
        }
    })();
</script>

<?php require_once('footer.php'); ?>
