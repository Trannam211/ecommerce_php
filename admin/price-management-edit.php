<?php require_once('header.php'); ?>

<?php
function admin_parse_decimal_number($value) {
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
    safe_redirect('price-management.php');
}

$product_id = (int)$_GET['id'];
if($product_id <= 0) {
    safe_redirect('price-management.php');
}

$error_message = '';
$success_message = '';

$statement = $pdo->prepare("SELECT * FROM tbl_product WHERE p_id=? LIMIT 1");
$statement->execute(array($product_id));
$product = $statement->fetch(PDO::FETCH_ASSOC);
if(!$product) {
    safe_redirect('price-management.php');
}

if(isset($_POST['form1'])) {
    $p_code = strtoupper(trim((string)($_POST['p_code'] ?? '')));
    $p_unit = trim((string)($_POST['p_unit'] ?? 'sp'));
    $p_cost_price = admin_parse_decimal_number($_POST['p_cost_price'] ?? '0');
    $p_profit_percent = admin_parse_decimal_number($_POST['p_profit_percent'] ?? '0');
    $p_current_price = admin_parse_decimal_number($_POST['p_current_price'] ?? '0');
    $p_old_price = admin_parse_decimal_number($_POST['p_old_price'] ?? '0');
    $p_low_stock_threshold = (int)preg_replace('/[^0-9-]/', '', (string)($_POST['p_low_stock_threshold'] ?? '0'));

    if($p_code === '') {
        $error_message .= "Mã sản phẩm không được để trống.<br>";
    }
    if($p_unit === '') {
        $p_unit = 'sp';
    }
    if($p_cost_price < 0) {
        $error_message .= "Giá vốn không hợp lệ.<br>";
    }
    if($p_profit_percent < 0) {
        $error_message .= "% lợi nhuận không hợp lệ.<br>";
    }
    if($p_low_stock_threshold < 0) {
        $p_low_stock_threshold = 0;
    }

    $statement = $pdo->prepare("SELECT p_id FROM tbl_product WHERE p_code=? AND p_id<>? LIMIT 1");
    $statement->execute(array($p_code, $product_id));
    if($statement->fetch(PDO::FETCH_ASSOC)) {
        $error_message .= "Mã sản phẩm đã tồn tại.<br>";
    }

    $auto_calc = isset($_POST['auto_calc']) && $_POST['auto_calc'] === '1';
    $suggested_price = round($p_cost_price * (1 + ($p_profit_percent / 100)), 0);
    if($suggested_price < 0) {
        $suggested_price = 0;
    }

    if($auto_calc) {
        $p_current_price = $suggested_price;
    }

    if($p_current_price <= 0) {
        $error_message .= "Giá bán phải lớn hơn 0.<br>";
    }
    if($p_old_price < 0) {
        $p_old_price = 0;
    }

    if($error_message === '') {
        $statement = $pdo->prepare("UPDATE tbl_product SET p_code=?, p_unit=?, p_cost_price=?, p_profit_percent=?, p_current_price=?, p_old_price=?, p_low_stock_threshold=? WHERE p_id=?");
        $statement->execute(array(
            $p_code,
            $p_unit,
            $p_cost_price,
            $p_profit_percent,
            $p_current_price,
            $p_old_price,
            $p_low_stock_threshold,
            $product_id
        ));

        $statement = $pdo->prepare("SELECT * FROM tbl_product WHERE p_id=? LIMIT 1");
        $statement->execute(array($product_id));
        $product = $statement->fetch(PDO::FETCH_ASSOC);

        $success_message = 'Đã cập nhật thông tin giá sản phẩm.';
    }
}

