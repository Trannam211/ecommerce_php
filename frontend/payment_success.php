<?php require_once('header.php'); ?>

<?php
$last_payment_id = isset($_SESSION['last_payment_id']) ? trim((string)$_SESSION['last_payment_id']) : '';
$last_payment = null;
$last_order_items = array();

if($last_payment_id !== '') {
    $statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE payment_id=? ORDER BY id DESC LIMIT 1");
    $statement->execute(array($last_payment_id));
    $last_payment = $statement->fetch(PDO::FETCH_ASSOC);

    if($last_payment) {
        $statement = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id=? ORDER BY id ASC");
        $statement->execute(array($last_payment_id));
        $last_order_items = $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}

$order_total_amount = 0;
if($last_payment) {
    if(isset($last_payment['order_total_amount']) && (float)$last_payment['order_total_amount'] > 0) {
        $order_total_amount = (float)$last_payment['order_total_amount'];
    } else {
        foreach($last_order_items as $order_item) {
            $order_total_amount += ((int)$order_item['quantity'] * (float)$order_item['unit_price']);
        }
    }
}
?>

<style>
    .payment-success-wrap {
        max-width: 860px;
        margin: 18px auto;
        border: 1px solid #dfe7f1;
        background: #fff;
        padding: 20px 18px;
        text-align: center;
    }

    .payment-success-title {
        margin: 0 0 10px;
        color: #1f2d3d;
        font-size: 24px;
        font-weight: 700;
    }

    .payment-success-text {
        margin: 0 0 20px;
        color: #4a5a6b;
        font-size: 14px;
        line-height: 1.6;
    }

    .payment-success-btn {
        min-width: 170px;
    }

    .payment-summary-box {
        margin-top: 18px;
        border: 1px solid #e4ebf5;
        background: #fafcff;
        padding: 14px;
        text-align: left;
    }

    .payment-summary-box h4 {
        margin: 0 0 10px;
        color: #1f2d3d;
        font-size: 18px;
        font-weight: 700;
    }

    .payment-summary-meta {
        margin-bottom: 10px;
        line-height: 1.7;
        color: #44586d;
    }

    .payment-summary-box table {
        margin-bottom: 0;
    }

    .payment-summary-box th,
    .payment-summary-box td {
        vertical-align: middle !important;
    }

    @media (max-width: 767px) {
        .payment-success-title {
            font-size: 20px;
        }

        .payment-success-wrap {
            padding: 16px 14px;
        }
    }
</style>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="payment-success-wrap">
                    <h3 class="payment-success-title">Chúc mừng! Thanh toán thành công.</h3>
                    <p class="payment-success-text">
                        Đơn hàng của bạn đã được ghi nhận trong hệ thống.<br>
                        Bạn có thể theo dõi trạng thái xử lý tại trang đơn hàng.
                    </p>

                    <?php if($last_payment): ?>
                        <div class="payment-summary-box">
                            <h4>Tóm tắt đơn hàng #<?php echo htmlspecialchars((string)$last_payment['payment_id'], ENT_QUOTES, 'UTF-8'); ?></h4>
                            <div class="payment-summary-meta">
                                <b>Khách hàng:</b> <?php echo htmlspecialchars((string)($last_payment['customer_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?><br>
                                <b>Email:</b> <?php echo htmlspecialchars((string)($last_payment['customer_email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?><br>
                                <b>Phương thức thanh toán:</b> <?php echo htmlspecialchars(format_payment_method_vi((string)($last_payment['payment_method'] ?? '')), ENT_QUOTES, 'UTF-8'); ?><br>
                                <b>Trạng thái thanh toán:</b> <?php echo htmlspecialchars(format_payment_status_vi((string)($last_payment['payment_status'] ?? '')), ENT_QUOTES, 'UTF-8'); ?><br>
                                <b>Trạng thái giao hàng:</b> <?php echo htmlspecialchars(format_shipping_status_vi((string)($last_payment['shipping_status'] ?? '')), ENT_QUOTES, 'UTF-8'); ?><br>
                                <b>Ngày đặt:</b> <?php echo htmlspecialchars((string)($last_payment['payment_date'] ?? ''), ENT_QUOTES, 'UTF-8'); ?><br>
                                <b>Tổng tiền:</b> <?php echo format_price_vnd($order_total_amount); ?>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Sản phẩm</th>
                                            <th>Size</th>
                                            <th>Màu</th>
                                            <th>Số lượng</th>
                                            <th>Đơn giá</th>
                                            <th>Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(count($last_order_items) === 0): ?>
                                            <tr>
                                                <td colspan="7" style="text-align:center;">Không có sản phẩm.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $order_idx = 0; foreach($last_order_items as $order_item): $order_idx++; ?>
                                                <tr>
                                                    <td><?php echo $order_idx; ?></td>
                                                    <td><?php echo htmlspecialchars((string)$order_item['product_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string)$order_item['size'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string)$order_item['color'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo (int)$order_item['quantity']; ?></td>
                                                    <td><?php echo format_price_vnd((float)$order_item['unit_price']); ?></td>
                                                    <td><?php echo format_price_vnd(((int)$order_item['quantity'] * (float)$order_item['unit_price'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>

                    <a href="customer-order.php" class="btn btn-success payment-success-btn">Xem đơn hàng</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>