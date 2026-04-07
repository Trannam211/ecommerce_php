<?php require_once('header.php'); ?>

<?php
function inventory_valid_ymd($value) {
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$value) === 1;
}

$today = date('Y-m-d');
$first_day_month = date('Y-m-01');

$from_date = isset($_GET['from_date']) ? trim((string)$_GET['from_date']) : $first_day_month;
$to_date = isset($_GET['to_date']) ? trim((string)$_GET['to_date']) : $today;
$snapshot_date = isset($_GET['snapshot_date']) ? trim((string)$_GET['snapshot_date']) : '';
$warning_threshold = isset($_GET['warning_threshold']) ? (int)$_GET['warning_threshold'] : 5;
if($warning_threshold < 1) {
    $warning_threshold = 1;
}

if(!inventory_valid_ymd($from_date)) {
    $from_date = $first_day_month;
}
if(!inventory_valid_ymd($to_date)) {
    $to_date = $today;
}
if($snapshot_date !== '' && !inventory_valid_ymd($snapshot_date)) {
    $snapshot_date = '';
}
if($from_date > $to_date) {
    $tmp = $from_date;
    $from_date = $to_date;
    $to_date = $tmp;
}

$statement = $pdo->prepare("SELECT p_id, p_code, p_name, p_unit, p_qty, p_cost_price, p_current_price, p_low_stock_threshold FROM tbl_product ORDER BY p_name ASC");
$statement->execute();
$product_rows = $statement->fetchAll(PDO::FETCH_ASSOC);

$metrics = array();
$total_current_qty = 0;
$total_inventory_cost = 0;

foreach($product_rows as $row) {
    $p_id = (int)$row['p_id'];
    $current_qty = (int)$row['p_qty'];
    $cost_price = (float)$row['p_cost_price'];

    $metrics[$p_id] = array(
        'import_qty' => 0,
        'import_value' => 0,
        'export_qty' => 0,
        'export_revenue' => 0,
        'imports_after_snapshot' => 0,
        'exports_after_snapshot' => 0
    );

    $total_current_qty += $current_qty;
    $total_inventory_cost += ($current_qty * $cost_price);
}

$range_import_qty = 0;
$range_export_qty = 0;
$range_import_value = 0;
$range_export_revenue = 0;

$statement = $pdo->prepare("SELECT i.p_id, i.import_qty, i.import_price, r.import_date
                            FROM tbl_import_receipt_item i
                            INNER JOIN tbl_import_receipt r ON r.receipt_id = i.receipt_id
                            WHERE r.status='Completed'");
$statement->execute();
$import_rows = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach($import_rows as $row) {
    $p_id = (int)$row['p_id'];
    if(!isset($metrics[$p_id])) {
        continue;
    }

    $day = substr((string)$row['import_date'], 0, 10);
    $qty = (int)$row['import_qty'];
    $price = (float)$row['import_price'];
    $value = $qty * $price;

    if($day >= $from_date && $day <= $to_date) {
        $metrics[$p_id]['import_qty'] += $qty;
        $metrics[$p_id]['import_value'] += $value;
        $range_import_qty += $qty;
        $range_import_value += $value;
    }

    if($snapshot_date !== '' && $day > $snapshot_date) {
        $metrics[$p_id]['imports_after_snapshot'] += $qty;
    }
}

$statement = $pdo->prepare("SELECT o.product_id, o.quantity, o.unit_price, p.payment_date, p.shipping_status
                            FROM tbl_order o
                            INNER JOIN tbl_payment p ON p.payment_id = o.payment_id");
$statement->execute();
$order_rows = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach($order_rows as $row) {
    $p_id = (int)$row['product_id'];
    if(!isset($metrics[$p_id])) {
        continue;
    }

    $shipping_status = normalize_shipping_status_code((string)$row['shipping_status']);
    if($shipping_status === 'Canceled') {
        continue;
    }

    $day = substr((string)$row['payment_date'], 0, 10);
    $qty = (int)$row['quantity'];
    $line_revenue = $qty * (float)$row['unit_price'];

    if($day >= $from_date && $day <= $to_date) {
        $metrics[$p_id]['export_qty'] += $qty;
        $metrics[$p_id]['export_revenue'] += $line_revenue;
        $range_export_qty += $qty;
        $range_export_revenue += $line_revenue;
    }

    if($snapshot_date !== '' && $day > $snapshot_date) {
        $metrics[$p_id]['exports_after_snapshot'] += $qty;
    }
}

$report_rows = array();
$low_stock_rows = array();

foreach($product_rows as $row) {
    $p_id = (int)$row['p_id'];
    $current_qty = (int)$row['p_qty'];
    $configured_threshold = isset($row['p_low_stock_threshold']) ? (int)$row['p_low_stock_threshold'] : 0;
    $effective_threshold = $configured_threshold > 0 ? $configured_threshold : $warning_threshold;

    $snapshot_stock = null;
    if($snapshot_date !== '') {
        $snapshot_stock = $current_qty + (int)$metrics[$p_id]['exports_after_snapshot'] - (int)$metrics[$p_id]['imports_after_snapshot'];
        if($snapshot_stock < 0) {
            $snapshot_stock = 0;
        }
    }

    $report_row = array(
        'product' => $row,
        'metric' => $metrics[$p_id],
        'snapshot_stock' => $snapshot_stock,
        'effective_threshold' => $effective_threshold
    );
    $report_rows[] = $report_row;

    if($current_qty <= $effective_threshold) {
        $low_stock_rows[] = $report_row;
    }
}

$total_low_stock = count($low_stock_rows);
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Báo cáo tồn kho</h1>
    </div>
</section>

<style>
.inventory-filter-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 10px 12px;
    align-items: end;
}

.inventory-filter-form .form-group {
    margin: 0;
}

.inventory-filter-form label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
}

