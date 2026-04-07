<?php require_once('header.php'); ?>

<?php
$keyword = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$updated_flag = (isset($_GET['updated']) && $_GET['updated'] === '1');

$sql = "SELECT p_id, p_code, p_name, p_unit, p_qty, p_old_price, p_current_price, p_cost_price, p_profit_percent, p_low_stock_threshold
        FROM tbl_product";
$params = array();
if($keyword !== '') {
    $sql .= " WHERE p_name LIKE ? OR p_code LIKE ?";
    $params[] = '%'.$keyword.'%';
    $params[] = '%'.$keyword.'%';
}
$sql .= " ORDER BY p_id DESC";

$statement = $pdo->prepare($sql);
$statement->execute($params);
$product_rows = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Quản lý giá sản phẩm</h1>
    </div>
    <div class="content-header-right">
        <a href="product.php" class="btn btn-primary btn-sm">Quay lại sản phẩm</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php if($updated_flag): ?>
                <div class="alert alert-success">Cập nhật thông tin giá thành công.</div>
            <?php endif; ?>

            <div class="box box-info">
                <div class="box-body">
                    <form method="get" action="price-management.php" class="form-inline">
                        <div class="form-group" style="margin-right:8px;">
                            <label for="q" style="margin-right:6px;">Tìm sản phẩm</label>
                            <input type="text" id="q" name="q" class="form-control" style="min-width:260px;" placeholder="Tên hoặc mã sản phẩm" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Lọc</button>
                        <a href="price-management.php" class="btn btn-default btn-sm">Xóa lọc</a>
                    </form>
                </div>
            </div>

            <div class="box box-info">
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th width="10">#</th>
                                <th width="90">Mã SP</th>
                                <th>Tên sản phẩm</th>
                                <th width="70">Đơn vị</th>
                                <th width="115">Giá vốn</th>
                                <th width="90">% lãi</th>
                                <th width="125">Giá gợi ý</th>
                                <th width="125">Giá bán hiện tại</th>
                                <th width="95">Tồn kho</th>
                                <th width="95">Ngưỡng thấp</th>
                                <th width="85">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $idx = 0; foreach($product_rows as $row): $idx++; ?>
                                <?php
                                $cost_price = isset($row['p_cost_price']) ? (float)$row['p_cost_price'] : 0;
                                $profit_percent = isset($row['p_profit_percent']) ? (float)$row['p_profit_percent'] : 0;
                                if($profit_percent < 0) {
                                    $profit_percent = 0;
                                }
                                $suggested_price = round($cost_price * (1 + ($profit_percent / 100)), 0);
                                if($suggested_price < 0) {
                                    $suggested_price = 0;
                                }
                                ?>
                                <tr>
                                    <td><?php echo $idx; ?></td>
                                    <td><?php echo htmlspecialchars((string)$row['p_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string)$row['p_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string)$row['p_unit'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo format_price_vnd($cost_price); ?></td>
                                    <td><?php echo number_format($profit_percent, 2); ?>%</td>
                                    <td><?php echo format_price_vnd($suggested_price); ?></td>
                                    <td><?php echo format_price_vnd((float)$row['p_current_price']); ?></td>
                                    <td><?php echo (int)$row['p_qty']; ?></td>
                                    <td><?php echo (int)$row['p_low_stock_threshold']; ?></td>
                                    <td>
                                        <a href="price-management-edit.php?id=<?php echo (int)$row['p_id']; ?>" class="btn btn-warning btn-xs">Sửa</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>
