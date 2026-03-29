<?php require_once('header.php'); ?>

<?php
// Check if the customer is logged in or not
if(!isset($_SESSION['customer'])) {
    header('location: '.BASE_URL.'logout.php');
    exit;
} else {
    // If customer is logged in, but admin make him inactive, then force logout this user.
    $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id=? AND cust_status=?");
    $statement->execute(array($_SESSION['customer']['cust_id'],0));
    $total = $statement->rowCount();
    if($total) {
        header('location: '.BASE_URL.'logout.php');
        exit;
    }
}
?>

<style>
    .order-page-compact {
        padding-bottom: 0;
    }

    .order-page-compact .account-content-card,
    .order-page-compact .table-responsive {
        margin-bottom: 0;
    }

    .order-page-compact .pagination {
        margin: 10px 0 0;
    }
</style>

<div class="page order-page-compact">
    <div class="container">
        <div class="account-layout">
            <div class="account-sidebar-col">
                <?php require_once('customer-sidebar.php'); ?>
            </div>
            <div class="account-content-col">
                <div class="user-content account-content-card">
                    <h3>Đơn hàng của tôi</h3>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo '#' ?></th>
                                    <th>Sản phẩm</th>
                                    <th>Ngày thanh toán</th>
                                    <th>Mã giao dịch</th>
                                    <th>Số tiền đã thanh toán</th>
                                    <th>Trạng thái thanh toán</th>
                                    <th>Phương thức thanh toán</th>
                                    <th>Mã đơn thanh toán</th>
                                </tr>
                            </thead>
                            <tbody>


            <?php
            /* ===================== Pagination Code Starts ================== */
            $adjacents = 5;

            $statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE customer_email=? ORDER BY id DESC");
            $statement->execute(array($_SESSION['customer']['cust_email']));
            $total_pages = $statement->rowCount();

            $targetpage = BASE_URL.'customer-order.php';
            $limit = 10;
            $page = @$_GET['page'];
            if($page) 
                $start = ($page - 1) * $limit;
            else
                $start = 0;
            
            
            $statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE customer_email=? ORDER BY id DESC LIMIT $start, $limit");
            $statement->execute(array($_SESSION['customer']['cust_email']));
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
           
            
            if ($page == 0) $page = 1;
            $prev = $page - 1;
            $next = $page + 1;
            $lastpage = ceil($total_pages/$limit);
            $lpm1 = $lastpage - 1;   
            $pagination = "";
            if($lastpage > 1)
            {   
                $pagination .= "<div class=\"pagination\">";
                if ($page > 1) 
                    $pagination.= "<a href=\"$targetpage?page=$prev\">&#171; Trang trước</a>";
                else
                    $pagination.= "<span class=\"disabled\">&#171; Trang trước</span>";    
                if ($lastpage < 7 + ($adjacents * 2))
                {   
                    for ($counter = 1; $counter <= $lastpage; $counter++)
                    {
                        if ($counter == $page)
                            $pagination.= "<span class=\"current\">$counter</span>";
                        else
                            $pagination.= "<a href=\"$targetpage?page=$counter\">$counter</a>";                 
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
                                $pagination.= "<a href=\"$targetpage?page=$counter\">$counter</a>";                 
                        }
                        $pagination.= "...";
                        $pagination.= "<a href=\"$targetpage?page=$lpm1\">$lpm1</a>";
                        $pagination.= "<a href=\"$targetpage?page=$lastpage\">$lastpage</a>";       
                    }
                    elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
                    {
                        $pagination.= "<a href=\"$targetpage?page=1\">1</a>";
                        $pagination.= "<a href=\"$targetpage?page=2\">2</a>";
                        $pagination.= "...";
                        for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
                        {
                            if ($counter == $page)
                                $pagination.= "<span class=\"current\">$counter</span>";
                            else
                                $pagination.= "<a href=\"$targetpage?page=$counter\">$counter</a>";                 
                        }
                        $pagination.= "...";
                        $pagination.= "<a href=\"$targetpage?page=$lpm1\">$lpm1</a>";
                        $pagination.= "<a href=\"$targetpage?page=$lastpage\">$lastpage</a>";       
                    }
                    else
                    {
                        $pagination.= "<a href=\"$targetpage?page=1\">1</a>";
                        $pagination.= "<a href=\"$targetpage?page=2\">2</a>";
                        $pagination.= "...";
                        for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
                        {
                            if ($counter == $page)
                                $pagination.= "<span class=\"current\">$counter</span>";
                            else
                                $pagination.= "<a href=\"$targetpage?page=$counter\">$counter</a>";                 
                        }
                    }
                }
                if ($page < $counter - 1) 
                    $pagination.= "<a href=\"$targetpage?page=$next\">Trang sau &#187;</a>";
                else
                    $pagination.= "<span class=\"disabled\">Trang sau &#187;</span>";
                $pagination.= "</div>\n";       
            } 
            /* ===================== Pagination Code Ends ================== */
            ?>


                                <?php
                                $tip = $page*10-10;
                                foreach ($result as $row) {
                                    $tip++;
                                    ?>
                                    <tr>
                                        <td><?php echo $tip; ?></td>
                                        <td>
                                            <?php
                                            $statement1 = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id=?");
                                            $statement1->execute(array($row['payment_id']));
                                            $result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);
                                            foreach ($result1 as $row1) {
                                                echo 'Tên sản phẩm: '.$row1['product_name'];
                                                echo '<br>Kích thước: '.$row1['size'];
                                                echo '<br>Màu sắc: '.$row1['color'];
                                                echo '<br>Số lượng: '.$row1['quantity'];
                                                echo '<br>Đơn giá: '.format_price_vnd($row1['unit_price']);
                                                echo '<br><br>';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $row['payment_date']; ?></td>
                                        <td><?php echo $row['txnid']; ?></td>
                                        <td><?php echo format_price_vnd($row['paid_amount']); ?></td>
                                        <td><?php echo $row['payment_status']; ?></td>
                                        <td><?php echo $row['payment_method']; ?></td>
                                        <td><?php echo $row['payment_id']; ?></td>
                                    </tr>
                                    <?php
                                } 
                                ?>                               
                                
                            </tbody>
                        </table>
                        <div class="pagination" style="overflow: hidden;">
                        <?php 
                            echo $pagination; 
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Disable injected chat widget on this page because it creates extra blank area below footer.
$before_body = '';
?>

<?php require_once('footer.php'); ?>