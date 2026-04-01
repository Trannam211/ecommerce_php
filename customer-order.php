<?php require_once('header.php'); ?>

<?php
// Check if the customer is logged in or not
if(!isset($_SESSION['customer'])) {
    safe_redirect(BASE_URL.'logout.php');
} else {
    // If customer is logged in, but admin make him inactive, then force logout this user.
    $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id=? AND cust_status=?");
    $statement->execute(array($_SESSION['customer']['cust_id'],0));
    $total = $statement->rowCount();
    if($total) {
        safe_redirect(BASE_URL.'logout.php');
    }
}

$customer_order_filter = isset($_GET['status']) ? trim((string)$_GET['status']) : 'all';
$allowed_customer_filters = array('all', 'pending', 'shipping', 'completed', 'canceled');
if(!in_array($customer_order_filter, $allowed_customer_filters, true)) {
    $customer_order_filter = 'all';
}

$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE customer_email=? ORDER BY id DESC");
$statement->execute(array($_SESSION['customer']['cust_email']));
$all_customer_orders = $statement->fetchAll(PDO::FETCH_ASSOC);

$customer_order_counts = array(
    'all' => count($all_customer_orders),
    'pending' => 0,
    'shipping' => 0,
    'completed' => 0,
    'canceled' => 0
);
$filtered_customer_orders = array();
foreach($all_customer_orders as $customer_order_row) {
    $status_code = normalize_shipping_status_code($customer_order_row['shipping_status']);
    if($status_code === 'Pending') {
        $customer_order_counts['pending']++;
    } elseif($status_code === 'Shipping') {
        $customer_order_counts['shipping']++;
    } elseif($status_code === 'Completed') {
        $customer_order_counts['completed']++;
    } elseif($status_code === 'Canceled') {
        $customer_order_counts['canceled']++;
    }

    if($customer_order_filter === 'all') {
        $filtered_customer_orders[] = $customer_order_row;
    } elseif($customer_order_filter === 'pending' && $status_code === 'Pending') {
        $filtered_customer_orders[] = $customer_order_row;
    } elseif($customer_order_filter === 'shipping' && $status_code === 'Shipping') {
        $filtered_customer_orders[] = $customer_order_row;
    } elseif($customer_order_filter === 'completed' && $status_code === 'Completed') {
        $filtered_customer_orders[] = $customer_order_row;
    } elseif($customer_order_filter === 'canceled' && $status_code === 'Canceled') {
        $filtered_customer_orders[] = $customer_order_row;
    }
}

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) {
    $page = 1;
}
$total_filtered_orders = count($filtered_customer_orders);
$lastpage = (int)ceil($total_filtered_orders / $limit);
if($lastpage < 1) {
    $lastpage = 1;
}
if($page > $lastpage) {
    $page = $lastpage;
}
$start = ($page - 1) * $limit;
$paged_customer_orders = array_slice($filtered_customer_orders, $start, $limit);

