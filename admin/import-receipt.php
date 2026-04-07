<?php require_once('header.php'); ?>

<?php
function generate_import_receipt_code(PDO $pdo) {
    $max_try = 8;
    for($i=0;$i<$max_try;$i++) {
        $candidate = 'PN-'.date('Ymd-His').'-'.mt_rand(10, 99);
        $statement = $pdo->prepare("SELECT receipt_id FROM tbl_import_receipt WHERE receipt_code=? LIMIT 1");
        $statement->execute(array($candidate));
        if(!$statement->fetch(PDO::FETCH_ASSOC)) {
            return $candidate;
        }
        usleep(10000);
    }
    return 'PN-'.date('Ymd-His').'-'.mt_rand(100, 999);
}

$error_message = '';
$success_message = '';

if(isset($_POST['form_create'])) {
    $import_date_input = trim((string)($_POST['import_date'] ?? ''));
    $note = trim((string)($_POST['note'] ?? ''));

    $import_date = date('Y-m-d H:i:s');
    if($import_date_input !== '') {
        $ts = strtotime($import_date_input);
        if($ts !== false) {
            $import_date = date('Y-m-d H:i:s', $ts);
        }
    }

    $receipt_code = generate_import_receipt_code($pdo);
    $created_by = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : 0;
    $now = date('Y-m-d H:i:s');

    $statement = $pdo->prepare("INSERT INTO tbl_import_receipt (receipt_code, import_date, status, note, created_by, created_at, updated_at, completed_at) VALUES (?,?,?,?,?,?,?,NULL)");
    $statement->execute(array(
        $receipt_code,
        $import_date,
        'Draft',
        $note,
        $created_by,
        $now,
        $now
    ));

    $new_receipt_id = (int)$pdo->lastInsertId();
    safe_redirect('import-receipt-edit.php?id='.$new_receipt_id);
}

if(isset($_GET['deleted']) && $_GET['deleted'] === '1') {
    $success_message = 'Đã xóa phiếu nhập nháp.';
}
if(isset($_GET['completed']) && $_GET['completed'] === '1') {
    $success_message = 'Phiếu nhập đã được hoàn tất và cập nhật tồn kho.';
}

$statement = $pdo->prepare(
    "SELECT r.*, 
        COALESCE(SUM(i.import_qty), 0) AS total_qty,
        COALESCE(SUM(i.import_qty * i.import_price), 0) AS total_amount
     FROM tbl_import_receipt r
     LEFT JOIN tbl_import_receipt_item i ON i.receipt_id = r.receipt_id
     GROUP BY r.receipt_id
     ORDER BY r.receipt_id DESC"
);
$statement->execute();
$receipt_rows = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Quản lý phiếu nhập</h1>
    </div>
    <div class="content-header-right">
        <a href="product.php" class="btn btn-primary btn-sm">Quay lại sản phẩm</a>
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
                    <h3 class="box-title">Tạo phiếu nhập mới</h3>
                </div>
                <div class="box-body">
                    <form action="" method="post" class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Ngày nhập</label>
                            <div class="col-sm-4">
                                <input type="datetime-local" name="import_date" class="form-control" value="<?php echo date('Y-m-d\TH:i'); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Ghi chú</label>
                            <div class="col-sm-8">
                                <textarea name="note" class="form-control" rows="3" placeholder="Nhập ghi chú cho phiếu nhập"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-8">
                                <button type="submit" class="btn btn-success" name="form_create">Tạo phiếu</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Danh sách phiếu nhập</h3>
                </div>
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th width="10">#</th>
                                <th width="140">Mã phiếu</th>
                                <th width="140">Ngày nhập</th>
                                <th width="100">Trạng thái</th>
                                <th width="90">Số dòng</th>
                                <th width="110">Tổng SL</th>
                                <th width="140">Tổng tiền nhập</th>
                                <th>Ghi chú</th>
                                <th width="150">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $idx = 0; foreach($receipt_rows as $row): $idx++; ?>
                                <?php
                                $status = isset($row['status']) ? (string)$row['status'] : 'Draft';
                                $status_label = ($status === 'Completed') ? 'Hoàn tất' : 'Nháp';
                                $status_badge = ($status === 'Completed') ? 'label label-success' : 'label label-default';

                                $statement_count = $pdo->prepare("SELECT COUNT(*) AS total_item FROM tbl_import_receipt_item WHERE receipt_id=?");
                                $statement_count->execute(array((int)$row['receipt_id']));
                                $item_count_row = $statement_count->fetch(PDO::FETCH_ASSOC);
                                $item_count = $item_count_row ? (int)$item_count_row['total_item'] : 0;
                                ?>
                                <tr>
                                    <td><?php echo $idx; ?></td>
                                    <td><?php echo htmlspecialchars((string)$row['receipt_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string)$row['import_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><span class="<?php echo $status_badge; ?>"><?php echo $status_label; ?></span></td>
                                    <td><?php echo $item_count; ?></td>
                                    <td><?php echo (int)$row['total_qty']; ?></td>
                                    <td><?php echo format_price_vnd((float)$row['total_amount']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars((string)$row['note'], ENT_QUOTES, 'UTF-8')); ?></td>
                                    <td>
                                        <a href="import-receipt-edit.php?id=<?php echo (int)$row['receipt_id']; ?>" class="btn btn-primary btn-xs" style="margin-bottom:4px;">Chi tiết</a>
                                        <?php if($status !== 'Completed'): ?>
                                            <a href="#" class="btn btn-danger btn-xs" data-href="import-receipt-delete.php?id=<?php echo (int)$row['receipt_id']; ?>" data-toggle="modal" data-target="#confirm-delete">Xóa</a>
                                        <?php endif; ?>
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

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Xác nhận xóa</h4>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn xóa phiếu nhập này không?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                <a class="btn btn-danger btn-ok">Xóa</a>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