.inventory-filter-actions {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

.inventory-summary-sections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 12px;
    margin-bottom: 14px;
}

.inventory-summary-section {
    border: 1px solid #d9e1ee;
    background: #fff;
}

.inventory-summary-section-header {
    padding: 10px 12px;
    border-bottom: 1px solid #eef2f7;
    font-size: 13px;
    font-weight: 700;
    color: #1d2b3a;
    display: flex;
    align-items: center;
    gap: 8px;
}

.inventory-summary-section-header .fa {
    opacity: 0.75;
}

.inventory-summary-section-body {
    padding: 12px;
}

.inventory-summary-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.inventory-summary-card {
    border: 1px solid #d9e1ee;
    background: #fff;
    padding: 12px;
}

.inventory-summary-card .label-title {
    font-size: 12px;
    color: #5f7088;
    margin-bottom: 6px;
    font-weight: 600;
}

.inventory-summary-card .label-value {
    font-size: 20px;
    color: #1d2b3a;
    font-weight: 700;
}

.inventory-subvalue {
    font-size: 12px;
    margin-top: 2px;
}

.inventory-detail-table th,
.inventory-detail-table td {
    vertical-align: middle;
}

.inventory-warning-hint {
    margin-bottom: 8px;
}

.inventory-warning-table td.dataTables_empty {
    text-align: center;
    padding: 16px 12px;
    font-style: italic;
}