$targetpage = BASE_URL.'customer-order.php?status='.$customer_order_filter;
$adjacents = 5;
$prev = $page - 1;
$next = $page + 1;
$lpm1 = $lastpage - 1;
$pagination = "";
if($lastpage > 1)
{
    $pagination .= "<div class=\"pagination\">";
    if ($page > 1)
        $pagination.= "<a href=\"$targetpage&page=$prev\">&#171; Trang trước</a>";
    else
        $pagination.= "<span class=\"disabled\">&#171; Trang trước</span>";

    if ($lastpage < 7 + ($adjacents * 2))
    {
        for ($counter = 1; $counter <= $lastpage; $counter++)
        {
            if ($counter == $page)
                $pagination.= "<span class=\"current\">$counter</span>";
            else
                $pagination.= "<a href=\"$targetpage&page=$counter\">$counter</a>";
        }
    }
    elseif($lastpage > 5 + ($adjacents * 2))
    {
        if($page < 1 + ($adjacents * 2))
        {
            for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
            {
                if ($counter == $page)
                    $pagination.= "<span class=\"current\">$counter</span>";
                else
                    $pagination.= "<a href=\"$targetpage&page=$counter\">$counter</a>";
            }
            $pagination.= "...";
            $pagination.= "<a href=\"$targetpage&page=$lpm1\">$lpm1</a>";
            $pagination.= "<a href=\"$targetpage&page=$lastpage\">$lastpage</a>";
        }
        elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
        {
            $pagination.= "<a href=\"$targetpage&page=1\">1</a>";
            $pagination.= "<a href=\"$targetpage&page=2\">2</a>";
            $pagination.= "...";
            for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
            {
                if ($counter == $page)
                    $pagination.= "<span class=\"current\">$counter</span>";
                else
                    $pagination.= "<a href=\"$targetpage&page=$counter\">$counter</a>";
            }
            $pagination.= "...";
            $pagination.= "<a href=\"$targetpage&page=$lpm1\">$lpm1</a>";
            $pagination.= "<a href=\"$targetpage&page=$lastpage\">$lastpage</a>";
        }
        else
        {
            $pagination.= "<a href=\"$targetpage&page=1\">1</a>";
            $pagination.= "<a href=\"$targetpage&page=2\">2</a>";
            $pagination.= "...";
            for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
            {
                if ($counter == $page)
                    $pagination.= "<span class=\"current\">$counter</span>";
                else
                    $pagination.= "<a href=\"$targetpage&page=$counter\">$counter</a>";
            }
        }
    }
    if ($page < $counter - 1)
        $pagination.= "<a href=\"$targetpage&page=$next\">Trang sau &#187;</a>";
    else
        $pagination.= "<span class=\"disabled\">Trang sau &#187;</span>";
    $pagination.= "</div>\n";
}
?>

<style>
    .order-page-compact {
        padding: 0 0 18px;
        background: linear-gradient(180deg, #f3f7ff 0px, #f5f5f5 180px);
    }

    .customer-order-card {
        border: none;
        background: transparent;
        padding: 0;
        box-shadow: none;
    }

    .customer-order-card h3 {
        margin: 0 0 12px;
        color: #1f3a8a;
        font-size: 24px;
        font-weight: 700;
    }

    .order-filter-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 16px;
        padding: 10px;
        border: 1px solid #f0f0f0;
        background: #fff;
    }

    .order-filter-nav a {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border: 1px solid #f0f0f0;
        border-radius: 2px;
        color: #666;
        background: #fafafa;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        line-height: 1;
        transition: all 0.2s ease;
    }

    .order-filter-nav a:hover {
        background: #edf4ff;
        border-color: #cfe0ff;
        color: #2563eb;
    }

    .order-filter-nav a.active {
        background: #edf4ff;
        border-color: #cfe0ff;
        color: #2563eb;
    }

    .order-filter-count {
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        color: #2563eb;
        background: #fff;
        border: 1px solid #cfe0ff;
    }

    .order-filter-nav a.active .order-filter-count {
        color: #2563eb;
        background: #fff;
    }

    .order-product-item {
        padding: 9px 0;
        border-bottom: 1px solid #f3f3f3;
    }

    .order-product-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .order-product-thumb {
        width: 64px;
        height: 64px;
        border: 1px solid #e5e7eb;
        background: #f8fafc;
        flex-shrink: 0;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .order-product-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .order-product-thumb-placeholder {
        font-size: 11px;
        color: #9ca3af;
        font-weight: 600;
        text-transform: uppercase;
    }

    .order-product-info {
        flex: 1;
        min-width: 0;
    }

    .order-product-item:first-child {
        padding-top: 0;
    }

    .order-product-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .order-product-name {
        font-weight: 600;
        font-size: 14px;
        color: #333;
        line-height: 1.4;
    }

    .order-product-qty {
        margin-top: 3px;
        font-size: 12px;
        color: #6b7280;
    }

    .customer-order-item-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 10px;
        border-bottom: 1px solid #f5f5f5;
        padding-bottom: 10px;
        margin-bottom: 10px;
    }

    .customer-order-item-title {
        font-size: 14px;
        font-weight: 700;
        color: #222;
    }

    .customer-order-item-status {
        display: inline-flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 8px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
        border-radius: 2px;
        border: 1px solid transparent;
    }

    .status-badge.payment-pending,
    .status-badge.shipping-pending {
        color: #555;
        background: #f8f8f8;
        border-color: #e3e3e3;
    }

    .status-badge.payment-completed,
    .status-badge.shipping-completed {
        color: #169a58;
        background: #edfff4;
        border-color: #bde9cd;
    }

    .status-badge.shipping-shipping {
        color: #d88705;
        background: #fff7e8;
        border-color: #ffe2ad;
    }

    .status-badge.shipping-canceled {
        color: #dd4b39;
        background: #fff1ef;
        border-color: #ffc9c2;
    }

    .customer-order-item-footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px dashed #d6e3fb;
    }

    .customer-order-total-box {
        display: inline-flex;
        align-items: baseline;
        gap: 8px;
    }

    .order-total-label {
        font-size: 12px;
        color: #777;
    }

    .order-payment-total {
        font-size: 20px;
        font-weight: 700;
        color: #1f3a8a;
        white-space: nowrap;
    }

    .order-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-width: 124px;
        margin: 0;
        padding: 8px 14px;
        border-radius: 2px;
        font-size: 12px;
        font-weight: 700;
    }

    .order-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 8px;
    }

    .btn-order-primary {
        color: #fff;
        background: #337ab7;
        border-color: #2e6da4;
    }

    .btn-order-primary:hover,
    .btn-order-primary:focus {
        color: #fff;
        background: #286090;
        border-color: #204d74;
    }

    .btn-order-danger-outline {
        color: #dd4b39;
        background: #fff;
        border: 1px solid #f0c3be;
    }

    .btn-order-danger-outline:hover,
    .btn-order-danger-outline:focus {
        color: #fff;
        background: #dd4b39;
        border-color: #c23321;
    }

    .customer-order-pagination {
        margin-top: 16px;
        padding: 10px;
        background: #fff;
        border: 1px solid #f0f0f0;
    }

    .customer-order-pagination .pagination {
        margin: 0;
    }

    .customer-order-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .customer-order-item {
        border: 1px solid #f0f0f0;
        background: #fff;
        padding: 14px;
        box-shadow: 0 1px 0 rgba(0, 0, 0, 0.03);
    }

    .customer-order-item-products {
        min-width: 0;
    }

    .customer-order-empty {
        border: 1px dashed #cfe0ff;
        background: #f5f9ff;
        padding: 18px;
        text-align: center;
        color: #1f3a8a;
    }

    @media (max-width: 767px) {
        .order-product-row {
            align-items: flex-start;
        }

        .customer-order-item-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .customer-order-item-status {
            justify-content: flex-start;
        }

        .customer-order-item-footer {
            flex-direction: column;
            align-items: flex-start;
        }

        .order-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .order-actions .btn {
            width: 100%;
            min-width: 0;
        }

        .order-payment-total {
            font-size: 18px;
        }
    }