$current_cost_price = isset($product['p_cost_price']) ? (float)$product['p_cost_price'] : 0;
$current_profit_percent = isset($product['p_profit_percent']) ? (float)$product['p_profit_percent'] : 0;
$current_suggested_price = round($current_cost_price * (1 + ($current_profit_percent / 100)), 0);
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Chỉnh sửa giá sản phẩm</h1>
    </div>
    <div class="content-header-right">
        <a href="price-management.php?updated=1" class="btn btn-primary btn-sm">Quay lại danh sách</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php if($error_message !== ''): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if($success_message !== ''): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <div class="box box-info">
                <div class="box-body">
                    <form action="" method="post" class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Tên sản phẩm</label>
                            <div class="col-sm-8" style="padding-top:7px;">
                                <b><?php echo htmlspecialchars((string)$product['p_name'], ENT_QUOTES, 'UTF-8'); ?></b>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Mã sản phẩm</label>
                            <div class="col-sm-4">
                                <input type="text" name="p_code" class="form-control" value="<?php echo htmlspecialchars((string)$product['p_code'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Đơn vị</label>
                            <div class="col-sm-4">
                                <input type="text" name="p_unit" class="form-control" value="<?php echo htmlspecialchars((string)$product['p_unit'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Giá vốn</label>
                            <div class="col-sm-4">
                                <input type="text" name="p_cost_price" class="form-control currency-input" inputmode="numeric" value="<?php echo number_format($current_cost_price, 0, '.', ','); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">% Lợi nhuận</label>
                            <div class="col-sm-4">
                                <input type="text" name="p_profit_percent" class="form-control" value="<?php echo number_format($current_profit_percent, 2, '.', ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Giá gợi ý</label>
                            <div class="col-sm-8" style="padding-top:7px;">
                                <span id="suggested_price_preview"><?php echo format_price_vnd($current_suggested_price); ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Giá bán</label>
                            <div class="col-sm-4">
                                <input type="text" name="p_current_price" class="form-control currency-input" inputmode="numeric" value="<?php echo number_format((float)$product['p_current_price'], 0, '.', ','); ?>">
                            </div>
                            <div class="col-sm-4" style="padding-top:7px;">
                                <label style="font-weight:500;">
                                    <input type="checkbox" name="auto_calc" value="1" checked> Tự động lấy theo giá gợi ý
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Giá niêm yết cũ</label>
                            <div class="col-sm-4">
                                <input type="text" name="p_old_price" class="form-control currency-input" inputmode="numeric" value="<?php echo number_format((float)$product['p_old_price'], 0, '.', ','); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Ngưỡng tồn kho thấp</label>
                            <div class="col-sm-4">
                                <input type="number" min="0" name="p_low_stock_threshold" class="form-control" value="<?php echo (int)$product['p_low_stock_threshold']; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Tồn kho hiện tại</label>
                            <div class="col-sm-8" style="padding-top:7px;">
                                <?php echo (int)$product['p_qty']; ?> <?php echo htmlspecialchars((string)$product['p_unit'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-8">
                                <button type="submit" class="btn btn-success" name="form1">Lưu thay đổi</button>
                                <a href="price-management.php" class="btn btn-default">Hủy</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function() {
    function parseMoney(value) {
        var digits = String(value || '').replace(/[^0-9]/g, '');
        return digits ? parseInt(digits, 10) : 0;
    }

    function formatMoney(value) {
        var num = Math.max(0, parseInt(value || 0, 10));
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') + ' VND';
    }

    function updateSuggestedPrice() {
        var costInput = document.querySelector('input[name="p_cost_price"]');
        var profitInput = document.querySelector('input[name="p_profit_percent"]');
        var preview = document.getElementById('suggested_price_preview');
        if(!costInput || !profitInput || !preview) {
            return;
        }
        var cost = parseMoney(costInput.value);
        var profit = parseFloat(String(profitInput.value || '0').replace(',', '.'));
        if(isNaN(profit) || profit < 0) {
            profit = 0;
        }
        var suggested = Math.round(cost * (1 + (profit / 100)));
        preview.textContent = formatMoney(suggested);
    }

    document.addEventListener('input', function(e) {
        if(e.target && (e.target.name === 'p_cost_price' || e.target.name === 'p_profit_percent')) {
            updateSuggestedPrice();
        }
    });

    updateSuggestedPrice();
})();
</script>

<?php require_once('footer.php'); ?>