.inventory-warning-table td.dataTables_empty:before {
    font-family: FontAwesome;
    content: "\f05a";
    margin-right: 6px;
    opacity: 0.7;
}
</style>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Bộ lọc báo cáo</h3>
                </div>
                <div class="box-body">
                    <form method="get" action="inventory-report.php" class="inventory-filter-form">
                        <div class="form-group">
                            <label for="from_date">Từ ngày</label>
                            <input type="date" id="from_date" name="from_date" class="form-control" value="<?php echo htmlspecialchars($from_date, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="to_date">Đến ngày</label>
                            <input type="date" id="to_date" name="to_date" class="form-control" value="<?php echo htmlspecialchars($to_date, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="snapshot_date">Xem tồn kho tại ngày</label>
                            <input type="date" id="snapshot_date" name="snapshot_date" class="form-control" value="<?php echo htmlspecialchars($snapshot_date, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="warning_threshold">Ngưỡng cảnh báo mặc định</label>
                            <input type="number" min="1" id="warning_threshold" name="warning_threshold" class="form-control" value="<?php echo (int)$warning_threshold; ?>">
                        </div>
                        <div class="inventory-filter-actions">
                            <button type="submit" class="btn btn-primary btn-sm">Xem báo cáo</button>
                            <a href="inventory-report.php" class="btn btn-default btn-sm">Mặc định</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="inventory-summary-sections">
                <div class="inventory-summary-section">
                    <div class="inventory-summary-section-header">
                        <i class="fa fa-cubes" aria-hidden="true"></i>
                        <span>Thông tin kho</span>
                    </div>
                    <div class="inventory-summary-section-body">
                        <div class="inventory-summary-grid">
                            <div class="inventory-summary-card">
                                <div class="label-title">Tổng tồn kho</div>
                                <div class="label-value"><?php echo number_format($total_current_qty); ?></div>
                            </div>
                            <div class="inventory-summary-card">
                                <div class="label-title">Giá trị tồn kho (giá vốn)</div>
                                <div class="label-value" style="font-size:18px;"><?php echo format_price_vnd($total_inventory_cost); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="inventory-summary-section">
                    <div class="inventory-summary-section-header">
                        <i class="fa fa-sign-in" aria-hidden="true"></i>
                        <span>Nhập kho (trong kỳ)</span>
                    </div>
                    <div class="inventory-summary-section-body">
                        <div class="inventory-summary-grid">
                            <div class="inventory-summary-card">
                                <div class="label-title">Tổng số lượng nhập</div>
                                <div class="label-value"><?php echo number_format($range_import_qty); ?></div>
                            </div>
                            <div class="inventory-summary-card">
                                <div class="label-title">Tổng giá trị nhập</div>
                                <div class="label-value" style="font-size:18px;"><?php echo format_price_vnd($range_import_value); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="inventory-summary-section">
                    <div class="inventory-summary-section-header">
                        <i class="fa fa-sign-out" aria-hidden="true"></i>
                        <span>Xuất kho (trong kỳ)</span>
                    </div>
                    <div class="inventory-summary-section-body">
                        <div class="inventory-summary-grid">
                            <div class="inventory-summary-card">
                                <div class="label-title">Tổng số lượng xuất</div>
                                <div class="label-value"><?php echo number_format($range_export_qty); ?></div>
                            </div>
                            <div class="inventory-summary-card">
                                <div class="label-title">Tổng doanh thu</div>
                                <div class="label-value" style="font-size:18px;"><?php echo format_price_vnd($range_export_revenue); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Chi tiết nhập/xuất theo sản phẩm</h3>
                </div>
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-hover table-striped inventory-detail-table">
                        <thead>
                            <tr>
                                <th>Tên sản phẩm</th>
                                <th width="140">Tồn hiện tại</th>
                                <th width="110">Nhập trong kỳ</th>
                                <th width="110">Xuất trong kỳ</th>
                                <th width="140">Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($report_rows as $entry): ?>
                                <?php
                                $product = $entry['product'];
                                $metric = $entry['metric'];
                                $current_qty = (int)$product['p_qty'];
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((string)$product['p_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-right" data-order="<?php echo $current_qty; ?>">
                                        <div><?php echo $current_qty; ?></div>
                                        <?php if($snapshot_date !== ''): ?>
                                            <div class="text-muted inventory-subvalue">
                                                Tại <?php echo htmlspecialchars($snapshot_date, ENT_QUOTES, 'UTF-8'); ?>: <?php echo (int)$entry['snapshot_stock']; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right" data-order="<?php echo (int)$metric['import_qty']; ?>"><?php echo (int)$metric['import_qty']; ?></td>
                                    <td class="text-right" data-order="<?php echo (int)$metric['export_qty']; ?>"><?php echo (int)$metric['export_qty']; ?></td>
                                    <td class="text-right" data-order="<?php echo (float)$metric['export_revenue']; ?>"><?php echo format_price_vnd((float)$metric['export_revenue']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">Cảnh báo tồn kho thấp</h3>
                </div>
                <div class="box-body table-responsive">
                    <?php if(count($low_stock_rows) === 0): ?>
                        <div class="inventory-warning-hint text-success">
                            <i class="fa fa-check-circle" aria-hidden="true"></i>
                            Tồn kho đang ở mức an toàn.
                        </div>
                    <?php endif; ?>
                    <table id="example2" class="table table-bordered table-hover table-striped inventory-warning-table" data-empty-table="Không có sản phẩm nào dưới ngưỡng cảnh báo.">
                        <thead>
                            <tr>
                                <th width="10">#</th>
                                <th width="95">Mã SP</th>
                                <th>Tên sản phẩm</th>
                                <th width="70">Đơn vị</th>
                                <th width="95">Tồn hiện tại</th>
                                <th width="95">Ngưỡng thấp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($low_stock_rows) > 0): ?>
                                <?php $low_idx = 0; foreach($low_stock_rows as $entry): $low_idx++; ?>
                                    <?php $product = $entry['product']; ?>
                                    <tr>
                                        <td><?php echo $low_idx; ?></td>
                                        <td><?php echo htmlspecialchars((string)$product['p_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)$product['p_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)$product['p_unit'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span class="label label-danger"><?php echo (int)$product['p_qty']; ?></span></td>
                                        <td><?php echo (int)$entry['effective_threshold']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>