</style>

<div class="page order-page-compact">
    <div class="container">
        <div class="account-layout">
            <div class="account-sidebar-col">
                <?php require_once('customer-sidebar.php'); ?>
            </div>
            <div class="account-content-col">
                <div class="user-content account-content-card customer-order-card">
                    <h3>Đơn hàng của tôi</h3>
                    <div class="order-filter-nav">
                        <a href="customer-order.php?status=all" class="<?php echo ($customer_order_filter === 'all') ? 'active' : ''; ?>"><span>Tất cả</span><span class="order-filter-count"><?php echo $customer_order_counts['all']; ?></span></a>
                        <a href="customer-order.php?status=pending" class="<?php echo ($customer_order_filter === 'pending') ? 'active' : ''; ?>"><span>Chờ xác nhận</span><span class="order-filter-count"><?php echo $customer_order_counts['pending']; ?></span></a>
                        <a href="customer-order.php?status=shipping" class="<?php echo ($customer_order_filter === 'shipping') ? 'active' : ''; ?>"><span>Đang giao</span><span class="order-filter-count"><?php echo $customer_order_counts['shipping']; ?></span></a>
                        <a href="customer-order.php?status=completed" class="<?php echo ($customer_order_filter === 'completed') ? 'active' : ''; ?>"><span>Đã giao</span><span class="order-filter-count"><?php echo $customer_order_counts['completed']; ?></span></a>
                        <a href="customer-order.php?status=canceled" class="<?php echo ($customer_order_filter === 'canceled') ? 'active' : ''; ?>"><span>Đã hủy</span><span class="order-filter-count"><?php echo $customer_order_counts['canceled']; ?></span></a>
                    </div>
                    <div class="customer-order-list">
                        <?php if(count($paged_customer_orders) === 0): ?>
                            <div class="customer-order-empty">Không có đơn hàng nào.</div>
                        <?php else: ?>
                            <?php foreach ($paged_customer_orders as $row): ?>
                                <?php
                                    $order_total_value = isset($row['order_total_amount']) ? (float)$row['order_total_amount'] : (float)$row['paid_amount'];
                                    if($order_total_value <= 0) {
                                        $order_total_value = (float)$row['paid_amount'];
                                    }

                                    $payment_status_code = trim((string)$row['payment_status']);
                                    $payment_badge_class = ($payment_status_code === 'Completed' || $payment_status_code === 'Paid')
                                        ? 'payment-completed'
                                        : 'payment-pending';

                                    $shipping_status_code = normalize_shipping_status_code($row['shipping_status']);
                                    $shipping_badge_class = 'shipping-pending';
                                    if($shipping_status_code === 'Shipping') {
                                        $shipping_badge_class = 'shipping-shipping';
                                    } elseif($shipping_status_code === 'Completed') {
                                        $shipping_badge_class = 'shipping-completed';
                                    } elseif($shipping_status_code === 'Canceled') {
                                        $shipping_badge_class = 'shipping-canceled';
                                    }

                                    $statement1 = $pdo->prepare("SELECT o.*, p.p_featured_photo FROM tbl_order o LEFT JOIN tbl_product p ON o.product_id = p.p_id WHERE o.payment_id=? ORDER BY o.id ASC");
                                    $statement1->execute(array($row['payment_id']));
                                    $result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <div class="customer-order-item">
                                    <div class="customer-order-item-header">
                                        <div class="customer-order-item-title">Sản phẩm đã đặt</div>
                                        <div class="customer-order-item-status">
                                            <span class="status-badge <?php echo $shipping_badge_class; ?>">
                                                <?php echo format_shipping_status_vi($shipping_status_code); ?>
                                            </span>
                                            <span class="status-badge <?php echo $payment_badge_class; ?>">
                                                <?php echo format_payment_status_vi($payment_status_code); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="customer-order-item-products">
                                        <?php if(count($result1) === 0): ?>
                                            <div class="order-product-item">
                                                <div class="order-product-name">Không có sản phẩm.</div>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($result1 as $row1): ?>
                                                <?php
                                                    $product_photo = isset($row1['p_featured_photo']) ? trim((string)$row1['p_featured_photo']) : '';
                                                    $product_qty = isset($row1['quantity']) ? (int)$row1['quantity'] : 0;
                                                ?>
                                                <div class="order-product-item">
                                                    <div class="order-product-row">
                                                        <div class="order-product-thumb">
                                                            <?php if($product_photo !== ''): ?>
                                                                <img src="assets/uploads/<?php echo htmlspecialchars($product_photo, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string)$row1['product_name'], ENT_QUOTES, 'UTF-8'); ?>">
                                                            <?php else: ?>
                                                                <span class="order-product-thumb-placeholder">No image</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="order-product-info">
                                                            <div class="order-product-name"><?php echo htmlspecialchars((string)$row1['product_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                            <div class="order-product-qty">Số lượng: <?php echo $product_qty; ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="customer-order-item-footer">
                                        <div class="customer-order-total-box">
                                            <span class="order-total-label">Tổng thanh toán:</span>
                                            <span class="order-payment-total"><?php echo format_price_vnd($order_total_value); ?></span>
                                        </div>
                                        <div class="order-actions">
                                            <a href="customer-order-detail.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-xs btn-order-primary"><i class="fa fa-eye"></i><span>Xem chi tiết</span></a>
                                            <?php if($shipping_status_code === 'Pending'): ?>
                                                <form action="customer-order-cancel.php" method="post" style="margin:0;" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?');">
                                                    <input type="hidden" name="order_id" value="<?php echo (int)$row['id']; ?>">
                                                    <button type="submit" class="btn btn-xs btn-order-danger-outline"><i class="fa fa-times-circle"></i><span>Hủy đơn</span></button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="customer-order-pagination">
                        <?php 
                            echo $pagination; 
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once('footer.php'); ?>